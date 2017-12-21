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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/Model/Feed/FeedProduct.php';
require_once dirname(__FILE__) . '/Model/Feed/Fields/Factory.php';
require_once dirname(__FILE__) . '/Helper/UrbitHelperForm.php';

/**
 * Class UrbitProductfeed
 */
class UrbitProductfeed extends Module
{
    const NAME = 'urbitproductfeed';

    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * UrbitProductfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbitproductfeed';
        $this->tab = 'administration';
        $this->version = '1.0.3.5';
        $this->author = 'Urbit';
        $this->module_key = 'a28ee08818efc46aecb78bc6ef2c9b3c';
        $this->need_instance = 1;
        $this->controllers = 'FeedModule';

        $this->fields = array(
            'factory' => new UrbitProductfeedFieldsFactory(),
        );

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Urbit Product Feed');
        $this->description = $this->l('Urbit Product Feed Module');
    }

    /**
     * @return Module
     */
    public static function getInstance()
    {
        return Module::getInstanceByName(static::NAME);
    }

    /**
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue('PRODUCTFEED_LIVE_MODE', false);

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader');
    }

    /**
     * @return mixed
     */
    public function uninstall()
    {
        Configuration::deleteByName('PRODUCTFEED_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output = '';
        $this->context->smarty->assign('active', 'intro');

        //link to controller (for ajax call)
        $this->context->smarty->assign('controllerlink', $this->context->link->getModuleLink('urbitproductfeed', 'feed', array()));

        if (((bool)Tools::isSubmit('submitProductfeedModule')) == true) {
            $output = $this->postProcess();
            $this->context->smarty->assign('active', 'account');
        }

        $config = $this->renderForm();
        $this->context->smarty->assign(
            array(
                'config' => $config,
                'urbitproductfeed_img_path'  => $this->_path.'views/img/',
              )
        );

        return $output . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }


    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new UrbitProductfeedUrbitHelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductfeedModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $valueArray = $this->getConfigFormValues();

        $valueArray['URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[]'] = json_decode(
            $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW'),
            true
        );
        $valueArray['URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE[]'] = explode(
            ',',
            $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE')
        );
        $valueArray['URBITPRODUCTFEED_TAGS_IDS[]'] = explode(
            ',',
            $this->getConfigValue('URBITPRODUCTFEED_TAGS_IDS')
        );
        $valueArray['URBITPRODUCTFEED_FILTER_CATEGORIES[]'] = explode(
            ',',
            $this->getConfigValue('URBITPRODUCTFEED_FILTER_CATEGORIES')
        );
        $valueArray['URBITPRODUCTFEED_FILTER_PRODUCT_ID[]'] = explode(
            ',',
            $this->getConfigValue('URBITPRODUCTFEED_FILTER_PRODUCT_ID')
        );

        $tokenInConfig = $this->getConfigValue('URBITPRODUCTFEED_FEED_TOKEN');

        if (!$tokenInConfig) {
            $newToken = $this->generateFeedToken();
            $valueArray['URBITPRODUCTFEED_FEED_TOKEN'] = $newToken;
            $this->updateConfigValue('URBITPRODUCTFEED_FEED_TOKEN', $newToken);
        } else {
            $valueArray['URBITPRODUCTFEED_FEED_TOKEN'] = $tokenInConfig;
        }

        $attributeTypes = $this->getAttributeTypes();

        $helper->tpl_vars = array(
            'fields_value'    => $valueArray,
            'languages'       => $this->context->controller->getLanguages(),
            'id_language'     => $this->context->language->id,
            'attribute_types' => $attributeTypes,
        );

        return $helper->generateForm($this->getProductFeedConfigForm());
    }

    /**
     * Return attribute types for Additional attributes's type selectbox
     * @return array
     */
    protected function getAttributeTypes()
    {
        return array(
            array(

                'name'  => 'String',
                'value' => 'string',
            ),
            array(
                'name'  => 'Number',
                'value' => 'number',
            ),
            array(
                'name'  => 'Boolean',
                'value' => 'boolean',
            ),
            array(
                'name'  => 'Datetimerange',
                'value' => 'datetimerange',
            ),
            array(
                'name'  => 'Float',
                'value' => 'float',
            ),
            array(
                'name'  => 'Text',
                'value' => 'text',
            ),
            array(
                'name'  => 'Time',
                'value' => 'time',
            ),
            array(
                'name'  => 'URL',
                'value' => 'url',
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Live mode'),
                        'name'    => 'URBITPRODUCTFEED_LIVE_MODE',
                        'is_bool' => true,
                        'desc'    => $this->l('Use this module in live mode'),
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col'    => 3,
                        'type'   => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc'   => $this->l('Enter a valid email address'),
                        'name'   => 'URBITPRODUCTFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ),
                    array(
                        'type'  => 'password',
                        'name'  => 'URBITPRODUCTFEED_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get value from ps_configuration for this key
     * If multistore enable => get config value only for current store
     * @param $key
     * @return string
     */
    protected function getConfigValue($key)
    {
        return (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()) ?
            Configuration::get($key, null, null, $this->context->shop->id) :
            Configuration::get($key, null);
    }

    /**
     * Update ps_configuration value for this key
     * If multistore enable => update config value only for current store
     * @param $key
     * @param $value
     */
    protected function updateConfigValue($key, $value)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()) {
            Configuration::updateValue($key, $value, false, null, $this->context->shop->id);
        } else {
            Configuration::updateValue($key, $value);
        }
    }

    /**
     * return options for attributes selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getAttributesOptions($withNotSetted = false)
    {
        $optionsForAttributeSelect = array();

        if ($withNotSetted) {
            $optionsForAttributeSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }


        $attributes = Attribute::getAttributes($this->context->language->id);
        foreach ($attributes as $attribute) {
            $optionsForAttributeSelect[] = array(
                'id'   => $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            );
        }

        return array_unique($optionsForAttributeSelect, SORT_REGULAR);
    }

    /**
     * return options for categories selects
     */
    protected function getCategoriesOptions()
    {
        $categories = Category::getNestedCategories(null, $this->context->language->id);

        $resultArray = array();

        foreach ($categories as $category) {
            $arr = array();
            $resultArray = array_merge($resultArray, $this->getCategoryInfo($category, $arr, ''));
        }

        return $resultArray;
    }

    /**
     * @param $category
     * @param $arr
     * @param $pref
     * @return array
     */
    protected function getCategoryInfo($category, $arr, $pref)
    {
        $arr[] = array(
            'id'   => $category['id_category'],
            'name' => $pref . $category['name'],
        );

        if (array_key_exists('children', $category)) {
            foreach ($category['children'] as $child) {
                $arr = $this->getCategoryInfo($child, $arr, $pref . $category['name'] . ' / ');
            }
        }

        return $arr;
    }

    /**
     * @param $withNotSetted
     * @return array
     */
    protected function getProductsOptions($withNotSetted = false)
    {
        $optionsForProductSelect = array();

        if ($withNotSetted) {
            $optionsForProductSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $products = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');

        foreach ($products as $product) {
            $optionsForProductSelect[] = array('id' => $product['id_product'], 'name' => $product['id_product'] . ' : ' . $product['name']);
        }

        return $optionsForProductSelect;
    }

    /**
     * return options for tags selects
     * @return array
     */
    protected function getTagsOptions()
    {
        $optionsForTagSelect = array();

        $tags = Tag::getMainTags($this->context->language->id);
        foreach ($tags as $tag) {
            $optionsForTagSelect[] = array('id' => $tag['name'], 'name' => $tag['name']);
        }

        return $optionsForTagSelect;
    }

    /**
     * @return array
     */
    protected function getCacheOptions()
    {
        return array(
            array(
                'id'   => 0.00000001,
                'name' => 'DISABLE CACHE',
            ),
            array(
                'id'   => 1,
                'name' => 'Hourly',
            ),
            array(
                'id'   => 24,
                'name' => 'Daily',
            ),
            array(
                'id'   => 168,
                'name' => 'Weekly',
            ),
            array(
                'id'   => 5040,
                'name' => 'Monthly',
            ),
        );
    }

    /**
     * @param bool $withNotSetted
     * @return array
     */
    protected function getCountriesOptions($withNotSetted = false)
    {
        $optionsForTaxesSelect = array();

        if ($withNotSetted) {
            $optionsForTaxesSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $optionsForTaxesSelect[] = array('id' => $country['id_country'], 'name' => $country['name']);
        }

        return $optionsForTaxesSelect;
    }

    protected function generateFeedToken()
    {
        return version_compare(_PS_VERSION_, "1.7", "<") ?
            Tools::encrypt(mt_rand(0, PHP_INT_MAX - 1) . Tools::getToken(false)):
            Tools::hash(mt_rand(0, PHP_INT_MAX - 1) . Tools::getToken(false));
    }

    protected function getFeedTokenFromConfig()
    {
        return $this->getConfigValue('URBITPRODUCTFEED_FEED_TOKEN');
    }

    /**
     * @return array
     */
    protected function getProductFeedConfigForm()
    {
        $optionsForCategorySelect = $this->getCategoriesOptions();
        $optionsForTagSelect = $this->getTagsOptions();
        $optionsForCacheSelect = $this->getCacheOptions();
        $optionsForTaxes = $this->getCountriesOptions(true);

        $fields_form = array();

        //Feed Cache
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Feed Cache'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Cache duration'),
                    'name'    => 'URBITPRODUCTFEED_CACHE_DURATION',
                    'options' => array(
                        'query' => $optionsForCacheSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('The extension uses caching system to reduce a site load and speed up the plug-in during the generation of the feed, so feed is created and saved to file at specific time intervals. The refresh interval is specified on the  \'Cache duration \' drop-down list.'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Feed token'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'urbit_token',
                    'label'   => $this->l('Token'),
                    'name'    => 'URBITPRODUCTFEED_FEED_TOKEN',
                    'token'  => $this->getFeedTokenFromConfig(),
                    'class'   => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Product Filters
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Filters'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBITPRODUCTFEED_FILTER_CATEGORIES[]',
                    'id'       => 'urbitproductfeed-filter-categories',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForCategorySelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' => $this->l('Filter by Categories using this multiselect lists where you can select several options (by using Ctrl+filter\'s name).
If there is no selected filter parameter (categories or tags or the number of products for filtering is zero), the system skips the filtering by this parameter.'),
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Tags'),
                    'name'     => 'URBITPRODUCTFEED_TAGS_IDS[]',
                    'id'       => 'urbitproductfeed-filter-tags',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForTagSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' =>     $this->l('Filter by Tags using this multiselect lists where you can select several options (by using Ctrl+filter\'s name).
                     If there is no selected filter parameter (categories or tags or the number of products for filtering is zero), the system skips the filtering by this parameter.'),
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Minimal Stock'),
                    'name'  => 'URBITPRODUCTFEED_MINIMAL_STOCK',
                    'id'    => 'urbitproductfeed-filter-minimal-stock',
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Filter your product export by stock amount'),
                ),
                array(
                    'type'    => 'urbit_product_id_filter',
                    'label'   => $this->l('Product ID'),
                    'name'    => 'URBITPRODUCTFEED_FILTER_PRODUCT_ID_NEW',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your Product ID Filter'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Product Dimentions
        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Product Dimentions'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Product parameters
        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Product parameters'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Color'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_COLOR',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Color field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Size'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_SIZE',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Size field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Gender'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_GENDER',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Gender field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Material'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_MATERIAL',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Material field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Pattern'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_PATTERN',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Pattern field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Age Group'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Age Group field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Condition'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_CONDITION',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Condition field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Size Type'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Size Type field in the drop down menu'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Brands'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_BRANDS',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your product Brands field in the drop down menu'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Prices
        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Prices'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getPriceInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Taxes
        $fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->l('Taxes'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Country'),
                    'name'     => 'URBITPRODUCTFEED_TAX_COUNTRY',
                    'multiple' => false,
                    'options'  => array(
                        'query' => $optionsForTaxes,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' => $this->l('Select your Country in the drop down menu'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fields_form[7]['form'] = array(
            'legend' => array(
                'title' => $this->l('Urbit attributes'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'urbit_additional_attributes',
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Create your Additional attributes'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array_merge(
            array(
                'URBITPRODUCTFEED_CACHE_DURATION'                     => $this->getConfigValue('URBITPRODUCTFEED_CACHE_DURATION'),
                'URBITPRODUCTFEED_TAX_COUNTRY'                        => $this->getConfigValue('URBITPRODUCTFEED_TAX_COUNTRY'),
                'URBITPRODUCTFEED_MINIMAL_STOCK'                      => $this->getConfigValue('URBITPRODUCTFEED_MINIMAL_STOCK') ? : 0,
                'URBITPRODUCTFEED_DIMENSION_HEIGHT'                   => $this->getConfigValue('URBITPRODUCTFEED_DIMENSION_HEIGHT'),
                'URBITPRODUCTFEED_DIMENSION_LENGTH'                   => $this->getConfigValue('URBITPRODUCTFEED_DIMENSION_LENGTH'),
                'URBITPRODUCTFEED_DIMENSION_WIDTH'                    => $this->getConfigValue('URBITPRODUCTFEED_DIMENSION_WIDTH'),
                'URBITPRODUCTFEED_DIMENSION_WEIGHT'                   => $this->getConfigValue('URBITPRODUCTFEED_DIMENSION_WEIGHT'),
                'URBITPRODUCTFEED_DIMENSION_UNIT'                     => $this->getConfigValue('URBITPRODUCTFEED_DIMENSION_UNIT'),
                'URBITPRODUCTFEED_WEIGHT_UNIT'                        => $this->getConfigValue('URBITPRODUCTFEED_WEIGHT_UNIT'),
                'URBITPRODUCTFEED_ATTRIBUTE_EAN'                      => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_EAN'),
                'URBITPRODUCTFEED_ATTRIBUTE_MPN'                      => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_MPN'),
                'URBITPRODUCTFEED_ATTRIBUTE_COLOR'                    => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_COLOR'),
                'URBITPRODUCTFEED_ATTRIBUTE_SIZE'                     => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_SIZE'),
                'URBITPRODUCTFEED_ATTRIBUTE_GENDER'                   => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_GENDER'),
                'URBITPRODUCTFEED_ATTRIBUTE_MATERIAL'                 => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_MATERIAL'),
                'URBITPRODUCTFEED_ATTRIBUTE_PATTERN'                  => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_PATTERN'),
                'URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP'                => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP'),
                'URBITPRODUCTFEED_ATTRIBUTE_CONDITION'                => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_CONDITION'),
                'URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE'                => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE'),
                'URBITPRODUCTFEED_ATTRIBUTE_BRANDS'                   => $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_BRANDS'),
                'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE'     => explode(',', $this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE')),
                'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW' => json_decode($this->getConfigValue('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW'), true),
                'URBITPRODUCTFEED_FILTER_CATEGORIES'                  => explode(',', $this->getConfigValue('URBITPRODUCTFEED_FILTER_CATEGORIES')),
                'URBITPRODUCTFEED_TAGS_IDS'                           => explode(',', $this->getConfigValue('URBITPRODUCTFEED_TAGS_IDS')),
                'URBITPRODUCTFEED_FILTER_PRODUCT_ID'                  => $this->getConfigValue('URBITPRODUCTFEED_FILTER_PRODUCT_ID'),
                'URBITPRODUCTFEED_FEED_TOKEN'                         => $this->getConfigValue('URBITPRODUCTFEED_FEED_TOKEN'),
            ),
            $this->fields['factory']->getInputsConfig(),
            $this->fields['factory']->getPriceInputsConfig()
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (in_array($key, array('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW'))) {
                $value = Tools::getValue($key) ?: null;
                $this->updateConfigValue($key, $value ? json_encode($value) : $value);

                continue;
            }

            if (in_array($key, array('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', 'URBITPRODUCTFEED_TAGS_IDS', 'URBITPRODUCTFEED_FILTER_CATEGORIES', 'URBITPRODUCTFEED_FILTER_PRODUCT_ID'))) {
                $value = Tools::getValue($key) ?: null;
                $this->updateConfigValue($key, $value ? implode(',', $value) : 'none');
            } else {
                $this->updateConfigValue($key, Tools::getValue($key));
            }
        }

        if (!(bool)preg_match('/^[0-9]{0,13}$/', Tools::getValue('URBITPRODUCTFEED_MINIMAL_STOCK')))  {
            $this->context->controller->errors[] = $this->l('Add a Minimal stock');
        }

        if (Tools::getValue('URBITPRODUCTFEED_MINIMAL_STOCK') == null) {
            $this->context->controller->errors[] = $this->l('Unable to validate an empty field');
        }

        if (Tools::getValue('URBITPRODUCTFEED_FEED_TOKEN') == null) {
            $this->context->controller->errors[] = $this->l('Unable to validate an empty field');
        }

        if (empty($this->context->controller->errors)) {
                return $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * return options for dimensions selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getDimensionsOptions($withNotSetted = false)
    {
        $optionsForDimensionsSelect = array();

        if ($withNotSetted) {
            $optionsForDimensionsSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $optionsForDimensionsSelect[] = array(
                'id'   => $feature['id_feature'],
                'name' => $feature['name'],
            );
        }

        return array_unique($optionsForDimensionsSelect, SORT_REGULAR);
    }

    /**
     * return options for selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getFeaturesAndAttributesOptions($withNotSetted = false)
    {
        $options = array();

        if ($withNotSetted) {
            $options[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $attributes = Attribute::getAttributes($this->context->language->id);

        foreach ($attributes as $attribute) {
            $options[] = array(
                'id'   => 'a' . $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            );
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $options[] = array(
                'id'   => 'f' . $feature['id_feature'],
                'name' => $feature['name'],
            );
        }

        return array_unique($options, SORT_REGULAR);
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path . 'views/js/multiselect.min.js');
    }
}
