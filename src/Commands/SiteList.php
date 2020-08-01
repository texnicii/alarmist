<?php

namespace Alarmist\Commands;

use Alarmist\Checkers\Http;

class SiteList extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		$sites = Http::siteList($this->message->chat->id);
		return count($sites) ? implode("\n", $sites) : 'empty';
	}
}
