<?php

class QrsoldproductsActivateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $code = Tools::getValue('code');
        $ownView = Tools::getValue('own') == 1;
        $petInfo = null;

        if ($code) {
            $query = '
                SELECT qc.*, q.code
                FROM '._DB_PREFIX_.'qsp_customer_codes qc
                JOIN '._DB_PREFIX_.'qsp_qr_codes q ON qc.id_qr_code = q.id_qr_code
                WHERE q.code = "'.pSQL($code).'" AND q.status = "ACTIVO"
            ';
            $petInfo = Db::getInstance()->getRow($query);
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
    }
}

