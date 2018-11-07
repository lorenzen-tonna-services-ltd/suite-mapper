<?php
namespace SuiteMapper\Mapping;

class Mapping
{
    /**
     * @var string
     */
    private $sourceTable;

    /**
     * @var string
     */
    private $destinationTable;

    /**
     * @var string
     */
    private $sourceIdentifier;

    /**
     * @var string
     */
    private $destinationIdentifier;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $mappingFields = [];

    /**
     * @return string
     */
    public function getSourceTable()
    {
        return $this->sourceTable;
    }

    /**
     * @param string $sourceTable
     */
    public function setSourceTable($sourceTable)
    {
        $this->sourceTable = $sourceTable;
    }

    /**
     * @return string
     */
    public function getDestinationTable()
    {
        return $this->destinationTable;
    }

    /**
     * @param string $destinationTable
     */
    public function setDestinationTable($destinationTable)
    {
        $this->destinationTable = $destinationTable;
    }

    /**
     * @return array
     */
    public function getMappingFields()
    {
        return $this->mappingFields;
    }

    /**
     * @param array $mappingFields
     */
    public function setMappingFields($mappingFields)
    {
        $this->mappingFields = $mappingFields;
    }

    /**
     * @param MappingField $mappingField
     */
    public function addMappingField(MappingField $mappingField)
    {
        $this->mappingFields[] = $mappingField;
    }

    /**
     * @return string
     */
    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param string $sourceIdentifier
     */
    public function setSourceIdentifier($sourceIdentifier)
    {
        $this->sourceIdentifier = $sourceIdentifier;
    }

    /**
     * @return string
     */
    public function getDestinationIdentifier()
    {
        return $this->destinationIdentifier;
    }

    /**
     * @param string $destinationIdentifier
     */
    public function setDestinationIdentifier($destinationIdentifier)
    {
        $this->destinationIdentifier = $destinationIdentifier;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        $fields = [];
        /** @var MappingField $field */
        foreach ($this->mappingFields as $field) {
            $fields[] = $field->toArray();
        }

        return [
            'source' => [
                'field' => $this->sourceIdentifier,
                'table' => $this->sourceTable
            ],
            'destination' => [
                'field' => $this->destinationIdentifier,
                'table' => $this->destinationTable
            ],
            'fields' => $fields,
            'status' => $this->status,
            'title' => $this->title
        ];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        $this->sourceTable = $data['source']['table'];
        $this->sourceIdentifier = $data['source']['field'];
        $this->destinationTable = $data['destination']['table'];
        $this->destinationIdentifier = $data['destination']['field'];
        $this->status = $data['status'];
        $this->title = $data['title'];

        foreach ($data['fields'] as $field) {
            $this->mappingFields[] = (new MappingField())->fromArray($field);
        }
        
        return $this;
    }
}
