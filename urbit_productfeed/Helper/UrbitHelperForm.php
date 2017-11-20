<?php

/**
 * Class Urbit_Productfeed_UrbitHelperForm
 */
class Urbit_Productfeed_UrbitHelperForm extends HelperFormCore
{
    /**
     * Urbit_Productfeed_UrbitHelperForm constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->base_folder = dirname(__FILE__).'/../views/templates/admin/';
        $this->base_tpl = 'urbit_form.tpl';
    }
}
