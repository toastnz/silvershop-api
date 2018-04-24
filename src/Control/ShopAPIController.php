<?php

namespace Toast\ShopAPI\Control;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use Toast\ShopAPI\Model\CartItemModel;
use Toast\ShopAPI\Model\CartModel;
use Toast\ShopAPI\Model\WishListItemModel;
use Toast\ShopAPI\Model\CompareListItemModel;
use Toast\ShopAPI\Model\ComponentModel;
use Toast\ShopAPI\Model\ProductModel;
use Toast\ShopAPI\Model\VariationModel;
use SilverStripe\Dev\Debug;

/**
 * Class ShopAPIController
 */
class ShopAPIController extends Controller
{
    /**
     * @var CartModel $cart
     */
    protected $cart;

    private static $url_handlers = [
        'cart//$Action/$ID/$OtherAction' => 'handleAction'
    ];

    private static $allowed_actions = [
        'cart',
        'item',
        'product',
        'clear',
        'component',
        'promocode',
        'ping',
        'shipping',
        'variation',
        'wishlist',
        'comparelist'
    ];

//    public function __construct()
//    {
//        parent::__construct();
//
//        $this->cart = CartModel::create();
//    }

    public function init()
    {
        parent::init();

        $this->cart = CartModel::create();
    }

    /* -----------------------------------------
     * Handlers
     * ----------------------------------------*/

    public function index(HTTPRequest $request)
    {
        return $this->processResponse($this->cart->get());
    }

    /**
     * @param HTTPRequest $request
     * @return string
     */
    public function promocode(HTTPRequest $request)
    {
        /** =========================================
         * @var OrderCoupon $coupon
         * ========================================*/

        if (Product::has_extension('ProductDiscountExtension')) {
            $code = $request->getVar('code');

            if ($code) {
                return $this->processResponse($this->cart->applyCoupon($code));
            }
        }

        // TODO: Add error response for module not installed

        return $this->processResponse($this->cart->get());
    }

    /**
     * Controls Order Items (quantities)
     *
     * @param HTTPRequest $request
     * @return string
     */
    public function item(HTTPRequest $request)
    {
        $id = $request->param('ID');

        if ($id && is_numeric($id)) {
            $item = CartItemModel::create($id);

            // TODO: Validation on quantity

            // process action
            switch ($request->param('OtherAction')) {
                case 'setQuantity':
                    return $this->processResponse($item->setQuantity($request->getVar('quantity')));
                case 'removeOne':
                    return $this->processResponse($item->addOrRemoveItems(false));
                case 'removeAll':
                    return $this->processResponse($item->setQuantity(0));
                case 'addOne':
                    return $this->processResponse($item->addOrRemoveItems(true));
                case 'removeQuantity':
                    return $this->processResponse($item->addOrRemoveItems(false, $request->getVar('quantity')));
                case 'addQuantity':
                    return $this->processResponse($item->addOrRemoveItems(true, $request->getVar('quantity')));
                default:
                    return $this->processResponse($this->cart->get());
            }
        }

        return $this->processResponse();
    }

    public function wishlist(HTTPRequest $request)
    {
        $id = $request->param('ID');
        $item = WishListItemModel::create($id);
        $action  = $request->param('OtherID');
        // process action
        switch ($request->param('OtherID')) {
            case 'toggle':
                return $this->processResponse($item->addOrRemoveItems(true));
            case 'move':
                return $this->processResponse($item->move(true));
            default:
                return $this->processResponse($this->cart->get());
        }
    }

    public function comparelist(HTTPRequest $request)
    {
        $id = $request->param('ID');
        $item = CompareListItemModel::create($id);
        // process action
        switch ($request->param('OtherID')) {
            case 'toggle':
                return $this->processResponse($item->addOrRemoveItems(true));
            default:
                return $this->processResponse($this->cart->get());
        }
    }


    public function shipping(HTTPRequest $request)
    {
        $cart = $this->cart;
        // process action
        switch ($request->param('OtherAction')) {
            case 'update':
                return $this->processResponse($cart->updateShipping($request->getVar('ID')));
            case 'get':
                return $this->processResponse($cart->getShipping());
            default:
                return $this->processResponse($cart->getShipping());
        }

        return $this->processResponse();
    }


    /**
     * @param HTTPRequest $request
     * @return string
     *
     * Checkout component model
     */
    public function component(HTTPRequest $request)
    {
        $type = $request->param('ID');

        if ($type) {
            $component = ComponentModel::create($type);

            return $this->processResponse($component->get());
        }

        return $this->processResponse($this->cart->get());
    }

    /**
     * Controls Product functions (get, add to cart)
     *
     * @param HTTPRequest $request
     * @return string
     */
    public function product(HTTPRequest $request)
    {
        $id = $request->param('ID');

        if ($id && is_numeric($id)) {
            $product = ProductModel::create($id);

            $cart = $this->cart;



            // process action
            switch ($request->param('OtherAction')) {
                case 'add':
                    return $this->processResponse($cart->addItem($id, $request->getVar('quantity')));
                case 'addVariation':
                    $productAttributes = explode("_", $request->getVar('ProductAttributes'));
                    return $this->processResponse($cart->addVariation($id, $request->getVar('quantity'), $productAttributes));
                default:
                    return $this->processResponse($product->get());
            }
        }

        return $this->processResponse();
    }

    /**
     * Controls variation functions (get)
     *
     * @param HTTPRequest $request
     * @return string
     */
    public function variation(HTTPRequest $request)
    {
        $id = $request->param('ID');

        if ($id && is_numeric($id)) {
            $variation = VariationModel::create($id);

            $cart = $this->cart;

            // process action
            switch ($request->param('OtherAction')) {
                default:
                    return $this->processResponse($variation->get());
            }
        }
        return $this->processResponse();
    }

    public function clear(HTTPRequest $request)
    {
        return $this->processResponse($this->cart->clear());
    }

    public function ping(HTTPRequest $request)
    {
        $hash = $this->cart->getHash() != $request->getVar('hash') ? $this->cart->getHash() : $request->getVar('hash');

        return $this->processResponse(['hash' => $hash]);
    }

    /* -----------------------------------------
     * Helpers
     * ----------------------------------------*/

    public function processResponse($data = [])
    {
        $elapsed = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

        $this->extend('updateResponseData', $data);

        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);

        $cart = $this->cart;
        if ($request->latestParam('Action') == 'wishlist' || $request->latestParam('Action') == 'comparelist'){
            $message = $data['message'];
            $method =$data['method'];
        }else{
            $method = $cart->getCalledMethod();
            $message = $cart->getMessage();
        }

        if ( $message == '' || $message == Null ){
            if (array_key_exists('message', $data)){
                $message = $data['message'];
            }else{
                $message = '';
            }

        }

        $response = [
            'request' => $request->httpMethod(),
            'status'  => $cart->getStatus(), // success, error
            'method'  => $method,
            'elapsed' => number_format($elapsed * 1000, 0) . 'ms',
            'message' => $message,
            'code'    => $cart->getCode(),
            'data'    => $data
        ];


        return json_encode($response, JSON_HEX_QUOT | JSON_HEX_TAG);
    }
}
