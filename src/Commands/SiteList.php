<?php

namespace Alarmist\Commands;

class SiteList extends Command implements CommandInterface
{

	private $storage = STORAGE . '/sites';

	public function exec(): ?string
	{
		$file = $this->storage . '/' . $this->message->chat->id;
		$sites = [];
		if (file_exists($file)) {
			foreach (file($file) as $line) {
				$siteItem = trim($line);
				$sites[$siteItem] = $siteItem;
			}
		}
		return count($sites) ? implode("\n", $sites) : 'empty';
	}
}
