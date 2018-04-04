<?php

namespace Toast\ShopAPI\Extension;

/**
 * Class SiteTreeExtension
 */
class SiteTreeExtension extends DataExtension
{
    public function getShopApiUrl()
    {
        return ShopAPIConfig::getApiUrl();
    }
}
