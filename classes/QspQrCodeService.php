<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;
use ZipArchive;

class QspQrCodeService
{
    private string $qrDir;
    private int $codeLen   = 8;
    private int $eccLevel  = EccLevel::L;  

    public function __construct()
    {
        $this->qrDir = _PS_MODULE_DIR_ . 'qrsoldproducts/qrs/';
        if (!is_dir($this->qrDir)) {
            mkdir($this->qrDir, 0755, true);
        }
    }

    public function createQrs(int $count, string $prefix = '', int $scalePxPerModule = 5): array
    {
        $created = [];
        $prefix  = strtoupper(trim($prefix));

        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateUniqueCode($prefix, $this->codeLen);

            $validation = strtoupper(Tools::passwdGen(12));

            $payload = $this->buildCompactPayload($code);

            $qr = new QspQrCode();
            $qr->code = $code;
            $qr->validation_code = $validation;
            $qr->status = 'SIN_ASIGNAR';
            $qr->date_created = date('Y-m-d H:i:s');
            $qr->add();

            $options   = $this->getQrOptions($this->eccLevel, $scalePxPerModule, 1);
            $imageData = (new QRCode($options))->render($payload);

            $filePath = $this->qrDir . $code . '.png';
            file_put_contents($filePath, $imageData);
            $created[] = $filePath;
        }

        return $created;
    }

    public function buildCompactPayload(string $code): string
    {
        $base = Context::getContext()->shop->getBaseURL(true);
        $base = preg_replace('#^https?://#i', '', $base);   
        $base = rtrim($base, '/');                       
        return $base . '/ei?code=' . $code;
    }

    private function generateUniqueCode(string $prefix, int $len): string
    {
        $db = Db::getInstance();
        $prefix = strtoupper($prefix);

        if (mb_strlen($prefix) >= $len) {
            $prefix = mb_substr($prefix, 0, $len);
        }

        $randomLen = $len - mb_strlen($prefix);

        for ($tries = 0; $tries < 20; $tries++) {
            $rand = $this->randomAlphaNum($randomLen);
            $code = $prefix . $rand;

            $exists = (int)$db->getValue('
                SELECT COUNT(*) FROM `'._DB_PREFIX_.'qsp_qr_codes` WHERE code = "'.pSQL($code).'"
            ');

            if ($exists === 0) {
                return $code;
            }
        }

        return $this->generateUniqueCode($prefix, $len + 1);
    }

    private function randomAlphaNum(int $n): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out = '';
        for ($i = 0; $i < $n; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $out;
    }

    private function getQrOptions(int $eccLevel, int $scalePxPerModule, int $quietzoneModules): QROptions
    {
        return new QROptions([
            'version'       => QRCode::VERSION_AUTO,  
            'eccLevel'      => $eccLevel,            
            'outputType'    => QRCode::OUTPUT_IMAGE_PNG,
            'scale'         => max(1, $scalePxPerModule),
            'imageBase64'   => false,
            'addQuietzone'  => true,
            'quietzoneSize' => max(1, $quietzoneModules),
        ]);
    }

    public function assignQrsToOrder(Order $order, array $qrCodes = [], array $filteredDetails = []): void
    {
        $orderDetails = $filteredDetails ?: $order->getOrderDetailList();
        $db = Db::getInstance();
        $qrIndex = 0;

        foreach ($orderDetails as $detail) {
            $qty = (int)$detail['product_quantity'];
            $idOrderDetail = (int)$detail['id_order_detail'];

            if (($qrIndex + $qty) > count($qrCodes)) {
                PrestaShopLogger::addLog("No hay suficientes QRs para el pedido #{$order->id}", 3);
                break;
            }

            for ($i = 0; $i < $qty; $i++) {
                $db->update('qsp_qr_codes', [
                    'status' => 'SIN_ACTIVAR',
                    'id_order_detail' => $idOrderDetail,
                    'date_assigned' => date('Y-m-d H:i:s')
                ], 'id_qr_code = ' . (int)$qrCodes[$qrIndex++]['id_qr_code']);
            }
        }
    }

    public function createAndAssingQrsToOrder(Order $order, int $totalQtyUnused, string $prefix = '', int $size = 250): void
    {
        $db = Db::getInstance();
        $orderDetails = $order->getOrderDetailList();

        // 1) Filtrar líneas del pedido que requieren QR
        $eligibleDetails = [];
        foreach ($orderDetails as $detail) {
            $idProduct = (int)$detail['product_id'];
            $hasQr = (bool)$db->getValue(
                'SELECT has_qr FROM `'._DB_PREFIX_.'qsp_product_qr_config` WHERE id_product='.(int)$idProduct
            );
            if ($hasQr) {
                $eligibleDetails[] = $detail;
            }
        }

        // 2) Cantidad total requerida
        $totalQty = array_reduce($eligibleDetails, fn($sum, $d) => $sum + (int)$d['product_quantity'], 0);

        if ($totalQty === 0) {
            PrestaShopLogger::addLog("Pedido #{$order->id} sin productos con QR configurado. No se asignan códigos.", 1);
            return;
        }

        // 3) Verificar cuántos QRs SIN_ASIGNAR hay disponibles
        $availableCount = (int)$db->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'qsp_qr_codes`
            WHERE status="SIN_ASIGNAR"
        ');

        // 4) Si faltan, crear solo los necesarios
        if ($availableCount < $totalQty) {
            $toCreate = $totalQty - $availableCount;
            PrestaShopLogger::addLog("Faltan {$toCreate} QRs para el pedido #{$order->id}. Creando los faltantes…", 1);
            $this->createQrs($toCreate, $prefix, $size);
        }

        // 5) Tomar exactamente los requeridos y asignar
        $qrCodes = $db->executeS('
            SELECT id_qr_code 
            FROM `'._DB_PREFIX_.'qsp_qr_codes`
            WHERE status="SIN_ASIGNAR"
            ORDER BY date_created ASC
            LIMIT '.(int)$totalQty
        );

        if (!$qrCodes || (int)count($qrCodes) < $totalQty) {
            // Defensa adicional: si por alguna razón aún no hay suficientes
            throw new PrestaShopException('No hay suficientes QRs disponibles para asignar al pedido.');
        }

        $this->assignQrsToOrder($order, $qrCodes, $eligibleDetails);
    }

    /**
     * Asignación manual de QRs seleccionados a un pedido.
     */
    public function assignManualQrsToOrder(Order $order, array $selectedQrIds): void
    {
        $db = Db::getInstance();

        // --- 1. obtener QRs actualmente asignados al pedido ---
        $currentQrs = $db->executeS('
            SELECT id_qr_code, status
            FROM '._DB_PREFIX_.'qsp_qr_codes q
            INNER JOIN '._DB_PREFIX_.'order_detail od
            ON q.id_order_detail = od.id_order_detail
            WHERE od.id_order = '.(int)$order->id
        );

        $currentIds = array_map('intval', array_column($currentQrs, 'id_qr_code'));
        $selectedQrIds = array_map('intval', $selectedQrIds);

        // --- 2. QRs a desasignar (estaban pero ya no están seleccionados) ---
        $toUnassign = array_diff($currentIds, $selectedQrIds);

        foreach ($toUnassign as $idQr) {
            $db->update(
                'qsp_qr_codes',
                [
                    'status'          => 'SIN_ASIGNAR',
                    'id_order_detail' => null,
                    'date_assigned'   => null,
                ],
                'id_qr_code = '.(int)$idQr
            );
        }

        // --- 3. QRs seleccionados: asignar o validar ---
        foreach ($selectedQrIds as $idQr) {
            $qr = $db->getRow('
                SELECT id_qr_code, status, id_order_detail
                FROM '._DB_PREFIX_.'qsp_qr_codes
                WHERE id_qr_code = '.(int)$idQr
            );

            if (!$qr) {
                throw new PrestaShopException('QR inválido seleccionado.');
            }

            // nunca permitir activos
            if ($qr['status'] === 'ACTIVO') {
                throw new PrestaShopException('No se pueden reasignar QRs activos.');
            }

            if ($qr['status'] === 'SIN_ASIGNAR') {
                // buscar un order_detail del pedido
                $orderDetailId = null;
                $orderDetailIds = $db->executeS('
                    SELECT od.id_order_detail
                    FROM '._DB_PREFIX_.'order_detail od
                    WHERE od.id_order = '.(int)$order->id.'
                    ORDER BY od.id_order_detail ASC
                ');
                if ($orderDetailIds && count($orderDetailIds) > 0) {
                    $orderDetailId = (int)$orderDetailIds[0]['id_order_detail'];
                }

                if (!$orderDetailId) {
                    throw new PrestaShopException('No se encontró detalle de pedido para asignar el QR.');
                }

                $db->update(
                    'qsp_qr_codes',
                    [
                        'status'          => 'SIN_ACTIVAR',
                        'id_order_detail' => (int)$orderDetailId,
                        'date_assigned'   => date('Y-m-d H:i:s'),
                    ],
                    'id_qr_code = '.(int)$idQr
                );

            } elseif ($qr['status'] === 'SIN_ACTIVAR') {
                // validar que ya pertenezca al mismo pedido
                $alreadyAssigned = $db->getValue('
                    SELECT COUNT(*)
                    FROM '._DB_PREFIX_.'order_detail od
                    WHERE od.id_order = '.(int)$order->id.'
                    AND od.id_order_detail = '.(int)$qr['id_order_detail']
                );

                if (!$alreadyAssigned) {
                    throw new PrestaShopException('El QR SIN_ACTIVAR no pertenece a este pedido.');
                }
                // si está bien, no se toca (se mantiene)
            }
        }
    }

    public function ensureQrsAssignedToOrder(Order $order, string $prefix = '', int $size = 250): void
    {
        if (!$this->orderHasProductsWithQr($order)) {
            PrestaShopLogger::addLog("Pedido #{$order->id} no tiene productos configurados con QR. Se omite asignación.", 1);
            return;
        }

        if (!$this->orderHasAssignedQrs($order)) {
            $this->createAndAssingQrsToOrder($order, 0, $prefix, $size);
            PrestaShopLogger::addLog("QRs asignados (y creados solo si faltaban) para el pedido #{$order->id}", 1);
        }
    }

    public function downloadQrs(array $codes): void
    {
        $files = [];
        foreach ($codes as $code) {
            $filePath = $this->qrDir . $code . '.png';
            if (file_exists($filePath)) {
                $files[] = $filePath;
            }
        }

        if (count($files) === 1) {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . basename($files[0]) . '"');
            header('Content-Length: ' . filesize($files[0]));
            readfile($files[0]);
            exit;
        } elseif (count($files) > 1) {
            $zipName = $this->qrDir . 'qr_lote_' . time() . '.zip';
            $zip     = new ZipArchive();

            if ($zip->open($zipName, ZipArchive::CREATE) === true) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
                header('Content-Length: ' . filesize($zipName));
                readfile($zipName);
                @unlink($zipName);
                exit;
            } else {
                throw new PrestaShopException('No se pudo crear el ZIP (ext-zip instalada?).');
            }
        } else {
            throw new PrestaShopException('No hay archivos para descargar.');
        }
    }

    public function downloadQrsFromOrder(Order $order): void
    {
        $codes = Db::getInstance()->executeS('
            SELECT code FROM `'._DB_PREFIX_.'qsp_qr_codes`
            WHERE id_order_detail IN (
                SELECT id_order_detail FROM `'._DB_PREFIX_.'order_detail`
                WHERE id_order = ' . (int)$order->id . '
            )
        ');
        if ($codes && count($codes)) {
            $this->downloadQrs(array_column($codes, 'code'));
        } else {
            throw new PrestaShopException('No se encontraron QRs asignados a esta orden.');
        }
    }

    public function downloadQrsByIds(array $ids): void
    {
        $codes = [];
        foreach ($ids as $id) {
            $qr = new QspQrCode((int)$id);
            if (Validate::isLoadedObject($qr)) {
                $codes[] = $qr->code;
            }
        }
        if (!empty($codes)) {
            $this->downloadQrs($codes);
        } else {
            throw new PrestaShopException('No se encontraron códigos QR válidos para descargar.');
        }
    }

    public function orderHasAssignedQrs(Order $order): bool
    {
        $result = Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'qsp_qr_codes`
            WHERE id_order_detail IN (
                SELECT id_order_detail FROM `' . _DB_PREFIX_ . 'order_detail`
                WHERE id_order = ' . (int)$order->id . '
            ) AND status IN ("SIN_ACTIVAR", "ACTIVO")
        ');
        return (int)$result > 0;
    }

    public function orderHasProductsWithQr(Order $order): bool
    {
        $db = Db::getInstance();
        foreach ($order->getOrderDetailList() as $detail) {
            $idProduct = (int)$detail['product_id'];
            $hasQr = (bool)$db->getValue(
                'SELECT has_qr FROM `'._DB_PREFIX_.'qsp_product_qr_config` WHERE id_product = '.(int)$idProduct
            );
            if ($hasQr) {
                return true;
            }
        }
        return false;
    }
}