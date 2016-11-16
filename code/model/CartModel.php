<?php

/**
 * Class CartModel
 */
class CartModel extends ShopModelBase
{
    protected $id;
    protected $hash;
    protected $quantity;
    protected $total_price;
    protected $total_price_nice;
    protected $cart_link;
    protected $checkout_link;
    protected $items = [];

    protected static $fields = [
        'code',
        'message',
        'id',
        'hash',
        'currency',
        'currency_symbol',
        'quantity',
        'total_price',
        'total_price_nice',
        'cart_link',
        'checkout_link',
        'items'
    ];

    public function __construct()
    {
        parent::__construct();

        $date       = date_create();
        $this->hash = hash('sha256', $date->format('U'));

        if ($this->order) {
            $this->hash             = hash('sha256', ShoppingCart::curr()->LastEdited . $this->order->ID);
            $this->id               = $this->order->ID;
            $this->quantity         = $this->order->Items()->Quantity();
            $this->total_price      = number_format($this->order->SubTotal(), 2);
            $this->total_price_nice = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $this->order->SubTotal());

            // Add items
            if ($this->order->Items()) {
                foreach ($this->order->Items() as $item) {
                    $this->items[] = CartItemModel::create($item->ID)->get();
                }
            }
        } else {
            $this->quantity         = 0;
            $this->total_price      = 0;
            $this->total_price_nice = 0;
        }
    }

    /**
     * Add a plain item (no variations)
     *
     * @param     $buyableID
     * @param int $quantity
     * @return array
     */
    public function addItem($buyableID, $quantity)
    {
        /** =========================================
         * @var Product $product
         * ========================================*/

        if ($buyableID && is_numeric($buyableID)) {

            // Implement the same logic as on the AddProductForm and the VariationForm
            $product = DataObject::get_by_id('Product', $buyableID);

            if ($product && $product->exists()) {
                $quantity = $quantity > 0 ? $quantity : 1;
                try {
                    $result = $this->cart->add($product, $quantity);
                } catch (Exception $e) {
                    $this->code         = 'error';
                    $this->message      = $e->getMessage();
                    $this->cart_updated = false;

                    return $this->getActionResponse();
                }

                if ($result === true || $result instanceof OrderItem) {
                    $this->code         = 'success';
                    $this->message      = $this->cart->getMessage();
                    $this->cart_updated = true;
                    // Set new total items
                    $this->total_items = $result instanceof OrderItem ? $result->Order()->Items()->Quantity() : $quantity;
                } else {
                    $this->code         = 'error';
                    $this->message      = $this->cart->getMessage();
                    $this->cart_updated = false;
                }
            } else {
                $this->code         = 'error';
                $this->message      = _t('SHOP_API_MESSAGES.ProductNotFound', 'Product does not exist');
                $this->cart_updated = false;
            }
        } else {
            $this->code         = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.IncorrectIDParam', 'Missing or malformed ID');
            $this->cart_updated = false;
        }

        return $this->getActionResponse();
    }

    /**
     * Add a product that has variations
     *
     * @param       $buyableID
     * @param int   $quantity
     * @param array $productAttributes
     * @return array
     */
    public function addVariation($buyableID, $quantity, $productAttributes = [])
    {
        /** =========================================
         * @var Product      $product
         * @var ProductModel $productModel
         * ========================================*/

        if ($buyableID && is_numeric($buyableID)) {

            $productModel = ProductModel::create($buyableID);

            if ($productAttributes && is_array($productAttributes)) {
                if ($productVariation = $productModel->getVariationByAttributes($productAttributes)) {
                    $quantity = $quantity > 0 ? $quantity : 1;
                    try {
                        $result = $this->cart->add($productVariation, $quantity);
                    } catch (Exception $e) {
                        $this->code         = 'error';
                        $this->message      = $e->getMessage();
                        $this->cart_updated = false;

                        return $this->getActionResponse();
                    }

                    if ($result === true || $result instanceof OrderItem) {
                        $this->code         = 'success';
                        $this->message      = $this->cart->getMessage();
                        $this->cart_updated = true;
                    } else {
                        $this->code         = 'error';
                        $this->message      = $this->cart->getMessage();
                        $this->cart_updated = false;
                    }
                } else {
                    $this->code         = 'error';
                    $this->message      = _t('SHOP_API_MESSAGES.VariationNotAvailable', 'That variation is not available');
                    $this->cart_updated = false;
                }
            } else {
                $this->code         = 'error';
                $this->message      = _t('SHOP_API_MESSAGES.IncorrectProductAttributesFormat', 'Missing [ProductAttributes] GET variable in correct format');
                $this->cart_updated = false;
            }

        } else {
            $this->code         = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.IncorrectIDParam', 'Missing or malformed ID');
            $this->cart_updated = false;
        }

        return $this->getActionResponse();
    }

    /**
     * Remove all items from the cart
     *
     * @return array
     */
    public function clear()
    {
        if ($this->order->Items()->exists()) {
            $this->order->Items()->removeAll();

            $this->code         = 'success';
            $this->message      = _t('SHOP_API_MESSAGES.CartCleared', 'Cart cleared');
            $this->cart_updated = true;
        } else {
            $this->code         = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.CartAlreadyEmpty', 'Cart already empty');
            $this->cart_updated = false;
        }

        return $this->getActionResponse();
    }

    public function getHash()
    {
        return $this->hash;
    }
}
