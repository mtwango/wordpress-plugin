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

- composer/installers:^1.9
- cweagans/composer-patches:^1.7
- drush/drush:^8.4

## Usage

### Configuration

Drupal repository must be defined:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.drupal.org/7"
        }
    ]
}
```

Plugin default values (AKA you don't need to add these if not overriding):

```json
{
    "extra": {
        "mona-plugin": {
            "composer-exit-on-patch-failure": true,
            "installer-paths": {
                "vendor/drupal": ["type:drupal-core"],
                "${webroot}/sites/all/libraries/{$name}": ["type:drupal-library"],
                "${webroot}/sites/all/modules/contrib/{$name}": ["type:drupal-module"],
                "${webroot}/sites/all/themes/{$name}": ["type:drupal-theme"],
                "${webroot}/sites/all/drush/{$name}": ["type:drupal-drush"]
            },
            "symlinks": {
            },
            "symlinks-force-create": false,
            "symlinks-skip-missing-target": false,
            "symlinks-absolute-path": false,
            "symlinks-throw-exception": true,
            "webroot": "public"
        }
    }
}
```

### Symlinking

Create the symlinks to `extra.mona-plugin.symlinks` section.

Set `symlinks-skip-missing-target` to true if we should not throw exception if target path doesn't exists
Set `symlinks-absolute-path` to true if you want to create realpath symlinks
Set `symlinks-throw-exception` to false if you dont want to break creating on some error while check symlinks
Set `symlinks-force-create` to force unlink link if something already exists on link path

You can set personal configs for any symlink.
For personal configs `link` must be defined

```json
{
    "extra": {
        "mona-plugin": {
            "symlinks": {
                "vendor/woocommerce/flexslider": "public/sites/all/libraries/flexslider"
            },
            "symlinks-force-create": false,
            "symlinks-skip-missing-target": false,
            "symlinks-absolute-path": false,
            "symlinks-throw-exception": true
        }
    }
}
```

### Adding library as a drupal-library

If you need library to be installed to `sites/all/libraries`,

you can list it as a Drupal library if it's found from [Packagist](https://packagist.org/):

```json
{
    "extra": {
        "mona-plugin": {
            "libraries": [
                "ckeditor/ckeditor"
            ]
        }
     }
}
```

or you can define a custom repository:

```json
{
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
}
```

## Forked from

This plugin is based on [ComposerSymlinks](https://github.com/somework/composer-symlinks) and modified to be used with
Drupal 7 Composer based installations. Mona Composer Plugin is released under same license.

## License

This component is under the MIT license. See the complete license in the [LICENSE](LICENSE) file.
