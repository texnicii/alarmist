<?php

namespace Alarmist\Commands;

use Alarmist\Checkers\Http;

class Stop extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		Http::erase($this->message->chat->id);
		return 'bye';
	}
}
