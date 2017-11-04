<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Rostenkowski\Translate\NeonDictionary\NeonDictionary;
use Rostenkowski\Translate\NeonDictionary\NeonDictionaryException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

// test error: invalid translation file
$filename = __DIR__ . '/not-existing.neon';
$cacheFilename = __DIR__ . '/temp/cache/translations/not-existing.php';
Assert::exception(function () use ($filename, $cacheFilename) {

	new NeonDictionary($filename, $cacheFilename);

}, NeonDictionaryException::class, sprintf("Translation file %s not found.", $filename));
