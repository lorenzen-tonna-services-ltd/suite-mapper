<?php
namespace SuiteMapper\Storage;

interface Storage
{
	public function writeJsonToFile($file, $json);
	public function readJsonFromFile($file);
}