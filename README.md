# collection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Code Climate][ico-cc]][link-cc]
[![Tests Coverage][ico-cc-coverage]][link-cc]

A PHP class for fast iterables manipulation (arrays, generators, iterators).

All predicate / mutator callables receive the current value, current key and the whole collection as arguments.

`map` & `filter` do not execute immediately, but instead stack up until you need to get a value (calling `squash`, `toArray`, `value`). Where possible helpers use `map` & `filter` in order to minimize RAM usage. This means that it is possible to iterate through a large file reading line by line while maintaining a minimal memory footprint.

## Install

Via Composer

``` bash
$ composer require vakata/collection
```

## Usage

``` php
$collection = \vakata\collection\Collection::from([1,2,3,4,5,6]); 
$result = $collection
    ->filter(function ($v) { return $v % 2 === 0; })
    ->map(function ($v) { return $v + 1; })
    ->head(2)
    ->toArray();
```

Read more in the [API docs](docs/README.md)

## Testing

``` bash
$ composer test
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email github@vakata.com instead of using the issue tracker.

## Credits

A big thanks to im0rtality and the great [Underscore package][https://github.com/Im0rtality/Underscore]

- [vakata][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/vakata/collection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/vakata/collection/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/vakata/collection.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/vakata/collection.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/vakata/collection.svg?style=flat-square
[ico-cc]: https://img.shields.io/codeclimate/github/vakata/collection.svg?style=flat-square
[ico-cc-coverage]: https://img.shields.io/codeclimate/coverage/github/vakata/collection.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/vakata/collection
[link-travis]: https://travis-ci.org/vakata/collection
[link-scrutinizer]: https://scrutinizer-ci.com/g/vakata/collection/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/vakata/collection
[link-downloads]: https://packagist.org/packages/vakata/collection
[link-author]: https://github.com/vakata
[link-contributors]: ../../contributors
[link-cc]: https://codeclimate.com/github/vakata/collection

