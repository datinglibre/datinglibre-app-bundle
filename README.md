# datinglibre-app-bundle

![Build Status](https://github.com/datinglibre/datinglibre-app-bundle/actions/workflows/datinglibre-app-bundle.yml/badge.svg)

This Symfony bundle is designed to be developed with [DatingLibre](https://github.com/datinglibre/DatingLibre) repository.

See the [development](https://github.com/datinglibre/DatingLibre/wiki/Development) section of the [Wiki](https://github.com/datinglibre/DatingLibre/wiki).

## Installation

The `datinglibre-app-bundle` includes many Behat test classes to test your implementation of DatingLibre.

These can be excluded from the production installation, by excluding them from the autoloader.

Add the following classmap exclusion to the `autoload` section of `composer.json`:

    "autoload": {
            "psr-4": {
                "App\\": "src/"
        },
        "exclude-from-classmap": ["**/Behat/**"]
    }

Install on production with 

    composer install --no-dev --classmap-authoritative

## Licence

Copyright 2020-2021 DatingLibre.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
