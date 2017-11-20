<?php

require_once dirname(__FILE__) . '/FieldAbstract.php';
require_once dirname(__FILE__) . '/FieldAttribute.php';
require_once dirname(__FILE__) . '/FieldFeature.php';
require_once dirname(__FILE__) . '/FieldCalculated.php';
require_once dirname(__FILE__) . '/FieldDB.php';

/**
 * Class Urbit_Productfeed_Fields_Factory
 */
class Urbit_Productfeed_Fields_Factory
{
    /**
     * @var array
     */
    protected $_inputs = [
        'URBIT_PRODUCTFEED_ATTRIBUTE_NAME'        => 'Name',
        'URBIT_PRODUCTFEED_ATTRIBUTE_DESCRIPTION' => 'Description',
        'URBIT_PRODUCTFEED_ATTRIBUTE_ID'          => 'Id',
        'URBIT_PRODUCTFEED_ATTRIBUTE_GTIN'        => 'GTIN',
        'URBIT_PRODUCTFEED_ATTRIBUTE_MPN'         => 'MPN',

        'URBIT_PRODUCTFEED_DIMENSION_HEIGHT_VALUE' => 'Height Value',
        'URBIT_PRODUCTFEED_DIMENSION_HEIGHT_UNIT'  => 'Height Unit',

        'URBIT_PRODUCTFEED_DIMENSION_LENGTH_VALUE' => 'Length Value',
        'URBIT_PRODUCTFEED_DIMENSION_LENGTH_UNIT'  => 'Length Unit',

        'URBIT_PRODUCTFEED_DIMENSION_WIDTH_VALUE' => 'Width Value',
        'URBIT_PRODUCTFEED_DIMENSION_WIDTH_UNIT'  => 'Width Unit',

        'URBIT_PRODUCTFEED_DIMENSION_WEIGHT_VALUE' => 'Weight Value',
        'URBIT_PRODUCTFEED_DIMENSION_WEIGHT_UNIT'  => 'Weight Unit',
    ];

    protected $_priceInputs = [
        'URBIT_PRODUCTFEED_REGULAR_PRICE_CURRENCY' => 'Regular Price Currency',
        'URBIT_PRODUCTFEED_REGULAR_PRICE_VALUE'    => 'Regular Price Value',
        'URBIT_PRODUCTFEED_REGULAR_PRICE_VAT'      => 'Regular Price VAT',
        'URBIT_PRODUCTFEED_SALE_PRICE_CURRENCY'    => 'Sale Price Currency',
        'URBIT_PRODUCTFEED_SALE_PRICE_VALUE'       => 'Sale Price Value',
        'URBIT_PRODUCTFEED_SALE_PRICE_VAT'         => 'Sale Price VAT',
        'URBIT_PRODUCTFEED_PRICE_EFFECTIVE_DATE'   => 'Price effective date',
    ];

    protected $_inventoryListInputs = [
        'URBIT_PRODUCTFEED_SCHEMA'           => 'Schema',
        'URBIT_PRODUCTFEED_CONTENT_LANGUAGE' => 'Content language',
        'URBIT_PRODUCTFEED_CONTENT_TYPE'     => 'Content type',
        'URBIT_PRODUCTFEED_CREATED_AT'       => 'Created at',
        'URBIT_PRODUCTFEED_UPDATED_AT'       => 'Updated at',
        'URBIT_PRODUCTFEED_TARGET_COUNTRY'   => 'Target countries (comma separated)',
        'URBIT_PRODUCTFEED_VERSION'          => 'Version',
        'URBIT_PRODUCTFEED_FEED_FORMAT'      => 'Feed format - encoding',
    ];

    /**
     * @param $product
     * @param $name
     * @return mixed
     */
    public static function processAttribute($product, $name)
    {
        $inputConfig = static::getInputConfig($name);

        if (empty($inputConfig) || $inputConfig == 'none' || $inputConfig == 'empty') {
            return false;
        }

        $cls = static::getFieldClassByFieldName($inputConfig);

        return $cls::processAttribute($product, $inputConfig);
    }

    /**
     * @param $product
     * @param $name
     * @return mixed
     */
    public static function processAttributeByKey($product, $name)
    {
        $cls = static::getFieldClassByFieldName($name);

        return $cls::processAttribute($product, $name);
    }

    /**
     * @return array
     */
    public function getInputs()
    {
        return $this->_generateInputs($this->_inputs);
    }

    /**
     * @return array
     */
    public function getPriceInputs()
    {
        return $this->_generateInputs($this->_priceInputs);
    }

    public function getInventoryListInputs()
    {
        return $this->_generateTextInputs($this->_inventoryListInputs);
    }

    /**
     * @param $name
     * @return string
     */
    public static function getInputConfig($name)
    {
        return Configuration::get($name, null);
    }

    /**
     * @return array
     */
    public function getInputsConfig()
    {
        $config = [];

        foreach ($this->_inputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getPriceInputsConfig()
    {
        $config = [];

        foreach ($this->_priceInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getInventoryListInputsConfig()
    {
        $config = [];

        foreach ($this->_inventoryListInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array_merge(
            [[
                'id'   => 'empty',
                'name' => static::getModule()->l('------ None------'),
            ]],
            Urbit_Productfeed_Fields_FieldCalculated::getOptions(),
            Urbit_Productfeed_Fields_FieldDB::getOptions(),
            Urbit_Productfeed_Fields_FieldAttribute::getOptions(),
            Urbit_Productfeed_Fields_FieldFeature::getOptions(),
            [[
                'id'   => 'none',
                'name' => static::getModule()->l('------ None------'),
            ]]
        );
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public static function getFieldClassByFieldName($name)
    {
        foreach ([
            Urbit_Productfeed_Fields_FieldCalculated::class,
            Urbit_Productfeed_Fields_FieldDB::class,
            Urbit_Productfeed_Fields_FieldAttribute::class,
            Urbit_Productfeed_Fields_FieldFeature::class,
        ] as $cls) {
            $prefix = $cls::getPrefix();

            if (preg_match("/^{$prefix}/", $name)) {
                return $cls;
            }
        }

        return false;
    }

    /**
     * @param array $inputOptions
     * @return array
     */
    protected function _generateInputs($inputOptions)
    {
        $inputs = [];

        foreach ($inputOptions as $key => $name) {
            $inputs[] = [
                'type'    => 'select',
                'label'   => static::getModule()->l($name),
                'name'    => $key,
                'options' => [
                    'query' => $this->getOptions(),
                    'id'    => 'id',
                    'name'  => 'name',
                ],
                'class'   => 'fixed-width-xxl',
            ];
        }

        return $inputs;
    }

    /**
     * @param $inputOptions
     * @return array
     */
    protected function _generateTextInputs($inputOptions)
    {
        $inputs = [];

        foreach ($inputOptions as $key => $name) {
            $inputs[] = [
                'type'  => 'text',
                'label' => static::getModule()->l($name),
                'name'  => $key,
                'class' => 'fixed-width-xxl',
            ];
        }

        return $inputs;
    }

    /**
     * @return Module
     */
    public static function getModule()
    {
        return Urbit_productfeed::getInstance();
    }
}
