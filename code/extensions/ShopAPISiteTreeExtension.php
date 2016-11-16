<?php

/**
 * Class ShopAPISiteTreeExtension
 */
class ShopAPISiteTreeExtension extends DataExtension
{
    public function getShopApiUrl()
    {
        return ShopAPIConfig::getApiUrl();
    }
}
