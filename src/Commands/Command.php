<?php

namespace Alarmist\Commands;

abstract class Command
{

	protected $message;

	function __construct(\TgBotApi\BotApiBase\Type\MessageType $message)
	{
		$this->message=$message;
	}
}
