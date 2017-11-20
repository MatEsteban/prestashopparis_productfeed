<?php

/**
 * Class Urbit_Productfeed_Fields_FieldFeature
 */
class Urbit_Productfeed_Fields_FieldFeature extends Urbit_Productfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Productfeed_FeedProduct $feedProduct
     * @param string $name
     * @return string
     */
    static function processAttribute(Urbit_Productfeed_FeedProduct $feedProduct, $name)
    {
        $features = $feedProduct->getProduct()->getFeatures();
        $featureValues = [];

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
    static function getOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'none',
            'name' => '------ Features ------',
        ];

        $features = Feature::getFeatures(Context::getContext()->language->id);

        foreach ($features as $feature) {
            $options[] = [
                'id'   => static::getPrefix() . $feature['id_feature'],
                'name' => $feature['name'],
            ];
        }

        return $options;
    }

    /**
     * @return string
     */
    static function getPrefix()
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
