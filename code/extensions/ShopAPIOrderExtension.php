<?php

/**
 * Class ShopAPIOrderExtension
 *
 * @property ShopAPIOrderExtension|Order $owner
 */
class ShopAPIOrderExtension extends DataExtension
{
    private static $db = [
        'Hash' => 'Varchar(256)'
    ];
}
