# Wordpress Composer Plugin

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

### Symlinking

Create the symlinks to `extra.wordpress-plugin.symlinks` section.

- Set `symlinks-skip-missing-target` to true if we should not throw exception if target path doesn't exists
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

## Development

Install with dev dependencies:

```
composer install
```

Run PHP CS Fixer:

```
vendor/bin/php-cs-fixer fix src
```

## Forked from

This plugin is based on [Mona Composer Plugin](https://github.com/druidfi/mona-plugin) and modified to be used with
WordPress Composer based installations. WordPress Composer Plugin is released under same license.

## License

This component is under the MIT license. See the complete license in the [LICENSE](LICENSE) file.
