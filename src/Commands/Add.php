<?php

namespace Alarmist\Commands;

use Alarmist\Checkers\Http;

class Add extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		$result=Http::siteAdd(Http::parseSites($this->message->text), $this->message->chat->id);
		return "{$result} new written";
	}
}
