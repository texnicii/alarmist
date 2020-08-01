<?php

namespace Alarmist\Checkers;

class Http implements CheckerInterface
{
	private $name = 'HTTP checker';
	const SITES_STORAGE = STORAGE . '/sites';

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
	/**
	 * Run HTTP check
	 *
	 * @return \Generator
	 */
	public function run(): \Generator
	{
		$dh = opendir(static::SITES_STORAGE);
		while ($file_chatId = readdir($dh)) {
			if (in_array($file_chatId, ['..', '.'])) continue;
			$path = static::SITES_STORAGE . '/' . $file_chatId;
			foreach (file($path) as $site) {
				$site = trim($site);
				$status = $this->curlRequest($site);
				#two stage check
				if ($status === false)
					$status = $this->curlRequest($site);

				yield $file_chatId => [
					'site' => $site,
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

	/**
	 * Get list of sites
	 *
	 * @param integer $chatId
	 * @return array
	 */
	public static function siteList(int $chatId): array
	{
		$file = static::SITES_STORAGE . '/' . $chatId;
		$sites = [];
		if (file_exists($file)) {
			foreach (file($file) as $line) {
				$siteItem = trim($line);
				$sites[$siteItem] = $siteItem;
			}
		}
		return $sites;
	}

	/**
	 * Add site
	 *
	 * @param array $newSites
	 * @param integer $chatId
	 * @return integer
	 */
	public static function siteAdd(array $newSites, int $chatId): int
	{
		if (!file_exists(static::SITES_STORAGE))
			mkdir(static::SITES_STORAGE);
		$c = 0;
		$sites = Http::siteList($chatId);
		foreach ($newSites as $site) {
			if (!isset($sites[$site])) {
				$sites[$site] = $site;
				$c++;
			}
		}
		if (false === file_put_contents(static::SITES_STORAGE . '/' . $chatId, implode("\n", $sites)))
			return 0;
		return $c;
	}

	/**
	 * Remove site
	 *
	 * @param array $delSites
	 * @param integer $chatId
	 * @return integer
	 */
	public static function siteDel(array $delSites, int $chatId): int
	{
		$c = 0;
		$sites = Http::siteList($chatId);
		foreach ($delSites as $site) {
			if (isset($sites[$site])) {
				unset($sites[$site]);
				$c++;
			}
		}
		if (false === file_put_contents(static::SITES_STORAGE . '/' . $chatId, implode("\n", $sites)))
			return 0;
		return $c;
	}

	/**
	 * Parse sites from message
	 *
	 * @param string $message
	 * @return array
	 */
	public static function parseSites(string $message): array
	{
		$sites = [];
		$textPrepared = str_replace(["\n", "\t", ","], " ", $message);
		$textPrepared = preg_replace('!\/\w+(?:\s|$)!i', '', $textPrepared);
		$textPrepared = preg_replace('!https?\:\/\/!i', '', $textPrepared);
		$split = explode(" ", $textPrepared);
		foreach ($split as $str) {
			$str = trim($str);
			if (preg_match('!\d+\.\d+\.\d+\.\d+!', $str) || preg_match('![^\.]+\.[^\.]+!', $str))
				$sites[$str] = $str;
		}
		return $sites;
	}

	/**
	 * Erace all sites list
	 *
	 * @param integer $chatId
	 * @return void
	 */
	public static function erase(int $chatId)
	{
		if (file_exists($file = static::SITES_STORAGE . '/' . $chatId))
			unlink($file);
	}
}
