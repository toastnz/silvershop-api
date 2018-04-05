<?php

namespace Toast\ShopAPI\Model;

use Omnipay\Common\Currency;
use SilverShop\Model\Modifiers\OrderModifier;
use SilverStripe\Core\Config\Config;

/**
 * Class ModifierModel
 */
class ModifierModel extends ShopModelBase
{
    /** @var OrderModifier $item */
    protected $modifier;

    protected $modifier_id;
    protected $title;
    protected $price;
    protected $price_nice;

    protected static $fields = [
        'modifier_id',
        'title',
        'price',
        'price_nice',
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Currency $unitMoney
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->modifier = OrderModifier::get()->byID($id);

            if ($this->modifier->exists()) {
                // Set the initial properties
                $this->modifier_id = $this->modifier->ID;
                $this->title       = $this->modifier->TableTitle();

                // Set prices
                $unitValue   = $this->modifier->TableValue();
                $this->price = $unitValue;

                // Format
                $nicePrice = sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $unitValue);

                $this->extend('updateDisplayPrice', $nicePrice, $unitValue);

                $this->price_nice = $nicePrice;
            }
        }
    }
}
