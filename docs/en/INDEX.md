# SilverShop API Documentation

## Configuration

### Image Sizing

The images returned with the cart model are resampled. Add your own yaml configuration to customise these to match you site design.

```yaml
ImageModel:
  image_sizes:
    small:
      width: 160
      height: 90
    medium:
      width: 320
      height: 210
    large:
      width: 640
      height: 360

```

## API Reference

SilverShop API is based around a few main endpoints.

* [cart](reference/cart.md)
* [product](reference/product.md)
* [item](reference/item.md)
* [promocode](reference/promocode.md)

## Implementation

View examples of real implementation [here](implementation.md).
