<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 *
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */

require_once dirname(__FILE__) . '/FieldAbstract.php';
require_once dirname(__FILE__) . '/FieldAttribute.php';
require_once dirname(__FILE__) . '/FieldFeature.php';
require_once dirname(__FILE__) . '/FieldCalculated.php';
require_once dirname(__FILE__) . '/FieldDB.php';

/**
 * Class UrbitProductfeedFieldsFactory
 */
class UrbitProductfeedFieldsFactory
{
    /**
     * @var array
     */
    protected $_inputs = array(
        'URBITPRODUCTFEED_ATTRIBUTE_NAME'        => 'Name',
        'URBITPRODUCTFEED_ATTRIBUTE_DESCRIPTION' => 'Description',
        'URBITPRODUCTFEED_ATTRIBUTE_ID'          => 'Id',
        'URBITPRODUCTFEED_ATTRIBUTE_GTIN'        => 'GTIN',
        'URBITPRODUCTFEED_ATTRIBUTE_MPN'         => 'MPN',

        'URBITPRODUCTFEED_DIMENSION_HEIGHT_VALUE' => 'Height Value',
        'URBITPRODUCTFEED_DIMENSION_HEIGHT_UNIT'  => 'Height Unit',

        'URBITPRODUCTFEED_DIMENSION_LENGTH_VALUE' => 'Length Value',
        'URBITPRODUCTFEED_DIMENSION_LENGTH_UNIT'  => 'Length Unit',

        'URBITPRODUCTFEED_DIMENSION_WIDTH_VALUE' => 'Width Value',
        'URBITPRODUCTFEED_DIMENSION_WIDTH_UNIT'  => 'Width Unit',

        'URBITPRODUCTFEED_DIMENSION_WEIGHT_VALUE' => 'Weight Value',
        'URBITPRODUCTFEED_DIMENSION_WEIGHT_UNIT'  => 'Weight Unit',
    );

    protected $_priceInputs = array(
        'URBITPRODUCTFEED_REGULAR_PRICE_CURRENCY' => 'Regular Price Currency',
        'URBITPRODUCTFEED_REGULAR_PRICE_VALUE'    => 'Regular Price Value',
        'URBITPRODUCTFEED_REGULAR_PRICE_VAT'      => 'Regular Price VAT',
        'URBITPRODUCTFEED_SALE_PRICE_CURRENCY'    => 'Sale Price Currency',
        'URBITPRODUCTFEED_SALE_PRICE_VALUE'       => 'Sale Price Value',
        'URBITPRODUCTFEED_SALE_PRICE_VAT'         => 'Sale Price VAT',
        'URBITPRODUCTFEED_PRICE_EFFECTIVE_DATE'   => 'Price effective date',
    );

    // protected $_inventoryListInputs = array(
    //     'URBITPRODUCTFEED_SCHEMA'           => 'Schema',
    //     'URBITPRODUCTFEED_CONTENT_LANGUAGE' => 'Content language',
    //     'URBITPRODUCTFEED_CONTENT_TYPE'     => 'Content type',
    //     'URBITPRODUCTFEED_CREATED_AT'       => 'Created at',
    //     'URBITPRODUCTFEED_UPDATED_AT'       => 'Updated at',
    //     'URBITPRODUCTFEED_TARGET_COUNTRY'   => 'Target countries (comma separated)',
    //     'URBITPRODUCTFEED_VERSION'          => 'Version',
    //     'URBITPRODUCTFEED_FEED_FORMAT'      => 'Feed format - encoding',
    // );

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
        $config = array();

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
        $config = array();

        foreach ($this->_priceInputs as $key => $name) {
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
            array(
              array(
                'id'   => 'empty',
                'name' => static::getModule()->l('------ None------'),
              )
            ),
            UrbitProductfeedFieldsFieldCalculated::getOptions(),
            UrbitProductfeedFieldsFieldDB::getOptions(),
            UrbitProductfeedFieldsFieldAttribute::getOptions(),
            UrbitProductfeedFieldsFieldFeature::getOptions(),
            array(
              array(
                'id'   => 'none',
                'name' => static::getModule()->l('------ None------'),
              )
            )
        );
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public static function getFieldClassByFieldName($name)
    {
        foreach (array(
            UrbitProductfeedFieldsFieldCalculated::class,
            UrbitProductfeedFieldsFieldDB::class,
            UrbitProductfeedFieldsFieldAttribute::class,
            UrbitProductfeedFieldsFieldFeature::class,
          ) as $cls) {
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
        $inputs = array();

        foreach ($inputOptions as $key => $name) {
            $inputs[] = array(
                'type'    => 'select',
                'label'   => static::getModule()->l($name),
                'name'    => $key,
                'options' => array(
                    'query' => $this->getOptions(),
                    'id'    => 'id',
                    'name'  => 'name',
                ),
                'class'   => 'fixed-width-xxl',
            );
        }

        return $inputs;
    }

    /**
     * @param $inputOptions
     * @return array
     */
    protected function _generateTextInputs($inputOptions)
    {
        $inputs = array();

        foreach ($inputOptions as $key => $name) {
            $inputs[] = array(
                'type'  => 'text',
                'label' => static::getModule()->l($name),
                'name'  => $key,
                'class' => 'fixed-width-xxl',
            );
        }

        return $inputs;
    }

    /**
     * @return Module
     */
    public static function getModule()
    {
        return UrbitProductfeed::getInstance();
    }
}
