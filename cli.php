<?php

use Alarmist\Bot;
use Alarmist\Checkers\Http as HttpChecker;

if (php_sapi_name() !== 'cli') die;

require __DIR__ . '/init.php';

$bot = new Bot(BOT_KEY, STORAGE);
$opt = getopt('cm', ['cron', 'me']);
if (isset($opt['cron']) | isset($opt['c'])) {
	$bot->check(new HttpChecker());
} elseif (isset($opt['me']) | isset($opt['m']))
	print_r($bot->getMe());
