dotblue/unfinalizer
===================

Removes `final` keyword from any source code for easier **testing**.

## Installation

```
$ composer require dotblue/unfinalizer
```

First, you have to require autoloader of `Unfinalizer`. Because removing `final` keyword relies on hacking your autoloaders, you have to bypass them all to autoload `Unfinalizer` (including Composer for example).

```php
require __DIR__ . '/vendor/dotblue/unfinalizer/autoload.php';
```

Secondly, create instance of `Unfinalizer` itself and configure temp directory:

```php
$unfinalizer = new DotBlue\Unfinalizer\Unfinalizer();
$unfinalizer->setTempDirectory(__DIR__ . '/vendor-unfinalized');
```

## Drivers

Last step is registering wrappers for various autoloaders that you may be using.

### Composer

Register  `DotBlue\Unfinalizer\Composer`. It's constructor requires absolute path to the `vendor` directory.

```php
$unfinalizer->register(new DotBlue\Unfinalizer\Composer(__DIR__ . '/vendor'));
```

> That's it, you don't need to require `vendor/autoload.php` by yourself anymore.
