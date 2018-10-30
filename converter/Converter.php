<?php
namespace SuiteMapper\Converter;

interface Converter
{
    public function getConvertedValue($input);
    public function getKey();
}