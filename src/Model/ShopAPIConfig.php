<?php

namespace Toast\ShopAPI\Model;

use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

/**
 * Class ShopAPIConfig
 */
class ShopAPIConfig
{
    use Extensible;
    use Injectable;
    use Configurable;

    private static $api_endpoint = 'shop-api';

    public static function getApiUrl()
    {
        return Controller::join_links(Director::absoluteBaseURL(), self::config()->get('api_endpoint'), 'cart');
    }

    public function getSiteCurrency()
    {
        $currency = ShopConfigExtension::get_site_currency();

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
