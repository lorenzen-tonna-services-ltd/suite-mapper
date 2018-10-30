<?php

class HttpStorage implements Storage
{
	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * HttpStorage constructor.
	 * @param $baseUrl string Base URL
	 */
	public function __construct($baseUrl)
	{
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @param string $file
	 * @param string $json
	 * @throws Exception
	 */
	public function writeJsonToFile($file, $json)
	{
		throw new Exception('HttpStorage does not support write actions.');
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function readJsonFromFile($file)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $file);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$json = trim(curl_exec($ch));

		curl_close($ch);

		return $json;
	}
}