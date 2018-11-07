<?php
namespace SuiteMapper\Mapping;

class MappingRelation
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $tableLeft; // contact

    /**
     * @var string
     */
    private $tableRight;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
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
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getTableLeft()
    {
        return $this->tableLeft;
    }

    /**
     * @param string $tableLeft
     */
    public function setTableLeft($tableLeft)
    {
        $this->tableLeft = $tableLeft;
    }

    /**
     * @return string
     */
    public function getTableRight()
    {
        return $this->tableRight;
    }

    /**
     * @param string $tableRight
     */
    public function setTableRight($tableRight)
    {
        $this->tableRight = $tableRight;
    }

    /**
     * @param bool $columnOnly
     * @return string
     */
    public function getIdentifier($columnOnly = false)
    {
        if ($columnOnly) {
            $parts = explode('.', $this->identifier);

            return $parts[1];
        }
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getTableDirection($table)
    {
        if ($this->tableRight == $table) {
            return 'right';
        } else if ($this->tableLeft == $table) {
            return 'left';
        }
        return '';
    }

    public function getIdentifierDirection()
    {
        $parts = explode('.', $this->identifier);

        if ($parts[0] == $this->tableLeft) {
            return 'left';
        }
        return 'right';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $fields = [];

        /** @var MappingRelationField $field */
        foreach ($this->fields as $field) {
            $fields[] = $field->toArray();
        }

        return [
            'fields' => $fields,
            'identifier' => $this->identifier,
            'table' => $this->table,
            'table-left' => $this->tableLeft,
            'table-right' => $this->tableRight,
            'status' => $this->status
        ];
    }

    /**
     * @param array $data
     * @return MappingRelation
     */
    public function fromArray(array $data)
    {
        $this->identifier = $data['identifier'];
        $this->table = $data['table'];
        $this->tableLeft = $data['table-left'];
        $this->tableRight = $data['table-right'];
        $this->status = $data['status'];

        $fields = [];

        foreach ($data['fields'] as $raw) {
            $fields[] = (new MappingRelationField())->fromArray($raw);
        }

        $this->fields = $fields;

        return $this;
    }
}