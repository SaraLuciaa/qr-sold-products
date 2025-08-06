<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class QspCustomerCode extends ObjectModel
{
    public $id_customer_code;
    public $id_qr_code;
    public $id_customer;
    public $user_name;
    public $user_type_dni;
    public $user_dni;
    public $user_birthdate;
    public $user_gender;
    public $user_stature_cm;
    public $user_address;
    public $user_mobile_country_id;
    public $user_mobile_number;
    public $user_home_country_id;
    public $user_home_number;
    public $user_work_country_id;
    public $user_work_number;
    public $user_weight_kg;
    public $user_has_eps;
    public $user_eps_name;
    public $user_has_prepaid;
    public $user_prepaid_name;
    public $user_blood_type;
    public $user_accepts_transfusions;
    public $user_organ_donor;
    public $extra_notes;
    public $date_activated;

    public static $definition = [
        'table' => 'qsp_customer_codes',
        'primary' => 'id_customer_code',
        'multilang' => false,
        'fields' => [
            'id_qr_code' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_customer' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'user_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 128,
            ],
            'user_type_dni' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 32,
            ],
            'user_dni' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 64,
            ],
            'user_birthdate' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'allow_null' => true,
            ],
            'user_gender' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 32,
                'allow_null' => true,
            ],
            'user_stature_cm' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'user_address' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
            ],
            'user_mobile_country_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'user_mobile_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 20,
            ],
            'user_home_country_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'user_home_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
                'allow_null' => true,
            ],
            'user_work_country_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'user_work_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
                'allow_null' => true,
            ],
            'user_weight_kg' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'allow_null' => true,
            ],
            'user_has_eps' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'user_eps_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 128,
                'allow_null' => true,
            ],
            'user_has_prepaid' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'user_prepaid_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 128,
                'allow_null' => true,
            ],
            'user_blood_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 8,
                'allow_null' => true,
            ],
            'user_accepts_transfusions' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'user_organ_donor' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'extra_notes' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'allow_null' => true,
            ],
            'date_activated' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'required' => true,
            ],
        ],
    ];
} 