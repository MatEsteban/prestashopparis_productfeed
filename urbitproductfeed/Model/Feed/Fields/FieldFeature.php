<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
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
 * Class UrbitProductfeedFieldsFieldFeature
 */
class UrbitProductfeedFieldsFieldFeature extends UrbitProductfeedFieldsFieldAbstract
{
    /**
     * @param UrbitProductfeedFeedProduct $feedProduct
     * @param string $name
     * @return string
     */
    public static function processAttribute(UrbitProductfeedFeedProduct $feedProduct, $name)
    {
        $features = $feedProduct->getProduct()->getFeatures();
        $featureValues = array();

        $id = static::getNameWithoutPrefix($name);

        foreach ($features as $feature) {
            if ($feature['id_feature'] == $id) {
                foreach (FeatureValue::getFeatureValueLang($feature['id_feature_value']) as $featureValueLang) {
                    $featureValues[] = $featureValueLang['value'] ?: '';
                }
            }
        }

        return implode(', ', $featureValues);
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = array();

        $options[] = array(
            'id'   => 'none',
            'name' => '------ Features ------',
        );

        $features = Feature::getFeatures(Context::getContext()->language->id);

        foreach ($features as $feature) {
            $options[] = array(
                'id'   => static::getPrefix() . $feature['id_feature'],
                'name' => $feature['name'],
            );
        }

        return $options;
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'f_';
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }
}
