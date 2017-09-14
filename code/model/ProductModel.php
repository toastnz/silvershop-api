<?php

/**
 * Class ProductModel
 */
class ProductModel extends ShopModelBase
{
    /** @var Product|ProductVariation|ShopAPIVariationExtension|ProductVariationsExtension $buyable */
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
         * @var Product|ProductVariation $buyable
         * @var ProductVariation         $variation
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->buyable = DataObject::get_by_id('Product', $id);

            if ($this->buyable && $this->buyable->exists()) {
                // Set the initial properties
                $this->id         = $this->buyable->ID;
                $this->title      = $this->buyable->Title;
                $this->price      = $this->buyable->getPrice();
                $this->price_nice = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $this->price);
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
                    $this->price = $priceRange->Min . ' - ' . $priceRange->Max;

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

                if (!($this->buyable instanceof ProductVariation)) {
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
    }

    public function getVariationByAttributes(array $attributes)
    {
        if (!is_array($attributes)) {
            return null;
        }

        $variations = ProductVariation::get()->filter("ProductID", $this->id);

        foreach ($attributes as $typeid => $valueid) {
            if (!is_numeric($typeid) || !is_numeric($valueid)) {
                return null;
            } //ids MUST be numeric
            $alias      = "A$typeid";
            $variations = $variations->innerJoin(
                "ProductVariation_AttributeValues",
                "\"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\"",
                $alias
            )->where("\"$alias\".\"ProductAttributeValueID\" = $valueid");
        }
        if ($variation = $variations->First()) {
            return $variation;
        }
        return false;
    }
}
