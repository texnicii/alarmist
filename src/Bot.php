<?php

namespace Alarmist;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;

class Bot
{
	public $api;
	private $offset_storage;
	private $command_register;

	public function __construct(string $botKey, string $storage)
	{
		$apiClient = new ApiClient(new RequestFactory(), new StreamFactory(), new GuzzleClient());
		$this->api = new BotApi($botKey, $apiClient, new BotApiNormalizer());
		$this->offset_storage = $storage . '/offset';
		$this->command_register = include __DIR__ . '/commandRegister.inc.php';
	}
	/**
	 * Send message to user
	 *
	 * @param int|string $userId
	 * @param string $message
	 * @return void
	 */
	public function send($userId, string $message)
	{
		$Message = \TgBotApi\BotApiBase\Method\SendMessageMethod::create($userId, $message);
		$Message->parseMode = $Message::PARSE_MODE_HTML;
		return $this->api->send($Message);
	}

	public function check(Checkers\CheckerInterface $checker)
	{
		foreach ($checker->run() as $chatId => $status) {
			if ($status['status'] === false)
				$this->send($chatId, "<b>Alarm</b> <i>" . date('Y-m-d H:i') . "</i> [{$status['ip']}] " . $checker->getName() . " fail");
		}
	}

	/**
	 * Get updates of messages (new messages and updates of old messages)
	 *
	 * @return array
	 */
	public function getLastMessages()
	{
		$updates = $this->api->getUpdates(\TgBotApi\BotApiBase\Method\GetUpdatesMethod::create());

		$this->clearOffsetExpired();

		foreach ($updates as $i => $data) {
			$f = $this->offset_storage . '/' . $data->updateId;
			if (file_exists($f)) {
				unset($updates[$i]);
				continue;
			} else touch($f);
		}

		return $updates;
	}

	public static function hasCommands(\TgBotApi\BotApiBase\Type\MessageType $message)
	{
		$commands = [];

		if (isset($message->entities)) {
			foreach ($message->entities as $ent) {
				if ($ent->type != 'bot_command') continue;
				$commands[] = trim(mb_substr($message->text, $ent->offset + 1, $ent->length));
			}
		}

		return $commands;
	}

	private function clearOffsetExpired()
	{
		$dh = opendir($this->offset_storage);
		while ($file = readdir($dh)) {
			if (in_array($file, ['..', '.'])) continue;
			$path = $this->offset_storage . '/' . $file;
			if ((filectime($path) + 172800) < time()) unlink($path);
		}
		closedir($dh);
	}

	public function execCommand(string $command, \TgBotApi\BotApiBase\Type\ChatType $chat)
	{
		if (isset($this->command_register[$command])) {
			$commandClass = __NAMESPACE__ . '\\' . $this->command_register[$command];
			if(class_exists($commandClass)){
				$C = new $commandClass;
				if ($reply = $C->exec()) {
					$this->send($chat->id, $reply);
				}
			}else{
				throw new Commands\Exeptions\commandClassCreateExeption("[chat:{$chat->id}] [command:$command] command execute error");
			}
		}else{
			$this->send($chat->id, 'unknown command');
		}
	}
}
