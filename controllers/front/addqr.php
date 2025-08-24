<?php

require_once _PS_MODULE_DIR_ . 'qrsoldproducts/classes/QspCustomerCode.php';

class QrsoldproductsAddqrModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    public function initContent()
    {
        parent::initContent();

        $this->loadCountries();
        $this->loadFormData();
        $this->assignSmartyVariables();
        $this->setTemplate('module:qrsoldproducts/views/templates/front/addqr.tpl');
    }


    public function postProcess()
    {
        error_log("DEBUG: postProcess ejecutado - submit_add_qr: " . (Tools::isSubmit('submit_add_qr') ? 'SÍ' : 'NO'));
        
        if (Tools::isSubmit('submit_add_qr')) {
            error_log("DEBUG: Formulario enviado");
            if ($this->validateForm()) {
                error_log("DEBUG: Validación exitosa");
                if ($this->isEditMode()) {
                    $this->updateCustomerData();
                } else {
                    $this->insertCustomerData();
                }
            } else {
                error_log("DEBUG: Validación falló");
            }
        }
    }

    private function loadCountries()
    {
        $id_lang = (int)$this->context->language->id;
        $countries = Db::getInstance()->executeS('
            SELECT c.id_country, cl.name, c.call_prefix
            FROM ' . _DB_PREFIX_ . 'country c
            INNER JOIN ' . _DB_PREFIX_ . 'country_lang cl ON cl.id_country = c.id_country
            WHERE cl.id_lang = ' . $id_lang . '
            ORDER BY cl.name ASC
        ');
        $this->context->smarty->assign('countries', $countries);

        // Estados: solo si hay país seleccionado
        $selected_country_id = Tools::getValue('user_country_id');
        $states = [];
        if ($selected_country_id) {
            $states = Db::getInstance()->executeS('
                SELECT s.id_state, s.name
                FROM ' . _DB_PREFIX_ . 'state s
                WHERE s.id_country = ' . (int)$selected_country_id . ' AND s.active = 1
                ORDER BY s.name ASC
            ');
        }
        $this->context->smarty->assign('states', $states);
    }

    private function handleImageUpload($id_customer_code)
    {
        if (!isset($_FILES['user_image']) || $_FILES['user_image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $upload_dir = _PS_MODULE_DIR_ . 'qrsoldproducts/views/img/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $id_customer_code . '.' . $ext;
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['user_image']['tmp_name'], $destination)) {
            return $filename;
        }

        return null;
    }


    private function loadFormData()
    {
        $editMode = false;
        $qrData = [];
        $customerId = (int)$this->context->customer->id;

        if (Tools::getIsset('edit_id')) {
            $editId = (int)Tools::getValue('edit_id');
            $editMode = true;

            $qrData = Db::getInstance()->getRow('
                SELECT cc.*, qr.status, qr.validation_code
                FROM ' . _DB_PREFIX_ . 'qsp_customer_codes cc
                INNER JOIN ' . _DB_PREFIX_ . 'qsp_qr_codes qr ON cc.id_qr_code = qr.id_qr_code
                WHERE cc.id_customer_code = ' . $editId . '
                AND cc.id_customer = ' . $customerId
            );

            if (!$qrData || $qrData['status'] !== 'ACTIVO') {
                Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
            }

            $qrData['contacts'] = Db::getInstance()->executeS('
                SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_contacts
                WHERE id_customer_code = ' . $editId . '
                ORDER BY contact_index
            ');

            $qrData['covid'] = Db::getInstance()->getRow('
                SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_covid_vaccine
                WHERE id_customer_code = ' . $editId
            );

            $qrData['conditions'] = Db::getInstance()->executeS('
                SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_conditions
                WHERE id_customer_code = ' . $editId
            );

            $qrData['allergies'] = Db::getInstance()->executeS('
                SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_allergies
                WHERE id_customer_code = ' . $editId
            );

            $qrData['medications'] = Db::getInstance()->executeS('
                SELECT * FROM ' . _DB_PREFIX_ . 'qsp_customer_medications
                WHERE id_customer_code = ' . $editId
            );
        }

        $this->context->smarty->assign([
            'edit_mode' => $editMode,
            'qr_data' => $qrData,
        ]);
    }

    private function assignSmartyVariables()
    {
        $this->context->smarty->assign([
            'error' => '',
            'success' => '',
            'customer' => $this->context->customer,
        ]);
    }

    private function validateForm()
    {
        $requiredFields = [
            'user_name', 'user_type_dni', 'user_dni',
            'user_mobile_country_id', 'user_mobile_number',
        ];

        if (!$this->isEditMode()) {
            $requiredFields[] = 'validation_code';
        }

        foreach ($requiredFields as $field) {
            if (trim((string)Tools::getValue($field)) === '') {
                $this->context->smarty->assign('error', 'Por favor completa todos los campos obligatorios.');
                return false;
            }
        }

        // >>> Validación específica de Contacto 1 (WhatsApp obligatorio)
        $names             = Tools::getValue('contact_name', []);
        $country_ids_wp    = Tools::getValue('contact_country_id_wp', []);
        $phones_wp         = Tools::getValue('contact_phone_number_wp', []);

        // Debe existir Contacto 1 y su WhatsApp
        if (!isset($names[0]) || trim($names[0]) === '') {
            $this->context->smarty->assign('error', 'Debes registrar al menos un contacto de emergencia.');
            return false;
        }
        if (!isset($country_ids_wp[0]) || (int)$country_ids_wp[0] <= 0) {
            $this->context->smarty->assign('error', 'Selecciona el país de WhatsApp para el primer contacto.');
            return false;
        }
        if (!isset($phones_wp[0]) || trim($phones_wp[0]) === '') {
            $this->context->smarty->assign('error', 'Ingresa el número de WhatsApp para el primer contacto.');
            return false;
        }

        // (Opcional) Si se diligencia el Contacto 2, validar también su WhatsApp
        if (isset($names[1]) && trim($names[1]) !== '') {
            if (!isset($country_ids_wp[1]) || (int)$country_ids_wp[1] <= 0) {
                $this->context->smarty->assign('error', 'Selecciona el país de WhatsApp para el segundo contacto.');
                return false;
            }
            if (!isset($phones_wp[1]) || trim($phones_wp[1]) === '') {
                $this->context->smarty->assign('error', 'Ingresa el número de WhatsApp para el segundo contacto.');
                return false;
            }
        }

        return true;
    }

    private function isEditMode()
    {
        $editId = (int)Tools::getValue('edit_id'); // toma GET o POST
        return $editId > 0;
    }

    private function normalizeCountryId($fieldName)
    {
        $raw = Tools::getValue($fieldName);
        
        // Si está vacío o es null, retornar null
        if (empty($raw) || trim($raw) === '' || $raw === '0') {
            return null;
        }
        
        $id = (int)$raw;

        if ($id <= 0) {
            return null; // evita insertar 0
        }

        // Verifica que exista el id_country
        $sql = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE id_country = '.(int)$id;
        $exists = (bool) Db::getInstance()->getValue($sql);

        return $exists ? $id : null;
    }

    private function fillCustomerData($customer, $id_qr_code = null, $customerId = null, $isNewRecord = false)
    {
        if ($id_qr_code !== null) {
            $customer->id_qr_code = (int)$id_qr_code;
        }
        if ($customerId !== null) {
            $customer->id_customer = (int)$customerId;
        }

        $customer->user_name = Tools::getValue('user_name');
        $customer->user_type_dni = Tools::getValue('user_type_dni');
        $customer->user_dni = Tools::getValue('user_dni');
        $customer->user_birthdate = trim(Tools::getValue('user_birthdate')) ?: null;
        $customer->user_gender = trim(Tools::getValue('user_gender')) ?: null;

        $customer->user_stature_cm = Tools::getValue('user_stature_cm') ? (int)Tools::getValue('user_stature_cm') : null;
        $customer->user_address = trim(Tools::getValue('user_address')) ?: null;
        $customer->user_city = trim(Tools::getValue('user_city')) ?: null;
        $customer->user_state_id = Tools::getValue('user_state_id') ? (int)Tools::getValue('user_state_id') : null;
        $customer->user_country_id = $this->normalizeCountryId('user_country_id');
        $customer->user_weight_kg = Tools::getValue('user_weight_kg') ? (float)Tools::getValue('user_weight_kg') : null;

        $customer->user_mobile_number = Tools::getValue('user_mobile_number');
        $customer->user_home_number = trim(Tools::getValue('user_home_number')) ?: null;
        $customer->user_work_number = trim(Tools::getValue('user_work_number')) ?: null;

        $customer->user_mobile_country_id = $this->normalizeCountryId('user_mobile_country_id');
        $customer->user_home_country_id = $this->normalizeCountryId('user_home_country_id');
        $customer->user_work_country_id = $this->normalizeCountryId('user_work_country_id');

        $customer->user_has_eps = (int)Tools::getValue('user_has_eps', 0);
        $customer->user_eps_name = trim(Tools::getValue('user_eps_name')) ?: null;

        $customer->user_has_prepaid = (int)Tools::getValue('user_has_prepaid', 0);
        $customer->user_prepaid_name = trim(Tools::getValue('user_prepaid_name')) ?: null;

        $customer->user_blood_type = trim(Tools::getValue('user_blood_type')) ?: null;
        $customer->user_accepts_transfusions = (int)Tools::getValue('user_accepts_transfusions', 1);
        $customer->user_organ_donor = (int)Tools::getValue('user_organ_donor', 0);

        $customer->extra_notes = trim(Tools::getValue('extra_notes')) ?: null;

        // Solo establecer date_activated si es un nuevo registro
        if ($isNewRecord) {
            $customer->date_activated = date('Y-m-d H:i:s');
        }
    }
    
    private function insertCustomerData()
    {
        $customerId = (int)$this->context->customer->id;

        // 1) Obtener el validation_code del POST
        $validationCode = Tools::getValue('validation_code');
        if (!$validationCode) {
            $this->context->smarty->assign('error', 'Falta el código de validación.');
            return;
        }

        // 2) Buscar el QR por código de validación (ajusta status según tu lógica)
        $qr = Db::getInstance()->getRow('
            SELECT id_qr_code, status
            FROM '._DB_PREFIX_.'qsp_qr_codes
            WHERE validation_code = "'.pSQL($validationCode).'" AND status = "SIN_ACTIVAR"
        ');

        if (!$qr) {
            $this->context->smarty->assign('error', 'El código de validación no es válido.');
            return;
        }

        $id_qr_code = (int)$qr['id_qr_code'];

        // 3) Crear el registro principal
        $customer = new QspCustomerCode();
        $this->fillCustomerData($customer, $id_qr_code, $customerId, true);

        if (!$customer->add()) {
            // Muestra el primer error de validación que lance ObjectModel
            $this->context->smarty->assign('error', $this->l('No se pudo registrar el QR. Verifica los campos obligatorios.'));
            return;
        }

        $image_name = $this->handleImageUpload($customer->id);
        if ($image_name) {
            $customer->user_image = $image_name;
            $customer->update();
        }

        // 4) Guardar tablas hijas
        $this->saveContacts($customer->id);
        $this->saveCovidInfo($customer->id);
        $this->saveConditions($customer->id);
        $this->saveAllergies($customer->id);
        $this->saveMedications($customer->id);

        // 5) Actualizar estado del QR a ACTIVO y asignar fecha
        Db::getInstance()->update('qsp_qr_codes', [
            'status' => pSQL('ACTIVO'),
            'date_assigned' => date('Y-m-d H:i:s'),
        ], 'id_qr_code = '.$id_qr_code);

        Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
    }


    private function updateCustomerData()
    {
        $editId = (int)Tools::getValue('edit_id');
        $customer = new QspCustomerCode($editId);

        // primero llena datos
        $this->fillCustomerData($customer, null, null, false);

        // luego maneja imagen (si hay) y asigna al objeto
        $image_name = $this->handleImageUpload($customer->id);
        if ($image_name) {
            $customer->user_image = $image_name;
        }

        // guarda una sola vez
        if (!$customer->update()) {
            $this->context->smarty->assign('error', 'No se pudo actualizar el registro. Verifica los campos obligatorios.');
            return;
        }

        $this->saveContacts($editId);
        $this->saveCovidInfo($editId);
        $this->saveConditions($editId);
        $this->saveAllergies($editId);
        $this->saveMedications($editId);

        Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
    }
        
    private function saveContacts($id_customer_code)
    {
        Db::getInstance()->delete('qsp_customer_contacts', 'id_customer_code = ' . (int)$id_customer_code);

        $names          = (array) Tools::getValue('contact_name', []);
        $phones         = (array) Tools::getValue('contact_phone', []);
        $emails         = (array) Tools::getValue('contact_email', []);
        $relations      = (array) Tools::getValue('contact_relationship', []);
        $country_ids    = (array) Tools::getValue('contact_country_id', []);
        $country_ids_wp = (array) Tools::getValue('contact_country_id_wp', []);
        $phones_wp      = (array) Tools::getValue('contact_phone_number_wp', []);

        $max = max(count($names), count($phones), count($emails), count($relations), count($country_ids), count($country_ids_wp), count($phones_wp));

        for ($i = 0; $i < $max; $i++) {
            $name = isset($names[$i]) ? trim((string)$names[$i]) : '';
            if ($i === 0 && $name === '') { // contacto 1 requerido
                $this->context->smarty->assign('error', 'Debes registrar al menos un contacto de emergencia.');
                return;
            }
            if ($i > 0 && $name === '') continue;

            $phone     = isset($phones[$i]) ? trim((string)$phones[$i]) : '';
            $email     = isset($emails[$i]) ? trim((string)$emails[$i]) : '';
            $relation  = isset($relations[$i]) ? trim((string)$relations[$i]) : '';
            $idCountry = isset($country_ids[$i]) ? (int)$country_ids[$i] : 0;

            $idCountryWp = isset($country_ids_wp[$i]) ? (int)$country_ids_wp[$i] : 0;
            $rawWp       = isset($phones_wp[$i]) ? (string)$phones_wp[$i] : '';
            $wpDigits    = preg_replace('/\D+/', '', $rawWp); // SOLO dígitos (igual que móvil)

            if ($idCountryWp <= 0 || $wpDigits === '') {
                if ($i === 0) {
                    $this->context->smarty->assign('error', 'Completa país y número de WhatsApp del primer contacto.');
                    return;
                } else {
                    continue;
                }
            }

            Db::getInstance()->insert('qsp_customer_contacts', [
                'id_customer_code'        => (int)$id_customer_code,
                'contact_index'           => (int)$i,
                'contact_name'            => pSQL($name),
                'contact_phone_number'    => pSQL($phone),
                'contact_email'           => pSQL($email),
                'relationship'            => pSQL($relation),
                'contact_country_id'      => (int)$idCountry,
                'contact_country_id_wp'   => (int)$idCountryWp,
                'contact_phone_number_wp' => pSQL($wpDigits), // ← número sin prefijo
            ]);
        }
    }

    private function saveCovidInfo($id_customer_code)
    {
        Db::getInstance()->delete('qsp_customer_covid_vaccine', 'id_customer_code = ' . (int)$id_customer_code);

        Db::getInstance()->insert('qsp_customer_covid_vaccine', [
            'id_customer_code' => (int)$id_customer_code,
            'vaccinated' => (int)Tools::getValue('vaccinated', 0),
            'doses' => (int)Tools::getValue('doses'),
            'last_dose_date' => pSQL(Tools::getValue('last_dose_date')),
            'notes' => pSQL(Tools::getValue('covid_notes')),
        ]);
    }

    private function saveConditions($id_customer_code)
    {
        Db::getInstance()->delete('qsp_customer_conditions', 'id_customer_code = ' . (int)$id_customer_code);

        $conditions = Tools::getValue('condition_name', []);
        $notes = Tools::getValue('condition_note', []);

        foreach ($conditions as $index => $condition) {
            if (empty($condition)) {
                continue;
            }

            Db::getInstance()->insert('qsp_customer_conditions', [
                'id_customer_code' => (int)$id_customer_code,
                'condition_name' => pSQL($condition),
                'note' => pSQL($notes[$index]),
            ]);
        }
    }

    private function saveAllergies($id_customer_code)
    {
        Db::getInstance()->delete('qsp_customer_allergies', 'id_customer_code = ' . (int)$id_customer_code);

        $allergens = Tools::getValue('allergen', []);
        $notes = Tools::getValue('allergy_note', []);

        foreach ($allergens as $index => $allergen) {
            if (empty($allergen)) {
                continue;
            }

            Db::getInstance()->insert('qsp_customer_allergies', [
                'id_customer_code' => (int)$id_customer_code,
                'allergen' => pSQL($allergen),
                'note' => pSQL($notes[$index]),
            ]);
        }
    }

    private function saveMedications($id_customer_code)
    {
        Db::getInstance()->delete('qsp_customer_medications', 'id_customer_code = ' . (int)$id_customer_code);

        $names = Tools::getValue('med_name', []);
        $doses = Tools::getValue('med_dose', []);
        $frequencies = Tools::getValue('med_frequency', []);
        $notes = Tools::getValue('med_note', []);

        foreach ($names as $index => $name) {
            if (empty($name)) {
                continue;
            }

            Db::getInstance()->insert('qsp_customer_medications', [
                'id_customer_code' => (int)$id_customer_code,
                'med_name' => pSQL($name),
                'dose' => pSQL($doses[$index]),
                'frequency' => pSQL($frequencies[$index]),
                'note' => pSQL($notes[$index]),
            ]);
        }
    }
}