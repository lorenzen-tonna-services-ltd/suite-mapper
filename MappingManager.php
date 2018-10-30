<?php
namespace SuiteMapper;

/**
 * Class MappingManager
 */
class MappingManager
{
	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @var array
	 */
	private $mappings = [];


	public function __construct(Storage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @param Storage $storage
	 */
	public function setStorage(Storage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @return Storage
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * @param string $type
	 * @return array|mixed
	 */
	public function getMappingsBySyncType($type)
	{
		if (!isset($this->mappings[$type])) {
			$this->getAvailableMappings($type);
		}
		if (isset($this->mappings[$type])) {
			return $this->mappings[$type];
		}
		return [];
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	private function getAvailableMappings($type)
	{
		$fileName = $type .'.json';

		$data = $this->storage->readJsonFromFile($fileName);
		if (empty($data)) {
			return false;
		}

		if (!isset($this->mappings[$type])) {
			$this->mappings[$type] = [];
		}

		$mappings = json_decode($data, true);
		foreach ($mappings as $mapping) {
			$this->mappings[$type][] = (new Mapping())->fromArray($mapping);
		}
		return true;
	}
}