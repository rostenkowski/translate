<?php declare(strict_types=1);

namespace Rostenkowski\Translate\NeonDictionary;


use function is_array;
use Nette\Neon\Neon;
use Rostenkowski\Translate\Dictionary;

final class NeonDictionary extends Dictionary
{

	/**
	 * @var string
	 */
	private $filename;

	/**
	 * @var string
	 */
	private $cacheFilename;


	public function __construct(string $filename, string $cacheFilename)
	{
		if (!is_file($filename)) {

			throw NeonDictionaryException::fileNotFound($filename);
		}

		$this->filename = $filename;
		$this->cacheFilename = $cacheFilename;
	}


	protected function lazyLoad()
	{
		if (!$this->isReady()) {

			if (is_file($this->cacheFilename)) {

				// load cache
				$this->setMessages(require $this->cacheFilename);

			} else {

				// load translations from neon file
				$decoded = Neon::decode(file_get_contents($this->filename));
				$translations = is_array($decoded) ? $decoded: [];

				// save cache
				$content = '<?php ' . PHP_EOL . 'return ' . var_export($translations, true) . ';' . PHP_EOL;
				file_put_contents("safe://$this->cacheFilename", $content);

				$this->setMessages($translations);
			}
		}
	}

}
