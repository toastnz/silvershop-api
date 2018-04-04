<?php

namespace Toast\ShopAPI\Extension;

/**
 * Class ProductExtension
 *
 * @property Product $owner
 */
class ProductExtension extends DataExtension
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

