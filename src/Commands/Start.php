<?php

namespace Alarmist\Commands;

class Start extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		return (new Help($this->message))->exec();
	}
}
