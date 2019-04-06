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

- composer/installers:^1.6
- cweagans/composer-patches:^1.6
- drupal/drupal:^7.65
- drush/drush:^8.2

## Usage

### Configuration

Create the symlinks definition adding a `mona-plugin` section inside the `extra` section of the composer.json
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
        "mona-plugin": {
            "symlinks": {
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

### Adding library as a drupal-library

If you need library to be installed to `sites/all/libraries`, you can define a custom repository:

```json
"repositories": [
    {
        "type":"package",
        "package": {
            "name": "ckeditor/ckeditor",
            "version": "4.1.2",
            "dist": {
                "type": "zip",
                "url": "https://github.com/ckeditor/ckeditor-releases/archive/4.1.2/full.zip"
            },
            "type": "drupal-library"
        }
    }
]
```

### Notes

- DO NOT use --no-plugins for composer install or update

## Forked from

This plugin is based on [ComposerSymlinks](https://github.com/somework/composer-symlinks) and modified to be used with
Drupal 7 Composer based installations. Mona Composer Plugin is released under same license.

## License

This component is under the MIT license. See the complete license in the [LICENSE](LICENSE) file.
