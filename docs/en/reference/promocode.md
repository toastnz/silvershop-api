# Promocode

A helper endpoint for when the [SilverShop Discounts](https://github.com/silvershop/silvershop-discounts) module is installed.

## Endpoint

/shop-api/cart/promocode?code=[Code]

### Parameters

| Name | Type   | Description             |
|------|--------|-------------------------|
| code | string | The requested promocode |

### Response

| Name         | Type    | Default   | Description                                    |
|--------------|---------|-----------|------------------------------------------------|
| code         | string  | 'success' | Either 'success' or 'error'                    |
| message      | string  |           | More detailed message for response             |
| cart_updated | boolean | false     | Whether the cart has successfully been updated |