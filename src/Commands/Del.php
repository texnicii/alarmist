<?php

namespace Alarmist\Commands;

use Alarmist\Checkers\Http;

class Del extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		$result = Http::siteDel(Http::parseSites($this->message->text), $this->message->chat->id);
		return "{$result} deleted";
	}
}
