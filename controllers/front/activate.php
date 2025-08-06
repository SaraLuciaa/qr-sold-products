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

            // Cargar datos adicionales con información de países
            $petInfo['contacts'] = Db::getInstance()->executeS("
                SELECT c.*, co.call_prefix, co_lang.name as country_name
                FROM " . _DB_PREFIX_ . "qsp_customer_contacts c
                LEFT JOIN " . _DB_PREFIX_ . "country co ON c.contact_country_id = co.id_country
                LEFT JOIN " . _DB_PREFIX_ . "country_lang co_lang ON co.id_country = co_lang.id_country 
                    AND co_lang.id_lang = " . (int)$this->context->language->id . "
                WHERE c.id_customer_code = $id
                ORDER BY c.contact_index
            ");
            
            // Cargar información de países para los teléfonos del usuario principal
            $countryInfo = Db::getInstance()->executeS("
                SELECT 
                    mobile_co.call_prefix as mobile_prefix,
                    mobile_lang.name as mobile_country_name,
                    home_co.call_prefix as home_prefix,
                    home_lang.name as home_country_name,
                    work_co.call_prefix as work_prefix,
                    work_lang.name as work_country_name
                FROM " . _DB_PREFIX_ . "qsp_customer_codes cc
                LEFT JOIN " . _DB_PREFIX_ . "country mobile_co ON cc.user_mobile_country_id = mobile_co.id_country
                LEFT JOIN " . _DB_PREFIX_ . "country_lang mobile_lang ON mobile_co.id_country = mobile_lang.id_country 
                    AND mobile_lang.id_lang = " . (int)$this->context->language->id . "
                LEFT JOIN " . _DB_PREFIX_ . "country home_co ON cc.user_home_country_id = home_co.id_country
                LEFT JOIN " . _DB_PREFIX_ . "country_lang home_lang ON home_co.id_country = home_lang.id_country 
                    AND home_lang.id_lang = " . (int)$this->context->language->id . "
                LEFT JOIN " . _DB_PREFIX_ . "country work_co ON cc.user_work_country_id = work_co.id_country
                LEFT JOIN " . _DB_PREFIX_ . "country_lang work_lang ON work_co.id_country = work_lang.id_country 
                    AND work_lang.id_lang = " . (int)$this->context->language->id . "
                WHERE cc.id_customer_code = $id
                LIMIT 1
            ");
            
            if ($countryInfo && count($countryInfo) > 0) {
                $petInfo = array_merge($petInfo, $countryInfo[0]);
            }

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