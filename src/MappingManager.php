<?php
namespace SuiteMapper;

use SuiteMapper\Mapping\Mapping;
use SuiteMapper\Mapping\MappingRelation;
use SuiteMapper\Storage\Storage;

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

    /**
     * @var array
     */
    private $relations = [];


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

    /**
     * @param Mapping $mapping
     * @return array|bool
     */
    public function getAvailableRelations(Mapping $mapping)
    {
        if (empty($this->relations)) {
            $data = $this->storage->readJsonFromFile('relations.json');
            if (empty($data)) {
                return false;
            }

            $relations = json_decode($data, true);
            foreach ($relations as $relation) {
                $this->relations[] = (new MappingRelation())->fromArray($relation);
            }
        }

        $availableRelations = [];

        /** @var MappingRelation $relation */
        foreach ($this->relations as $relation) {
            if ($mapping->getDestinationTable() == $relation->getTableLeft() ||
                $mapping->getDestinationTable() == $relation->getTableRight()) {
                $availableRelations[] = $relation;
            }
        }

        return $availableRelations;
    }
}