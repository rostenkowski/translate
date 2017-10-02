<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


use Exception;

class TranslatorException extends Exception
{


	public static function nonStringMessage($message)
	{
		return new static(sprintf("Message must be string, but %s given.", var_export($message, true)));
	}

}
