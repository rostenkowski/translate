# Translate 

High Performance Localization Component for PHP 7

[![release](http://github-release-version.herokuapp.com/github/rostenkowski/translate/release.svg?style=flat)](https://github.com/rostenkowski/translate/releases/latest)
[![Build Status](https://travis-ci.org/rostenkowski/translate.svg?branch=master)](https://travis-ci.org/rostenkowski/translate)
[![Coverage Status](https://coveralls.io/repos/github/rostenkowski/translate/badge.svg)](https://coveralls.io/github/rostenkowski/translate)
[![Daily Downloads](https://poser.pugx.org/rostenkowski/translate/d/daily)](https://packagist.org/packages/rostenkowski/translate)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/rostenkowski/translate/blob/master/LICENSE)

```bash
composer require rostenkowski/translate
```

## Requirements

- PHP 7.1+
- nette/di
- nette/neon
- nette/safe-stream
- nette/utils
- nette/tester

## Translations 

Translations are stored by default in *.neon files in this format:  

```yml
# simple message
Welcome!: Vítejte!

# with placeholder
Hi %s!: Ahoj %s! 

# multiple forms
You have %s points.: 
  - Máte %s bod.
  - Máte %s body.
  - Máte %s bodů.
```


### Usage with Nette Framework

Put your translations to `%appDir%/translations` directory as `cs_CZ.neon` etc.

```yml
# register extension
extensions:
  translate: Rostenkowski\Translate
  
# configuration
translate:
  default: cs_CZ
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


## Contributing

```bash
# run tests
vendor/bin/tester -c tests/php.ini tests/

# code coverage report (requires xdebug)
vendor/bin/tester -c tests/php-coverage.ini --coverage-src src/ --coverage ~/coverage-report.html tests/  

# remove test artifacts
rm -rf tests/temp/cache/
```
