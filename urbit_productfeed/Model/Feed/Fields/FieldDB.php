<?php

/**
 * Class Urbit_Productfeed_Fields_FieldDB
 */
class Urbit_Productfeed_Fields_FieldDB extends Urbit_Productfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Productfeed_FeedProduct $product
     * @param string $name
     * @return mixed
     */
    public static function processAttribute(Urbit_Productfeed_FeedProduct $product, $name)
    {
        return $product->getProductAttribute(static::getNameWithoutPrefix($name));
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = [
            [
                'id' => 'none',
                'name' => static::getModule()->l('------ Db Fields ------')
            ],
        ];

        foreach (Product::$definition['fields'] as $key => $field) {
            $options[] = [
                'id' => static::getPrefix() . $key,
                'name' => static::getModule()->l($key)
            ];
        }

        return $options;
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
     * @return string
     */
    public static function getPrefix()
    {
        return 'db_';
    }
}
