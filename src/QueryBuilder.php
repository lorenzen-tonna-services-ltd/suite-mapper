<?php
namespace SuiteMapper;

use SuiteMapper\Converter\Converter;
use SuiteMapper\Mapping\Mapping;
use SuiteMapper\Mapping\MappingField;

class QueryBuilder
{
	private $mapping;

	/**
	 * QueryBuilder constructor.
	 *
	 * @param Mapping $mapping
	 */
	public function __construct(Mapping $mapping)
	{
		$this->mapping = $mapping;
	}

	/**
	 * @param array $data
	 */
	public function getExistsQuery(array $data)
	{
		//
	}

	/**
	 * @param array $data
	 * @return string
	 */
	public function getDeleteQuery(array $data)
	{
		$query  = 'DELETE FROM ' . $this->mapping->getDestinationTable();
		$query .= ' WHERE ' . $this->mapping->getDestinationIdentifier() . ' =';
		$query .= ' "'. $data[$this->mapping->getSourceIdentifier()] .'";';

		return $query;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	public function getUpdateQuery(array $data)
	{
		$query  = 'UPDATE ' . $this->mapping->getDestinationTable();
		$query .= ' SET ';

		/** @var MappingField $mappingField */
		foreach ($this->mapping->getMappingFields() as $mappingField) {
			if (isset($data[$mappingField->getSourceField()])) {
				$query .= $mappingField->getDestinationField() . ' = ';
			}
		}

		return $query;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	public function getInsertQuery(array $data)
	{
		$values = '';

		$query  = 'INSERT INTO ' . $this->mapping->getDestinationTable();
		$query .= '(';

		/** @var MappingField $mappingField */
		foreach ($this->mapping->getMappingFields() as $mappingField) {
			$query .= $mappingField->getDestinationField() . ',';

			if (isset($data[$mappingField->getSourceField()])) {
				$value = $data[$mappingField->getSourceField()];
				if ($mappingField->getConverter() instanceof Converter) {
					$value = $mappingField->getConverter()->getConvertedValue($value);
				}

				$values .= '"'. $value . '",';
			}
		}

		$query = substr($query, 0, -1);
		$values = substr($values, 0, -1);

		$query .= ') VALUES (' . $values .');';

		return $query;
	}
}