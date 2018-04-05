<?php

namespace Toast\ShopAPI\Extension;

use SilverShop\Model\Order;
use SilverStripe\ORM\DataExtension;
use Toast\ShopAPI\Model\ShopAPIConfig;

/**
 * Class OrderExtension
 *
 * @property Order $owner
 */
class OrderExtension extends DataExtension
{
    private static $db = [
        'Hash' => 'Varchar(256)'
    ];

    public function getShopApiUrl()
    {
        return ShopAPIConfig::getApiUrl();
    }
}
