<?php
namespace SuiteMapper\Hook;

interface Hook
{
    public function execute(array &$data);

    public function getSyncType();
    public function getExecType();
}