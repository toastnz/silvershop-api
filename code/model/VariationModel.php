<?php

/**
 * Class VariationModel
 */
class VariationModel extends ShopModelBase
{
    /** @var ProductVariation $variation */
    protected $variation;

    protected $id;
    protected $image;
    protected $title;
    protected $price;
    protected $priceNice;
    protected $sku;

    protected static $fields = [
        'id',
        'image',
        'title',
        'price',
        'priceNice',
        'sku'
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var ProductVariation $variation
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->variation = DataObject::get_by_id('ProductVariation', $id);

            if ($this->variation->exists()) {
                $this->id        = $this->variation->ID;
                $this->title     = $this->variation->Title;
                $this->priceNice = $this->variation->dbObject('Price')->Nice();
                $this->price     = $this->variation->Price;
                $this->sku       = $this->variation->InternalItemID;
            }
        }
    }
}
