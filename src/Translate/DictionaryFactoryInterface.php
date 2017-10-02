<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


interface DictionaryFactoryInterface
{

	public function create(string $locale): DictionaryInterface;

}
