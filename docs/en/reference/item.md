# Cart Item

Represents an OrderItem.

## Model Reference

| Name                 | Type                  | Default  | Description                                                                                 |
|----------------------|-----------------------|----------|---------------------------------------------------------------------------------------------|
| item_id              | int                   |          | The ID of the OrderItem                                                                     |
| product_id           | int                   |          | The ID of the Product (OrderItem->Buyable())                                                |
| title                | string                |          | TableTitle - to display in the cart                                                         |
| link                 | string                |          | Absolute link to the Product                                                                |
| add_link             | string                |          | Absolute link to the endpoint to add another of this product to the cart                    |
| add_quantity_link    | string                |          | Absolute link to the endpoint to add a specific quantity of this product to the cart        |
| remove_link          | string                |          | Absolute link to the endpoint to remove one of this OrderItem from the cart                 |
| remove_quantity_link | string                |          | Absolute link to the endpoint to remove a specific quantity of this OrderItem from the cart |
| remove_all_link      | string                |          | Absolute link to the endpoint to remove all of this OrderItem from the cart                 |
| price                | float                 | 0.0      | Unit price of this OrderItem                                                                |
| price_nice           | string                | '$0.00'' | Formatted string of the unit price                                                          |
| quantity             | int                   |          | Quantity of the OrderItem in the cart                                                       |
| total_price          | int                   | 0.0      | Total price of this OrderItem (quantity * unit price)                                       |
| total_price_nice     | string                | '$0.00'  | Formatted string of the total price for this line item                                      |
| product_image        | array<ImageModel>     | []       | Holds an array of various sizes for a product image. See [Image Model](imagemodel.md)       |
| categories           | array                 | []       | Holds an array of categories with the category ID and title                                 |
| variations           | array<VariationModel> | []       | Holds an array of all product variations. See [Variation Model](variation.md)               |

## Methods

### setQuantity

URL: /shop-api/cart/item/[ItemID]/setQuantity?quantity=[Quantity]

Sets the quantity of an OrderItem.

#### Parameters

| Name     | Type | Description                 |
|----------|------|-----------------------------|
| quantity | int  | Quantity to set the item to |

### removeOne

URL: /shop-api/cart/item/[ItemID]/removeOne

Removes one from the cart. If the quantity reaches 0, the item is removed from the cart.

### removeQuantity

URL: /shop-api/cart/item/[ItemID]/removeQuantity?quantity=[Quantity]

Removes a specific quantity from these items.


| Name     | Type | Description                 |
|----------|------|-----------------------------|
| quantity | int  | Amount of items to remove   |


### removeAll

URL: /shop-api/cart/item/[ItemID]/removeAll

Removes all of this item from the cart.

### addOne

URL: /shop-api/cart/item/[ItemID]/addOne

Adds one more to the quantity of this item.

### addQuantity

URL: /shop-api/cart/item/[ItemID]/addQuantity?quantity=[Quantity]

Adds a specific quantity of items.

| Name     | Type | Description                 |
|----------|------|-----------------------------|
| quantity | int  | Amount of items to add      |
