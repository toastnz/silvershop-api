<?php

/**
 * Class ShopAPIConfig
 */
class ShopAPIConfig extends Object
{
    private static $api_endpoint = 'shop-api';

    public static function getApiUrl()
    {
        return Controller::join_links(Director::absoluteBaseURL(), self::config()->get('api_endpoint'), 'cart');
    }

    public function getSiteCurrency()
    {
        $currency = ShopConfig::get_site_currency();

        $this->extend('updateSiteCurrency', $currency);

        return $currency;
    }

    public function getSiteCurrencySymbol()
    {
        $symbol = '$';

        $this->extend('updateSiteCurrencySymbol', $symbol);

        return $symbol;
    }
}
