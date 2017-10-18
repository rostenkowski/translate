<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Rostenkowski\Translate\NeonDictionary\NeonDictionaryFactory;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$dataDir = __DIR__ . '/translations';
$tempDir = __DIR__ . '/temp/cache';

$translator = new Translator(new NeonDictionaryFactory($dataDir, $tempDir));

// test: simple message
Assert::equal('Welcome!', $translator->translate('Welcome!'));

// test: plural forms
Assert::equal('You have <span class="badge">1</span> unread message.',
	$translator->translate('You have %s unread messages.', 1));
Assert::equal('You have <span class="badge">2</span> unread messages.',
	$translator->translate('You have %s unread messages.', 2));
Assert::equal('You have <span class="badge">5</span> unread messages.',
	$translator->translate('You have %s unread messages.', 5));

// test: process plural forms with parameters
Assert::equal('You have <span class="badge">5</span> points. Thank you John!',
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
Assert::equal('Máte <span class="badge">1</span> nepřečtenou zprávu.',
	$translator->translate('You have %s unread messages.', 1));
Assert::equal('Máte <span class="badge">2</span> nepřečtené zprávy.',
	$translator->translate('You have %s unread messages.', 2));
Assert::equal('Máte <span class="badge">5</span> nepřečtených zpráv.',
	$translator->translate('You have %s unread messages.', 5));

// test: undefined plural form
$message = 'You have %s unread articles.';
Assert::same('Máte <span class="badge">5</span> nepřečtené články.', $translator->translate($message, 5));

// test error: non-string message
$message = [];
Assert::exception(function () use ($translator, $message) {
	$translator->translate($message);
}, TranslatorException::class, sprintf("Message must be string, but %s given.", var_export($message, true)));

// test: NULL count
Assert::same('Máte <span class="badge">%s</span> nepřečtených zpráv.', $translator->translate('You have %s unread messages.', NULL));
