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
	private $me;

	public function __construct(string $botKey, string $storage)
	{
		$apiClient = new ApiClient(new RequestFactory(), new StreamFactory(), new GuzzleClient());
		$this->api = new BotApi($botKey, $apiClient, new BotApiNormalizer());
		$this->offset_storage = $storage . '/offset';
		if (!file_exists($this->offset_storage))
			mkdir($this->offset_storage);
		$this->command_register = include __DIR__ . '/commandRegister.inc.php';
		$this->me = $this->api->getMe(\TgBotApi\BotApiBase\Method\GetMeMethod::create());
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

	/**
	 * Run checker
	 *
	 * @param Checkers\CheckerInterface $checker
	 * @return void
	 */
	public function check(Checkers\CheckerInterface $checker)
	{
		foreach ($checker->run() as $chatId => $status) {
			if ($status['status'] === false)
				$this->send($chatId, "<b>Alarm</b> <i>" . date('Y-m-d H:i') . "</i> [{$status['site']}] " . $checker->getName() . " fail");
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

	/**
	 * Skip messages in the queue to don't send to chats
	 *
	 * @return void
	 */
	public function skipOldMessages()
	{
		$updates = $this->api->getUpdates(\TgBotApi\BotApiBase\Method\GetUpdatesMethod::create());
		foreach ($updates as $i => $data) {
			touch($this->offset_storage . '/' . $data->updateId);
		}
		$this->clearOffsetExpired();
	}

	/**
	 * Find commands into message
	 *
	 * @param \TgBotApi\BotApiBase\Type\MessageType $message
	 * @return array
	 */
	public static function hasCommands(\TgBotApi\BotApiBase\Type\MessageType $message): array
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

	/**
	 * Exec command
	 *
	 * @param string $command
	 * @param \TgBotApi\BotApiBase\Type\MessageType $message
	 * @return void
	 * 
	 * @throws commandClassCreateExeption
	 */
	public function execCommand(string $command, \TgBotApi\BotApiBase\Type\MessageType $message)
	{
		if ($message->chat->type == 'group' && strstr($command, '@')) {
			list($c, $botName) = explode('@', $command);
			if ($botName != $this->me->username) return;
			$command = $c;
		}
		if (isset($this->command_register[$command])) {
			$commandClass = __NAMESPACE__ . '\\' . $this->command_register[$command];
			if (class_exists($commandClass)) {
				$C = new $commandClass($message);
				if ($reply = $C->exec()) {
					$this->send($message->chat->id, $reply);
				}
			} else {
				throw new Commands\Exeptions\commandClassCreateExeption("[chat:{$message->chat->id}] [command:$command] command execute error");
			}
		} else {
			$this->send($message->chat->id, 'unknown command');
		}
	}

	/**
	 * Get the value of me
	 */
	public function getMe()
	{
		return $this->me;
	}
}
