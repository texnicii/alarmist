<?php
error_reporting(-1);

define('BOT_KEY', '');
define('STORAGE',__DIR__.'/storage');

if(!file_exists(STORAGE)) mkdir(STORAGE);

require __DIR__.'/vendor/autoload.php';

//TODO move BOT_KEY to env 