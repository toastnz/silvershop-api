<?php

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