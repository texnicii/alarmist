<?php

namespace Alarmist\Checkers;

class Http implements CheckerInterface
{
	private $ipsDir;
	private $name = 'HTTP checker';

	public function __construct($ipsDir)
	{
		$this->ipsDir = $ipsDir;
	}

	/**
	 * Request to server via CURL
	 *
	 * @param string $ip
	 * @return void
	 */
	private function curlRequest(string $ip)
	{
		$ch = curl_init($ip);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		return curl_exec($ch);
	}

	public function run(): \Generator
	{
		$dh = opendir($this->ipsDir);
		while ($file_chatId = readdir($dh)) {
			if (in_array($file_chatId, ['..', '.'])) continue;
			$path = $this->ipsDir . '/' . $file_chatId;
			foreach (file($path) as $ip) {
				$ip = trim($ip);
				if (!preg_match('!\d+\.\d+\.\d+\.\d+!', $ip)) continue;
				$status = $this->curlRequest($ip);
				#two stage check
				if ($status === false)
					$status = $this->curlRequest($ip);

				yield $file_chatId => [
					'ip' => $ip,
					'status' => $status
				];
			}
		}
		closedir($dh);
	}

	/**
	 * Get the value of name
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
