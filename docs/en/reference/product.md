# Product

Represents a Product.

## Model Reference

| Name          | Type              | Default | Description                                                                                 |
|---------------|-------------------|---------|---------------------------------------------------------------------------------------------|
| id            | int               |         | ID of the Product                                                                           |
| title         | string            |         | Title of the Product                                                                        |
| price         | float             | 0.0     | Price of the product. Takes in to consideration current logged in user (e.g. trade pricing) |
| price_nice    | string            | '$0.00' | Formatted string of product price                                                           |
| sku           | string            |         | InternalItemID of product                                                                   |
| add_link      | string            |         | Absolute URL of the API endpoint to add this product                                        |
| product_image | array<ImageModel> | []      | Image for the product. See [Image Model](image.md).                                         |

## Methods

### /shop-api/cart/product/[ID]

Returns the requested product in the format above.

### /shop-api/cart/product/[ID]/add?quantity=[Quantity]

| Name      | Type | Default | Description                       |
|-----------|----- |---------|-----------------------------------|
| quantity  | int  | 1       | Number of items to add (optional) |

### /shop-api/cart/product/[ID]/addVariation?quantity=[Quantity]&ProductAttributes=[Product Attributes]

If a product has variations, this endpoint should be used. 

A Product can generate form fields representing the attributes and values, and these can be serialised and sent straight through.

| Name              | Type  | Default | Description                                                                   |
|-------------------|-------|---------|-------------------------------------------------------------------------------|
| quantity          | int   | 1       | Number of items to add (optional)                                             |
| ProductAttributes | array | []      | Product attributes - these are taken directly from the Form on a product page |

## Response Format

Any methods called above will return in the following format

| Name         | Type    | Default   | Description                                    |
|--------------|---------|-----------|------------------------------------------------|
| code         | string  | 'success' | Either 'success' or 'error'                    |
| message      | string  |           | More detailed message for response             |
| cart_updated | boolean | false     | Whether the cart has successfully been updated |
| refresh      | array   | []        | List of components that should be updated      |
| quantity     | int     | 0         | Total quantity of all items                    |