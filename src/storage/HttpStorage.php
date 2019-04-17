<?php
namespace SuiteMapper\Storage;

class HttpStorage implements Storage
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $htUser;

    /**
     * @var string
     */
    private $htPassword;

    /**
     * @param $baseUrl string Base URL
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $file
     * @param string $json
     *
     * @throws \Exception
     */
    public function writeJsonToFile($file, $json)
    {
        throw new \Exception('HttpStorage does not support write actions.');
    }

    public function getUser()
    {
        return $this->htUser;
    }

    public function setUser($user)
    {
        $this->htUser = $user;
    }

    public function getPassword()
    {
        return $this->htPassword;
    }

    public function setPassword($password)
    {
        $this->htPassword = $password;
    }

    /**
     * @param string $file
     * @return string
     */
    public function readJsonFromFile($file)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $file);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($this->getUser()) && !empty($this->getPassword())) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->htUser .':'. $this->htPassword);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = trim(curl_exec($ch));

        curl_close($ch);

        return $json;
    }
}