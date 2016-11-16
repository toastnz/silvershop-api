<?php

/**
 * Class ShopAPIProductExtension
 *
 * @property Product|ShopAPIProductExtension $owner
 */
class ShopAPIProductExtension extends DataExtension
{
    public function getApiEndpointUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'product', $this->owner->ID);
    }

    public function getAddUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'product', $this->owner->ID, 'add');
    }

    public function getAddVariationUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'product', $this->owner->ID, 'addVariation');
    }
}

/**
 * Class ShopAPIVariationExtensions
 *
 * @method Product Product()
 *
 * @property ProductVariation|ShopAPIVariationExtension|ProductVariationsExtension $owner
 */
class ShopAPIVariationExtension extends DataExtension
{
    public function AbsoluteLink()
    {
        if ($this->owner->Product() && $this->owner->Product()->exists()) {
            return $this->owner->Product()->AbsoluteLink();
        }

        return '';
    }
}

/**
 * Class ShopAPIOrderItemExtension
 *
 * @property ShopAPIOrderItemExtension|OrderItem $owner
 */
class ShopAPIOrderItemExtension extends DataExtension
{
    public function getAddOneUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'item', $this->owner->ID, 'addOne');
    }

    public function getRemoveOneUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'item', $this->owner->ID, 'removeOne');
    }

    public function getRemoveAllUrl()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'item', $this->owner->ID, 'removeAll');
    }
}
