<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;

class Extension extends CompilerExtension
{

	private $defaults = [
		'default'    => 'en_US',
		'dictionary' => [
			'factory' => NeonDictionaryFactory::class,
			'args' => [
				'directory' => '%appDir%/translations',
				'cache'     => '%tempDir%/cache/translations',
			]
		],
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// expand options
		$config = Helpers::expand($this->validateConfig($this->defaults), $builder->parameters);

		// add default neon dictionary factory
		$builder
			->addDefinition($this->prefix('dictionaryFactory'))
			->setFactory($config['dictionary']['factory'], array_values($config['dictionary']['args']))
			->setAutowired(true);

		// add translator
		$builder
			->addDefinition($this->prefix('translator'))
			->setFactory(Translator::class)
			->addSetup('setLocale', [$config['default']])
			->setAutowired(true);

	}

}
