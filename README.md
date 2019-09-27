# selective/artifact

A artifact generator for PHP.

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/artifact.svg?style=flat-square)](https://packagist.org/packages/selective/artifact)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/selective-php/artifact/master.svg?style=flat-square)](https://travis-ci.org/selective-php/artifact)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/selective-php/artifact.svg?style=flat-square)](https://scrutinizer-ci.com/g/selective-php/artifact/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/selective-php/artifact.svg?style=flat-square)](https://scrutinizer-ci.com/g/selective-php/artifact/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/artifact.svg?style=flat-square)](https://packagist.org/packages/selective/artifact/stats)


## Requirements

* PHP 7.1+

## Installation

```bash
composer require selective/artifact
```

Add this script to composer.json

```json
{
    "scripts": {
        "build": "artifact build"
    }
}
```

## Usage

To generate the artifact (zip file) for deployment, run:

```bash
composer build
```

The generated artifact will be stored in the `build/` directory.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
