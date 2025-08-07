<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'qrsoldproducts/classes/QspQrCode.php';
require_once _PS_MODULE_DIR_ . 'qrsoldproducts/classes/QspQrCodeService.php';

class QrSoldProducts extends Module
{
    public function __construct()
    {
        $this->name = 'qrsoldproducts';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'SaraLuciaaa';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('QR Sold Products');
        $this->description = $this->l('Gestión de QRs para productos vendidos');
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install()
    {
        return parent::install()
            && $this->createDatabaseTables()
            && $this->installTab()
            && $this->registerHook('displayAdminOrderTop')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall()
    {
        // Borrar imágenes de /views/img/uploads
        $this->deleteFolderContents(_PS_MODULE_DIR_ . 'qrsoldproducts/views/img/uploads');

        // Borrar imágenes de /qrs en public_html
        $this->deleteFolderContents(_PS_MODULE_DIR_ . 'qrsoldproducts/qrs');

        return parent::uninstall()
            && $this->removeDatabaseTables()
            && $this->uninstallTab();
    }

    private function createDatabaseTables()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_qr_codes` (
            `id_qr_code` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `code` VARCHAR(64) NOT NULL UNIQUE,
            `validation_code` VARCHAR(64) NOT NULL,
            `status` ENUM("SIN_ASIGNAR", "SIN_ACTIVAR", "ACTIVO") NOT NULL DEFAULT "SIN_ASIGNAR",
            `id_order_detail` INT DEFAULT NULL,
            `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_assigned` DATETIME NULL,
            PRIMARY KEY (`id_qr_code`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_codes` (
            `id_customer_code` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_qr_code` INT UNSIGNED NOT NULL,
            `id_customer` INT UNSIGNED DEFAULT NULL,
            `user_image` VARCHAR(255) DEFAULT NULL,
            `user_name` VARCHAR(128) NOT NULL,
            `user_type_dni` ENUM("CC","TI","CE") NOT NULL,
            `user_dni` VARCHAR(64) NOT NULL,
            `user_birthdate` DATE DEFAULT NULL,
            `user_gender` ENUM("MASCULINO","FEMENINO","OTRO") DEFAULT NULL,
            `user_stature_cm` SMALLINT UNSIGNED DEFAULT NULL,
            `user_address` VARCHAR(255) DEFAULT NULL,

            `user_mobile_country_id` INT UNSIGNED NOT NULL,
            `user_mobile_number` VARCHAR(20) NOT NULL,

            `user_home_country_id` INT UNSIGNED DEFAULT NULL,
            `user_home_number` VARCHAR(20) DEFAULT NULL,

            `user_work_country_id` INT UNSIGNED DEFAULT NULL,
            `user_work_number` VARCHAR(20) DEFAULT NULL,

            `user_weight_kg` DECIMAL(5,2) DEFAULT NULL,
            `user_has_eps` TINYINT(1) NOT NULL DEFAULT 0,
            `user_eps_name` VARCHAR(128) DEFAULT NULL,
            `user_has_prepaid` TINYINT(1) NOT NULL DEFAULT 0,
            `user_prepaid_name` VARCHAR(128) DEFAULT NULL,
            `user_blood_type` ENUM("O+","O-","A+","A-","B+","B-","AB+","AB-") DEFAULT NULL,
            `user_accepts_transfusions` TINYINT(1) NOT NULL DEFAULT 1,
            `user_organ_donor` TINYINT(1) NOT NULL DEFAULT 0,
            `extra_notes` TEXT DEFAULT NULL,
            `date_activated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_customer_code`),
            INDEX (`id_qr_code`),
            FOREIGN KEY (`id_qr_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_qr_codes`(`id_qr_code`) ON DELETE CASCADE,
            FOREIGN KEY (`user_mobile_country_id`) REFERENCES `' . _DB_PREFIX_ . 'country`(`id_country`),
            FOREIGN KEY (`user_home_country_id`) REFERENCES `' . _DB_PREFIX_ . 'country`(`id_country`),
            FOREIGN KEY (`user_work_country_id`) REFERENCES `' . _DB_PREFIX_ . 'country`(`id_country`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_contacts` (
            `id_contact` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_code` INT UNSIGNED NOT NULL,
            `contact_index` TINYINT UNSIGNED NOT NULL,
            `contact_name` VARCHAR(128) NOT NULL,

            `contact_country_id` INT UNSIGNED NOT NULL,
            `contact_phone_number` VARCHAR(20) NOT NULL,

            `contact_email` VARCHAR(128) DEFAULT NULL,
            `relationship` VARCHAR(128) DEFAULT NULL,
            PRIMARY KEY (`id_contact`),
            UNIQUE KEY `uniq_contact_slot` (`id_customer_code`,`contact_index`),
            FOREIGN KEY (`id_customer_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_customer_codes`(`id_customer_code`) ON DELETE CASCADE,
            FOREIGN KEY (`contact_country_id`) REFERENCES `' . _DB_PREFIX_ . 'country`(`id_country`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_covid_vaccine` (
            `id_covid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_code` INT UNSIGNED NOT NULL,
            `vaccinated` TINYINT(1) NOT NULL DEFAULT 0,
            `doses` TINYINT UNSIGNED DEFAULT NULL,
            `last_dose_date` DATE DEFAULT NULL,
            `notes` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id_covid`),
            INDEX (`id_customer_code`),
            FOREIGN KEY (`id_customer_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_customer_codes`(`id_customer_code`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_conditions` (
            `id_condition` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_code` INT UNSIGNED NOT NULL,
            `condition_name` VARCHAR(128) NOT NULL,
            `note` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id_condition`),
            INDEX (`id_customer_code`),
            FOREIGN KEY (`id_customer_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_customer_codes`(`id_customer_code`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_allergies` (
            `id_allergy` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_code` INT UNSIGNED NOT NULL,
            `allergen` VARCHAR(128) NOT NULL,
            `note` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id_allergy`),
            INDEX (`id_customer_code`),
            FOREIGN KEY (`id_customer_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_customer_codes`(`id_customer_code`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'qsp_customer_medications` (
            `id_medication` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_code` INT UNSIGNED NOT NULL,
            `med_name` VARCHAR(128) NOT NULL,
            `dose` VARCHAR(64) DEFAULT NULL,
            `frequency` VARCHAR(64) DEFAULT NULL,
            `note` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id_medication`),
            INDEX (`id_customer_code`),
            FOREIGN KEY (`id_customer_code`) REFERENCES `' . _DB_PREFIX_ . 'qsp_customer_codes`(`id_customer_code`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . '';

        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."qsp_product_qr_config` (
            `id_product` INT(11) NOT NULL,
            `has_qr` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_product`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function removeDatabaseTables()
    {
        $sql = [];

        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_contacts`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_covid_vaccine`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_conditions`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_allergies`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_medications`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_customer_codes`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_qr_codes`;";
        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."qsp_product_qr_config`;";

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminQrCodeManager';
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Gestión de QRs';
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;

        return $tab->add();
    }

    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminQrCodeManager');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_qr_config')) {
            $selected_products = array_map('intval', Tools::getValue('products_with_qr', []));

            Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_.'qsp_product_qr_config`
                WHERE id_product NOT IN (' . implode(',', $selected_products ?: [0]) . ')'
            );

            foreach ($selected_products as $id_product) {
                Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'qsp_product_qr_config` (id_product, has_qr)
                    VALUES (' . (int)$id_product . ', 1)
                    ON DUPLICATE KEY UPDATE has_qr = 1
                ');
            }

            $output .= $this->displayConfirmation($this->l('Configuración actualizada correctamente.'));
        }

        $products = Product::getProducts(Context::getContext()->language->id, 0, 1000, 'id_product', 'ASC');
        $configured = Db::getInstance()->executeS('SELECT id_product FROM `'._DB_PREFIX_.'qsp_product_qr_config`');
        $selected_ids = array_map('intval', array_column($configured, 'id_product'));
        $selected_ids_json = json_encode($selected_ids);

        $this->context->smarty->assign([
            'products' => $products,
            'selected_ids' => $selected_ids,
            'selected_ids_json' => $selected_ids_json,
            'form_action' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function hookDisplayAdminOrderTop($params)
    {
        $orderId = (int)$params['id_order'];
        $context = Context::getContext();
        $order = new Order($orderId);

        $service = new QspQrCodeService();

        if (!$service->orderHasProductsWithQr($order)) {
            return '';
        }

        if ($service->orderHasAssignedQrs($order)) {
            $link = $context->link->getAdminLink('AdminQrCodeManager') . '&download_order_qrs=' . $orderId;
            return '<a href="' . $link . '" class="btn btn-default"><i class="icon-download"></i> Descargar QRs</a>';
        }

        $link = $context->link->getAdminLink('AdminQrCodeManager') . '&assign_order_form=' . $orderId;
        return '<a href="' . $link . '" class="btn btn-default"><i class="icon-link"></i> Asignar QRs</a>';
    }

    public function hookDisplayCustomerAccount()
    {
        $link = $this->context->link->getPageLink('module-qrsoldproducts-manageqr-custom');

        $this->context->smarty->assign([
            'manage_qr_link' => $link,
        ]);

        return $this->display(__FILE__, 'views/templates/front/btn_qr_manage_user.tpl');
    }

    public function hookModuleRoutes($params)
    {
        return [
            'module-qrsoldproducts-buscar-custom' => [
                'controller' => 'activate',
                'rule' => 'ei',
                'keywords' => [
                    'code' => ['regexp' => '[_a-zA-Z0-9\-]+', 'param' => 'code'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],

            'module-qrsoldproducts-manageqr-custom' => [
                'controller' => 'manageqr',
                'rule' => 'mis-qrs',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'module-qrsoldproducts-addqr-custom' => [
                'controller' => 'addqr',
                'rule' => 'agregar-qr',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'module-qrsoldproducts-locationhook-custom' => [
                'controller' => 'locationhook',
                'rule' => 'locationhook',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
        ];
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if (!isset($params['newOrderStatus']) || !isset($params['id_order'])) {
            return;
        }

        $orderStatus = $params['newOrderStatus'];
        $idOrder = (int)$params['id_order'];

        if ((int)$orderStatus->id === (int)Configuration::get('PS_OS_PAYMENT')) {
            $order = new Order($idOrder);

            try {
                (new QspQrCodeService())->ensureQrsAssignedToOrder($order);
            } catch (PrestaShopException $e) {
                PrestaShopLogger::addLog('Error al asignar QRs al pedido '.$idOrder.': '.$e->getMessage(), 3);
            }
        }
    }
}