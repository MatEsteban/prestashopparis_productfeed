<?php

require_once dirname(__FILE__) . '/FieldAbstract.php';
require_once dirname(__FILE__) . '/Factory.php';

/**
 * Class Urbit_Productfeed_Fields_FieldCalculated
 */
class Urbit_Productfeed_Fields_FieldCalculated extends Urbit_Productfeed_Fields_FieldAbstract
{
    const FUNCTION_PREFIX = 'getProduct';

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @param string $name
     */
    public static function processAttribute(Urbit_Productfeed_FeedProduct $feedProduct, $name)
    {
        $static = new static();
        $funcName = static::FUNCTION_PREFIX . static::getNameWithoutPrefix($name);

        return $static->{$funcName}($feedProduct);
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return mixed
     */
    public static function processPrices(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $static = new static();
        $funcName = static::FUNCTION_PREFIX . "Prices";

        return $static->{$funcName}($feedProduct);
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'none',
            'name' => Urbit_productfeed::getInstance()->l('------ Calculated ------'),
        ];

        $methods = (new ReflectionClass(static::class))->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), static::FUNCTION_PREFIX) !== false) {
                $name = str_replace(static::FUNCTION_PREFIX, '', $method->getName());

                if (!empty($name)) {
                    $options[] = [
                        'id'   => static::getPrefix() . $name,
                        'name' => $name,
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'calc_';
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getProductId(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        if (empty($this->combination)) {
            return (string)$feedProduct->getProduct()->id;
        }

        $combination = $feedProduct->getCombination();
        $true = isset($combination['reference']) && $combination['reference'];
        $cid = $feedProduct->getCombId();

        return ($true ? $combination['reference'] : $feedProduct->getProduct()->id) . '-' . $cid;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return mixed
     */
    protected function getProductDescription(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return $feedProduct->getProduct()->description[Context::getContext()->language->id];
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getProductName(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $context = Context::getContext();
        $name = $feedProduct->getProduct()->name[$context->language->id];

        // combination
        if (!empty($feedProduct->getCombination())) {
            $attributeResume = $feedProduct->getProduct()->getAttributesResume($context->language->id);
            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $feedProduct->getCombId()) {
                    foreach ($feedProduct->getProduct()->getAttributeCombinationsById($attributesSet['id_product_attribute'], $context->language->id) as $attribute) {
                        $name = $name . ' ' . $attribute['attribute_name'];
                    }
                    break;
                }
            }
        }

        return $name;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return mixed|string
     */
    protected function getRegularCurrency(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_REGULAR_PRICE_CURRENCY') ?
            Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_REGULAR_PRICE_CURRENCY') :
            $this->getProductCurrency($feedProduct)
        ;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return mixed|string
     */
    protected function getSaleCurrency(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_SALE_PRICE_CURRENCY') ?
            Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_SALE_PRICE_CURRENCY') :
            $this->getProductCurrency($feedProduct)
        ;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getRegularPrice(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_REGULAR_PRICE') ?
            Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_REGULAR_PRICE') :
            $this->getProductRegularPrice($feedProduct)
        ;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getSalePrice(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_SALE_PRICE') ?
            Urbit_Productfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_PRODUCTFEED_SALE_PRICE') :
            $this->getProductSalePrice($feedProduct)
        ;
    }

    /**
     * @param $feedProduct
     * @return null
     */
    protected function getProductTaxRate($feedProduct)
    {
        $product = $feedProduct->getProduct();

        $taxCountry = Configuration::get('URBIT_PRODUCTFEED_TAX_COUNTRY');

        $taxRate = null;
        $defaultCountryTax = null;

        $groupId = $product->getIdTaxRulesGroup();
        $rules = TaxRule::getTaxRulesByGroupId($feedProduct->getContext()->language->id, $groupId);

        foreach ($rules as $rule) {
            if ($rule['id_country'] == $taxCountry) {
                $taxRate = $rule['rate'];
            }
        }

        return $taxRate;
    }


    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @param bool $useReduction
     * @return string
     */
    protected function getPrice(Urbit_Productfeed_FeedProduct $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;

        $price = Product::getPriceStatic($feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null), 6, null, false, $useReduction);
        $priceWithTax = ($taxRate) ? $price + ($price * ($taxRate / 100)) : $price;

        return number_format($priceWithTax, 2, '.', '');
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @param $taxRate
     * @param bool $useReduction
     * @return array
     */
    protected function getSaleDate(Urbit_Productfeed_FeedProduct $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $sp = null;

        Product::getPriceStatic(
            $feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null),
            6, null, false, $useReduction, null, null, null, null, null, $sp
        );

        return [
            'from' => $sp['from'],
            'to'   => $sp['to'],
        ];
    }

    /**
     * @param $salePriceDateArray
     * @return null|string
     */
    protected function formattedSalePriceDate($salePriceDateArray)
    {
        if ($salePriceDateArray['from'] != '0000-00-00 00:00:00' && $salePriceDateArray['to'] != '0000-00-00 00:00:00') {

            $tz = Configuration::get('PS_TIMEZONE');
            $dtFrom = new DateTime($salePriceDateArray['from'], new DateTimeZone($tz));
            $dtTo = new DateTime($salePriceDateArray['to'], new DateTimeZone($tz));

            return $dtFrom->format('Y-m-d\TH:iO') . '/' . $dtTo->format('Y-m-d\TH:iO');
        }

        return null;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return array
     */
    protected function getProductPrice(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        //////////////////////////
        // Why it's commented ? //
        /////////////////////////

        /*$product = $feedProduct->getProduct();

        $taxCountry = Configuration::get('URBIT_PRODUCTFEED_TAX_COUNTRY');

        $taxRate = null;
        $defaultCountryTax = null;
        $countryId = $feedProduct->getContext()->country->id;

        $groupId = $product->getIdTaxRulesGroup();
        $rules = TaxRule::getTaxRulesByGroupId($feedProduct->getContext()->language->id, $groupId);

        foreach ($rules as $rule) {
            if ($rule['id_country'] == $taxCountry) {
                $taxRate = $rule['rate'];
            }

            if ($rule['id_country'] == $countryId) {
                $defaultCountryTax = $rule['rate'];
            }
        }

        $regularPrice = $this->getRegularPrice($feedProduct);
        $salePrice = $this->getSalePrice($feedProduct);

        if ($taxRate) { //tax rate for country in module config
            $prices = [
                [
                    "currency" => $this->getRegularCurrency($feedProduct),
                    "value"    => $regularPrice,
                    "type"     => "regular",
                    "vat"      => $taxRate * 100,
                ],
            ];

            if ($regularPrice !== $salePrice) {
                $prices[] = [
                    "currency" => $this->getSaleCurrency($feedProduct),
                    "value"    => $salePrice,
                    "type"     => "sale",
                    "vat"      => $taxRate * 100,
                ];
            }
        } elseif ($defaultCountryTax) { //if tax rule has a shop's country
            $prices = [
                [
                    "currency" => $this->getRegularCurrency($feedProduct),
                    "value"    => $regularPrice,
                    "type"     => "regular",
                    "vat"      => $defaultCountryTax * 100,
                ],
            ];

            if ($regularPrice !== $salePrice) {
                $prices[] = [
                    "currency" => $this->getSaleCurrency($feedProduct),
                    "value"    => $salePrice,
                    "type"     => "sale",
                    "vat"      => $defaultCountryTax * 100,
                ];
            }
        } else { //without tax
            $prices = [
                [
                    "currency" => $this->getRegularCurrency($feedProduct),
                    "value"    => $regularPrice,
                    "type"     => "regular",
                ],
            ];

            if ($regularPrice !== $salePrice) {
                $prices[] = [
                    "currency" => $this->getSaleCurrency($feedProduct),
                    "value"    => $salePrice,
                    "type"     => "sale",
                ];
            }
        }

        return $prices;*/
    }

    /**
     * @param $feedProduct
     * @return string
     */
    protected function getProductRegularPrice($feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, false);
    }

    /**
     * @param $feedProduct
     * @return string
     */
    protected function getProductSalePrice($feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, true);
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return null|string
     */
    protected function getProductPriceEffectiveDate(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        $date = $this->getSaleDate($feedProduct, $taxRate, true);

        return $this->formattedSalePriceDate($date);
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return array
     */
    protected function getProductCategories(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $categories = [];

        $categoriesInfo = Product::getProductCategoriesFull($product->id);

        foreach ($categoriesInfo as $category) {
            $allCategories = Category::getCategories();
            $parentId = null;

            foreach ($allCategories as $allCategory) {
                foreach ($allCategory as $childCategory) {
                    if ($childCategory['infos']['id_category'] == $category['id_category']) {
                        $parentId = $childCategory['infos']['id_parent'];
                        break;
                    }
                }
            }

            $categories[] = [
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            ];
        }

        return $categories;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return array
     */
    protected function getProductImages(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $linkRewrite = $product->link_rewrite;

        $additional_images = [];
        $image = null;
        $coverImageId = null;

        // combination
        if (!empty($this->combination)) {
            $combinationImagesIds = $product->getCombinationImages($feedProduct->getContext()->language->id);

            if (isset($combinationImagesIds[$feedProduct->getCombId()])) {
                $combinationImagesIds = $combinationImagesIds[$feedProduct->getCombId()];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $combinationImagesId['id_image'], 'large_default');
                    }
                // if combination hasn't own image
                } else {
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
                }
            }
        // simple product
        } else {
            $coverImageId = Product::getCover($product->id)['id_image'];

            $additionalImages = Image::getImages($feedProduct->getContext()->language->id, $product->id);

            foreach ($additionalImages as $img) {
                $imageId = (new Image((int)$img['id_image']))->id;

                if ((int) $coverImageId == $imageId) {
                    continue;
                }

                $link = new Link;

                $additional_image_link = 'http://' . $link->getImageLink($linkRewrite[1], $imageId, 'large_default');
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
            }
        }

        return [
            'additional_image_links' => $additional_images,
            'image_link'             => $image,
        ];
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return array
     */
    protected function getProductAttributes(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();;
        $attributes = [];

        $additionalAttributes = json_decode(Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true);

        //check product features
        $FrontFeatures = $product->getFrontFeatures($feedProduct->getContext()->language->id);

        if (!empty($FrontFeatures)) {
            foreach ($FrontFeatures as $frontFeature) {
                foreach ($additionalAttributes as $attribute) {
                    if ((isset($attribute['name'])) && ('f_' . $frontFeature['id_feature'] == $attribute['name'])) {
                        $attributes[] = [
                            'name'  => $frontFeature['name'],
                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                            'unit'  => (isset($attribute['unit']) && $attribute['unit'] != "") ? $attribute['unit'] : null,
                            'value' => $frontFeature['value'],
                        ];
                    }
                }
            }
        }

        //check product attributes
        $attributeCombinations = $product->getAttributeCombinations($feedProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                foreach ($additionalAttributes as $attribute) {
                    if ((isset($attribute['name'])) && ('a_' . $attributeCombination['id_attribute_group'] == $attribute['name']) && $attributeCombination['id_product_attribute'] == $this->combId) {
                        $attributes[] = [
                            'name'  => $attributeCombination['group_name'],
                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                            'value' => $attributeCombination['attribute_name'],
                        ];
                    }
                }
            }
        }

        if (!empty($attributes)) {
            return $attributes;
        }

        return [];
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getProductNameOld(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $name = $product->name[$feedProduct->getContext()->language->id];

        // combination
        if (!empty($this->combination)) {
            $attributeResume = $product->getAttributesResume($feedProduct->getContext()->language->id);
            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $feedProduct->getCombId()) {
                    foreach ($product->getAttributeCombinationsById($attributesSet['id_product_attribute'], $feedProduct->getContext()->language->id) as $attribute) {
                        $name .= ' ' . $attribute['attribute_name'];
                    }

                    break;
                }
            }
        }

        return $name;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return array
     */
    protected function getProductBrands(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $brands = [];

        if ($product->id_manufacturer != "0") {
            $brands[] = [
                'name' => Manufacturer::getNameById($product->id_manufacturer),
            ];
        }

        return $brands;
    }

    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @return string
     */
    protected function getProductCurrency(Urbit_Productfeed_FeedProduct $feedProduct)
    {
        return Context::getContext()->currency->iso_code;
    }
}
