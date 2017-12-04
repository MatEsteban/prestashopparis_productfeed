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
require_once dirname(__FILE__) . '/Factory.php';

/**
 * Class UrbitProductfeedFieldsFieldCalculated
 */
class UrbitProductfeedFieldsFieldCalculated extends UrbitProductfeedFieldsFieldAbstract
{
    const FUNCTION_PREFIX = 'getProduct';

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @param string $name
     */
    public static function processAttribute(UrbitProductfeedFeedProduct $feedProduct, $name)
    {
        $static = new static();
        $funcName = static::FUNCTION_PREFIX . static::getNameWithoutPrefix($name);

        return $static->{$funcName}($feedProduct);
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return mixed
     */
    public static function processPrices(UrbitProductfeedFeedProduct $feedProduct)
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
        $options = array();

        $options[] = array(
            'id'   => 'none',
            'name' => UrbitProductfeed::getInstance()->l('------ Calculated ------'),
        );

        $methods = (new ReflectionClass(static::class))->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), static::FUNCTION_PREFIX) !== false) {
                $name = str_replace(static::FUNCTION_PREFIX, '', $method->getName());

                if (!empty($name)) {
                    $options[] = array(
                        'id'   => static::getPrefix() . $name,
                        'name' => $name,
                    );
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getProductId(UrbitProductfeedFeedProduct $feedProduct)
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return mixed
     */
    protected function getProductDescription(UrbitProductfeedFeedProduct $feedProduct)
    {
        return $feedProduct->getProduct()->description[Context::getContext()->language->id];
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getProductName(UrbitProductfeedFeedProduct $feedProduct)
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return mixed|string
     */
    protected function getRegularCurrency(UrbitProductfeedFeedProduct $feedProduct)
    {
        return UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_REGULAR_PRICE_CURRENCY') ?
            UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_REGULAR_PRICE_CURRENCY') :
            $this->getProductCurrency($feedProduct)
        ;
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return mixed|string
     */
    protected function getSaleCurrency(UrbitProductfeedFeedProduct $feedProduct)
    {
        return UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_SALE_PRICE_CURRENCY') ?
            UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_SALE_PRICE_CURRENCY') :
            $this->getProductCurrency($feedProduct)
        ;
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getRegularPrice(UrbitProductfeedFeedProduct $feedProduct)
    {
        return UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_REGULAR_PRICE') ?
            UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_REGULAR_PRICE') :
            $this->getProductRegularPrice($feedProduct)
        ;
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getSalePrice(UrbitProductfeedFeedProduct $feedProduct)
    {
        return UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_SALE_PRICE') ?
              UrbitProductfeedFieldsFactory::processAttribute($feedProduct, 'URBITPRODUCTFEED_SALE_PRICE') :
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

        $taxCountry = Configuration::get('URBITPRODUCTFEED_TAX_COUNTRY');
        $shopCountryId = $feedProduct->getContext()->country->id;

        $taxRate = null;
        $defaultCountryTax = null;

        $groupId = $product->getIdTaxRulesGroup();
        $rules = TaxRule::getTaxRulesByGroupId($feedProduct->getContext()->language->id, $groupId);

        foreach ($rules as $rule) {
            if ($rule['id_country'] == $taxCountry) {
                $taxRate = $rule['rate'];
            }

            if ($rule['id_country'] == $shopCountryId) {
                $defaultCountryTax = $rule['rate'];
            }
        }

        //IMS format price Urb-it
        return $taxRate ? $taxRate * 100 : ($defaultCountryTax ? $defaultCountryTax * 100 : 0);
    }


    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @param bool $useReduction
     * @return string
     */
    protected function getPrice(UrbitProductfeedFeedProduct $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;

        $price = Product::getPriceStatic($feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null), 6, null, false, $useReduction);
        $priceWithTax = ($taxRate) ? $price + ($price * ($taxRate / 10000)) : $price;

        return number_format($priceWithTax, 2, '.', '');

    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @param $taxRate
     * @param bool $useReduction
     * @return array
     */
    protected function getSaleDate(UrbitProductfeedFeedProduct $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $sp = null;

        Product::getPriceStatic(
            $feedProduct->getProduct()->id,
            $useTax,
            ($feedProduct->getCombId() ? $feedProduct->getCombId() : null),
            6,
            null,
            false,
            $useReduction,
            null,
            null,
            null,
            null,
            null,
            $sp
        );

        return array(
            'from' => $sp['from'],
            'to'   => $sp['to'],
        );
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return array
     */
    protected function getProductPrice(UrbitProductfeedFeedProduct $feedProduct)
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return null|string
     */
    protected function getProductPriceEffectiveDate(UrbitProductfeedFeedProduct $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        $date = $this->getSaleDate($feedProduct, $taxRate, true);

        return $this->formattedSalePriceDate($date);
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return array
     */
    protected function getProductCategories(UrbitProductfeedFeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $categories = array();

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

            $categories[] = array(
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            );
        }

        return $categories;
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return array
     */
    protected function getProductImages(UrbitProductfeedFeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $linkRewrite = $product->link_rewrite;

        $additional_images = array();
        $image = null;
        $coverImageId = null;

        // combination
        if (!empty($this->combination)) {
            $combinationImagesIds = $product->getCombinationImages($feedProduct->getContext()->language->id);

            if (isset($combinationImagesIds[$feedProduct->getCombId()])) {
                $combinationImagesIds = $combinationImagesIds[$feedProduct->getCombId()];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $combinationImagesId['id_image'], ImageType::getFormattedName('large'));
                    }
                // if combination hasn't own image
                } else {
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, ImageType::getFormattedName('large'));
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

                $additional_image_link = 'http://' . $link->getImageLink($linkRewrite[1], $imageId, ImageType::getFormattedName('large'));
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, ImageType::getFormattedName('large'));
            }
        }

        return array(
            'additional_image_links' => $additional_images,
            'image_link'             => $image,
        );
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return array
     */
    protected function getProductAttributes(UrbitProductfeedFeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $attributes = array();

        $additionalAttributes = json_decode(Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true);

        //check product features
        $FrontFeatures = $product->getFrontFeatures($feedProduct->getContext()->language->id);

        if (!empty($FrontFeatures)) {
            foreach ($FrontFeatures as $frontFeature) {
                foreach ($additionalAttributes as $attribute) {
                    if ((isset($attribute['name'])) && ('f_' . $frontFeature['id_feature'] == $attribute['name'])) {
                        $attributes[] = array(
                            'name'  => $frontFeature['name'],
                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                            'unit'  => (isset($attribute['unit']) && $attribute['unit'] != "") ? $attribute['unit'] : null,
                            'value' => $frontFeature['value'],
                        );
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
                        $attributes[] = array(
                            'name'  => $attributeCombination['group_name'],
                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                            'value' => $attributeCombination['attribute_name'],
                        );
                    }
                }
            }
        }

        if (!empty($attributes)) {
            return $attributes;
        }

        return array();
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getProductNameOld(UrbitProductfeedFeedProduct $feedProduct)
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
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return array
     */
    protected function getProductBrands(UrbitProductfeedFeedProduct $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $brands = array();

        if ($product->id_manufacturer != "0") {
            $brands[] = array(
                'name' => Manufacturer::getNameById($product->id_manufacturer),
            );
        }

        return $brands;
    }

    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @return string
     */
    protected function getProductCurrency(UrbitProductfeedFeedProduct $feedProduct)
    {
        return Context::getContext()->currency->iso_code;
    }
}
