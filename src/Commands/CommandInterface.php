<?php

namespace Alarmist\Commands;

interface CommandInterface
{
	public function exec(): ?string;
}
