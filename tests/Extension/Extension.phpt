<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Nette\Localization\ITranslator;
use Nette\Utils\Finder;
use const TEMP_DIR;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$tempDir = TEMP_DIR;

// create container
$loader = new  ContainerLoader($tempDir, true);
$class = $loader->load(function (Compiler $compiler) use ($tempDir) {

	// use extensions extension to load our extension
	$compiler->addExtension('extensions', new ExtensionsExtension());

	$compiler->addConfig(['parameters' => [
		'appDir'  => __DIR__,
		'tempDir' => $tempDir,
	]]);

	// load our config file
	$compiler->loadConfig(__DIR__ . '/config.neon');
});

$container = new $class;
$translator = $container->getByType(ITranslator::class);

// czech locale is set in test config
Assert::equal('Vítejte!', $translator->translate('Welcome!'));
