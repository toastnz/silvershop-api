<?php

namespace Toast\ShopAPI\Extension;

use SilverShop\Model\Variation\Variation;
use SilverStripe\ORM\DataExtension;

/**
 * Class VariationExtension
 *
 * @property Variation $owner
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