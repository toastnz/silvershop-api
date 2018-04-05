<?php

use PHPUnit\Framework\Assert;
use SilverStripe\Dev\FunctionalTest;
use Toast\ShopAPI\Model\ShopAPIConfig;

/**
 * Class CartModelTest
 *
 * @mixin Assert
 */
class CartModelTest extends FunctionalTest
{
    public function testGetCart()
    {
        $endpoint = ShopAPIConfig::getApiUrl();

        $cart = $this->get($endpoint);

        $this->assertEquals(200, $cart->getStatusCode());
    }
}