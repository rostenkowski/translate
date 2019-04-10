# Translate [![Build Status](https://travis-ci.org/rostenkowski/translate.svg?branch=master)](https://travis-ci.org/rostenkowski/translate) [![Coverage Status](https://coveralls.io/repos/github/rostenkowski/translate/badge.svg)](https://coveralls.io/github/rostenkowski/translate)

Minimalistic yet powerful high performance message localization PHP 7.1+ component 
not only for [Nette](https://nette.org) framework

```bash
composer require rostenkowski/translate
```

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
  translate: Rostenkowski\Translate\Extension
  
# configuration
translate:
  default: cs_CZ
```

### Advanced options

You can use special plural form for the count of `0` (zero). 
In translation source file you can define this form under special index `-1`.
```yaml
"%s problems detected":
  -1: "No problem detected"
  - "%s problem detected" 
  - "%s problems detected" 
``` 
```php
$translator->useSpecialZeroForm = true;
$translator->translate('%s problems detected', 0);
// "No problem detected" instead of "0 problems detected"
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
vendor/bin/tester -c tests/php-coverage.ini --coverage-src src/ --coverage ~/report.html tests/  

# remove test artifacts
rm -rf tests/temp/cache/
```
