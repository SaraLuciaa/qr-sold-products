<?php

class QrsoldproductsActivateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $code = Tools::getValue('code');
        $ownView = (int)Tools::getValue('own') === 1;
        $petInfo = null;
        $errorMessage = '';

        try {
            if (!$code) {
                throw new Exception('No se recibió ningún código QR.');
            }

            // Validar existencia de tablas críticas
            $tables = [
                'qsp_qr_codes',
                'qsp_customer_codes',
                'qsp_customer_contacts',
                'qsp_customer_covid_vaccine',
                'qsp_customer_conditions',
                'qsp_customer_allergies',
                'qsp_customer_medications'
            ];

            foreach ($tables as $table) {
                $fullTable = _DB_PREFIX_ . $table;
                $exists = Db::getInstance()->executeS("SHOW TABLES LIKE '" . pSQL($fullTable) . "'");
                if (empty($exists)) {
                    throw new Exception("Falta la tabla: " . $fullTable);
                }
            }

            // Buscar QR activo
            $query = '
                SELECT qc.*, q.code, q.validation_code
                FROM ' . _DB_PREFIX_ . 'qsp_customer_codes qc
                INNER JOIN ' . _DB_PREFIX_ . 'qsp_qr_codes q ON qc.id_qr_code = q.id_qr_code
                WHERE q.code = "' . pSQL($code) . '" AND q.status = "ACTIVO"
            ';

            $petInfo = Db::getInstance()->getRow($query);

            if (!$petInfo) {
                throw new Exception('QR no válido o inactivo.');
            }

            $id = (int)$petInfo['id_customer_code'];

            // Cargar datos adicionales
            $petInfo['contacts'] = Db::getInstance()->executeS("
                SELECT * FROM " . _DB_PREFIX_ . "qsp_customer_contacts
                WHERE id_customer_code = $id
                ORDER BY contact_index
            ");

            $petInfo['covid'] = Db::getInstance()->getRow("
                SELECT * FROM " . _DB_PREFIX_ . "qsp_customer_covid_vaccine
                WHERE id_customer_code = $id
            ");

            $petInfo['conditions'] = Db::getInstance()->executeS("
                SELECT * FROM " . _DB_PREFIX_ . "qsp_customer_conditions
                WHERE id_customer_code = $id
            ");

            $petInfo['allergies'] = Db::getInstance()->executeS("
                SELECT * FROM " . _DB_PREFIX_ . "qsp_customer_allergies
                WHERE id_customer_code = $id
            ");

            $petInfo['medications'] = Db::getInstance()->executeS("
                SELECT * FROM " . _DB_PREFIX_ . "qsp_customer_medications
                WHERE id_customer_code = $id
            ");

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ERROR ACTIVATE: " . $errorMessage . "\n", FILE_APPEND);
        }

        $this->context->smarty->assign([
            'pet' => $petInfo,
            'code' => $code,
            'own' => $ownView,
            'edit_link' => $petInfo
                ? $this->context->link->getModuleLink('qrsoldproducts', 'addqr', ['edit_id' => $petInfo['id_customer_code']])
                : '',
            'error_message' => $errorMessage
        ]);

        $this->setTemplate('module:qrsoldproducts/views/templates/front/activate.tpl');
    }
}