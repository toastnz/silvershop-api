# Modifier

Represents an OrderModifier. Can be tax, shipping, or any custom modifier that has been integrated with SilverShop.

## Model Reference

| Name        | Type   | Default | Description                       |
|-------------|--------|---------|-----------------------------------|
| modifier_id | int    |         | ID of the Modifier                |
| title       | string |         | TableTitle to display in the cart |
| price       | float  | 0.0     | Sub-Total of this modifier        |
| price_nice  | string | '$0.00' | Formatted sub-total               |