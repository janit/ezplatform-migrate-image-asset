# Migrate eZ Platform image field data to image assets

This repository contains an eZ Platform 3.x compatible Symfony
command that migrates data from the <a href="https://doc.ezplatform.com/en/latest/api/field_type_reference/#image-field-type">image field type</a> to the <a href="https://doc.ezplatform.com/en/latest/api/field_type_reference/#imageasset-field-type">image asset field type</a>.

This script does not do any content type modification, it simply copies and links image data to make moving from image fields to image asset fields easier.

To enable this Command you should make sure it is configured appropriately in config/services.yaml as described in <a href="https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber">the Symfony Event Dispatcher documentation</a>.

Once enabled you can run the command as follows:

```
./bin/console app:migrate-image-to-asset success_story screenshot screenshot_asset 9372
````

Required arguments are:

- `type_identifier`: Content type identifier to be modified
- `source_field`: Field identifier of source image field
- `target_field`: Field identifier of target image asset field
- `target_location`: Location id of location where created images are to be placed

More information in the blog post: <a href="https://www.ibexa.co/blog/converting-image-fields-to-use-the-image-asset-field-type-in-ez-platform">Converting image fields to use the image asset field type in eZ Platform</a>
