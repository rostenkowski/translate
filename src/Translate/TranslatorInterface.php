<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Nette\Localization\ITranslator;

interface TranslatorInterface extends ITranslator
{

	public function setLocale(string $locale): TranslatorInterface;

}
