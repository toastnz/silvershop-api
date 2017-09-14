# SilverShop API Documentation

## Configuration

### Image Sizing

The images returned with the cart model are resampled. Add your own yaml configuration to customise these to match you site design.

Below is the default. You can replace the image structure entirely, add or remove sizes, and define your own pixel dimensions.

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
* [image](reference/image.md)
* [item](reference/item.md)
* [modifier](reference/modifier.md)
* [product](reference/product.md)
* [promocode](reference/promocode.md)
* [variation](reference/variation.md)
