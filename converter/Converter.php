<?php

interface Converter
{
    public function getConvertedValue($input);
    public function getKey();
}