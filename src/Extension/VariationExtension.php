<?php

namespace Toast\ShopAPI\Extension;

/**
 * Class VariationExtension
 *
 * @property ProductVariation $owner
 */
class VariationExtension extends DataExtension
{
    public function AbsoluteLink()
    {
        if ($this->owner->Product() && $this->owner->Product()->exists()) {
            return $this->owner->Product()->AbsoluteLink();
        }

        return '';
    }
}