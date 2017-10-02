<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Rostenkowski\Translate\NeonDictionary\NeonDictionaryException;
use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

// test: load dictionary from cache
Assert::noError(function () {
	$dataDir = __DIR__ . '/translations';
	$cacheDir = __DIR__ . '/temp/cache';
	$factory = new NeonDictionaryFactory($dataDir, $cacheDir);
	$cacheFile = "$cacheDir/cs_CZ.php";

	if (file_exists($cacheFile)) {
		unlink($cacheFile);
	}

	// load without cache
	$dictionary = $factory->create('cs_CZ');
	$dictionary->get('Welcome!');

	Assert::true(file_exists($cacheFile), 'Cache file not found.');

	// load from cache
	$dictionary = $factory->create('cs_CZ');
	$dictionary->get('Welcome!');
});

// test error: translation dir not found
$dataDir = __DIR__ . '/does-not-exist';
$tempDir = __DIR__ . '/temp/cache';
Assert::exception(function () use ($dataDir, $tempDir) {

	new NeonDictionaryFactory($dataDir, $tempDir);

}, NeonDictionaryException::class, sprintf("Translation directory %s not found.", $dataDir));

// test error: cache dir is not writable
$cacheDir = __DIR__ . '/not-a-directory';
touch($cacheDir);
Assert::exception(function () use ($cacheDir) {

	new NeonDictionaryFactory(__DIR__ . '/translations', $cacheDir);

}, NeonDictionaryException::class, sprintf("Cache directory %s is not writable.", $cacheDir));
unlink($cacheDir);
