<?php

require_once _PS_MODULE_DIR_ . 'qrsoldproducts/qrsoldproducts.php';
require_once _PS_MODULE_DIR_ . 'qrsoldproducts/classes/QspQrCode.php';
require_once _PS_MODULE_DIR_ . 'qrsoldproducts/classes/QspQrCodeService.php';
require_once _PS_MODULE_DIR_ . 'qrsoldproducts/vendor/autoload.php';

class AdminQrCodeManagerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'qsp_qr_codes';
        $this->className = 'QspQrCode';
        $this->identifier = 'id_qr_code';
        $this->lang = false;
        $this->module = Module::getInstanceByName('qrsoldproducts');

        parent::__construct();

        $this->fields_list = [
            'id_qr_code' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'code' => [ 'title' => 'Código QR' ],
            'validation_code' => [ 'title' => 'Código de validación' ],
            'status' => [
                'title' => 'Estado',
                'type' => 'select',
                'list' => [
                    ['id' => 'SIN_ASIGNAR', 'name' => 'SIN ASIGNAR'],
                    ['id' => 'SIN_ACTIVAR', 'name' => 'SIN ACTIVO'],
                    ['id' => 'ACTIVO', 'name' => 'ACTIVO'],
                ],
                'filter_key' => 'a!status',
                'order_key' => 'a!status',
            ],
            'date_created' => [
                'title' => 'Fecha creación',
                'type' => 'datetime',
            ],
            'date_assigned' => [
                'title' => 'Fecha asignación',
                'type' => 'datetime',
            ],
            'id_order' => [
                'title' => 'ID Pedido',
                'align' => 'center',
            ],
            'id_product' => [
                'title' => 'ID Producto',
                'align' => 'center',
            ],
        ];

        $this->bulk_actions = [
            'download_selected' => [
                'text' => $this->trans('Descargar seleccionados', [], 'Modules.Qrsoldproducts.Admin'),
                'confirm' => $this->trans('¿Estás seguro de que deseas descargar los QRs seleccionados?', [], 'Modules.Qrsoldproducts.Admin'),
            ],
            'export_excel' => [
                'text' => $this->trans('Exportar a Excel', [], 'Modules.Qrsoldproducts.Admin'),
                'confirm' => $this->trans('¿Exportar a Excel los QRs seleccionados?', [], 'Modules.Qrsoldproducts.Admin'),
            ],
        ];

        $this->addRowAction('download');
    }

    public function renderList()
    {
        // Botón para abrir el formulario de generación masiva
        $this->toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&token=' . $this->token . '&generate_bulk=1',
            'desc' => $this->trans('Generar QRs', [], 'Modules.Qrsoldproducts.Admin'),
            'icon' => 'process-icon-new',
        ];

        // Evita navegación al hacer clic en filas
        $this->list_no_link = true;

        return parent::renderList();
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        $this->_join .= '
            LEFT JOIN '._DB_PREFIX_.'order_detail od 
                ON a.id_order_detail = od.id_order_detail';

        $this->_select .= '
            a.date_assigned,
            od.id_order,
            od.product_id AS id_product';

        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
    }

    public function initContent()
    {
        if (Tools::getValue('generate_bulk')) {
            // Muestra la plantilla del formulario de generación
            $this->setTemplate('generate_bulk.tpl');
        } elseif (Tools::getValue('assign_order_form')) {
            // Formulario de asignación por pedido (si lo usas)
            $this->context->smarty->assign([
                'id_order' => (int)Tools::getValue('assign_order_form'),
                'token' => Tools::getAdminTokenLite('AdminQrCodeManager'),
                'back_link' => Context::getContext()->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => (int)Tools::getValue('assign_order_form'),
                    'vieworder' => 1
                ]),
            ]);
            $this->setTemplate('assign_form.tpl');
        } else {
            parent::initContent();
        }
    }

    public function postProcess()
    {
        $service = new QspQrCodeService();

        // --- Generación masiva de QRs ---
        if (Tools::isSubmit('submitGenerateQr')) {
            try {
                $count   = max(1, (int)Tools::getValue('bulk_qr_count', 10));
                $prefix  = Tools::getValue('qr_prefix', '');
                $size    = (int)Tools::getValue('qr_size', 250);
                $margin  = (int)Tools::getValue('qr_margin', 5);
                $version = (int)Tools::getValue('qr_version', 0); // 0 = dejar que la lib decida

                // Limpieza/saneamiento simple del prefijo (máx 3 chars, alfanumérico y guión)
                $prefix = Tools::substr(preg_replace('/[^A-Za-z0-9\-]/', '', $prefix), 0, 3);

                // Llamado flexible para soportar firmas de 3 o 5 parámetros:
                // 1) createQrs($count, $prefix, $size, $margin, $version)
                // 2) createQrs($count, $prefix, $size)
                $files = [];
                try {
                    // Intento con 5 parámetros
                    $files = $service->createQrs($count, $prefix, $size, $margin, $version);
                } catch (ArgumentCountError $e) {
                    // Fallback a firma antigua (3 parámetros)
                    $files = $service->createQrs($count, $prefix, $size);
                }

                if (Tools::getValue('download_zip') && is_array($files) && count($files) > 0) {
                    $service->downloadQrs(array_map(function ($filePath) {
                        return pathinfo($filePath, PATHINFO_FILENAME);
                    }, $files));
                }

                $this->confirmations[] = sprintf('%d %s', count($files), $this->trans('códigos QR generados correctamente.', [], 'Modules.Qrsoldproducts.Admin'));
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        // --- Asignar QRs a un pedido ---
        if (Tools::isSubmit('submit_assign_qrs')) {
            try {
                $order  = new Order((int)Tools::getValue('id_order'));
                $prefix = Tools::getValue('qr_prefix', '');
                $prefix = Tools::substr(preg_replace('/[^A-Za-z0-9\-]/', '', $prefix), 0, 3);

                $service->ensureQrsAssignedToOrder($order, $prefix);

                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => $order->id,
                    'vieworder' => 1,
                    'conf' => 4
                ]));
            } catch (PrestaShopException $e) {
                $this->errors[] = $this->trans('No se pudieron asignar los QRs: ', [], 'Modules.Qrsoldproducts.Admin') . $e->getMessage();
            }
        }

        // --- Descarga individual desde acción de fila ---
        if ($id = Tools::getValue('download_qr')) {
            try {
                $service->downloadQrsByIds([(int)$id]);
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        // --- Descarga masiva desde bulk action ---
        if (Tools::isSubmit('submitBulkdownload_selectedqsp_qr_codes')) {
            try {
                $ids = Tools::getValue('qsp_qr_codesBox', []);
                if (!empty($ids)) {
                    $service->downloadQrsByIds(array_map('intval', $ids));
                }
            } catch (PrestaShopException $e) {
                $this->errors[] = $this->trans('Error al descargar: ', [], 'Modules.Qrsoldproducts.Admin') . $e->getMessage();
            }
        }

        // --- Exportar a Excel desde bulk action ---
        if (Tools::isSubmit('submitBulkexport_excelqsp_qr_codes')) {
            try {
                $ids = Tools::getValue('qsp_qr_codesBox', []);
                if (empty($ids)) {
                    $this->errors[] = $this->trans('No seleccionaste ningún registro.', [], 'Modules.Qrsoldproducts.Admin');
                } else {
                    $this->exportQrsToExcel(array_map('intval', $ids));
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        // --- Descargar ZIP de QRs por pedido (si lo usas en otra vista) ---
        if ($orderId = Tools::getValue('download_order_qrs')) {
            try {
                $service->downloadQrsFromOrder(new Order((int)$orderId));
            } catch (PrestaShopException $e) {
                $this->errors[] = $this->trans('No se pudieron descargar los QRs del pedido: ', [], 'Modules.Qrsoldproducts.Admin') . $e->getMessage();
            }
        }
    }

    public function displayDownloadLink($token = null, $id, $name = null)
    {
        return '<a href="' . self::$currentIndex . '&token=' . $this->token . '&download_qr=' . (int)$id . '" class="btn btn-default">
                    <i class="icon-download"></i> ' . $this->trans('Descargar', [], 'Modules.Qrsoldproducts.Admin') . '
                </a>';
    }

    protected function exportQrsToExcel(array $ids)
    {
        if (empty($ids)) {
            throw new PrestaShopException('No hay IDs para exportar.');
        }

        // Datos básicos para Excel
        $in = implode(',', array_map('intval', $ids));
        $sql = 'SELECT id_qr_code, code, validation_code
                FROM '._DB_PREFIX_.'qsp_qr_codes
                WHERE id_qr_code IN ('.$in.')';
        $rows = Db::getInstance()->executeS($sql);

        if (!$rows) {
            throw new PrestaShopException('No se encontraron registros para exportar.');
        }

        // URL pública de los PNG generados por el módulo: /modules/qrsoldproducts/qrs/{code}.png
        $moduleBaseUri = $this->module->getPathUri(); // .../modules/qrsoldproducts/
        $qrBaseUri = rtrim(Context::getContext()->shop->getBaseURL(true), '/') . rtrim($moduleBaseUri, '/').'/qrs/';

        // Crear Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Código QR');
        $sheet->setCellValue('B1', 'Código de validación');
        $sheet->setCellValue('C1', 'URL del QR');

        // Filas
        $rowNum = 2;
        foreach ($rows as $r) {
            $code = (string)$r['code'];
            $validation = (string)$r['validation_code'];
            $url = $qrBaseUri . $code . '.png';

            $sheet->setCellValue('A'.$rowNum, $code);
            $sheet->setCellValue('B'.$rowNum, $validation);
            $sheet->setCellValue('C'.$rowNum, $url);
            $rowNum++;
        }

        // Auto-ajustar columnas
        foreach (['A','B','C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Enviar al navegador
        $filename = 'qrs_export_'.date('Ymd_His').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}