<?php

namespace Rostenkowski\Translate;


interface PluralInterface
{

	public function plural(string $locale, int $count): int;

}
