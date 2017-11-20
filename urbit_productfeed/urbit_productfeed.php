<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/Model/Feed/FeedProduct.php';
require_once dirname(__FILE__) . '/Model/Feed/Fields/Factory.php';
require_once dirname(__FILE__) . '/Helper/UrbitHelperForm.php';

/**
 * Class Urbit_productfeed
 */
class Urbit_productfeed extends Module
{
    const NAME = 'urbit_productfeed';

    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Urbit_productfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbit_productfeed';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Urbit';
        $this->need_instance = 1;

        $this->fields = [
            'factory' => new Urbit_Productfeed_Fields_Factory(),
        ];

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

        include(dirname(__FILE__) . '/sql/install.php');

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

        include(dirname(__FILE__) . '/sql/uninstall.php');

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
        if (((bool)Tools::isSubmit('submitProductfeedModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new Urbit_Productfeed_UrbitHelperForm();

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

        $valueArray['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[]'] = json_decode(Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true);
        $valueArray['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null));
        $valueArray['URBIT_PRODUCTFEED_TAGS_IDS[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_TAGS_IDS', null));
        $valueArray['URBIT_PRODUCTFEED_FILTER_CATEGORIES[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_FILTER_CATEGORIES', null));

        $attributeTypes = $this->getAttributeTypes();

        $helper->tpl_vars = [
            'fields_value'    => $valueArray,
            'languages'       => $this->context->controller->getLanguages(),
            'id_language'     => $this->context->language->id,
            'attribute_types' => $attributeTypes,
        ];

        return $helper->generateForm($this->getProductFeedConfigForm());
    }

    /**
     * @return array
     */
    protected function getAttributeTypes()
    {
        return [
            [
                'name'  => 'String',
                'value' => 'string',
            ],
            [
                'name'  => 'Number',
                'value' => 'number',
            ],
            [
                'name'  => 'Boolean',
                'value' => 'boolean',
            ],
            [
                'name'  => 'Datetimerange',
                'value' => 'datetimerange',
            ],
            [
                'name'  => 'Float',
                'value' => 'float',
            ],
            [
                'name'  => 'Text',
                'value' => 'text',
            ],
            [
                'name'  => 'Time',
                'value' => 'time',
            ],
            [
                'name'  => 'URL',
                'value' => 'url',
            ],
        ];
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Live mode'),
                        'name'    => 'URBIT_PRODUCTFEED_LIVE_MODE',
                        'is_bool' => true,
                        'desc'    => $this->l('Use this module in live mode'),
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col'    => 3,
                        'type'   => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc'   => $this->l('Enter a valid email address'),
                        'name'   => 'URBIT_PRODUCTFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ],
                    [
                        'type'  => 'password',
                        'name'  => 'URBIT_PRODUCTFEED_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * return options for attributes selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getAttributesOptions($withNotSetted = false)
    {
        $optionsForAttributeSelect = [];

        if ($withNotSetted) {
            $optionsForAttributeSelect[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }


        $attributes = Attribute::getAttributes($this->context->language->id);
        foreach ($attributes as $attribute) {
            $optionsForAttributeSelect[] = [
                'id'   => $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            ];
        }

        return array_unique($optionsForAttributeSelect, SORT_REGULAR);
    }

    /**
     * return options for categories selects
     */
    protected function getCategoriesOptions()
    {
        $categories = Category::getNestedCategories(null, $this->context->language->id);

        $resultArray = [];

        foreach ($categories as $category) {
            $arr = [];
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
        $arr[] = [
            'id'   => $category['id_category'],
            'name' => $pref . $category['name'],
        ];

        if (array_key_exists('children', $category)) {
            foreach ($category['children'] as $child) {
                $arr = $this->getCategoryInfo($child, $arr, $pref . $category['name'] . ' / ');
            }
        }

        return $arr;
    }

    /**
     * return options for tags selects
     * @return array
     */
    protected function getTagsOptions()
    {
        $optionsForTagSelect = [];

        $tags = Tag::getMainTags($this->context->language->id);
        foreach ($tags as $tag) {
            $optionsForTagSelect[] = ['id' => $tag['name'], 'name' => $tag['name']];
        }

        return $optionsForTagSelect;
    }

    /**
     * @return array
     */
    protected function getCacheOptions()
    {
        return [
            [
                'id'   => 0.00000001,
                'name' => 'DISABLE CACHE',
            ],
            [
                'id'   => 1,
                'name' => 'Hourly',
            ],
            [
                'id'   => 24,
                'name' => 'Daily',
            ],
            [
                'id'   => 168,
                'name' => 'Weekly',
            ],
            [
                'id'   => 5040,
                'name' => 'Monthly',
            ],
        ];
    }

    /**
     * @param bool $withNotSetted
     * @return array
     */
    protected function getCountriesOptions($withNotSetted = false)
    {
        $optionsForTaxesSelect = [];

        if ($withNotSetted) {
            $optionsForTaxesSelect[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }


        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $optionsForTaxesSelect[] = ['id' => $country['id_country'], 'name' => $country['name']];
        }

        return $optionsForTaxesSelect;
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

        $fields_form = [];

        //Feed Cache
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Feed Cache'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Cache duration'),
                    'name'    => 'URBIT_PRODUCTFEED_CACHE_DURATION',
                    'options' => [
                        'query' => $optionsForCacheSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        // Product Filters
        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $this->l('Product Filters'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBIT_PRODUCTFEED_FILTER_CATEGORIES[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForCategorySelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
                [
                    'type'     => 'select',
                    'label'    => $this->l('Tags'),
                    'name'     => 'URBIT_PRODUCTFEED_TAGS_IDS[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForTagSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Minimal Stock'),
                    'name'  => 'URBIT_PRODUCTFEED_MINIMAL_STOCK',
                    'class' => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        //Product Dimentions
        $fields_form[2]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Product Dimentions'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => $this->fields['factory']->getInputs(),
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        //Product parameters
        $fields_form[3]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Product parameters'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Color'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_COLOR',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Size'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Gender'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_GENDER',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Material'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Pattern'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Age Group'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Condition'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Size Type'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Brands'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        //Prices
        $fields_form[5]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Prices'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => $this->fields['factory']->getPriceInputs(),
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        //Taxes
        $fields_form[6]['form'] = [
            'legend' => [
                'title' => $this->l('Taxes'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Country'),
                    'name'     => 'URBIT_PRODUCTFEED_TAX_COUNTRY',
                    'multiple' => false,
                    'options'  => [
                        'query' => $optionsForTaxes,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $fields_form[7]['form'] = [
            'legend' => [
                'title' => $this->l('Urbit attributes'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'urbit_additional_attributes',
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW',
                    'options' => [
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $fields_form[8]['form'] = [
            'legend' => [
                'title' => $this->l('Custom Inventory List'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => $this->fields['factory']->getInventoryListInputs(),
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array_merge([
            'URBIT_PRODUCTFEED_CACHE_DURATION'                     => Configuration::get('URBIT_PRODUCTFEED_CACHE_DURATION', null),
            'URBIT_PRODUCTFEED_TAX_COUNTRY'                        => Configuration::get('URBIT_PRODUCTFEED_TAX_COUNTRY', null),
            'URBIT_PRODUCTFEED_MINIMAL_STOCK'                      => Configuration::get('URBIT_PRODUCTFEED_MINIMAL_STOCK', null),
            'URBIT_PRODUCTFEED_DIMENSION_HEIGHT'                   => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_HEIGHT', null),
            'URBIT_PRODUCTFEED_DIMENSION_LENGTH'                   => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_LENGTH', null),
            'URBIT_PRODUCTFEED_DIMENSION_WIDTH'                    => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_WIDTH', null),
            'URBIT_PRODUCTFEED_DIMENSION_WEIGHT'                   => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_WEIGHT', null),
            'URBIT_PRODUCTFEED_DIMENSION_UNIT'                     => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_UNIT', null),
            'URBIT_PRODUCTFEED_WEIGHT_UNIT'                        => Configuration::get('URBIT_PRODUCTFEED_WEIGHT_UNIT', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_EAN'                      => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_EAN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_MPN'                      => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_MPN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_COLOR'                    => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_COLOR', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE'                     => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_GENDER'                   => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_GENDER', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL'                 => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN'                  => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS'                   => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE'     => explode(',', Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null)),
            'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW' => json_decode(Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true),
            'URBIT_PRODUCTFEED_FILTER_CATEGORIES'                  => explode(',', Configuration::get('URBIT_PRODUCTFEED_FILTER_CATEGORIES', null)),
            'URBIT_PRODUCTFEED_TAGS_IDS'                           => explode(',', Configuration::get('URBIT_PRODUCTFEED_TAGS_IDS', null)),
        ], $this->fields['factory']->getInputsConfig(),
            $this->fields['factory']->getPriceInputsConfig(),
            $this->fields['factory']->getInventoryListInputsConfig()
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (in_array($key, ['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW'])) {
                $value = Tools::getValue($key) ?: null;
                Configuration::updateValue($key, $value ? json_encode($value) : $value);

                continue;
            }

            if (in_array($key, ['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', 'URBIT_PRODUCTFEED_TAGS_IDS', 'URBIT_PRODUCTFEED_FILTER_CATEGORIES'])) {
                $value = Tools::getValue($key) ?: null;
                Configuration::updateValue($key, $value ? implode(',', $value) : null);
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    /**
     * return options for dimensions selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getDimensionsOptions($withNotSetted = false)
    {
        $optionsForDimensionsSelect = [];

        if ($withNotSetted) {
            $optionsForDimensionsSelect[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $optionsForDimensionsSelect[] = [
                'id'   => $feature['id_feature'],
                'name' => $feature['name'],
            ];
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
        $options = [];

        if ($withNotSetted) {
            $options[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }

        $attributes = Attribute::getAttributes($this->context->language->id);

        foreach ($attributes as $attribute) {
            $options[] = [
                'id'   => 'a' . $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            ];
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $options[] = [
                'id'   => 'f' . $feature['id_feature'],
                'name' => $feature['name'],
            ];
        }

        return array_unique($options, SORT_REGULAR);
    }
}
