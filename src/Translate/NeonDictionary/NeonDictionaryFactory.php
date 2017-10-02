<?php declare(strict_types=1);

namespace Rostenkowski\Translate\NeonDictionary;


use Rostenkowski\Translate\DictionaryFactoryInterface;
use Rostenkowski\Translate\DictionaryInterface;

final class NeonDictionaryFactory implements DictionaryFactoryInterface
{

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var string
	 */
	private $cacheDir;


	public function __construct(string $directory, string $cacheDir, int $cacheDirMode = 0775)
	{
		if (!is_dir($directory)) {

			throw NeonDictionaryException::translationDirNotFound($directory);
		}

		$this->directory = $directory;

		if (!is_dir($cacheDir) && @!mkdir($cacheDir, $cacheDirMode, true) || !is_writable($cacheDir)) {

			throw NeonDictionaryException::cacheDirIsNotWritable($cacheDir);
		}

		$this->cacheDir = $cacheDir;
	}


	public function create(string $locale): DictionaryInterface
	{
		$sourceFile = "$this->directory/$locale.neon";
		$cacheFile = "$this->cacheDir/$locale.php";

		return new NeonDictionary($sourceFile, $cacheFile);
	}

}
