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

	public const ZERO_INDEX = -1;

	/**
	 * @var bool
	 */
	public $useSpecialZeroForm = false;

	/**
	 * @var bool
	 */
	public $debugMode = false;

	/**
	 * current locale
	 *
	 * @var string
	 */
	private $locale = 'en_US';

	/**
	 * @var DictionaryInterface
	 */
	private $dictionary;

	/**
	 * @var NeonDictionaryFactory
	 */
	private $dictionaryFactory;

	/**
	 * default plural schema (english-compatible)
	 *
	 * @var string
	 */
	private $defaultScheme = 'nplurals=2; plural=(n != 1)';

	/**
	 * @var int
	 */
	private $evalCounter = 0;

	/**
	 * @var int
	 */
	private $evalCacheHitCounter = 0;

	/**
	 * @var array
	 */
	private $evalCache = [];

	/**
	 * @var LoggerInterface
	 */
	private $logger;


	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * locale-indexed map of irregular plural schemas
	 *
	 * @var string[]
	 */
	private $schemas = [
		// czech
		'cs_CZ' => 'nplurals=3; plural=(n==1) ? 0 : ((n>=2 && n<=4) ? 1 : 2)',
		// croatian
		'cr_CR' => 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : (n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2))',
		// french
		'fr_FR' => 'nplurals=2; plural=(n > 1)',
		// indonesian
		'id_ID' => 'nplurals=1; plural=0',
		// icelandic
		'is_IS' => 'nplurals=2; plural=(n%10!=1 || n%100==11)',
		// japanese
		'ja_JP' => 'nplurals=1; plural=0',
		// georgian
		'ka_GE' => 'nplurals=1; plural=0',
		// korean
		'ko_KR' => 'nplurals=1; plural=0',
		// lao
		'lo_LA' => 'nplurals=1; plural=0',
		// lithuanian
		'lt_LT' => 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : (n%10>=2 && (n%100<10 or n%100>=20) ? 1 : 2))',
		// macedonian
		'mk_MK' => 'nplurals=2; plural= n==1 || n%10==1 ? 0 : 1',
		// maltese
		'mt_MT' => 'nplurals=4; plural=(n==1 ? 0 : (n==0 || ( n%100>1 && n%100<11) ? 1 : ((n%100>10 && n%100<20 ) ? 2 : 3)))',
		// malay
		'ms_MY' => 'nplurals=1; plural=0',
		// burmese
		'my_MM' => 'nplurals=1; plural=0',
		// polish
		'pl_PL' => 'nplurals=3; plural=(n==1 ? 0 : (n%10>=2 && n%10<=4 && (n%100<10 || (n%100>=20) ? 1 : 2)))',
		// slovak
		'sk_SK' => 'nplurals=3; plural=(n==1) ? 0 : ((n>=2 && n<=4) ? 1 : 2)',
		// slovenian
		'sl_SL' => 'nplurals=4; plural=(n%100==1 ? 1 : ((n%100==2 ? 2 : n%100==3) || (n%100==4 ? 3 : 0)))',
		// romanian
		'ro_RO' => 'nplurals=3; plural=(n==1 ? 0 : ((n==0 || (n%100 > 0 && n%100 < 20)) ? 1 : 2));',
		// russian
		'ru_RU' => 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : (n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2))',
		// thai
		'th_TH' => 'nplurals=1; plural=0',
		// turkish
		'tr_TR' => 'nplurals=2; plural=(n>1)',
		// ukrainian
		'uk_UA' => 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : (n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2))',
		// uzbek
		'uz_UZ' => 'nplurals=2; plural=(n > 1)',
		// vietnamese
		'vi_VN' => 'nplurals=1; plural=0',
		// chinese
		'zh_CN' => 'nplurals=1; plural=0',
	];


	public function __construct(DictionaryFactoryInterface $dictionaryFactory)
	{
		$this->dictionaryFactory = $dictionaryFactory;
	}


	public function getStats()
	{
		return [
			'evalCacheHitCounter' => $this->evalCacheHitCounter,
			'evalCounter'         => $this->evalCounter,
		];
	}


	public function setLocale(string $locale): TranslatorInterface
	{
		if ($locale !== $this->locale) {

			$this->locale = $locale;

			// change locale -> unset current dictionary
			$this->dictionary = NULL;
		}

		return $this;
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
		if ($this->isDebugMode()) {
			throw new TranslatorException($message);
		}
	}


	public function translate($message, int $count = NULL): string
	{
		// avoid processing for empty values
		if ($message === NULL || $message === '') {

			return '';
		}

		// numbers are formatted using locale settings (count parameter is used to define decimals)
		if (is_numeric($message)) {
			$formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
			$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $count);
			$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $count);

			return $formatter->format($message);
		}

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
					$form = $this->plural($count);
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


	public function isDebugMode(): bool
	{
		return $this->debugMode;
	}


	public function setDebugMode(bool $debugMode)
	{
		$this->debugMode = $debugMode;
	}


	private function plural(int $count): int
	{
		// special zero
		if ($this->useSpecialZeroForm === true && $count === 0) {

			return self::ZERO_INDEX;
		}

		// cache eval results
		if (array_key_exists($cacheKey = "$this->locale.$count", $this->evalCache)) {
			$this->evalCacheHitCounter++;

			return $this->evalCache[$cacheKey];
		}

		// evaluate schema
		$schema = $this->defaultScheme;
		if (isset($this->schemas[$this->locale])) {
			$schema = $this->schemas[$this->locale];
		}

		// create php code from the schema
		$code = preg_replace('/([a-z]+)/', '$$1', "n=$count; " . $schema) . '; return (int) $plural;';
		$this->evalCounter++;

		return $this->evalCache[$cacheKey] = eval($code);
	}

}
