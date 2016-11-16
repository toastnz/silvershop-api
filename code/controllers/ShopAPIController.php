<?php

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
        'ping'
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
            ShoppingCart::curr()->setField('Hash', $this->cart->getHash());
            ShoppingCart::curr()->write();
        }

        return json_encode($data);
    }
}
