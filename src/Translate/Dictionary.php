<?php declare(strict_types=1);

namespace Rostenkowski\Translate;


abstract class Dictionary implements DictionaryInterface
{

	/**
	 * @var array
	 */
	private $messages;


	public function has(string $message): bool
	{
		$this->lazyLoad();

		return array_key_exists($message, $this->messages);
	}


	public function get(string $message)
	{
		$this->lazyLoad();

		return $this->messages[$message];
	}


	protected function isReady()
	{
		return is_array($this->messages);
	}


	protected function setMessages(array $messages): DictionaryInterface
	{
		$this->messages = $messages;

		return $this;
	}

}
