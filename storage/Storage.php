<?php

interface Storage
{
	public function writeJsonToFile($json);
	public function readJsonFromFile($file);
}