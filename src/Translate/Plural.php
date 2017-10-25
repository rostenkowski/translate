<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use function array_key_exists;

class Plural implements PluralInterface
{


	/**
	 * default plural schema (english-compatible)
	 *
	 * @var string
	 */
	private $defaultSchema = 'nplurals=2; plural=(n != 1)';

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


	public function plural(string $locale, int $count): int
	{
		// cache eval results
		if (array_key_exists($cacheKey = "$locale.$count", $this->evalCache)) {
			$this->evalCacheHitCounter++;

			return $this->evalCache[$cacheKey];
		}

		// evaluate schema
		$schema = $this->defaultSchema;
		if (isset($this->schemas[$locale])) {
			$schema = $this->schemas[$locale];
		}

		// create php code from the schema
		$code = preg_replace('/([a-z]+)/', '$$1', "n=$count; " . $schema) . '; return (int) $plural;';
		$this->evalCounter++;

		return $this->evalCache[$cacheKey] = eval($code);
	}

}
