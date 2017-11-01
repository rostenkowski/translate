<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use NumberFormatter;
use Psr\Log\LoggerInterface;
use function array_key_exists;
use function array_shift;
use function end;
use function func_get_args;
use function gettype;
use function is_numeric;
use function is_object;
use function key;
use function method_exists;
use function ReturnTypes\returnAlias;
use function sprintf;

final class Translator implements TranslatorInterface
{


	private const ZERO_INDEX = -1;

	/**
	 * indicates debug mode
	 *
	 * @var bool
	 */
	private $debugMode = false;

	/**
	 * current locale
	 *
	 * @var string
	 */
	private $locale = 'en_US';

	/**
	 * @var DictionaryInterface|NULL
	 */
	private $dictionary;

	/**
	 * @var DictionaryFactoryInterface
	 */
	private $dictionaryFactory;

	/**
	 * @var LoggerInterface|NULL
	 */
	private $logger;


	public function __construct(DictionaryFactoryInterface $dictionaryFactory, LoggerInterface $logger = NULL, $debugMode = false)
	{
		$this->dictionaryFactory = $dictionaryFactory;
		$this->logger = $logger;
		$this->debugMode = $debugMode;
	}


	public function setDebugMode(bool $debugMode)
	{
		$this->debugMode = $debugMode;
	}


	public function setLocale(string $locale): TranslatorInterface
	{
		if ($locale !== $this->locale) {
			$this->locale = $locale;
			$this->dictionary = NULL;
		}

		return $this;
	}


	public function translate($message, int $count = NULL): string
	{
		// avoid processing for empty values
		if ($message === NULL || $message === '') {
			return '';
		}

		// convert to string
		if (is_object($message) && method_exists($message, '__toString')) {
			$message = (string) $message;
		}

		// numbers are formatted using locale settings (count parameter is used to define decimals)
		if (is_numeric($message)) {
			return $this->formatNumber($message, (int) $count);
		}

		// check message to be string
		if (!is_string($message)) {
			return $this->warn('Message must be string, but %s given.', gettype($message));
		}

		// create dictionary on first access
		if ($this->dictionary === NULL) {
			$this->dictionary = $this->dictionaryFactory->create($this->locale);
		}

		// translation begins
		$result = $message;
		if ($this->dictionary->has($message)) {

			$translation = $this->dictionary->get($message);

			// simple translation
			$result = $translation;

			// process plural
			if (is_array($translation)) {

				if ($count === NULL) {
					$this->warn('Multiple plural forms are available (message: %s), but the $count is NULL.', $message);
				}

				// choose the right plural form based on count
				$form = 0;
				if ($count !== NULL) {
					// special zero
					if ($count === 0 && array_key_exists(self::ZERO_INDEX, $translation)) {
						$form = self::ZERO_INDEX;
					} else {
						$form = $this->plural($count);
					}
				}

				if (!array_key_exists($form, $translation)) {
					$this->warn('Plural form not defined. (message: %s, form: %s)', $message, $form);
				}

				// fallback form
				if ($count === NULL || !array_key_exists($form, $translation)) {
					end($translation);
					$form = key($translation);
				}

				// custom plural form translation
				$result = $translation[$form];

			}

			// protection against accidentally empty-string translations
			if ($result === '') {
				$result = $message;
			}
		}

		// process parameters
		$args = func_get_args();

		// remove message
		array_shift($args);

		// remove count if not provided or explicitly set to NULL
		if ($count === NULL) {
			array_shift($args);
		}

		if (count($args)) {

			// preserve some nette placeholders
			$template = str_replace(['%label', '%name', '%value'], ['%%label', '%%name', '%%value'], $result);

			// apply parameters
			$result = vsprintf($template, $args);
		}

		return $result;
	}


	private function formatNumber($number, int $decimals = 0): string
	{
		$formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
		$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
		$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

		return $formatter->format($number);
	}


	private function warn($message): string
	{
		// format message
		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
			$message = sprintf($message, ...$args);
		}

		// log to psr logger
		if ($this->logger !== NULL) {
			$message = 'translator: ' . $message;
			$this->logger->warning($message);
		}

		// throw exception in debug mode
		if ($this->debugMode === true) {
			throw new TranslatorException($message);
		}

		return $message;
	}


	private function plural(int $n): int
	{
		switch ($this->locale) {
			// english (compatible)
			default:
				return $n === 1 ? 0 : 1;
			case 'id_ID': // indonesian
			case 'ja_JP': // japanese
			case 'ka_GE': // georgian
			case 'ko_KR': // korean
			case 'lo_LA': // lao
			case 'ms_MY': // malay
			case 'my_MM': // burmese
			case 'th_TH': // thai
			case 'vi_VN': // vietnam
			case 'zh_CN': // chinese (simplified)
				return 0;
			case 'cr_CR': // croatian
			case 'ru_RU': // russian
			case 'uk_UA': // ukrainian
				return $n % 10 == 1 && $n % 100 !== 11 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? 1 : 2);
			case 'fr_FR': // french
			case 'tr_TR': // turkish
			case 'uz_UZ': // uzbek
				return $n > 1 ? 1 : 0;
			case 'cs_CZ': // czech
				return $n === 1 ? 0 : (($n >= 2 && $n <= 4) ? 1 : 2);
			case 'is_IS': // icelandic
				return ($n % 10 !== 1 || $n % 100 == 11) ? 1 : 0;
			case 'lt_LT': // lithuanian
				return $n % 10 == 1 && $n % 100 !== 11 ? 0 : ($n % 10 >= 2 && ($n % 100 < 10 or $n % 100 >= 20) ? 1 : 2);
			case 'lv_LV': // latvian
				return ($n % 10 === 1 && $n % 100 !== 11) ? 0 : ($n !== 0 ? 1 : 2);
			case 'mk_MK': // macedonian
				return $n == 1 || $n % 10 == 1 ? 0 : 1;
			case 'mt_MT': // maltese
				return $n == 1 ? 0 : ($n == 0 || ($n % 100 > 1 && $n % 100 < 11) ? 1 : (($n % 100 > 10 && $n % 100 < 20) ? 2 : 3));
			case 'pl_PL': // polish
				return $n == 1 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || ($n % 100 >= 20)) ? 1 : 2);
			case 'sk_SK': // slovak
				return $n == 1 ? 0 : ($n >= 2 && $n <= 4 ? 1 : 2);
			case 'sl_SL': // slovenian
				return $n % 100 == 1 ? 0 : ($n % 100 == 2 ? 1 : ($n % 100 == 3 || $n % 100 == 4 ? 2 : 3));
			case 'ro_RO': // romanian
				return $n == 1 ? 0 : (($n == 0 || ($n % 100 > 0 && $n % 100 < 20)) ? 1 : 2);
		}
	}


	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
