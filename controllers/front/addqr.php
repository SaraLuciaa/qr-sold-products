<?php

class QrsoldproductsAddqrModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $authRedirection = 'index';
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $error = '';
        $success = '';
        $editMode = false;
        $qrData = [];
        $customerId = (int)$this->context->customer->id;

        // Modo edición
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

        if (Tools::isSubmit('submit_qr_code')) {
            $validation_code = Tools::getValue('validation_code');
            $user_name = Tools::getValue('user_name');
            $user_type_dni = Tools::getValue('user_type_dni');
            $user_dni = Tools::getValue('user_dni');
            $user_birthdate = Tools::getValue('user_birthdate');
            $user_gender = Tools::getValue('user_gender');
            $user_stature_cm = Tools::getValue('user_stature_cm');
            $user_address = Tools::getValue('user_address');
            $user_phone_mobile = Tools::getValue('user_phone_mobile');
            $user_phone_home = Tools::getValue('user_phone_home');
            $user_phone_work = Tools::getValue('user_phone_work');
            $user_whatsapp_e164 = Tools::getValue('user_whatsapp_e164');
            $user_weight_kg = Tools::getValue('user_weight_kg');
            $user_has_eps = Tools::getValue('user_has_eps') ? 1 : 0;
            $user_eps_name = Tools::getValue('user_eps_name');
            $user_has_prepaid = Tools::getValue('user_has_prepaid') ? 1 : 0;
            $user_prepaid_name = Tools::getValue('user_prepaid_name');
            $user_blood_type = Tools::getValue('user_blood_type');
            $user_accepts_transfusions = Tools::getValue('user_accepts_transfusions') ? 1 : 0;
            $user_organ_donor = Tools::getValue('user_organ_donor') ? 1 : 0;
            $extra_notes = Tools::getValue('extra_notes');

            // Datos de contacto
            $contact_name = Tools::getValue('contact_name');
            $contact_phone = Tools::getValue('contact_phone');
            $contact_whatsapp_e164 = Tools::getValue('contact_whatsapp_e164');
            $contact_email = Tools::getValue('contact_email');
            $relationship = Tools::getValue('relationship');

            // Datos de COVID
            $vaccinated = Tools::getValue('vaccinated') ? 1 : 0;
            $doses = Tools::getValue('doses');
            $last_dose_date = Tools::getValue('last_dose_date');
            $covid_notes = Tools::getValue('covid_notes');

            // Condiciones médicas
            $conditions = Tools::getValue('conditions', []);
            $condition_notes = Tools::getValue('condition_notes', []);

            // Alergias
            $allergies = Tools::getValue('allergies', []);
            $allergy_notes = Tools::getValue('allergy_notes', []);

            // Medicamentos
            $medications = Tools::getValue('medications', []);
            $med_doses = Tools::getValue('med_doses', []);
            $med_frequencies = Tools::getValue('med_frequencies', []);
            $med_notes = Tools::getValue('med_notes', []);

            if (!$user_name || !$user_type_dni || !$user_dni || (!$editMode && !$validation_code)) {
                $error = 'Por favor completa todos los campos obligatorios.';
            } else if (empty($contact_name[0]) || empty($contact_phone[0])) {
                    $error = 'El contacto de emergencia debe tener nombre y número de celular.';
            }else {
                if ($editMode) {
                    // Actualizar datos principales
                    $updated = Db::getInstance()->update('qsp_customer_codes', [
                        'user_name' => pSQL($user_name),
                        'user_type_dni' => pSQL($user_type_dni),
                        'user_dni' => pSQL($user_dni),
                        'user_birthdate' => pSQL($user_birthdate),
                        'user_gender' => pSQL($user_gender),
                        'user_stature_cm' => (int)$user_stature_cm,
                        'user_address' => pSQL($user_address),
                        'user_phone_mobile' => pSQL($user_phone_mobile),
                        'user_phone_home' => pSQL($user_phone_home),
                        'user_phone_work' => pSQL($user_phone_work),
                        'user_whatsapp_e164' => pSQL($user_whatsapp_e164),
                        'user_weight_kg' => (float)$user_weight_kg,
                        'user_has_eps' => $user_has_eps,
                        'user_eps_name' => pSQL($user_eps_name),
                        'user_has_prepaid' => $user_has_prepaid,
                        'user_prepaid_name' => pSQL($user_prepaid_name),
                        'user_blood_type' => pSQL($user_blood_type),
                        'user_accepts_transfusions' => $user_accepts_transfusions,
                        'user_organ_donor' => $user_organ_donor,
                        'extra_notes' => pSQL($extra_notes),
                    ], 'id_customer_code = ' . (int)$editId . ' AND id_customer = ' . $customerId);

                    if ($updated) {
                        // Actualizar contacto
                        $this->updateContact($editId, $contact_name, $contact_phone, $contact_whatsapp_e164, $contact_email, $relationship);
                        
                        // Actualizar COVID
                        $this->updateCovid($editId, $vaccinated, $doses, $last_dose_date, $covid_notes);
                        
                        // Actualizar condiciones
                        $this->updateConditions($editId, $conditions, $condition_notes);
                        
                        // Actualizar alergias
                        $this->updateAllergies($editId, $allergies, $allergy_notes);
                        
                        // Actualizar medicamentos
                        $this->updateMedications($editId, $medications, $med_doses, $med_frequencies, $med_notes);

                        Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
                    } else {
                        $error = 'Error al guardar los cambios.';
                    }
                } else {
                    // Validar QR
                    $qr = Db::getInstance()->getRow('
                        SELECT id_qr_code, status FROM ' . _DB_PREFIX_ . 'qsp_qr_codes
                        WHERE validation_code = "' . pSQL($validation_code) . '"
                    ');

                    if (!$qr) {
                        $error = 'El código QR no es válido o el código de validación no coincide.';
                    } elseif ($qr['status'] === 'ACTIVO') {
                        $error = 'Este código QR ya fue activado.';
                    } else {
                        $id_qr_code = (int)$qr['id_qr_code'];

                        $inserted = Db::getInstance()->insert('qsp_customer_codes', [
                            'id_qr_code' => $id_qr_code,
                            'id_customer' => $customerId,
                            'user_name' => pSQL($user_name),
                            'user_type_dni' => pSQL($user_type_dni),
                            'user_dni' => pSQL($user_dni),
                            'user_birthdate' => pSQL($user_birthdate),
                            'user_gender' => pSQL($user_gender),
                            'user_stature_cm' => (int)$user_stature_cm,
                            'user_address' => pSQL($user_address),
                            'user_phone_mobile' => pSQL($user_phone_mobile),
                            'user_phone_home' => pSQL($user_phone_home),
                            'user_phone_work' => pSQL($user_phone_work),
                            'user_whatsapp_e164' => pSQL($user_whatsapp_e164),
                            'user_weight_kg' => (float)$user_weight_kg,
                            'user_has_eps' => $user_has_eps,
                            'user_eps_name' => pSQL($user_eps_name),
                            'user_has_prepaid' => $user_has_prepaid,
                            'user_prepaid_name' => pSQL($user_prepaid_name),
                            'user_blood_type' => pSQL($user_blood_type),
                            'user_accepts_transfusions' => $user_accepts_transfusions,
                            'user_organ_donor' => $user_organ_donor,
                            'extra_notes' => pSQL($extra_notes),
                            'date_activated' => date('Y-m-d H:i:s'),
                        ]);

                        if ($inserted) {
                            $id_customer_code = Db::getInstance()->Insert_ID();
                            
                            // Insertar contacto
                            $this->insertContact($id_customer_code, $contact_name, $contact_phone, $contact_whatsapp_e164, $contact_email, $relationship);
                            
                            // Insertar COVID
                            $this->insertCovid($id_customer_code, $vaccinated, $doses, $last_dose_date, $covid_notes);
                            
                            // Insertar condiciones
                            $this->insertConditions($id_customer_code, $conditions, $condition_notes);
                            
                            // Insertar alergias
                            $this->insertAllergies($id_customer_code, $allergies, $allergy_notes);
                            
                            // Insertar medicamentos
                            $this->insertMedications($id_customer_code, $medications, $med_doses, $med_frequencies, $med_notes);

                            Db::getInstance()->update('qsp_qr_codes', [
                                'status' => 'ACTIVO',
                                'date_assigned' => date('Y-m-d H:i:s'),
                            ], 'id_qr_code = ' . $id_qr_code);

                            Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
                        } else {
                            $error = 'Error al guardar los datos.';
                        }
                    }
                }
            }
        }

        $this->context->smarty->assign([
            'error' => $error,
            'success' => $success,
            'customer' => $this->context->customer,
            'edit_mode' => $editMode,
            'qr_data' => $qrData,
        ]);

        $this->setTemplate('module:qrsoldproducts/views/templates/front/addqr.tpl');
    }

    private function insertContact($id_customer_code, $names, $phones, $whatsapps, $emails, $relationships)
    {
        foreach ($names as $i => $name) {
            if (empty($name) || empty($phones[$i])) {
                continue; // Ignorar contactos incompletos
            }

            Db::getInstance()->insert('qsp_customer_contacts', [
                'id_customer_code' => $id_customer_code,
                'contact_index' => $i,
                'contact_name' => pSQL($name),
                'contact_phone' => pSQL($phones[$i] ?? ''),
                'contact_whatsapp_e164' => pSQL($whatsapps[$i] ?? ''),
                'contact_email' => pSQL($emails[$i] ?? ''),
                'relationship' => pSQL($relationships[$i] ?? ''),
            ]);
        }
    }


    private function updateContact($id_customer_code, $names, $phones, $whatsapps, $emails, $relationships)
    {
        Db::getInstance()->delete('qsp_customer_contacts', 'id_customer_code = ' . (int)$id_customer_code);

        $this->insertContact($id_customer_code, $names, $phones, $whatsapps, $emails, $relationships);
    }

    private function insertCovid($id_customer_code, $vaccinated, $doses, $last_dose_date, $notes)
    {
        Db::getInstance()->insert('qsp_customer_covid_vaccine', [
            'id_customer_code' => $id_customer_code,
            'vaccinated' => $vaccinated,
            'doses' => (int)$doses,
            'last_dose_date' => pSQL($last_dose_date),
            'notes' => pSQL($notes),
        ]);
    }

    private function updateCovid($id_customer_code, $vaccinated, $doses, $last_dose_date, $notes)
    {
        Db::getInstance()->update('qsp_customer_covid_vaccine', [
            'vaccinated' => $vaccinated,
            'doses' => (int)$doses,
            'last_dose_date' => pSQL($last_dose_date),
            'notes' => pSQL($notes),
        ], 'id_customer_code = ' . $id_customer_code);
    }

    private function insertConditions($id_customer_code, $conditions, $notes)
    {
        foreach ($conditions as $index => $condition) {
            if ($condition) {
                Db::getInstance()->insert('qsp_customer_conditions', [
                    'id_customer_code' => $id_customer_code,
                    'condition_name' => pSQL($condition),
                    'note' => pSQL($notes[$index] ?? ''),
                ]);
            }
        }
    }

    private function updateConditions($id_customer_code, $conditions, $notes)
    {
        // Eliminar condiciones existentes
        Db::getInstance()->delete('qsp_customer_conditions', 'id_customer_code = ' . $id_customer_code);
        
        // Insertar nuevas condiciones
        $this->insertConditions($id_customer_code, $conditions, $notes);
    }

    private function insertAllergies($id_customer_code, $allergies, $notes)
    {
        foreach ($allergies as $index => $allergy) {
            if ($allergy) {
                Db::getInstance()->insert('qsp_customer_allergies', [
                    'id_customer_code' => $id_customer_code,
                    'allergen' => pSQL($allergy),
                    'note' => pSQL($notes[$index] ?? ''),
                ]);
            }
        }
    }

    private function updateAllergies($id_customer_code, $allergies, $notes)
    {
        // Eliminar alergias existentes
        Db::getInstance()->delete('qsp_customer_allergies', 'id_customer_code = ' . $id_customer_code);
        
        // Insertar nuevas alergias
        $this->insertAllergies($id_customer_code, $allergies, $notes);
    }

    private function insertMedications($id_customer_code, $medications, $doses, $frequencies, $notes)
    {
        foreach ($medications as $index => $medication) {
            if ($medication) {
                Db::getInstance()->insert('qsp_customer_medications', [
                    'id_customer_code' => $id_customer_code,
                    'med_name' => pSQL($medication),
                    'dose' => pSQL($doses[$index] ?? ''),
                    'frequency' => pSQL($frequencies[$index] ?? ''),
                    'note' => pSQL($notes[$index] ?? ''),
                ]);
            }
        }
    }

    private function updateMedications($id_customer_code, $medications, $doses, $frequencies, $notes)
    {
        // Eliminar medicamentos existentes
        Db::getInstance()->delete('qsp_customer_medications', 'id_customer_code = ' . $id_customer_code);
        
        // Insertar nuevos medicamentos
        $this->insertMedications($id_customer_code, $medications, $doses, $frequencies, $notes);
    }
}