<?php

namespace Alarmist\Commands;

class Start implements CommandInterface
{
	function __construct()
	{
	}

	public function exec(): ?string
	{
		return 'start command executed';
	}
}
