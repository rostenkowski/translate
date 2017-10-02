<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


final class Translator implements TranslatorInterface
{

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
	 * default plural scheme (english-compatible)
	 *
	 * @var string
	 */
	private $defaultScheme = 'nplurals=2; plural=(n != 1)';

	/**
	 * locale-indexed map of irregular schemes
	 *
	 * @var string[]
	 */
	private $schemes = [
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


	public function setLocale(string $locale): TranslatorInterface
	{
		if ($locale !== $this->locale) {

			$this->locale = $locale;

			// change locale -> unset current dictionary
			$this->dictionary = NULL;
		}

		return $this;
	}


	public function translate($message, int $count = NULL): string
	{
		// avoid processing translation for empty values
		if ($message === NULL || $message === '') {

			return $message;
		}

		// avoid non-string values
		if (!is_string($message)) {

			throw TranslatorException::nonStringMessage($message);
		}

		// create dictionary on first access
		if ($this->dictionary === NULL) {
			$this->dictionary = $this->dictionaryFactory->create($this->locale);
		}

		// translation exists
		if ($this->dictionary->has($message)) {

			$translation = $this->dictionary->get($message);

			// multiple plural forms of this message are available
			if (is_array($translation)) {

				// choose the right form
				$form = $this->plural($count);

				// the right plural form may not be defined
				if (!array_key_exists($form, $translation)) {
					while ($form > 0) {
						if (array_key_exists(--$form, $translation)) {
							break;
						}
					}
				}

				// custom plural form translation
				$result = $translation[$form];

			} else {

				// simple translation
				$result = $translation;
			}

		} else {

			// use untranslated message as translation
			$result = $message;
		}

		$args = func_get_args();

		// remove message
		array_shift($args);

		// remove count if not provided
		if ($count === NULL || $count === 0) {
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


	private function plural(int $count): int
	{
		$scheme = $this->defaultScheme;

		if (isset($this->schemes[$this->locale])) {
			$scheme = $this->schemes[$this->locale];
		}

		$code = preg_replace('/([a-z]+)/', '$$1', "n=$count; " . $scheme) . '; return (int) $plural;';

		return eval($code);
	}

}
