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
 * Class UrbitProductfeedFieldsFieldDB
 */
class UrbitProductfeedFieldsFieldDB extends UrbitProductfeedFieldsFieldAbstract
{
    /**
     * @param UrbitProductfeedFeedProduct $product
     * @param string $name
     * @return mixed
     */
    public static function processAttribute(UrbitProductfeedFeedProduct $product, $name)
    {
        return $product->getProductAttribute(static::getNameWithoutPrefix($name));
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = array(
            array(
                'id' => 'none',
                'name' => static::getModule()->l('------ Db Fields ------')
            ),
        );

        foreach (Product::$definition['fields'] as $key => $field) {
            $options[] = array(
                'id' => static::getPrefix() . $key,
                'name' => static::getModule()->l($key)
            );
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
