# Mona Composer Plugin

Composer plugin to symlink paths to create Composer based Drupal 7 installation.

See more in Mona repository: https://github.com/druidfi/mona

## Installation

To install the latest stable version of this component, open a console and execute the following command:

```
$ composer require druidfi/mona-plugin
```

## Includes

This plugin will also require for you the following packages:

- Drush 8.2.x

## Usage

### Configuration

Create the symlinks definition adding a `druidfi/mona-plugin` section inside the `extra` section of the composer.json
file.

Set `skip-missing-target` to true if we should not throw exception if target path doesn't exists  
Set `absolute-path` to true if you want to create realpath symlinks  
Set `throw-exception` to false if you dont want to break creating on some error while check symlinks  
Set `force-create` to force unlink link if something already exists on link path    

You can set personal configs for any symlink.  
For personal configs `link` must be defined  

```json
{
    "extra": {
        "druidfi/mona-plugin": {
            "symlinks": {
                "vendor/ckeditor/ckeditor": "public/sites/all/libraries/ckeditor",
                "vendor/drupal/authorize.php": "public/authorize.php",
                "vendor/drupal/cron.php": "public/cron.php",
                "vendor/drupal/index.php": "public/index.php",
                "vendor/drupal/robots.txt": "public/robots.txt",
                "vendor/drupal/update.php": "public/update.php",
                "vendor/drupal/xmlrpc.php": "public/xmlrpc.php",
                "vendor/drupal/includes": "public/includes",
                "vendor/drupal/misc": "public/misc",
                "vendor/drupal/modules": "public/modules",
                "vendor/drupal/profiles/standard": "public/profiles/standard",
                "vendor/drupal/themes": "public/themes",
                "vendor/drupal_modules": "public/sites/all/modules/contrib",
                "vendor/drupal_themes/omega": "public/sites/all/themes/omega",
                "vendor/woocommerce/flexslider": "public/sites/all/libraries/flexslider"
            },
            "force-create": false,
            "skip-missing-target": false,
            "absolute-path": false,
            "throw-exception": true
        }
    }
}
```

### Notes

- DO NOT use --no-plugins for composer install or update

## Forked from

This plugin is based on [ComposerSymlinks](https://github.com/somework/composer-symlinks) and modified to be used with
Drupal 7 Composer based installations. Mona Composer Plugin is released under same license.

## License

This component is under the MIT license. See the complete license in the [LICENSE](LICENSE) file.
