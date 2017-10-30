<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Psr\Log\LoggerInterface;
use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;
use Tester\Assert;
use const M_PI;
use function spy;

require __DIR__ . '/bootstrap.php';

$dataDir = __DIR__ . '/translations';
$tempDir = __DIR__ . '/temp/cache';

$translator = new Translator(new NeonDictionaryFactory($dataDir, $tempDir));

// test: simple message
Assert::equal('Welcome!', $translator->translate('Welcome!'));

// test: plural forms
Assert::equal('You have 1 unread message.',
	$translator->translate('You have %s unread messages.', 1));
Assert::equal('You have 2 unread messages.',
	$translator->translate('You have %s unread messages.', 2));
Assert::equal('You have 5 unread messages.',
	$translator->translate('You have %s unread messages.', 5));

// test: process plural forms with parameters
Assert::equal('You have 5 points. Thank you John!',
	$translator->translate('You have %s points. Thank you %s!', 5, 'John'));

// test: process parameters for an untranslated message
Assert::equal('Hi Bernardette!',
	$translator->translate('Hi %s!', NULL, 'Bernardette'));

// custom locale
$translator->setLocale('cs_CZ');

// test: empty message is allowed
Assert::equal('', $translator->translate(''));

// test: simple message
Assert::equal('Vítejte!', $translator->translate('Welcome!'));

// test: plural forms
Assert::equal('Máte 1 nepřečtenou zprávu.',
	$translator->translate('You have %s unread messages.', 1));
Assert::equal('Máte 2 nepřečtené zprávy.',
	$translator->translate('You have %s unread messages.', 2));
Assert::equal('Máte 5 nepřečtených zpráv.',
	$translator->translate('You have %s unread messages.', 5));

// test: undefined plural form
$message = 'You have %s unread articles.';
Assert::same('Máte 5 nepřečtené články.', $translator->translate($message, 5));

// test error: non-string message in production mode
Assert::same('', $translator->translate([]));

// test: NULL count
Assert::same('Máte %s nepřečtených zpráv.', $translator->translate('You have %s unread messages.', NULL));

// test: NULL count in strict mode
Assert::exception(function () use ($translator) {
	$translator->setDebugMode(true);
	$translator->translate('You have %s unread messages.', NULL);
}, TranslatorException::class, 'Multiple plural forms are available (message: You have %s unread messages.), but the $count is NULL.');

// test: accidentally empty translation
Assert::same('Article author', $translator->translate('Article author'));

// test: special form for the parametrized translation with count = 0 (zero)
$translator->setDebugMode(true);
Assert::same("Čas vypršel", $translator->translate('You have %s seconds', 0));
Assert::same("Máte 1 vteřinu", $translator->translate('You have %s seconds', 1));
Assert::same("Máte 2 vteřiny", $translator->translate('You have %s seconds', 2));
Assert::same("Máte 5 vteřin", $translator->translate('You have %s seconds', 5));

// test: string objects
Assert::same('foo', $translator->translate(new class
{

	function __toString() { return 'foo'; }
}));

// test: error: non-string message in debug mode
Assert::exception(function () use ($translator, $message) {
	$translator->translate([]);
}, TranslatorException::class, 'Message must be string, but array given.');

// test: psr logger
$logger = spy(LoggerInterface::class);

$translator->setDebugMode(false);
$translator->setLogger($logger);
$translator->translate([]);

$logger->shouldHaveReceived()->warning('translator: Message must be string, but array given.');

// test: translate numbers
$translator->setLocale('cs_CZ');
Assert::same('3,14', $translator->translate(M_PI, 2));

$translator->setLocale('de_DE');
Assert::same('3,14', $translator->translate(M_PI, 2));

$translator->setLocale('en_GB');
Assert::same('3.14', $translator->translate(M_PI, 2));

$translator->setLocale('en_US');
Assert::same('3.14', $translator->translate(M_PI, 2));

// test: plural

// croatian
$translator->setLocale('cr_CR');
Assert::same('1 spavaća soba', $translator->translate('%s bedrooms', 1));
Assert::same('2 spavaće sobe', $translator->translate('%s bedrooms', 2));
Assert::same('5 spavaćih soba', $translator->translate('%s bedrooms', 5));
Assert::same('100 spavaćih soba', $translator->translate('%s bedrooms', 100));

// french
$translator->setLocale('fr_FR');
Assert::same('1 chambre', $translator->translate('%s bedrooms', 1));
Assert::same('2 chambres', $translator->translate('%s bedrooms', 2));
Assert::same('5 chambres', $translator->translate('%s bedrooms', 5));
Assert::same('100 chambres', $translator->translate('%s bedrooms', 100));

// slovenian
$translator->setLocale('sl_SL');
Assert::same('1 spalnica', $translator->translate('%s bedrooms', 1));
Assert::same('2 spalnici', $translator->translate('%s bedrooms', 2));
Assert::same('3 spalnice', $translator->translate('%s bedrooms', 3));
Assert::same('5 spalnic', $translator->translate('%s bedrooms', 5));
Assert::same('100 spalnic', $translator->translate('%s bedrooms', 100));

// burmese
$translator->setLocale('my_MM');
Assert::same('1 အိပ်ခန်း', $translator->translate('%s bedrooms', 1));
Assert::same('2 အိပ်ခန်း', $translator->translate('%s bedrooms', 2));
Assert::same('3 အိပ်ခန်း', $translator->translate('%s bedrooms', 3));
Assert::same('5 အိပ်ခန်း', $translator->translate('%s bedrooms', 5));
Assert::same('100 အိပ်ခန်း', $translator->translate('%s bedrooms', 100));

// malay
$translator->setLocale('ms_MY');
Assert::same('1 bilik tidur', $translator->translate('%s bedrooms', 1));
Assert::same('2 bilik tidur', $translator->translate('%s bedrooms', 2));
Assert::same('3 bilik tidur', $translator->translate('%s bedrooms', 3));
Assert::same('5 bilik tidur', $translator->translate('%s bedrooms', 5));
Assert::same('100 bilik tidur', $translator->translate('%s bedrooms', 100));

// slovak
$translator->setLocale('sk_SK');
Assert::same('1 spálňa', $translator->translate('%s bedrooms', 1));
Assert::same('2 spálne', $translator->translate('%s bedrooms', 2));
Assert::same('5 spální', $translator->translate('%s bedrooms', 5));
Assert::same('100 spální', $translator->translate('%s bedrooms', 100));

// romanian
$translator->setLocale('ro_RO');
Assert::same('1 dormitor', $translator->translate('%s bedrooms', 1));
Assert::same('2 dormitoare', $translator->translate('%s bedrooms', 2));
Assert::same('5 dormitoare', $translator->translate('%s bedrooms', 5));
Assert::same('100 dormitoare', $translator->translate('%s bedrooms', 100));

// russian
$translator->setLocale('ru_RU');
Assert::same('1 спальня', $translator->translate('%s bedrooms', 1));
Assert::same('2 спальни', $translator->translate('%s bedrooms', 2));
Assert::same('5 спален', $translator->translate('%s bedrooms', 5));
Assert::same('100 спален', $translator->translate('%s bedrooms', 100));

// ukrainian
$translator->setLocale('uk_UA');
Assert::same('1 спальня', $translator->translate('%s bedrooms', 1));
Assert::same('2 спальні', $translator->translate('%s bedrooms', 2));
Assert::same('5 спалень', $translator->translate('%s bedrooms', 5));
Assert::same('100 спалень', $translator->translate('%s bedrooms', 100));

// chinese (simplified)
$translator->setLocale('zh_CN');
Assert::same('1间卧室', $translator->translate('%s bedrooms', 1));
Assert::same('2间卧室', $translator->translate('%s bedrooms', 2));
Assert::same('5间卧室', $translator->translate('%s bedrooms', 5));
Assert::same('100间卧室', $translator->translate('%s bedrooms', 100));

// japanese
$translator->setLocale('ja_JP');
Assert::same('1ベッドルーム', $translator->translate('%s bedrooms', 1));
Assert::same('2ベッドルーム', $translator->translate('%s bedrooms', 2));
Assert::same('5ベッドルーム', $translator->translate('%s bedrooms', 5));
Assert::same('100ベッドルーム', $translator->translate('%s bedrooms', 100));

$translator->setLocale('vi_VN');
Assert::same('1 phòng ngủ', $translator->translate('%s bedrooms', 1));
Assert::same('2 phòng ngủ', $translator->translate('%s bedrooms', 2));
Assert::same('5 phòng ngủ', $translator->translate('%s bedrooms', 5));
Assert::same('100 phòng ngủ', $translator->translate('%s bedrooms', 100));

$translator->setLocale('uz_UZ');
Assert::same('1 xonali', $translator->translate('%s bedrooms', 1));
Assert::same('2 yotoq xonasi', $translator->translate('%s bedrooms', 2));
Assert::same('5 yotoq xonasi', $translator->translate('%s bedrooms', 5));
Assert::same('100 yotoq xonasi', $translator->translate('%s bedrooms', 100));

$translator->setLocale('tr_TR');
Assert::same('1 yatak odası', $translator->translate('%s bedrooms', 1));
Assert::same('2 yatak odası', $translator->translate('%s bedrooms', 2));
Assert::same('5 yatak odası', $translator->translate('%s bedrooms', 5));
Assert::same('100 yatak odası', $translator->translate('%s bedrooms', 100));

$translator->setLocale('th_TH');
Assert::same('1 ห้องนอน', $translator->translate('%s bedrooms', 1));
Assert::same('2 ห้องนอน', $translator->translate('%s bedrooms', 2));
Assert::same('5 ห้องนอน', $translator->translate('%s bedrooms', 5));
Assert::same('100 ห้องนอน', $translator->translate('%s bedrooms', 100));

$translator->setLocale('is_IS');
Assert::same('1 svefnherbergi', $translator->translate('%s bedrooms', 1));
Assert::same('2 svefnherbergi', $translator->translate('%s bedrooms', 2));
Assert::same('5 svefnherbergi', $translator->translate('%s bedrooms', 5));
Assert::same('100 svefnherbergi', $translator->translate('%s bedrooms', 100));

// korean
$translator->setLocale('ko_KR');
Assert::same('1 개 침실', $translator->translate('%s bedrooms', 1));
Assert::same('2 개 침실', $translator->translate('%s bedrooms', 2));
Assert::same('5 개 침실', $translator->translate('%s bedrooms', 5));
Assert::same('100 개 침실', $translator->translate('%s bedrooms', 100));

// lao
$translator->setLocale('lo_LA');
Assert::same('1 ຫ້ອງນອນ', $translator->translate('%s bedrooms', 1));
Assert::same('2 ຫ້ອງນອນ', $translator->translate('%s bedrooms', 2));
Assert::same('5 ຫ້ອງນອນ', $translator->translate('%s bedrooms', 5));
Assert::same('100 ຫ້ອງນອນ', $translator->translate('%s bedrooms', 100));

// latvian
$translator->setLocale('lv_LV');
Assert::same('1 miegamasis', $translator->translate('%s bedrooms', 1));
Assert::same('2 miegamieji', $translator->translate('%s bedrooms', 2));
Assert::same('5 miegamieji', $translator->translate('%s bedrooms', 5));
Assert::same('100 miegamieji', $translator->translate('%s bedrooms', 100));

// lithuanian
$translator->setLocale('lt_LT');
Assert::same('1 guļamistaba', $translator->translate('%s bedrooms', 1));
Assert::same('2 guļamistabas', $translator->translate('%s bedrooms', 2));
Assert::same('5 guļamistabas', $translator->translate('%s bedrooms', 5));
Assert::same('100 guļamistabas', $translator->translate('%s bedrooms', 100));

// maltese
$translator->setLocale('mt_MT');
Assert::same('1 kamra tas-sodda', $translator->translate('%s bedrooms', 1));
Assert::same('2 kamra tas-sodda', $translator->translate('%s bedrooms', 2));
Assert::same('5 kamra tas-sodda', $translator->translate('%s bedrooms', 5));
Assert::same('100 kamra tas-sodda', $translator->translate('%s bedrooms', 100));

// macedonian
$translator->setLocale('mk_MK');
Assert::same('1 спална соба', $translator->translate('%s bedrooms', 1));
Assert::same('2 спални', $translator->translate('%s bedrooms', 2));
Assert::same('5 спални', $translator->translate('%s bedrooms', 5));
Assert::same('100 спални', $translator->translate('%s bedrooms', 100));

// polish
$translator->setLocale('pl_PL');
Assert::same('1 spálňa', $translator->translate('%s bedrooms', 1));
Assert::same('2 spálne', $translator->translate('%s bedrooms', 2));
Assert::same('5 spální', $translator->translate('%s bedrooms', 5));
Assert::same('100 spální', $translator->translate('%s bedrooms', 100));

// indonesian
$translator->setLocale('id_ID');
Assert::same('1 kamar tidur', $translator->translate('%s bedrooms', 1));
Assert::same('2 kamar tidur', $translator->translate('%s bedrooms', 2));
Assert::same('5 kamar tidur', $translator->translate('%s bedrooms', 5));
Assert::same('100 kamar tidur', $translator->translate('%s bedrooms', 100));

// georgian
$translator->setLocale('ka_GE');
Assert::same('1 საძინებელი', $translator->translate('%s bedrooms', 1));
Assert::same('2 საძინებელი', $translator->translate('%s bedrooms', 2));
Assert::same('5 საძინებელი', $translator->translate('%s bedrooms', 5));
Assert::same('100 საძინებელი', $translator->translate('%s bedrooms', 100));
