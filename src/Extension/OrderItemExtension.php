<?php

namespace Toast\ShopAPI\Extension;

use SilverShop\Model\OrderItem;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use Toast\ShopAPI\Model\ShopAPIConfig;

/**
 * Class OrderItemExtension
 *
 * @property OrderItem $owner
 */
class OrderItemExtension extends DataExtension
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