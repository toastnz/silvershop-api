<?php

/**
 * Class CartItemModel
 */
class CartItemModel extends ShopModelBase
{
    /** @var OrderItem $item */
    protected $item;

    /** @var Product|ProductVariation $buyable */
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
            $this->item = OrderItem::get_by_id('OrderItem', $id);

            if ($this->item->exists()) {
                // Set the initial properties
                $this->item_id  = $this->item->ID;
                $this->title    = $this->item->TableTitle();
                $this->quantity = $this->item->Quantity;

                // Set prices
                $unitValue  = $this->item->UnitPrice();
                $totalValue = $this->item->Total();

                $this->price       = $unitValue;
                $this->total_price = $totalValue;

                // Format
                $this->price_nice       = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $unitValue);
                $this->total_price_nice = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $totalValue);

                $this->endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/cart/item', $this->item->ID);

                // Set the product variables
                $this->buyable = $this->item->Buyable();

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
                    $variations = ($this->buyable instanceof ProductVariation) ? $this->buyable->Product()->Variations() : $this->buyable->Variations();

                    if ($variations) {
                        foreach ($variations as $variation) {
                            $this->variations[] = VariationModel::create($variation->ID)->get();
                        }
                    }

                    // Set the image
                    if ($this->buyable->Image()) {
                        $this->product_image = ImageModel::create($this->buyable->Image()->ID)->get();
                    }

                    // Set the categories
                    if ($this->buyable->ParentID) {
                        $this->categories[] = [
                            'id' => $this->buyable->ParentID,
                            'title' => $this->buyable->Parent()->Title
                        ];
                    }

                    if (!($this->buyable instanceof ProductVariation)) {
                        if ($this->buyable->ProductCategories()) {
                            foreach ($this->buyable->ProductCategories() as $category) {
                                $this->categories[] = [
                                    'id' => $category->ID,
                                    'title' => $category->Title
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Simple getter to quickly retrieve this order item's quantity
     *
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Explicitly set the quantity of an order item
     *
     * @param $quantity
     * @return array
     */
    public function setQuantity($quantity)
    {
        if ($this->buyable) {
            if (is_numeric($quantity)) {
                try {
                    $result = $this->cart->setQuantity($this->buyable, $quantity);
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
                    $this->total_items  = $this->order ? $this->order->Items()->Quantity() : $quantity;
                } else {
                    $this->code         = 'error';
                    $this->message      = $this->cart->getMessage();
                    $this->cart_updated = false;
                }

            } else {
                $this->code         = 'error';
                $this->message      = _t('SHOP_API_MESSAGES.QuantityMissing', 'Quantity missing or not a number');
                $this->cart_updated = false;
            }
        } else {
            $this->code         = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.ProductNotFound', 'Product does not exist');
            $this->cart_updated = false;
        }

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
        if ($this->buyable) {
            try {
                $result = $add ? $this->cart->add($this->buyable, $quantity) : $this->cart->remove($this->buyable, $quantity);
            } catch (Exception $e) {
                $this->code         = 'error';
                $this->message      = $e->getMessage();
                $this->cart_updated = false;

                return $this->getActionResponse();
            }

            if ($result === true || $result instanceof OrderItem) {
                $this->code = 'success';
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
                $this->message      = $this->cart->getMessage();
                $this->cart_updated = true;
                $this->total_items  = $this->order ? $this->order->Items()->Quantity() : $quantity;
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

        return $this->getActionResponse();
    }

}
