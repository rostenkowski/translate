<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Mockery;
use Tester\Environment;
use const TEMP_DIR;
use function lcg_value;

$dir = dirname(__DIR__);

require "$dir/vendor/autoload.php";

define('TEMP_DIR', __DIR__ . '/temp/' . (string) lcg_value());

@mkdir(TEMP_DIR, 0775, true);

Environment::setup();

Mockery::globalHelpers();
