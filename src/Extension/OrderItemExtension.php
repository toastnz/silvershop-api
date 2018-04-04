<?php

namespace Toast\ShopAPI\Extension;

/**
 * Class OrderItemExtension
 *
 * @property OrderItem $owner
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

    public function getSetQuantity()
    {
        return Controller::join_links(ShopAPIConfig::getApiUrl(), 'item', $this->owner->ID, 'setQuantity');
    }
}