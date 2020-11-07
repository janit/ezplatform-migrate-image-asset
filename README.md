# Migrate eZ Platform image field data to image assets

This repository contains an eZ Platform 3.x compatible Symfony
command that migrates data from the <a href="https://doc.ezplatform.com/en/latest/api/field_type_reference/#image-field-type">image field type</a> to the <a href="https://doc.ezplatform.com/en/latest/api/field_type_reference/#imageasset-field-type">image asset field type</a>.

This script does not do any content type modification, it simply copies and links image data to make moving from image fields to image asset fields easier.

Install the bundle using Composer:

```
composer req janit/ezplatform-migrate-image-asset
```

Once installed you can run the command as follows:

```
./bin/console janit:migrate_image_to_asset success_story screenshot screenshot_asset 9372
````

Required arguments are:

- `type_identifier`: Content type identifier to be modified
- `source_field`: Field identifier of source image field
- `target_field`: Field identifier of target image asset field
- `target_location`: Location id of location where created images are to be placed

More information in the blog post: <a href="https://www.ibexa.co/blog/converting-image-fields-to-use-the-image-asset-field-type-in-ez-platform">Converting image fields to use the image asset field type in eZ Platform</a>

## eZ Platform is now Ibexa DXP

Going forward from version 3.2 eZ Platform (Enterprise Edition) will be known as the [Ibexa DXP technology](https://www.ibexa.co/products) that is the base for three products: [Ibexa Content](https://www.ibexa.co/products/ibexa-content), [Ibexa Experience](https://www.ibexa.co/products/ibexa-experience) and [Ibexa Commerce](https://www.ibexa.co/products/ibexa-commerce). Instructions in this code should be relevant since Ibexa DXP is an evolution of eZ Platform, not a revolution. Learn more from the [Ibexa DXP v3.2 launch post](https://www.ibexa.co/blog/product-launch-introducing-ibexa-dxp-3.2) and the [Ibexa developer portal](https://developers.ibexa.co).
