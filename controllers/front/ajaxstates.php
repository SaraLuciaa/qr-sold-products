<?php
// Controlador AJAX para devolver los estados de un país en formato JSON
class QrsoldproductsAjaxstatesModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        header('Content-Type: application/json');
        $country_id = (int)Tools::getValue('country_id');
        $states = [];
        if ($country_id > 0) {
            $states = Db::getInstance()->executeS('
                SELECT id_state, name FROM '._DB_PREFIX_.'state
                WHERE id_country = '.(int)$country_id.' AND active = 1
                ORDER BY name ASC
            ');
        }
        echo json_encode($states);
        exit;
    }
}
