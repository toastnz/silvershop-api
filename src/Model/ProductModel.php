<?php

namespace Toast\ShopAPI\Model;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;

/**
 * Class ProductModel
 */
class ProductModel extends ShopModelBase
{
    /** @var Product|Variation $buyable */
    protected $buyable;

    protected $endpoint;

    protected $id;
    protected $product_image;
    protected $title;
    protected $link;
    protected $price;
    protected $price_nice;
    protected $add_link;
    protected $sku;
    protected $categories = [];
    protected $variations = [];

    protected static $fields = [
        'id',
        'title',
        'link',
        'price',
        'price_nice',
        'sku',
        'add_link',
        'product_image',
        'categories',
        'variations'
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Product|Variation $buyable
         * @var Variation         $variation
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->buyable = DataObject::get_by_id(Product::class, $id);

            if ($this->buyable && $this->buyable->exists()) {
                // Set the initial properties
                $this->id         = $this->buyable->ID;
                $this->title      = $this->buyable->Title;
                $this->price      = $this->buyable->getPrice();
                $this->price_nice = sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $this->price);
                $this->link       = $this->buyable->AbsoluteLink();
                $this->sku        = $this->buyable->InternalItemID;

                $this->endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/product', $this->id);

                // Process variations
                if ($variations = $this->buyable->Variations()) {
                    foreach ($variations as $variation) {
                        $this->variations[] = VariationModel::create($variation->ID)->get();
                    }
                }

                if (!empty($this->variations)) {
                    // Save price range
                    $priceRange  = $this->buyable->PriceRange();
                    if ($priceRange){
                        $this->price = $priceRange->Min . ' - ' . $priceRange->Max;
                    }else{
                        $this->price = 'POA';
                    }

                    // set up query
                    $attributes = $this->buyable->VariationAttributeTypes();
                    $attrQuery  = [];
                    foreach ($attributes as $attribute) {
                        $attrQuery['ProductAttributes'][$attribute->ID] = '';
                    }

                    $this->add_link = Controller::join_links($this->endpoint, 'addVariation') . '?' . http_build_query($attrQuery);
                } else {
                    $this->add_link = Controller::join_links($this->endpoint, 'add');
                }

                // Set the image
                if ($this->buyable->Image()) {
                    $this->product_image = ImageModel::create($this->buyable->Image()->ID)->get();
                } else {
                    $this->product_image = ImageModel::create(0)->get();
                }

                // Set the categories
                if ($this->buyable->ParentID) {
                    $this->categories[] = [
                        'id'    => $this->buyable->ParentID,
                        'title' => $this->buyable->Parent()->Title
                    ];
                }

                if (!($this->buyable instanceof Variation)) {
                    if ($this->buyable->ProductCategories()) {
                        foreach ($this->buyable->ProductCategories() as $category) {
                            $this->categories[] = [
                                'id'    => $category->ID,
                                'title' => $category->Title
                            ];
                        }
                    }
                }
            }
        }

        $this->extend('onAfterSetup');
    }

    public function getVariationByAttributes(array $attributes)
    {
        if (!is_array($attributes)) {
            return null;
        }

        $variations = Variation::get()->filter("ProductID", $this->id);

        foreach ($attributes as $typeid => $valueid) {
            if (!is_numeric($typeid) || !is_numeric($valueid)) {
                return null;
            } //ids MUST be numeric
            $alias      = "A$typeid";
            $variations = $variations->innerJoin(
                "Variation_AttributeValues",
                "\"Variation\".\"ID\" = \"$alias\".\"VariationID\"",
                $alias
            )->where("\"$alias\".\"ProductAttributeValueID\" = $valueid");
        }
        if ($variation = $variations->First()) {
            return $variation;
        }
        return false;
    }
}
