<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Mockery;
use Tester\Environment;

$dir = dirname(__DIR__);

require "$dir/vendor/autoload.php";

@mkdir(__DIR__ . '/temp', 0775, true);

Environment::setup();

Mockery::globalHelpers();
