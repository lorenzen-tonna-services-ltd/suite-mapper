<?php

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
			'source' => $this->sourceTable,
			'destination' => $this->destinationTable,
			'fields' => $fields
		];
	}
}