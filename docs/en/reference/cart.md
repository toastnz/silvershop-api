# Cart

The main API endpoint. Returns a json encoded string with details of the current cart.

## Model Reference

| Name                | Type            | Default | Description                                                                                 |
|---------------------|-----------------|---------|---------------------------------------------------------------------------------------------|
| hash                | string          | null    | Contains a SHA256 encoded string that can be checked to see if the cart needs to be updated |
| currency            | string          | 'NZD'   | Defaults to SilverShop\ShopConfig::base_currency                                            |
| currency_symbol     | string          | '$'     |                                                                                             |
| quantity            | int             | 0       | Returns total number of order items in cart                                                 |
| total_price         | float           | 0.0     | Total cost of order including modifiers                                                     |
| total_price_nice    | string          | '$0.00' | Formatted string of order total                                                             |
| subtotal_price      | float           | 0.0     | Sub-total of order, not including modifiers (shipping, tax, etc)                            |
| subtotal_price_nice | string          | '$0.00' | Formatted string of order sub-total                                                         |
| cart_link           | string          |         | Links to first CartPage instance                                                            |
| checkout_link       | string          |         | Links to first CheckoutPage instance                                                        |
| continue_link       | string          |         | Links to the ContinuePage() method on the first CartPage instance                           |
| items               | array<CartItem> | []      | Holds all information of items in the cart. See [Cart Item](item.md)                                                  |
| modifiers           | array<Modifier> | []      | Holds all information of order modifiers. See [Modifier](modifier.md)                                                    |


## Methods

### /shop-api/cart

Returns the current cart.

### /shop-api/cart/promocode

See [promocode](promocode.md).