<?php
namespace SuiteMapper;

use SuiteMapper\Converter\Converter;
use SuiteMapper\Mapping\Mapping;
use SuiteMapper\Mapping\MappingField;
use SuiteMapper\Mapping\MappingRelation;

class QueryBuilder
{
    /**
     * @var Mapping
     */
    private $mapping;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * QueryBuilder constructor.
     *
     * @param Mapping $mapping
     * @param \PDO $pdo
     */
    public function __construct(Mapping $mapping, \PDO $pdo)
    {
        $this->mapping = $mapping;
        $this->pdo = $pdo;
    }

    /**
     * @param array $data
     */
    public function createOrUpdate(array $data)
    {
        $query  = 'SELECT COUNT(*) AS entries FROM ' . $this->mapping->getDestinationTable();
        $query .= ' WHERE `' . $this->mapping->getDestinationIdentifier() .'` =';
        $query .= ' "'. $data[$this->mapping->getSourceIdentifier()] .'";';

        $result = $this->pdo->query($query)->fetch(\PDO::FETCH_ASSOC);
        if ($result['entries'] >= 1) {
            $this->update($data);
        } else {
            $this->create($data);
        }
    }

    /**
     * @param array $data
     */
    public function delete(array $data)
    {
        $query  = 'DELETE FROM ' . $this->mapping->getDestinationTable();
        $query .= ' WHERE `' . $this->mapping->getDestinationIdentifier() . '` =';
        $query .= ' "'. $data[$this->mapping->getSourceIdentifier()] .'";';

        $this->pdo->query($query);
    }

    /**
     * @param array $data
     */
    private function update(array $data)
    {
        $query  = 'UPDATE ' . $this->mapping->getDestinationTable();
        $query .= ' SET ';

        /** @var MappingField $mappingField */
        foreach ($this->mapping->getMappingFields() as $mappingField) {
            if (isset($data[$mappingField->getSourceField()])) {
                $query .= ' `'.$mappingField->getDestinationField() . '` = ';

                $value = $data[$mappingField->getSourceField()];
                if ($mappingField->getConverter() instanceof Converter) {
                    $value = $mappingField->getConverter()->getConvertedValue($value);
                }

                $query .= '"' . $value . '",';
            }
        }

        /* remove the , that is now left at the end of the query */
        $query = mb_substr($query, 0, -1);

        $query .= ' WHERE `' . $this->mapping->getDestinationIdentifier() . '` =';
        $query .= ' "'. $data[$this->mapping->getSourceIdentifier()] .'";';

        $this->pdo->query($query);
    }

    /**
     * @param array $data
     */
    private function create(array $data)
    {
        $values = '';

        $query  = 'INSERT INTO ' . $this->mapping->getDestinationTable();
        $query .= '(`'. $this->mapping->getDestinationIdentifier() . '`,';

        $values .= '"'. $data[$this->mapping->getSourceIdentifier()] . '",';

        /** @var MappingField $mappingField */
        foreach ($this->mapping->getMappingFields() as $mappingField) {
            if (isset($data[$mappingField->getSourceField()])) {
                $query .= '`' . $mappingField->getDestinationField() . '`,';

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

        $this->pdo->query($query);
    }

    public function relate(array $relations, array $data)
    {
        // users (left) reminders (right) reminders.user_id
        /** @var MappingRelation $relation */
        foreach ($relations as $relation) {
            $tableDir = $relation->getTableDirection($this->mapping->getDestinationTable());
            $identifierDir = $relation->getIdentifierDirection();

            if ($tableDir == $identifierDir) {
                // if table direction and identifier same direction = use mapping data (though need to use mapped field)

            } else {
                // if table direction and identifier different = select from table where reminders.user_id = users.id
            }

            // get uuids of both sides (users) (reminders)
            // does this specific relation exist already?
            // if not, but entities exist, create relation
            // if not, but not all entities exist, skip creation
            // if, but not all entities exist, delete
        }
    }
}