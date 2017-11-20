<?php

/**
 * Class Urbit_Productfeed_Fields_FieldAbstract
 */
abstract class Urbit_Productfeed_Fields_FieldAbstract
{
    /**
     * @return Module
     */
    public static function getModule()
    {
        return Urbit_productfeed::getInstance();
    }
}
