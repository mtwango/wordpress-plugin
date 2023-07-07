# WordPress Composer Plugin

[![Tests](https://github.com/mtwango/wordpress-plugin/actions/workflows/tests.yml/badge.svg)](https://github.com/mtwango/wordpress-plugin/actions/workflows/tests.yml)

Composer plugin to symlink paths to create Composer based WordPress installation.

## Install / update

To install / update the latest stable version of this component, open a console and execute the following command:

```
composer require mtwango/wordpress-plugin
```

## Includes

This plugin will also require for you the following packages:

- composer/installers:^2.2
- cweagans/composer-patches:^1.7

## Usage

### Configuration

WordPress' plugins and themes repository must be defined:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://wpackagist.org",
            "only": [
                "wpackagist-plugin/*",
                "wpackagist-theme/*"
            ]
        }
    ]
}
```

Plugin default values (AKA you don't need to add these if not overriding):

```json
{
    "extra": {
        "wordpress-plugin": {
            "composer-exit-on-patch-failure": true,
            "installer-paths": {
                "johnpbloch/wordpress-core": ["type:wordpress-core"],
                "${webroot}/wp-content/plugins/{$name}": ["type:wordpress-plugin"],
                "${webroot}/wp-content/themes/{$name}": ["type:wordpress-theme"]
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

### .gitignore

This plugin has example `.gitignore` included in `assets` folder, which ignores WordPress Core and by default
all plugins. There is an example how to exclude custom plugins from `.gitignore`. You are free to copy it to your
webroot or modify it and have it elsewhere.

### Symlinking

Create the symlinks to `extra.wordpress-plugin.symlinks` section.

- Set `symlinks-skip-missing-target` to true if we should not throw exception if target path doesn't exist
- Set `symlinks-absolute-path` to true if you want to create realpath symlinks
- Set `symlinks-throw-exception` to false if you don't want to break creating on some error while check symlinks
- Set `symlinks-force-create` to force unlink link if something already exists on link path

You can set personal configs for any symlink.

For personal configs `link` must be defined

```json
{
    "extra": {
        "wordpress-plugin": {
            "symlinks": {
                "vendor/namespace/must-use": "public/wp-content/mu-plugins/must-use"
            },
            "symlinks-force-create": false,
            "symlinks-skip-missing-target": false,
            "symlinks-absolute-path": false,
            "symlinks-throw-exception": true
        }
    }
}
```

## WordPress

### Core

As WordPress core, it is intended to use package `johnpbloch/wordpress-core`

### Updates

If you want to manage your WordPress core, themes and plugins with Composer, you should disable automatic updates.
You can use plugins to do that, for example [Easy Updates Manager](https://wordpress.org/plugins/stops-core-theme-and-plugin-updates/).
Or you can either use included plugin `composer-plugin-no-updates` (in _mu-plugins_ folder or in _plugins_ folder, if you want to be able to disable it) or following manual configuration:

To **disable WordPress Core updates**, add following line in your `wp-config.php`:

```php
define( 'WP_AUTO_UPDATE_CORE', false );
```

To **disable plugin and theme updates**, you need to use filters in your theme's `functions.php` file:

```php
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );
```

## Testing

Install with dev dependencies:

```
composer install
```

Run tests from root:

```
./vendor/bin/phpcs src --standard=Drupal --colors
```

## Forked from

This plugin is based on [Mona Composer Plugin](https://github.com/druidfi/mona-plugin) and modified to be used with
WordPress Composer based installations. WordPress Composer Plugin is released under same license.

## License

This component is under the MIT license. See the complete license in the [LICENSE](LICENSE) file.
