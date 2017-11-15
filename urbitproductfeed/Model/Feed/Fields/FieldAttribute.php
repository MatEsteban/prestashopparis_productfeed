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

/**
 * Class UrbitProductfeedFieldsFieldAttribute
 */
class UrbitProductfeedFieldsFieldAttribute extends UrbitProductfeedFieldsFieldAbstract
{
    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @param string $name
     * @return array
     */
    public static function processAttribute(UrbitProductfeedFeedProduct $feedProduct, $name)
    {
        $product = $feedProduct->getProduct();
        $attributes = array();

        $attributeCombinations = $product->getAttributeCombinations($feedProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                $attributes[] = array(
                    'name'  => $attributeCombination['group_name'],
                    'type'  => 'string',
                    'value' => $attributeCombination['attribute_name'],
                );
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = array();

        $options[] = array(
            'id' => 'none',
            'name' => static::getModule()->l('------ Attributes ------')
        );

        $attributes = Attribute::getAttributes(Context::getContext()->language->id);

        foreach ($attributes as $attribute) {
            $options[] = array(
                'id'   => static::getPrefix() . $attribute['id_attribute_group'],
                'name' => static::getModule()->l($attribute['attribute_group']),
            );
        }

        return array_unique($options, SORT_REGULAR);
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'a_';
    }
}
