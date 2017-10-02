# Translate 

High Performance Localization Component for PHP 7

[![Build Status](https://travis-ci.org/rostenkowski/translate.svg?branch=master)](https://travis-ci.org/rostenkowski/translate)
[![Coverage Status](https://coveralls.io/repos/github/rostenkowski/translate/badge.svg)](https://coveralls.io/github/rostenkowski/translate)
[![Latest Stable Version](https://poser.pugx.org/rostenkowski/translate/v/stable)](https://github.com/rostenkowski/translate/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/rostenkowski/translate/blob/master/LICENSE)


## Usage

```bash
composer require rostenkowski/translate
```

Put your translations to `<LOCALE>.neon` files this way: 

```neon
# simple translation
"Welcome!": "Vítejte!"

# translation with placeholder
"Hi %s!": "Ahoj %s!" 

# translation with multiple plural forms
"You have %s points.": 
	- "Máte %s bod."
	- "Máte %s body."
	- "Máte %s bodů."
```


### Usage with Nette Framework

Register extension:
```neon
extensions:
	translate: Rostenkowski\Translate
```
All parameters are optional with these defaults:
```neon
translate:
	default: en_US
	dictionary: 
		factory: Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory
		args: [%appDir%/translations, %tempDir%/cache/translations]
```


### Usage with plain PHP

```php
<?php

namespace App;

require __DIR__ . '/vendor/autoload.php';

use Rostenkowski\Translate\Translator;
use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;

// both translations and cache are in the same directory
$translator = new Translator(new NeonDictionaryFactory(__DIR__, __DIR__));
$translator->setLocale('cs_CZ');
$translator->translate('Welcome!');
```


## Requirements

- PHP 7.1+
- nette/di
- nette/neon
- nette/safe-stream
- nette/utils
- nette/tester


## Contributing

```bash
# run tests
vendor/bin/tester -c tests/php.ini tests/

# code coverage report (requires xdebug)
vendor/bin/tester -c tests/php-coverage.ini --coverage-src src/ --coverage ~/coverage-report.html tests/  

# remove test artifacts
rm -rf tests/temp/cache/
```
