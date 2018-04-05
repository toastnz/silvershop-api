<?php

namespace Toast\ShopAPI\Model;

use SilverShop\Model\Variation\Variation;
use SilverStripe\ORM\DataObject;

/**
 * Class VariationModel
 */
class VariationModel extends ShopModelBase
{
    /** @var Variation $variation */
    protected $variation;

    protected $id;
    protected $image;
    protected $title;
    protected $price;
    protected $price_nice;
    protected $sku;

    protected static $fields = [
        'id',
        'image',
        'title',
        'price',
        'price_nice',
        'sku'
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Variation $variation
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->variation = DataObject::get_by_id(Variation::class, $id);

            if ($this->variation->exists()) {
                $this->id         = $this->variation->ID;
                $this->title      = $this->variation->Title;
                $this->price_nice = $this->variation->dbObject('Price')->Nice();
                $this->price      = $this->variation->Price;
                $this->sku        = $this->variation->InternalItemID;
            }
        }
    }
}
