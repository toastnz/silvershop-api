<?php

namespace Toast\Control;

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
        'shipping'
    ];

    public function __construct()
    {
        $this->cart = CartModel::create();

        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

    /* -----------------------------------------
     * Handlers
     * ----------------------------------------*/

    public function index(SS_HTTPRequest $request)
    {
        return $this->processResponse($this->cart->get());
    }

    /**
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function promocode(SS_HTTPRequest $request)
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

        return $this->processResponse($this->cart->get());
    }

    /**
     * Controls Order Items (quantities)
     *
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function item(SS_HTTPRequest $request)
    {
        $id = $request->param('ID');

        if ($id && is_numeric($id)) {
            $item = CartItemModel::create($id);

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


    public function shipping(SS_HTTPRequest $request)
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
     * @param SS_HTTPRequest $request
     * @return string
     *
     * Checkout component model
     */
    public function component(SS_HTTPRequest $request)
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
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function product(SS_HTTPRequest $request)
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
                    return $this->processResponse($cart->addVariation($id, $request->getVar('quantity'), $request->getVar('ProductAttributes')));
                default:
                    return $this->processResponse($product->get());
            }
        }

        return $this->processResponse();
    }

    public function clear(SS_HTTPRequest $request)
    {
        return $this->processResponse($this->cart->clear());
    }

    public function ping(SS_HTTPRequest $request)
    {
        $hash = $this->cart->getHash() != $request->getVar('hash') ? $this->cart->getHash() : $request->getVar('hash');

        return $this->processResponse(['hash' => $hash]);
    }

    /* -----------------------------------------
     * Helpers
     * ----------------------------------------*/

    public function processResponse($data = [])
    {
        if ($this->request->param('Action') != 'ping' && !empty($this->request->param('Action'))) {
            if ($cart = ShoppingCart::curr()) {
                $cart->setField('Hash', $this->cart->getHash());
                $cart->write();
            }
        }

        $this->extend('updateResponseData', $data);

        return json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG);
    }
}
