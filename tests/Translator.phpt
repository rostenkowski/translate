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
// special zero mode is opt-in
$translator->useSpecialZeroForm = true;
$translator->setDebugMode(true);
Assert::same("Čas vypršel", $translator->translate('You have %s seconds', 0));
Assert::same("Máte 1 vteřinu", $translator->translate('You have %s seconds', 1));
Assert::same("Máte 2 vteřiny", $translator->translate('You have %s seconds', 2));
Assert::same("Máte 5 vteřin", $translator->translate('You have %s seconds', 5));

// test: eval cache hit counters
$stats = $translator->getStats();
Assert::same(5, $stats['evalCacheHitCounter']);
Assert::same(6, $stats['evalCounter']);

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

// test: format number
$translator->setLocale('cs_CZ');
Assert::same('3,14', $translator->translate(M_PI, 2));

$translator->setLocale('de_DE');
Assert::same('3,14', $translator->translate(M_PI, 2));

$translator->setLocale('en_GB');
Assert::same('3.14', $translator->translate(M_PI, 2));

$translator->setLocale('en_US');
Assert::same('3.14', $translator->translate(M_PI, 2));
