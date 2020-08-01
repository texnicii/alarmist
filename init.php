<?php
error_reporting($_ENV['PHP_ERROR_REPORTING']??E_ALL);

define('BOT_KEY',  $_ENV['BOT_KEY']);
define('STORAGE', __DIR__ . '/storage');

if (!file_exists(STORAGE)) mkdir(STORAGE);

require __DIR__ . '/vendor/autoload.php';