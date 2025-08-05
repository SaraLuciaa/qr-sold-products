<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QspQrCodeService
{
    private string $qrDir;

    public function __construct()
    {
        $this->qrDir = _PS_MODULE_DIR_ . 'qrsoldproducts/qrs/';
        if (!is_dir($this->qrDir)) {
            mkdir($this->qrDir, 0755, true);
        }
    }

    public function createQrs(int $count, string $prefix = '', int $size = 250): array
    {
        $created = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper($prefix . Tools::passwdGen(10 - strlen($prefix)));
            $validation = strtoupper(Tools::passwdGen(12));
            $url = Context::getContext()->shop->getBaseURL(true) . 'ei?code=' . $code;

            $qr = new QspQrCode();
            $qr->code = $code;
            $qr->validation_code = $validation;
            $qr->status = 'SIN_ASIGNAR';
            $qr->date_created = date('Y-m-d H:i:s');
            $qr->add();

            $options = $this->getQrOptions(QRCode::VERSION_AUTO, $size, 5);
            $imageData = (new QRCode($options))->render($url);

            $filePath = $this->qrDir . $code . '.png';
            file_put_contents($filePath, $imageData);
            $created[] = $filePath;
        }

        return $created;
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

        $eligibleDetails = [];

        foreach ($orderDetails as $detail) {
            $idProduct = (int)$detail['product_id'];
            $hasQr = (bool) $db->getValue('SELECT has_qr FROM `'._DB_PREFIX_.'qsp_product_qr_config` WHERE id_product = '.(int)$idProduct);
            if ($hasQr) {
                $eligibleDetails[] = $detail;
            }
        }

        $totalQty = array_reduce($eligibleDetails, fn($sum, $d) => $sum + (int)$d['product_quantity'], 0);

        if ($totalQty === 0) {
            PrestaShopLogger::addLog("Pedido #{$order->id} sin productos con QR configurado. No se asignan códigos.", 1);
            return;
        }

        $this->createQrs($totalQty, $prefix, $size);

        $qrCodes = $db->executeS('
            SELECT id_qr_code FROM ' . _DB_PREFIX_ . 'qsp_qr_codes
            WHERE status = "SIN_ASIGNAR"
            ORDER BY date_created DESC
            LIMIT ' . (int)$totalQty
        );

        $this->assignQrsToOrder($order, $qrCodes, $eligibleDetails);
    }

    public function ensureQrsAssignedToOrder(Order $order, string $prefix = '', int $size = 250): void
    {
        if (!$this->orderHasProductsWithQr($order)) {
            PrestaShopLogger::addLog("Pedido #{$order->id} no tiene productos configurados con QR. Se omite asignación.", 1);
            return;
        }

        if (!$this->orderHasAssignedQrs($order)) {
            $this->createAndAssingQrsToOrder($order, 0, $prefix, $size);
            PrestaShopLogger::addLog("QRs creados y asignados automáticamente para el pedido #{$order->id}", 1);
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
            $zip = new ZipArchive();

            if ($zip->open($zipName, ZipArchive::CREATE) === true) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
                header('Content-Length: ' . filesize($zipName));
                readfile($zipName);
                unlink($zipName);
                exit;
            }
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
            $hasQr = (bool) $db->getValue('SELECT has_qr FROM `'._DB_PREFIX_.'qsp_product_qr_config` WHERE id_product = '.(int)$idProduct);
            if ($hasQr) {
                return true;
            }
        }
        return false;
    }

    private function getQrOptions($version, $size, $margin): QROptions
    {
        return new QROptions([
            'version' => $version,
            'eccLevel' => QRCode::ECC_H,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => (int)($size / 25),
            'quietzoneSize' => $margin,   
            'imageBase64' => false,
        ]);
    }
}