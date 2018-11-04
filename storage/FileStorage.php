<?php
namespace SuiteMapper\Storage;

class FileStorage implements Storage
{
    /**
     * @var string
     */
    private $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function writeJsonToFile($file, $json)
    {
        if (is_file($this->directory .'/'. $file)) {
            unlink($this->directory .'/'. $file);
        }

        file_put_contents($this->directory .'/'. $file, $json);
    }

    public function readJsonFromFile($file)
    {
        if (is_file($this->directory .'/'. $file)) {
            $json = file_get_contents($this->directory .'/'. $file);

            return $json;
        }

        return '';
    }
}