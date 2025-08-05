<?php

class QrsoldproductsActivateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        try {
            $code = Tools::getValue('code');
            $ownView = Tools::getValue('own') == 1;
            $petInfo = null;

            // Log the incoming request
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ACTIVATE REQUEST - Code: " . $code . "\n", FILE_APPEND);

            // Check if database tables exist
            $tables = [
                _DB_PREFIX_ . 'qsp_qr_codes',
                _DB_PREFIX_ . 'qsp_customer_codes',
                _DB_PREFIX_ . 'qsp_customer_contacts',
                _DB_PREFIX_ . 'qsp_customer_covid_vaccine',
                _DB_PREFIX_ . 'qsp_customer_conditions',
                _DB_PREFIX_ . 'qsp_customer_allergies',
                _DB_PREFIX_ . 'qsp_customer_medications'
            ];

            $missingTables = [];
            foreach ($tables as $table) {
                $exists = Db::getInstance()->executeS("SHOW TABLES LIKE '$table'");
                if (empty($exists)) {
                    $missingTables[] = $table;
                }
            }

            if (!empty($missingTables)) {
                file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "MISSING TABLES: " . implode(', ', $missingTables) . "\n", FILE_APPEND);
                throw new Exception("Database tables missing: " . implode(', ', $missingTables));
            }

            if ($code) {
                $query = '
                    SELECT qc.*, q.code, q.validation_code
                    FROM '._DB_PREFIX_.'qsp_customer_codes qc
                    JOIN '._DB_PREFIX_.'qsp_qr_codes q ON qc.id_qr_code = q.id_qr_code
                    WHERE q.code = "'.pSQL($code).'" AND q.status = "ACTIVO"
                ';
                
                // Log the query
                file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "QUERY: " . $query . "\n", FILE_APPEND);
                
                $petInfo = Db::getInstance()->getRow($query);
                
                // Log the result
                file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "RESULT: " . print_r($petInfo, true) . "\n", FILE_APPEND);
                
                if ($petInfo) {
                    // Obtener contactos
                    $contactsQuery = '
                        SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_contacts
                        WHERE id_customer_code = ' . (int)$petInfo['id_customer_code'] . '
                        ORDER BY contact_index
                    ';
                    $petInfo['contacts'] = Db::getInstance()->executeS($contactsQuery);
                    
                    // Obtener información de COVID
                    $covidQuery = '
                        SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_covid_vaccine
                        WHERE id_customer_code = ' . (int)$petInfo['id_customer_code']
                    ';
                    $petInfo['covid'] = Db::getInstance()->getRow($covidQuery);
                    
                    // Obtener condiciones médicas
                    $conditionsQuery = '
                        SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_conditions
                        WHERE id_customer_code = ' . (int)$petInfo['id_customer_code']
                    ';
                    $petInfo['conditions'] = Db::getInstance()->executeS($conditionsQuery);
                    
                    // Obtener alergias
                    $allergiesQuery = '
                        SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_allergies
                        WHERE id_customer_code = ' . (int)$petInfo['id_customer_code']
                    ';
                    $petInfo['allergies'] = Db::getInstance()->executeS($allergiesQuery);
                    
                    // Obtener medicamentos
                    $medicationsQuery = '
                        SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_medications
                        WHERE id_customer_code = ' . (int)$petInfo['id_customer_code']
                    ';
                    $petInfo['medications'] = Db::getInstance()->executeS($medicationsQuery);
                }
            }

            $this->context->smarty->assign([
                'pet' => $petInfo,
                'code' => $code,
                'own' => $ownView,
                'edit_link' => $petInfo
                    ? $this->context->link->getModuleLink('qrsoldproducts', 'addqr', ['edit_id' => $petInfo['id_customer_code']])
                    : '',
            ]);

            $this->setTemplate('module:qrsoldproducts/views/templates/front/activate.tpl');
            
        } catch (Exception $e) {
            // Log the error
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Display a user-friendly error
            $this->context->smarty->assign([
                'error_message' => 'Ha ocurrido un error al procesar la solicitud. Por favor, inténtelo de nuevo más tarde.',
                'pet' => null,
                'code' => $code ?? '',
                'own' => false,
                'edit_link' => '',
            ]);
            
            $this->setTemplate('module:qrsoldproducts/views/templates/front/activate.tpl');
        }
    }
}

