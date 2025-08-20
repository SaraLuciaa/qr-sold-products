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
            'type' => 'int',
            'filter_key' => 'a!id_qr_code',
            'order_key'  => 'a!id_qr_code',
        ],
        'code' => [
            'title' => 'Código QR',
            'type' => 'text',
            'filter_key' => 'a!code',
            'order_key'  => 'a!code',
        ],
        'validation_code' => [
            'title' => 'Código de validación',
            'type' => 'text',
            'filter_key' => 'a!validation_code',
            'order_key'  => 'a!validation_code',
        ],
        'status' => [
            'title'      => 'Estado',
            'type'       => 'select',
            'list'       => [
                'SIN_ASIGNAR' => 'SIN ASIGNAR',
                'SIN_ACTIVAR' => 'SIN ACTIVAR',
                'ACTIVO'      => 'ACTIVO',
            ],
            'filter_key' => 'a!status',
            'order_key'  => 'a!status',
            'callback'         => 'renderStatus',
            'callback_object'  => $this,
        ],
        'date_created' => [
            'title'      => 'Fecha creación',
            'type'       => 'datetime',
            'filter_key' => 'a!date_created',
            'order_key'  => 'a!date_created',
        ],
        'date_assigned' => [
            'title'      => 'Fecha asignación',
            'type'       => 'datetime',
            'filter_key' => 'a!date_assigned',
            'order_key'  => 'a!date_assigned',
        ],
        'id_order' => [
            'title'      => 'ID Pedido',
            'align'      => 'center',
            'type'       => 'int',
            'filter_key' => 'od!id_order',
            'order_key'  => 'od!id_order',
        ],
        'id_product' => [
            'title'      => 'ID Producto',
            'align'      => 'center',
            'type'       => 'int',
            'filter_key' => 'od!product_id',   
            'order_key'  => 'od!product_id',
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

    public function renderStatus($value, $row)
    {
        static $labels = [
            'SIN_ASIGNAR' => 'SIN ASIGNAR',
            'SIN_ACTIVAR' => 'SIN ACTIVAR',
            'ACTIVO'      => 'ACTIVO',
        ];
        return isset($labels[$value]) ? $labels[$value] : $value;
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

    public function initContent()
    {
        if (Tools::getValue('generate_bulk')) {
            // Muestra la plantilla del formulario de generación
            $this->setTemplate('generate_bulk.tpl');
        } elseif (Tools::getValue('assign_order_form')) {
            $idOrder = (int)Tools::getValue('assign_order_form');
            $search  = trim((string)Tools::getValue('q', ''));
            $limit   = (int)Tools::getValue('limit', 500);

            $totalRequired = $this->getOrderTotalRequiredWithQr($idOrder);
            $availableQrs  = $this->getAvailableQrs($search, $limit);

            $this->context->smarty->assign([
                'id_order'        => $idOrder,
                'token'           => Tools::getAdminTokenLite('AdminQrCodeManager'),
                'back_link'       => Context::getContext()->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => $idOrder,
                    'vieworder' => 1
                ]),
                'q'               => $search,
                'limit'           => $limit,
                'total_required'  => $totalRequired,
                'available_qrs'   => $availableQrs,
            ]);

            $this->setTemplate('assign_form.tpl');
        } else {
            parent::initContent();
        }
    }

    public function postProcess()
    {
        parent::postProcess();

        $service = new QspQrCodeService();

        // --- Generación masiva de QRs ---
        if (Tools::isSubmit('submitGenerateQr')) {
            try {
                $count  = max(1, (int)Tools::getValue('bulk_qr_count', 10));
                $prefix = Tools::getValue('qr_prefix', '');

                // Sanea prefijo: solo A-Z, 0-9 y '-', máx 3 chars
                $prefix = Tools::substr(preg_replace('/[^A-Za-z0-9\-]/', '', $prefix), 0, 3);

                $files = [];
                try {
                    // Preferido: firma (count, prefix)
                    $files = $service->createQrs($count, $prefix);
                } catch (ArgumentCountError $e) {
                    // Compatibilidad: si tu servicio aún requiere tamaño, usa 250 por defecto
                    $files = $service->createQrs($count, $prefix, 250);
                }

                $this->confirmations[] = sprintf(
                    '%d %s',
                    is_array($files) ? count($files) : (int)$count,
                    $this->trans('códigos QR generados correctamente.', [], 'Modules.Qrsoldproducts.Admin')
                );
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

        if (Tools::isSubmit('submit_manual_assign_qrs')) {
            try {
                $order = new Order((int)Tools::getValue('id_order'));
                if (!Validate::isLoadedObject($order)) {
                    throw new PrestaShopException('Pedido inválido.');
                }

                $selected = Tools::getValue('selected_qrs', []); // checkboxes del tpl
                if (!is_array($selected)) { $selected = []; }

                $service = new QspQrCodeService();
                $service->assignManualQrsToOrder($order, array_map('intval', $selected));

                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => $order->id,
                    'vieworder' => 1,
                    'conf' => 4
                ]));
            } catch (Exception $e) {
                $this->errors[] = 'Error al asignar manualmente: '.$e->getMessage();
            }
        }

        // --- Asignación automática disparada desde el hook del pedido ---
        if ($orderId = (int)Tools::getValue('assign_auto_qrs')) {
            try {
                $order = new Order($orderId);
                if (!Validate::isLoadedObject($order)) {
                    throw new PrestaShopException('Pedido inválido.');
                }
                $service = new QspQrCodeService();
                // Usa la lógica existente (creará si hace falta y asigna)
                $service->ensureQrsAssignedToOrder($order);

                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => $order->id,
                    'vieworder' => 1,
                    'conf' => 4
                ]));
            } catch (Exception $e) {
                $this->errors[] = 'No se pudo asignar automáticamente: '.$e->getMessage();
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

        // --- Exportar a Excel QRs de un pedido completo ---
        if ($orderId = (int)Tools::getValue('export_order_excel')) {
            try {
                $ids = $this->getQrIdsByOrder($orderId);
                if (empty($ids)) {
                    throw new PrestaShopException('Este pedido no tiene QRs asignados.');
                }
                $this->exportQrsToExcel($ids); // ya hace el stream + exit
            } catch (Exception $e) {
                $this->errors[] = 'No se pudo exportar el Excel: '.$e->getMessage();
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

    private function getOrderTotalRequiredWithQr(int $idOrder): int
    {
        $db = Db::getInstance();
        $details = Db::getInstance()->executeS('
            SELECT od.id_order_detail, od.product_id, od.product_quantity
            FROM `'._DB_PREFIX_.'order_detail` od
            WHERE od.id_order='.(int)$idOrder
        );

        if (!$details) { return 0; }

        $total = 0;
        foreach ($details as $d) {
            $hasQr = (bool)$db->getValue(
                'SELECT has_qr FROM `'._DB_PREFIX_.'qsp_product_qr_config` WHERE id_product='.(int)$d['product_id']
            );
            if ($hasQr) {
                $total += (int)$d['product_quantity'];
            }
        }
        return $total;
    }

    private function getAvailableQrs(string $q = '', int $limit = 500): array
    {
        $limit = max(1, min(2000, $limit));
        $sql = 'SELECT id_qr_code, code, validation_code, date_created
                FROM `'._DB_PREFIX_.'qsp_qr_codes`
                WHERE status="SIN_ASIGNAR"';

        if ($q !== '') {
            $qEsc = pSQL($q);
            $sql .= ' AND (code LIKE "%'.$qEsc.'%" OR validation_code LIKE "%'.$qEsc.'%")';
        }

        $sql .= ' ORDER BY date_created DESC LIMIT '.$limit;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    private function getQrIdsByOrder(int $idOrder): array
    {
        $rows = Db::getInstance()->executeS('
            SELECT q.id_qr_code
            FROM `'._DB_PREFIX_.'qsp_qr_codes` q
            INNER JOIN `'._DB_PREFIX_.'order_detail` od
                ON od.id_order_detail = q.id_order_detail
            WHERE od.id_order = '.(int)$idOrder.'
            AND q.status IN ("SIN_ACTIVAR","ACTIVO")
        ');
        return $rows ? array_map('intval', array_column($rows, 'id_qr_code')) : [];
    }
}