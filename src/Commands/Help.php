<?php

namespace Alarmist\Commands;

class Help extends Command implements CommandInterface
{

	public function exec(): ?string
	{
		return
			"/help - show available commands\n" .
			"/list - show list of added sites\n" .
			"/add - add site, e.g. <i>/add 127.0.0.1 or /add 127.0.0.1, 127.0.0.2 domain.com</i>\n" .
			"/del - remove site, e.g. <i>/del domain.com or /del 127.0.0.1, 127.0.0.2<i>";
	}
}
