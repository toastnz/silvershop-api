<?php

namespace Toast\ShopAPI\Model;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CartPageController;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

/**
 * Class ShopModelBase
 */
abstract class ShopModelBase
{
    use Extensible;
    use Injectable;
    use Configurable;

    /** @var Order $order */
    protected $order;

    /** @var ShoppingCart $cart */
    protected $cart;

    protected $code;
    protected $message;
    protected $cart_updated = false;
    protected $refresh = [];
    protected $total_items = 0;
    protected $currency;
    protected $currency_symbol;
    protected $cart_link;
    protected $checkout_link;
    protected $continue_link;
    protected $shipping_id;

    protected static $fields = [];

    public function __construct()
    {
        // Common fields
        $this->code            = 'success';
        $this->message         = '';
        $this->currency        = $this->getSiteCurrency();
        $this->currency_symbol = $this->getSiteCurrencySymbol();
        $this->total_items     = $this->order ? $this->order->Items()->Quantity() : 0;

        // retrieve the order
        if (class_exists('ShoppingCart')) {
            $this->order = ShoppingCart::curr();
            $this->cart  = ShoppingCart::singleton();

            // Set links
            $cartBase = Controller::join_links(Director::absoluteBaseURL(), CartPageController::config()->url_segment);
            if ($page = CartPage::get()->first()) {
                $cartBase = $page->AbsoluteLink();
            }
            $this->cart_link = $cartBase;

            $checkoutBase = Controller::join_links(Director::absoluteBaseURL(), CheckoutPageController::config()->url_segment);
            if ($page = CheckoutPage::get()->first()) {
                $checkoutBase = $page->AbsoluteLink();
            }
            $this->checkout_link = $checkoutBase;
            // This means
            if ($cartPage = SiteTree::get_one('CartPage')) {
                if ($continue = $cartPage->ContinuePage()) {
                    $this->continue_link = $continue->AbsoluteLink();
                }
            }
        } else {
            user_error('Missing Silvershop module', E_USER_WARNING);
        }
    }

    public function get()
    {
        $result = [];
        array_map(function ($field) use (&$result) {
            $result[$field] = $this->{$field};
        }, static::$fields);

        return $result;
    }

    public function getActionResponse()
    {
        $refreshComponents = $this->refresh;

        $this->extend('updateRefreshComponents', $refreshComponents);

        $data = [
            'code'         => $this->code,
            'message'      => $this->message,
            'cart_updated' => $this->cart_updated,
            'refresh'      => $refreshComponents,
            'quantity'     => $this->total_items,
            'shipping_id'  => $this->shipping_id,
        ];

        $this->extend('onBeforeActionResponse', $data);

        return $data;
    }

    public function getSiteCurrency()
    {
        $currency = singleton('ShopAPIConfig')->getSiteCurrency();

        $this->extend('updateSiteCurrency', $currency);

        return $currency;
    }

    public function getSiteCurrencySymbol()
    {
        $symbol = singleton('ShopAPIConfig')->getSiteCurrencySymbol();

        $this->extend('updateSiteCurrencySymbol', $symbol);

        return $symbol;
    }


}
