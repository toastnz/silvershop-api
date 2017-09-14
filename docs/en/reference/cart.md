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

## JSON Response

### Empty Cart

```json
{
    "code": "success",
    "message": "",
    "id": null,
    "hash": "619b39...e3e4f5d",
    "currency": "NZD",
    "currency_symbol": "$",
    "quantity": 0,
    "total_price": 0,
    "total_price_nice": 0,
    "subtotal_price": 0,
    "subtotal_price_nice": 0,
    "cart_link": "http://mysite.local/cart/",
    "checkout_link": "http://mysite.local/checkout/",
    "continue_link": "http://mysite.local/",
    "items": [],
    "modifiers": []
}
```

### Full Cart

```json
{
    "code": "success",
    "message": "",
    "id": 27,
    "hash": "3d349de8...ef6fd7e6",
    "currency": "NZD",
    "currency_symbol": "$",
    "quantity": 1,
    "total_price": "30.00",
    "total_price_nice": "$30.00",
    "subtotal_price": "30.00",
    "subtotal_price_nice": "$30.00",
    "cart_link": "http://mysite.local/cart/",
    "checkout_link": "http://mysite.local/checkout/",
    "continue_link": "http://mysite.local/",
    "items": [
        {
            "item_id": 66,
            "product_id": 6048,
            "title": "Example Product",
            "description": null,
            "link": "http://mysite.local/products/test-category/example-product/",
            "add_link": "http://mysite.local/shop-api/cart/item/66/addOne",
            "add_quantity_link": "http://mysite.local/shop-api/cart/item/66/removeQuantity",
            "remove_link": "http://mysite.local/shop-api/cart/item/66/removeOne",
            "remove_quantity_link": "http://mysite.local/shop-api/cart/item/66/addQuantity",
            "remove_all_link": "http://mysite.local/shop-api/cart/item/66/removeAll",
            "price": 30,
            "price_nice": "$30.00",
            "quantity": 1,
            "total_price": 30,
            "total_price_nice": "$30.00",
            "product_image": {
                "alt": "example.jpg",
                "sizes": {
                    "small": {
                        "src": "http://mysite.local/assets/Uploads/Products/_resampled/FillWzE2MCw5MF0/example.jpg",
                        "width": 160,
                        "height": 90
                    },
                    "medium": {
                        "src": "http://mysite.local/assets/Uploads/Products/_resampled/FillWzMyMCwyMTBd/example.jpg",
                        "width": 320,
                        "height": 210
                    },
                    "large": {
                        "src": "http://mysite.local/assets/Uploads/Products/_resampled/FillWzY0MCwzNjBd/example.jpg",
                        "width": 640,
                        "height": 360
                    }
                }
            },
            "categories": [
                {
                    "id": 1,
                    "title": "Test Category"
                }
            ],
            "variations": []
        }
    ],
    "modifiers": [
        {
            "modifier_id": 25992,
            "title": "Shipping",
            "price": "0.00",
            "price_nice": "$0.00"
        },
        {
            "modifier_id": 25994,
            "title": "Tax @ 15.0%",
            "price": "3.84",
            "price_nice": "$3.84"
        }
    ]
}
```