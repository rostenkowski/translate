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
use function sprintf;

final class Translator implements TranslatorInterface
{


	private const ZERO_INDEX = -1;

	/**
	 * indicates whether to use special zero form for plural messages
	 *
	 * @var bool
	 */
	private $useSpecialZeroForm = false;

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
	 * @var PluralInterface
	 */
	private $plural;

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

		$this->plural = new Plural();
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

		// numbers are formatted using locale settings (count parameter is used to define decimals)
		if (is_numeric($message)) {
			return $this->formatNumber($message, (int) $count);
		}

		// convert to string
		if (is_object($message) && method_exists($message, '__toString')) {
			$message = (string) $message;
		}

		// check message to be string
		if (!is_string($message)) {
			$this->warn('Message must be string, but %s given.', gettype($message));

			return '';
		}

		// create dictionary on first access
		if ($this->dictionary === NULL) {
			$this->dictionary = $this->dictionaryFactory->create($this->locale);
		}

		// translation begins
		$result = $message;
		if ($this->dictionary->has($message)) {

			$translation = $this->dictionary->get($message);

			// process plural
			if (is_array($translation)) {

				if ($count === NULL) {
					$this->warn('Multiple plural forms are available (message: %s), but the $count is NULL.', $message);
				}

				// choose the right plural form based on count
				$form = 0;
				if ($count !== NULL) {

					// special zero
					if ($this->useSpecialZeroForm === true && $count === 0) {
						$form = self::ZERO_INDEX;
					} else {
						$form = $this->plural->plural($this->locale, $count);
					}
				}

				if (!array_key_exists($form, $translation)) {
					$this->warn('Plural form not defined. (message: %s, form: %s)', $message, $form);
				}

				if ($count === NULL || !array_key_exists($form, $translation)) {

					// fallback to last defined
					end($translation);
					$form = key($translation);
				}

				// custom plural form translation
				$result = $translation[$form];

			} else {

				// simple translation
				$result = $translation;
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


	private function warn($message)
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
	}


	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @param bool $useSpecialZeroForm
	 */
	public function setUseSpecialZeroForm(bool $useSpecialZeroForm)
	{
		$this->useSpecialZeroForm = $useSpecialZeroForm;
	}

}
