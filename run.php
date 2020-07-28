<?php

use Alarmist\Bot;

if (php_sapi_name() !== 'cli') die;

require __DIR__ . '/init.php';

$bot = new Bot(BOT_KEY, STORAGE);

echo "Message receiver starting...\n";
while (1) {
	foreach ($bot->getLastMessages() as $update) {
		foreach (Bot::hasCommands($update->message) as $command) {
			$bot->execCommand($command, $update->message->chat);
		}
	}
	sleep(2);
}
