<?php

namespace Toast\ShopAPI\Extension;

use SilverStripe\ORM\DataExtension;
use Toast\ShopAPI\Model\ShopAPIConfig;

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
