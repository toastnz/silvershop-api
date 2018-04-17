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
class CompareListItemModel extends ShopModelBase
{
    /** @var OrderItem $item */
    protected $item;

    /** @var Product|Variation $buyable */
    protected $buyable;

    protected $endpoint;

    protected $item_id;
    protected $product_id;
    protected $title;
    protected $link;
    protected $toggle_link;

    protected static $fields = [
        'item_id',
        'product_id',
        'title',
        'link',
        'toggle_link'
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

                // Set the initial properties
                $this->item_id     = $this->item->ID;
                $this->product_id = $this->item->ID;
                $this->title       = $this->item->Title;
                $this->link          = $this->item->AbsoluteLink();
                $this->endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/wishlist', $this->item->ID);
                $this->toggle_link          = Controller::join_links($this->endpoint, 'toggle');
            }
        }

        $this->extend('onAfterSetup');
    }

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
