<?php

use Alarmist\Bot;

if (php_sapi_name() !== 'cli') die;

require __DIR__ . '/init.php';

$bot = new Bot(BOT_KEY, STORAGE);

foreach ($bot->getLastMessages() as $update) {
	print_r(Bot::hasCommands($update->message));
}

#print_r($m);
