<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class QspQrCode extends ObjectModel
{
    public $id_qr_code;
    public $code;
    public $validation_code;
    public $status;
    public $id_order_detail;
    public $date_created;
    public $date_assigned;

    public static $definition = [
        'table' => 'qsp_qr_codes',
        'primary' => 'id_qr_code',
        'multilang' => false,
        'fields' => [
            'code' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 64,
            ],
            'validation_code' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 64,
            ],
            'status' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 32,
            ],
            'id_order_detail' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'date_created' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
            'date_assigned' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
        ],
    ];
}