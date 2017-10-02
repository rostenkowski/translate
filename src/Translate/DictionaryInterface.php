<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


interface DictionaryInterface
{

	/**
	 * @param string $message
	 * @return string|array
	 */
	public function get(string $message);


	public function has(string $message): bool;

}
