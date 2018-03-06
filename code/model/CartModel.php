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
    protected $subtotal_price;
    protected $subtotal_price_nice;
    protected $cart_link;
    protected $checkout_link;
    protected $items = [];
    protected $modifiers = [];

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
        'subtotal_price',
        'subtotal_price_nice',
        'cart_link',
        'checkout_link',
        'continue_link',
        'items',
        'modifiers',
        'shipping_id',
    ];

    public function __construct()
    {
        parent::__construct();

        $date       = date_create();
        $this->hash = hash('sha256', $date->format('U'));
        if ($this->order) {
            $this->hash                = hash('sha256', ShoppingCart::curr()->LastEdited . $this->order->ID);
            $this->id                  = $this->order->ID;
            $this->quantity            = $this->order->Items()->Quantity();
            $this->subtotal_price      = number_format($this->order->SubTotal(), 2);
            $this->subtotal_price_nice = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $this->order->SubTotal());
            $this->total_price         = number_format($this->order->Total(), 2);
            $this->total_price_nice    = sprintf('%s%.2f', Config::inst()->get('Currency', 'currency_symbol'), $this->order->Total());

            // Add items
            if ($this->order->Items()) {
                foreach ($this->order->Items() as $item) {
                    $this->items[] = CartItemModel::create($item->ID)->get();
                }
            }

            // Add modifiers
            if ($this->order->Modifiers()) {
                foreach ($this->order->Modifiers() as $modifier) {
                    if ($modifier->ShowInTable()) {
                        $this->modifiers[] = ModifierModel::create($modifier->ID)->get();
                    }
                }
            }
        } else {
            $this->quantity            = 0;
            $this->total_price         = 0;
            $this->total_price_nice    = 0;
            $this->subtotal_price      = 0;
            $this->subtotal_price_nice = 0;
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
                    $this->code    = 'success';
                    $this->message = _t(
                        'SHOP_API_MESSAGES.ItemAdded',
                        'Item{plural} added successfully.',
                        ['plural' => $quantity == 1 ? '' : 's']
                    );
                    // Set the cart updated flag, and which components to refresh
                    $this->cart_updated = true;
                    $this->refresh      = [
                        'cart',
                        'summary',
                        'shippingmethod'
                    ];
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
                        $this->code    = 'success';
                        $this->message = _t(
                            'SHOP_API_MESSAGES.ItemAdded',
                            'Item{plural} added successfully.',
                            ['plural' => $quantity == 1 ? '' : 's']
                        );
                        // Set the cart updated flag, and which components to refresh
                        $this->cart_updated = true;
                        $this->refresh      = [
                            'cart',
                            'summary',
                            'shippingmethod'
                        ];
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

    public function applyCoupon($code)
    {
        /** =========================================
         * @var OrderCoupon $coupon
         * ========================================*/

        if ($coupon = OrderCoupon::get_by_code($code)) {
            if (!$coupon->validateOrder($this->order, ["CouponCode" => $code])) {
                $this->code         = 'error';
                $this->message      = _t('SHOP_API_MESSAGES.CouponInvalid', 'Could not apply coupon.');
                $this->cart_updated = false;
            } else {
                Session::set("cart.couponcode", strtoupper($code));

                $this->order->getModifier("OrderDiscountModifier", true);

                $this->code    = 'success';
                $this->message = _t('SHOP_API_MESSAGES.CouponApplied', 'Coupon applied.');
                // Set the cart updated flag, and which components to refresh
                $this->cart_updated = true;
                $this->refresh      = [
                    'cart',
                    'summary'
                ];
            }
        } else {
            $this->code         = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.CouponNotFound', 'Coupon could not be found');
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

            $this->code    = 'success';
            $this->message = _t('SHOP_API_MESSAGES.CartCleared', 'Cart cleared');
            // Set the cart updated flag, and which components to refresh
            $this->cart_updated = true;
            $this->refresh      = [
                'cart',
                'summary'
            ];
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

    public function updateShipping($zoneID, $addressDetails)
    {
        if(!$zoneID) {
            $this->code = 'failed';
            $this->message = _t('SHOP_API_MESSAGES.ShippingUpdated', 'No Zone ID');
            // Set the cart updated flag, and which components to refresh
            $this->cart_updated = false;
            $this->refresh = [
                'cart',
                'summary',
                'shipping'
            ];
        }elseif (!$addressDetails['ShippingAddressCheckoutComponent_FirstName'] && !$addressDetails['ShippingAddressCheckoutComponent_Surname'] && !$addressDetails['ShippingAddressCheckoutComponent_Email'] && !$addressDetails['ShippingAddressCheckoutComponent_Phone'] && !$addressDetails['ShippingAddressCheckoutComponent_PostalCode'] && !$addressDetails['ShippingAddressCheckoutComponent_City'] && !$addressDetails['ShippingAddressCheckoutComponent_Address']){
            $this->code = 'failed';
            $this->message = _t('SHOP_API_MESSAGES.ShippingUpdated', 'No Address details');
            // Set the cart updated flag, and which components to refresh
            $this->cart_updated = false;
            $this->refresh = [
                'cart',
                'summary',
                'shipping'
            ];
        }else {
            // find the shiping option with the zone $zoneID added to it
            $shipping = ZonedShippingRate::get()->exclude('ZonedShippingMethodID', 0)->filter(['ZoneId' => $zoneID])->first();
            // search shipping methods that container
            if ($shipping == Null || !$this->order) {
                $this->code = 'failed';
                $this->message = _t('SHOP_API_MESSAGES.ShippingUpdated', 'Cart shipping updated');
                // Set the cart updated flag, and which components to refresh
                $this->cart_updated = false;
                $this->refresh = [
                    'cart',
                    'summary',
                    'shipping'
                ];
            } else {
                if ($this->order->getShippingAddress()->ID == 0) {
                    $address = new Address();
                    $address->FirstName = $addressDetails['ShippingAddressCheckoutComponent_FirstName'];
                    $address->Surname = $addressDetails['ShippingAddressCheckoutComponent_Surname'];
                    $address->Email = $addressDetails['ShippingAddressCheckoutComponent_Email'];
                    $address->Phone = $addressDetails['ShippingAddressCheckoutComponent_Phone'];
                    $address->PostalCode = $addressDetails['ShippingAddressCheckoutComponent_PostalCode'];
                    $address->State = Zone::get()->byID($zoneID)->Title;
                    $address->City = $addressDetails['ShippingAddressCheckoutComponent_City'];
                    $address->Address = $addressDetails['ShippingAddressCheckoutComponent_Address'];

                    $address->write();

                    $this->order->ShippingAddressID = $address->ID;
                }else{
                    $address = $this->order->ShippingAddress();
                    $address->FirstName = $addressDetails['ShippingAddressCheckoutComponent_FirstName'];
                    $address->Surname = $addressDetails['ShippingAddressCheckoutComponent_Surname'];
                    $address->Email = $addressDetails['ShippingAddressCheckoutComponent_Email'];
                    $address->Phone = $addressDetails['ShippingAddressCheckoutComponent_Phone'];
                    $address->PostalCode = $addressDetails['ShippingAddressCheckoutComponent_PostalCode'];
                    $address->State = Zone::get()->byID($zoneID)->Title;
                    $address->City = $addressDetails['ShippingAddressCheckoutComponent_City'];
                    $address->Address = $addressDetails['ShippingAddressCheckoutComponent_Address'];

                    $address->write();
                }

                $Zone = Zone::get()->byID($zoneID);
                $this->order->ShippingAddress()->State = $Zone->Title;
                $this->order->ShippingAddress()->write();
                $shippingID = $shipping->ZonedShippingMethodID;
                $this->order->setShippingMethod(ShippingMethod::get()->byID($shippingID));
                $this->code = 'success';
                $this->message = _t('SHOP_API_MESSAGES.ShippingUpdated', 'Cart shipping updated');
                // Set the cart updated flag, and which components to refresh
                $this->cart_updated = true;
                $this->shipping_id = $shippingID;

                if ($Zone){
                    if (array_key_exists('RuralShipping', $addressDetails))
                        $isRural = $addressDetails['RuralShipping'];
                    else{
                        $isRural = 0;
                    }
//                    $address = Address::get()->byID($this->order->ShippingAddressID);
                    $address->RuralShipping = $isRural;
                    $address->write();

                    if ($isRural){

                        $ZoneShippingMethods = ShippingMethod::get()->filter(['Name:PartialMatch' => 'Rural']);
                        foreach ($ZoneShippingMethods as $ZoneShippingMethod){
                            $ZoneShippingRegions = ZonedShippingRate::get()->filter(['ZonedShippingMethodID' => $ZoneShippingMethod->ID, 'ZoneID' => $Zone->ID])->Sort('Rate ASC');
                            if ($ZoneShippingRegions->count() >= 1){
                                $this->order->setShippingMethod(ShippingMethod::get()->byID($ZoneShippingMethod->ID));
                                $shippingID = $ZoneShippingMethod->ID;
                                $ZoneShippingRegionsSelected = $ZoneShippingRegions;
                            }
                        }
//                        Debug::dump($ZoneShippingRegionsSelected);
//
//
                        $ZoneShippingRegion = $ZoneShippingRegionsSelected->first();
//
                        $ZoneRate = $ZoneShippingRegion->Rate;
                        $this->shipping_id = $ZoneShippingRegion->ZonedShippingMethodID;

                    }else{

                        $ZoneShippingRegions = ZonedShippingRate::get()->filter(['ZonedShippingMethodID' => $this->order->ShippingMethodID, 'ZoneID' => $Zone->ID])->Sort('Rate ASC');
                        $ZoneShippingRegion = $ZoneShippingRegions->first();
                        $ZoneRate = $ZoneShippingRegion->Rate;
                    }

                }else{
                    $this->message = _t('SHOP_API_MESSAGES.GetShipping', 'No current zone set');
                }
                $this->shipping_rate = $ZoneRate;
                // check if this this order qulifies for any table shipping.
                $tableShippingOptions = $this->order->getShippingEstimates()->filter(['ClassName' => 'TableShippingMethod'])->Sort('Rate ASC');
                if ($tableShippingOptions->count() >= 1){
                    $newShippingOption = $tableShippingOptions->first();
                    $this->shipping_id = $newShippingOption->ID;
                    $this->shipping_rate = $newShippingOption->Rate;
                    $shippingID = $newShippingOption->ID;
                    $this->order->setShippingMethod(ShippingMethod::get()->byID($shippingID));
                    $this->owner->order->ShippingTotal = $this->shipping_rate;
                }
                $this->refresh = [
                    'cart',
                    'summary',
                    'shipping'
                ];
            }
        }

        return $this->getActionResponse();
    }

    public function getShipping()
    {


        // If there is a current shipping option
        if ($this->order->ShippingAddressID){
            $this->message = _t('SHOP_API_MESSAGES.GetShipping', 'Get current shipping method');
            $ZoneTitle =  Address::get()->byID($this->order->ShippingAddressID)->State;
            $Zone =  Zone::get()->filter('Name', $ZoneTitle)->first();
            if (ShippingMethod::get()->byID($this->order->ShippingMethodID)->ClassName == 'ZonedShippingMethod'){
                if ($Zone){
                    $ZoneRate = ZonedShippingRate::get()->filter(['ZonedShippingMethodID' => $this->order->ShippingMethodID, 'ZoneID' => $Zone->ID])->first()->Rate;
                }else{
                    $this->message = _t('SHOP_API_MESSAGES.GetShipping', 'No current zone set');
                }
                $this->zoneShippingRate = $this->order->ShippingMethodID;
                $this->shipping_id = $this->order->ShippingMethodID;
                $this->shipping_rate = $ZoneRate;
            }else{
                $this->shipping_id = $this->order->ShippingMethodID;
                $this->shipping_rate = ShippingMethod::get()->byID($this->order->ShippingMethodID)->Rate;
            }



            // if shipping method is zoned

            $this->cart_updated = false;
            $this->refresh      = [
                'cart',
                'summary',
                'shipping'
            ];
        }else{
            $this->message = _t('SHOP_API_MESSAGES.GetShipping', 'No current shipping method');
            $this->shipping_id = Null;
            // if shipping method is zoned

            $this->cart_updated = false;
            $this->refresh      = [
                'cart',
                'summary',
                'shipping'
            ];
        }

        return $this->getActionResponse();
    }
}
