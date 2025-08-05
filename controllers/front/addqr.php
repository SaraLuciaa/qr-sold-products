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
                SELECT cc.*, qr.status
                FROM ' . _DB_PREFIX_ . 'qsp_customer_codes cc
                INNER JOIN ' . _DB_PREFIX_ . 'qsp_qr_codes qr ON cc.id_qr_code = qr.id_qr_code
                WHERE cc.id_customer_code = ' . $editId . '
                AND cc.id_customer = ' . $customerId
            );

            if (!$qrData || $qrData['status'] !== 'ACTIVO') {
                Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
            }
        }

        if (Tools::isSubmit('submit_qr_code')) {
            $validation_code = Tools::getValue('validation_code');
            $user_name = Tools::getValue('user_name');
            $user_type_dni = Tools::getValue('user_type_dni');
            $user_dni = Tools::getValue('user_dni');
            $user_Birthdate = Tools::getValue('user_Birthdate');
            $user_gender = Tools::getValue('user_gender');
            $user_stature = Tools::getValue('user_stature');
            $user_address = Tools::getValue('user_address');
            $user_phone_mobile = Tools::getValue('user_phone_mobile');
            $user_phone_home = Tools::getValue('user_phone_home');
            $user_phone_work = Tools::getValue('user_phone_work');
            $user_weight = Tools::getValue('user_weight');
            $user_eps = Tools::getValue('user_eps');
            $user_eps_name = Tools::getValue('user_eps_name');
            $user_prepaid = Tools::getValue('user_prepaid');
            $user_prepaid_name = Tools::getValue('user_prepaid_name');
            $user_blood_type = Tools::getValue('user_blood_type');
            $user_donor = Tools::getValue('user_donor');
            $owner_name = Tools::getValue('owner_name');
            $owner_phone = Tools::getValue('owner_phone');
            $owner_email = Tools::getValue('owner_email');
            $owner_relationship = Tools::getValue('owner_relationship');
            $user_covid = Tools::getValue('user_covid');
            $user_diseases = Tools::getValue('user_diseases');
            $medical_info = Tools::getValue('medical_info');
            $extra_notes = Tools::getValue('extra_notes');

            if (!$user_name || !$owner_name || !$owner_phone || (!$editMode && !$validation_code)) {
                $error = 'Por favor completa todos los campos obligatorios.';
            } else {
                if ($editMode) {
                    // Actualizar
                    $updated = Db::getInstance()->update('qsp_customer_codes', [
                        'user_name' => pSQL($user_name),
                        'user_type_dni' => pSQL($user_type_dni),
                        'user_dni' => pSQL($user_dni),
                        'user_Birthdate' => pSQL($user_Birthdate),
                        'user_gender' => pSQL($user_gender),
                        'user_stature' => pSQL($user_stature),
                        'user_address' => pSQL($user_address),
                        'user_phone_mobile' => pSQL($user_phone_mobile),
                        'user_phone_home' => pSQL($user_phone_home),
                        'user_phone_work' => pSQL($user_phone_work),
                        'user_weight' => pSQL($user_weight),
                        'user_eps' => pSQL($user_eps),
                        'user_eps_name' => pSQL($user_eps_name),
                        'user_prepaid' => pSQL($user_prepaid),
                        'user_prepaid_name' => pSQL($user_prepaid_name),
                        'user_blood_type' => pSQL($user_blood_type),
                        'user_donor' => pSQL($user_donor),
                        'owner_name' => pSQL($owner_name),
                        'owner_phone' => pSQL($owner_phone),
                        'owner_email' => pSQL($owner_email),
                        'owner_relationship' => pSQL($owner_relationship),
                        'user_covid' => pSQL($user_covid),
                        'user_diseases' => pSQL($user_diseases),
                        'medical_info' => pSQL($medical_info),
                        'extra_notes' => pSQL($extra_notes),
                    ], 'id_customer_code = ' . (int)$editId . ' AND id_customer = ' . $customerId);

                    if ($updated) {
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
                        'user_Birthdate' => pSQL($user_Birthdate),
                        'user_gender' => pSQL($user_gender),
                        'user_stature' => pSQL($user_stature),
                        'user_address' => pSQL($user_address),
                        'user_phone_mobile' => pSQL($user_phone_mobile),
                        'user_phone_home' => pSQL($user_phone_home),
                        'user_phone_work' => pSQL($user_phone_work),
                        'user_weight' => pSQL($user_weight),
                        'user_eps' => pSQL($user_eps),
                        'user_eps_name' => pSQL($user_eps_name),
                        'user_prepaid' => pSQL($user_prepaid),
                        'user_prepaid_name' => pSQL($user_prepaid_name),
                        'user_blood_type' => pSQL($user_blood_type),
                        'user_donor' => pSQL($user_donor),
                        'owner_name' => pSQL($owner_name),
                        'owner_phone' => pSQL($owner_phone),
                        'owner_email' => pSQL($owner_email),
                        'owner_relationship' => pSQL($owner_relationship),
                        'user_covid' => pSQL($user_covid),
                        'user_diseases' => pSQL($user_diseases),
                        'medical_info' => pSQL($medical_info),
                        'extra_notes' => pSQL($extra_notes),
                            'date_activated' => date('Y-m-d H:i:s'),
                        ]);

                        if ($inserted) {
                            Db::getInstance()->update('qsp_qr_codes', [
                                'status' => 'ACTIVO',
                                'date_assigned' => date('Y-m-d H:i:s'),
                            ], 'id_qr_code = ' . $id_qr_code);

                            Tools::redirect($this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom'));
                        } else {
                            $error = 'Error al guardar los datos de la mascota.';
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
}