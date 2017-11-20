<?php

/**
 * Class Urbit_Productfeed_Fields_FieldAttribute
 */
class Urbit_Productfeed_Fields_FieldAttribute extends Urbit_Productfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @param string $name
     * @return array
     */
    static function processAttribute(Urbit_Productfeed_FeedProduct $feedProduct, $name)
    {
        $product = $feedProduct->getProduct();
        $attributes = [];

        $attributeCombinations = $product->getAttributeCombinations($feedProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                $attributes[] = [
                    'name'  => $attributeCombination['group_name'],
                    'type'  => 'string',
                    'value' => $attributeCombination['attribute_name'],
                ];
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = [];

        $options[] = [
            'id' => 'none',
            'name' => static::getModule()->l('------ Attributes ------')
        ];

        $attributes = Attribute::getAttributes(Context::getContext()->language->id);

        foreach ($attributes as $attribute) {
            $options[] = [
                'id'   => static::getPrefix() . $attribute['id_attribute_group'],
                'name' => static::getModule()->l($attribute['attribute_group']),
            ];
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
