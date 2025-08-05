<?php

class QrsoldproductsManageqrModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    public function initContent()
    {
        parent::initContent();

        $customerId = $this->context->customer->id;

        $qrs = Db::getInstance()->executeS('
            SELECT qc.*, qr.code
            FROM '._DB_PREFIX_.'qsp_customer_codes qc
            INNER JOIN '._DB_PREFIX_.'qsp_qr_codes qr ON qc.id_qr_code = qr.id_qr_code
            WHERE qc.id_customer = '.(int)$customerId.'
            ORDER BY qc.date_activated DESC
        ');

        $addQrLink = $this->context->link->getPageLink('module-qrsoldproducts-addqr-custom');

        $this->context->smarty->assign([
            'qrs' => $qrs,
            'add_qr_link' => $addQrLink,
        ]);

        $this->setTemplate('module:qrsoldproducts/views/templates/front/manageqr.tpl');
    }
}