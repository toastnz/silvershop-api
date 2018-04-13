<?php

namespace Toast\ShopAPI\Model;

use Exception;
use Omnipay\Common\Currency;
use SilverShop\Model\OrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;

/**
 * Class CartItemModel
 */
class WishListItemModel extends ShopModelBase
{
    /** @var OrderItem $item */
    protected $item;

    /** @var Product|Variation $buyable */
    protected $buyable;

    protected $endpoint;

    protected $item_id;
    protected $product_id;
    protected $title;
    protected $description;
    protected $link;
    protected $add_link;
    protected $add_quantity_link;
    protected $remove_link;
    protected $remove_quantity_link;
    protected $remove_all_link;
    protected $price;
    protected $price_nice;
    protected $quantity;
    protected $total_price;
    protected $total_price_nice;
    protected $product_image;
    protected $categories = [];
    protected $variations = [];

    protected static $fields = [
        'item_id',
        'product_id',
        'title',
        'description',
        'link',
        'add_link',
        'add_quantity_link',
        'remove_link',
        'remove_quantity_link',
        'remove_all_link',
        'price',
        'price_nice',
        'quantity',
        'total_price',
        'total_price_nice',
        'product_image',
        'categories',
        'variations'
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Currency $unitMoney
         * @var Currency $totalMoney
         * ========================================*/

        parent::__construct();



        if ($id && is_numeric($id)) {
            // Get an order item
            $this->item = Product::get_by_id(Product::class, $id);

            if ($this->item->exists()) {

                // Set the product variables
//                $this->buyable = $this->item->Buyable();

                // Set the initial properties
                $this->item_id  = $this->item->ID;
                $this->title    = $this->item->Title;
                $this->quantity = $this->item->Quantity;

                $this->endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/cart/item', $this->item->ID);

                if ($this->buyable && $this->buyable->exists()) {
                    $this->product_id = $this->buyable->ID;
                    $this->link       = $this->buyable->AbsoluteLink();

                    // Set API links
                    $this->add_link             = Controller::join_links($this->endpoint, 'addOne');
                    $this->remove_link          = Controller::join_links($this->endpoint, 'removeOne');
                    $this->remove_all_link      = Controller::join_links($this->endpoint, 'removeAll');
                    $this->add_quantity_link    = Controller::join_links($this->endpoint, 'removeQuantity');
                    $this->remove_quantity_link = Controller::join_links($this->endpoint, 'addQuantity');

                    // Add variables
                    $variations = ($this->buyable instanceof Variation) ? $this->buyable->Product()->Variations() : $this->buyable->Variations();

                    if ($variations) {
                        foreach ($variations as $variation) {
                            $this->variations[] = VariationModel::create($variation->ID)->get();
                        }
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
        }

        $this->extend('onAfterSetup');
    }

}
