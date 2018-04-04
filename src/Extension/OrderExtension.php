<?php

namespace Toast\ShopAPI\Extension;

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
