<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;
use Tester\Assert;
use const TEMP_DIR;

require __DIR__ . '/../../bootstrap.php';

$dataDir = __DIR__ . '/translations';
$tempDir = TEMP_DIR;

$t = new Translator(new NeonDictionaryFactory($dataDir, $tempDir));

// test: plural
$message = '%s bedrooms';
// croatian
$t->setLocale('cr_CR');
Assert::same('1 spavaća soba', $t->translate($message, 1));
Assert::same('2 spavaće sobe', $t->translate($message, 2));
Assert::same('5 spavaćih soba', $t->translate($message, 5));
Assert::same('100 spavaćih soba', $t->translate($message, 100));

// czech
$t->setLocale('cs_CZ');
Assert::same('1 ložnice', $t->translate($message, 1));
Assert::same('2 ložnice', $t->translate($message, 2));
Assert::same('5 ložnic', $t->translate($message, 5));
Assert::same('100 ložnic', $t->translate($message, 100));

// english
$t->setLocale('en_US');
Assert::same('1 bedroom', $t->translate($message, 1));
Assert::same('2 bedrooms', $t->translate($message, 2));
Assert::same('5 bedrooms', $t->translate($message, 5));
Assert::same('100 bedrooms', $t->translate($message, 100));

// french
$t->setLocale('fr_FR');
Assert::same('1 chambre', $t->translate($message, 1));
Assert::same('2 chambres', $t->translate($message, 2));
Assert::same('5 chambres', $t->translate($message, 5));
Assert::same('100 chambres', $t->translate($message, 100));

// slovenian
$t->setLocale('sl_SL');
Assert::same('1 spalnica', $t->translate($message, 1));
Assert::same('2 spalnici', $t->translate($message, 2));
Assert::same('3 spalnice', $t->translate($message, 3));
Assert::same('5 spalnic', $t->translate($message, 5));
Assert::same('100 spalnic', $t->translate($message, 100));

// burmese
$t->setLocale('my_MM');
Assert::same('1 အိပ်ခန်း', $t->translate($message, 1));
Assert::same('2 အိပ်ခန်း', $t->translate($message, 2));
Assert::same('3 အိပ်ခန်း', $t->translate($message, 3));
Assert::same('5 အိပ်ခန်း', $t->translate($message, 5));
Assert::same('100 အိပ်ခန်း', $t->translate($message, 100));

// malay
$t->setLocale('ms_MY');
Assert::same('1 bilik tidur', $t->translate($message, 1));
Assert::same('2 bilik tidur', $t->translate($message, 2));
Assert::same('3 bilik tidur', $t->translate($message, 3));
Assert::same('5 bilik tidur', $t->translate($message, 5));
Assert::same('100 bilik tidur', $t->translate($message, 100));

// slovak
$t->setLocale('sk_SK');
Assert::same('1 spálňa', $t->translate($message, 1));
Assert::same('2 spálne', $t->translate($message, 2));
Assert::same('5 spální', $t->translate($message, 5));
Assert::same('100 spální', $t->translate($message, 100));

// romanian
$t->setLocale('ro_RO');
Assert::same('1 dormitor', $t->translate($message, 1));
Assert::same('2 dormitoare', $t->translate($message, 2));
Assert::same('5 dormitoare', $t->translate($message, 5));
Assert::same('100 dormitoare', $t->translate($message, 100));

// russian
$t->setLocale('ru_RU');
Assert::same('1 спальня', $t->translate($message, 1));
Assert::same('2 спальни', $t->translate($message, 2));
Assert::same('5 спален', $t->translate($message, 5));
Assert::same('100 спален', $t->translate($message, 100));

// ukrainian
$t->setLocale('uk_UA');
Assert::same('1 спальня', $t->translate($message, 1));
Assert::same('2 спальні', $t->translate($message, 2));
Assert::same('5 спалень', $t->translate($message, 5));
Assert::same('100 спалень', $t->translate($message, 100));

// chinese (simplified)
$t->setLocale('zh_CN');
Assert::same('1间卧室', $t->translate($message, 1));
Assert::same('2间卧室', $t->translate($message, 2));
Assert::same('5间卧室', $t->translate($message, 5));
Assert::same('100间卧室', $t->translate($message, 100));

// japanese
$t->setLocale('ja_JP');
Assert::same('1ベッドルーム', $t->translate($message, 1));
Assert::same('2ベッドルーム', $t->translate($message, 2));
Assert::same('5ベッドルーム', $t->translate($message, 5));
Assert::same('100ベッドルーム', $t->translate($message, 100));

$t->setLocale('vi_VN');
Assert::same('1 phòng ngủ', $t->translate($message, 1));
Assert::same('2 phòng ngủ', $t->translate($message, 2));
Assert::same('5 phòng ngủ', $t->translate($message, 5));
Assert::same('100 phòng ngủ', $t->translate($message, 100));

$t->setLocale('uz_UZ');
Assert::same('1 xonali', $t->translate($message, 1));
Assert::same('2 yotoq xonasi', $t->translate($message, 2));
Assert::same('5 yotoq xonasi', $t->translate($message, 5));
Assert::same('100 yotoq xonasi', $t->translate($message, 100));

$t->setLocale('tr_TR');
Assert::same('1 yatak odası', $t->translate($message, 1));
Assert::same('2 yatak odası', $t->translate($message, 2));
Assert::same('5 yatak odası', $t->translate($message, 5));
Assert::same('100 yatak odası', $t->translate($message, 100));

$t->setLocale('th_TH');
Assert::same('1 ห้องนอน', $t->translate($message, 1));
Assert::same('2 ห้องนอน', $t->translate($message, 2));
Assert::same('5 ห้องนอน', $t->translate($message, 5));
Assert::same('100 ห้องนอน', $t->translate($message, 100));

$t->setLocale('is_IS');
Assert::same('1 svefnherbergi', $t->translate($message, 1));
Assert::same('2 svefnherbergi', $t->translate($message, 2));
Assert::same('5 svefnherbergi', $t->translate($message, 5));
Assert::same('100 svefnherbergi', $t->translate($message, 100));

// korean
$t->setLocale('ko_KR');
Assert::same('1 개 침실', $t->translate($message, 1));
Assert::same('2 개 침실', $t->translate($message, 2));
Assert::same('5 개 침실', $t->translate($message, 5));
Assert::same('100 개 침실', $t->translate($message, 100));

// lao
$t->setLocale('lo_LA');
Assert::same('1 ຫ້ອງນອນ', $t->translate($message, 1));
Assert::same('2 ຫ້ອງນອນ', $t->translate($message, 2));
Assert::same('5 ຫ້ອງນອນ', $t->translate($message, 5));
Assert::same('100 ຫ້ອງນອນ', $t->translate($message, 100));

// latvian
$t->setLocale('lv_LV');
Assert::same('1 miegamasis', $t->translate($message, 1));
Assert::same('2 miegamieji', $t->translate($message, 2));
Assert::same('5 miegamieji', $t->translate($message, 5));
Assert::same('100 miegamieji', $t->translate($message, 100));

// lithuanian
$t->setLocale('lt_LT');
Assert::same('1 guļamistaba', $t->translate($message, 1));
Assert::same('2 guļamistabas', $t->translate($message, 2));
Assert::same('5 guļamistabas', $t->translate($message, 5));
Assert::same('100 guļamistabas', $t->translate($message, 100));

// maltese
$t->setLocale('mt_MT');
Assert::same('1 kamra tas-sodda', $t->translate($message, 1));
Assert::same('2 kamra tas-sodda', $t->translate($message, 2));
Assert::same('5 kamra tas-sodda', $t->translate($message, 5));
Assert::same('100 kamra tas-sodda', $t->translate($message, 100));

// macedonian
$t->setLocale('mk_MK');
Assert::same('1 спална соба', $t->translate($message, 1));
Assert::same('2 спални', $t->translate($message, 2));
Assert::same('5 спални', $t->translate($message, 5));
Assert::same('100 спални', $t->translate($message, 100));

// polish
$t->setLocale('pl_PL');
Assert::same('1 spálňa', $t->translate($message, 1));
Assert::same('2 spálne', $t->translate($message, 2));
Assert::same('5 spální', $t->translate($message, 5));
Assert::same('100 spální', $t->translate($message, 100));

// indonesian
$t->setLocale('id_ID');
Assert::same('1 kamar tidur', $t->translate($message, 1));
Assert::same('2 kamar tidur', $t->translate($message, 2));
Assert::same('5 kamar tidur', $t->translate($message, 5));
Assert::same('100 kamar tidur', $t->translate($message, 100));

// georgian
$t->setLocale('ka_GE');
Assert::same('1 საძინებელი', $t->translate($message, 1));
Assert::same('2 საძინებელი', $t->translate($message, 2));
Assert::same('5 საძინებელი', $t->translate($message, 5));
Assert::same('100 საძინებელი', $t->translate($message, 100));
