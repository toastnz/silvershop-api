<?php

/**
 * Class ShopModelBase
 */
abstract class ShopModelBase extends Object
{
    /** @var Order $order */
    protected $order;

    /** @var ShoppingCart $cart */
    protected $cart;

    protected $code;
    protected $message;
    protected $cart_updated = false;
    protected $total_items = 0;
    protected $currency;
    protected $currency_symbol;
    protected $cart_link;
    protected $checkout_link;
    protected $continue_link;

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
            $cartBase = Controller::join_links(Director::absoluteBaseURL(), CartPage_Controller::config()->url_segment);
            if ($page = CartPage::get()->first()) {
                $cartBase = $page->Link();
            }
            $this->cart_link     = $cartBase;

            $checkoutBase = Controller::join_links(Director::absoluteBaseURL(), CheckoutPage_Controller::config()->url_segment);
            if ($page = CheckoutPage::get()->first()) {
                $checkoutBase = $page->Link();
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

        parent::__construct();
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
        return [
            'code' => $this->code,
            'message' => $this->message,
            'cart_updated' => $this->cart_updated,
            'quantity' => $this->total_items
        ];
    }

    public function getSiteCurrency()
    {
        return singleton('ShopAPIConfig')->getSiteCurrency();
    }

    public function getSiteCurrencySymbol()
    {
        return singleton('ShopAPIConfig')->getSiteCurrencySymbol();
    }
}
