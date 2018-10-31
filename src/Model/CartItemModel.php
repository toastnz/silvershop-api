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
class CartItemModel extends ShopModelBase
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
    protected $internal_item_id;
    protected $price;
    protected $price_nice;
    protected $total_items;
    protected $total_price;
    protected $total_price_nice;
    protected $product_image;
    protected $categories = [];
    protected $variations = [];
    protected $related_products = [];

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
        'internal_item_id',
        'price',
        'price_nice',
        'total_items',
        'total_price',
        'total_price_nice',
        'product_image',
        'categories',
        'variations',
        'related_products'

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
            $this->item = OrderItem::get_by_id(OrderItem::class, $id);

            if ($this->item->exists()) {

                // Set the product variables
                $this->buyable = $this->item->Buyable();

                // Set the initial properties
                $this->item_id     = $this->item->ID;
                $this->internal_item_id = $this->item->Product()->InternalItemID;
                $this->title       = $this->item->TableTitle();
                $this->total_items = $this->item->Quantity;

                // Set prices
                $unitValue  = $this->item->UnitPrice();
                $totalValue = $this->item->Total();

                // Run any extensions
                $this->extend('updateUnitSubTotal', $unitValue);
                $this->extend('updateUnitTotal', $totalValue);

                $this->price       = $unitValue;
                $this->total_price = $totalValue;

                // Format
                $this->price_nice       = sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $unitValue);
                $this->total_price_nice = sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $totalValue);

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

                    if ($this->buyable->RelatedProducts()){
                        foreach ($this->buyable->RelatedProducts()->limit(5) as $relatedProduct){
                            $endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/cart/item', $relatedProduct->ID);
//                            $unitValue  = $additionalOption->UnitPrice();
//                            $totalValue = $additionalOption->Total();
                            $this->related_products[] =  [
                                'item_id' => $relatedProduct->ID,
//                                'product_id' => $additionalOption->buyable->ID,
                                'title' => $relatedProduct->Title,
                                'description' => Null,
                                'link' => $relatedProduct->AbsoluteLink(),
                                'add_link' => Controller::join_links($endpoint, 'addOne'),
                                'add_quantity_link' => Controller::join_links($endpoint, 'addQuantity'),
                                'remove_link' => Controller::join_links($endpoint, 'removeOne'),
                                'remove_quantity_link' => Controller::join_links($endpoint, 'removeQuantity'),
                                'remove_all_link' => Controller::join_links($endpoint, 'removeAll'),
                                'internal_item_id' => $relatedProduct->InternalItemID,
                                'price' => $relatedProduct->price,
//                                'price_nice' => sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $unitValue),
                                'total_items' => $relatedProduct->Quantity,
                                'total_price' => $relatedProduct->total_price,
                                'product_image' => ImageModel::create($relatedProduct->Image()->ID)->get()
//                                'total_price_nice' => sprintf('%s%.2f', Config::inst()->get(Currency::class, 'currency_symbol'), $totalValue)
                            ];
                        }
                    }

                }
            }
        }

        $this->extend('onAfterSetup');
    }

    /**
     * Simple getter to quickly retrieve this order item's quantity
     *
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->total_items;
    }

    /**
     * Explicitly set the quantity of an order item
     *
     * @param $quantity
     * @return array
     */
    public function setQuantity($quantity)
    {
        $this->called_method = 'setQuantity';

        if ($this->buyable) {

            $this->extend('onBeforeSetQuantity', $quantity, $this->buyable);

            if (is_numeric($quantity)) {
                try {
                    $result = $this->cart->setQuantity($this->buyable, $quantity);
                } catch (Exception $e) {
                    $this->code         = 400;
                    $this->status       = 'error';
                    $this->message      = $e->getMessage();
                    $this->cart_updated = false;

                    return $this->getActionResponse();
                }

                if ($result === true || $result instanceof OrderItem) {
                    $this->status  = 'success';
                    $this->message = $this->cart->getMessage();
                    // Set the cart updated flag, and which components to refresh
                    $this->cart_updated = true;
                    $this->refresh      = [
                        'cart',
                        'summary',
                        'shippingmethod'
                    ];

                    $this->total_items = $this->order ? $this->order->Items()->Quantity() : $quantity;

                } else {
                    $this->code         = 400;
                    $this->status       = 'error';
                    $this->message      = $this->cart->getMessage();
                    $this->cart_updated = false;
                }

            } else {
                $this->code         = 400;
                $this->status       = 'error';
                $this->message      = _t('SHOP_API_MESSAGES.QuantityMissing', 'Quantity missing or not a number');
                $this->cart_updated = false;
            }
        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.ProductNotFound', 'Product does not exist');
            $this->cart_updated = false;
        }

        $this->extend('onAfterSetQuantity');

        return $this->getActionResponse();
    }

    /**
     * Add or remove one item
     *
     * @param bool $add
     * @param int  $quantity
     * @return array
     */
    public function addOrRemoveItems($add = true, $quantity = 1)
    {
        $this->called_method = 'addOrRemoveItems';

        if ($this->buyable) {
            try {
                $result = $add ? $this->cart->add($this->buyable, $quantity) : $this->cart->remove($this->buyable, $quantity);
            } catch (Exception $e) {
                $this->status       = 'error';
                $this->message      = $e->getMessage();
                $this->cart_updated = false;

                return $this->getActionResponse();
            }

            if ($result === true || $result instanceof OrderItem) {


                if ($add) {
                    $this->extend('onAfterAddItem', $quantity, $result);
                } else {
                    $this->extend('onAfterRemoveItem', $quantity, $result);
                }

                $this->status = 'success';
                if ($add) {
                    $this->message = _t(
                        'SHOP_API_MESSAGES.ItemAdded',
                        'Item{plural} added successfully.',
                        ['plural' => $quantity == 1 ? '' : 's']
                    );
                } else {
                    $this->message = _t(
                        'SHOP_API_MESSAGES.ItemRemoved',
                        'Item{plural} removed from cart.',
                        ['plural' => $quantity == 1 ? '' : 's']
                    );
                }
                $this->message = $this->cart->getMessage();
                // Set the cart updated flag, and which components to refresh
                $this->cart_updated = true;
                $this->refresh      = [
                    'cart',
                    'summary',
                    'shippingmethod'
                ];
                $this->total_items  = $this->order ? $this->order->Items()->Quantity() : $quantity;
            } else {
                $this->code         = 400;
                $this->status       = 'error';
                $this->message      = $this->cart->getMessage();
                $this->cart_updated = false;
            }
        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.ProductNotFound', 'Product does not exist');
            $this->cart_updated = false;
        }

        $this->extend('onAddOrRemoveItems');

        return $this->getActionResponse();
    }

}
