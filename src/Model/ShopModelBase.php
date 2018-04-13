<?php

namespace Toast\ShopAPI\Model;

use Exception;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CartPageController;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;

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

    /**
     * @var int
     *
     * Status code
     */
    protected $code;

    /**
     * @var string
     *
     * Status of the request - success|error
     */
    protected $status;

    /**
     * @var string
     *
     * Method called in PHP, eg, addItem()
     */
    protected $called_method;

    /**
     * @var string
     *
     * Relevant message
     */
    protected $message;

    /**
     * @var bool
     *
     * Lets us know if the cart was modified at all
     */
    protected $cart_updated = false;

    /**
     * @var array
     *
     * List of components in the checkout that need to be updated through ajax
     */
    protected $refresh = [];

    /**
     * @var int
     *
     * Total number of items in cart
     */
    protected $total_items = 0;

    /**
     * @var string
     *
     * 3 character currency code
     */
    protected $currency;

    /**
     * @var string
     *
     * Currency symbol - eg $
     */
    protected $currency_symbol;

    /**
     * @var string
     *
     * Absolute link to the cart page
     */
    protected $cart_link;

    /**
     * @var string
     *
     * Absolute link to the checkout page
     */
    protected $checkout_link;

    /**
     * @var string
     *
     * Absolute link to continue shopping (catalog)
     */
    protected $continue_link;

    /**
     * @var string
     *
     * Holds the time in microseconds since the request was made
     */
    protected $elapsed;

    protected $shipping_id;

    protected static $fields = [];

    public function __construct()
    {
        // Common fields
        $this->status        = 'success';
        $this->called_method = 'cart';
        $this->code          = 200;
        $this->message       = '';
        $this->elapsed       = $_SERVER["REQUEST_TIME_FLOAT"];

        // Shop specific
        $this->currency        = $this->getSiteCurrency();
        $this->currency_symbol = $this->getSiteCurrencySymbol();
        $this->total_items     = $this->order ? $this->order->Items()->Quantity() : 0;

        // retrieve the order
        if (class_exists(ShoppingCart::class)) {

            try {
                $this->cart  = ShoppingCart::singleton();
                $this->order = $this->cart->current();
            } catch (Exception $e) {
                $this->status  = 'error';
                $this->code    = 400;
                $this->message = $e->getMessage();
            }

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
            if ($cartPage = SiteTree::get_one(CartPage::class)) {
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

        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);

        $elapsed = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

        $data = [
            'cart_updated' => $this->cart_updated,
            'refresh'      => $refreshComponents,
            'quantity'     => $this->total_items,
            'shipping_id'  => $this->shipping_id,
        ];

        $response = [
            'request' => $request->httpMethod(),
            'status'  => $this->status, // success, error
            'method'  => $this->called_method,
            'elapsed' => number_format($elapsed * 1000, 0) . 'ms',
            'message' => $this->message,
            'code'    => $this->code,
            'data'    => $data
        ];

        $this->extend('onBeforeActionResponse', $response);

        return $response;
    }

    public function getSiteCurrency()
    {
        $currency = singleton(ShopAPIConfig::class)->getSiteCurrency();

        $this->extend('updateSiteCurrency', $currency);

        return $currency;
    }

    public function getSiteCurrencySymbol()
    {
        $symbol = singleton(ShopAPIConfig::class)->getSiteCurrencySymbol();

        $this->extend('updateSiteCurrencySymbol', $symbol);

        return $symbol;
    }

    /** -----------------------------------------
     * Getters
     * ----------------------------------------*/

    public function getStatus()
    {
        return $this->status;
    }

    public function getCalledMethod()
    {
        return $this->called_method;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
    {
        return $this->code;
    }
}
