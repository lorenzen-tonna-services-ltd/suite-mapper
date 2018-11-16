<?php
namespace SuiteMapper;

use SuiteMapper\Converter\Converter;
use SuiteMapper\Mapping\Mapping;
use SuiteMapper\Mapping\MappingField;
use SuiteMapper\Mapping\MappingRelation;
use SuiteMapper\Mapping\MappingRelationField;

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
     * @var array
     */
    private $table2module = [
        'contacts' => 'Contacts',
        'ws_wechselservice' => 'ws_Wechselservice',
    ];

    /**
     * @var array 
     */
    private $type2suite = [
        'REMINDER' => 'rmd_reminder_cstm',
        'OFFER' => 'ang_angebote_cstm',
        'CHANGESERVICE' => 'ws_wechselservice_cstm',
        'USER' => 'contacts_cstm',
    ];

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
            if ($this->mapping->getSourceTable() == 'external-id-mapping' && $this->mapping->getDestinationTable() != 'eim_externalidmapping') {
                if (isset($data['type']) && $this->type2suite[$data['type']] == $this->mapping->getDestinationTable()) {
                    $this->create($data);
                }

                return;
            }
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
            if (in_array($mappingField->getDestinationField(), ['email', 'email_primary'])) {
                $this->email(
                    $data[$this->mapping->getSourceIdentifier()],
                    $data[$mappingField->getSourceField()],
                    ($mappingField->getDestinationField() == 'email_primary')
                );

                continue;
            }

            if (isset($data[$mappingField->getSourceField()])) {
                $query .= ' `'.$mappingField->getDestinationField() . '` = ';

                $value = $data[$mappingField->getSourceField()];
                if ($mappingField->getConverter() instanceof Converter) {
                    $value = $mappingField->getConverter()->getConvertedValue($value);
                }

                /* json field handling - can go to any level of depthness */
                if (is_array($value)) {
                    $arrayElement = $this->getArrayElement($data, $mappingField->getSourceField());
                    if ($arrayElement) {
                        $query .= '"' . $arrayElement . '",';
                    } else {
                        $values .= '"",';
                    }
                } else {
                    $query .= '"' . $value . '",';
                }
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
            if (in_array($mappingField->getDestinationField(), ['email', 'email_primary'])) {
                $this->email(
                    $data[$this->mapping->getSourceIdentifier()],
                    $data[$mappingField->getSourceField()],
                    ($mappingField->getDestinationField() == 'email_primary')
                );

                continue;
            }

            if (isset($data[$mappingField->getSourceField()])) {
                $query .= '`' . $mappingField->getDestinationField() . '`,';

                $value = $data[$mappingField->getSourceField()];
                if ($mappingField->getConverter() instanceof Converter) {
                    $value = $mappingField->getConverter()->getConvertedValue($value);
                }

                if (is_array($value)) {
                    $arrayElement = $this->getArrayElement($data, $mappingField->getSourceField());
                    if ($arrayElement) {
                        $values .= '"' . $arrayElement . '",';
                    } else {
                        $values .= '"",';
                    }
                } else {
                    $values .= '"'. $value . '",';
                }
            }
        }

        $query = substr($query, 0, -1);
        $values = substr($values, 0, -1);

        $query .= ') VALUES (' . $values .');';

        $this->pdo->query($query);
    }

    /**
     * @param string $uuid
     * @param string $email
     * @param bool $primary
     */
    private function email($uuid, $email, $primary)
    {
        $statement = $this->pdo->prepare('SELECT id FROM email_addresses WHERE email_address = ?');
        $statement->execute([$email]);

        $id = $statement->fetchColumn(0);
        if (empty($id)) {
            $id = $this->generateUUID();

            $statement = $this->pdo->prepare('INSERT INTO email_addresses(id,email_address,email_address_caps,date_created) VALUES(?,?,?,?)');
            $statement->execute([$id, $email, mb_strtoupper($email), date('Y-m-d H:i:s')]);
        }

        $statement = $this->pdo->prepare('SELECT id FROM email_addr_bean_rel WHERE email_address_id = ? AND bean_module = ?');
        $statement->execute([$id, $this->table2module[$this->mapping->getDestinationTable()]]);

        $idRelation = $statement->fetchColumn(0);
        if (empty($idRelation)) {
            $statement = $this->pdo->prepare('INSERT INTO email_addr_bean_rel(id,email_address_id,bean_id,bean_module,primary_address,date_created) VALUES(?,?,?,?,?,?)');
            $statement->execute([$this->generateUUID(), $id, $uuid, $this->table2module[$this->mapping->getDestinationTable()], (int)$primary, date('Y-m-d H:i:s')]);
        }
    }

    public function relate(array $relations, array $data)
    {
        /** @var MappingRelation $relation */
        foreach ($relations as $relation) {
            $tableDir = $relation->getTableDirection($this->mapping->getDestinationTable());
            $identifierDir = $relation->getIdentifierDirection();

            $relationId = null;
            /** @var MappingRelationField $field */
            foreach ($relation->getFields() as $field) {
                if ($field->getField() == 'id') {
                    $relationId = $field->getValue();
                }
            }

            // identifier will usually be in a table that is not 'contacts'
            // so likely this case will mostly be entities like reminders, change-services, etc.
            if ($tableDir == $identifierDir) {
                /* get the identifiers value (eg. user id) */
                $mainEntityUUID = null;

                /** @var MappingField $field */
                foreach ($this->mapping->getMappingFields() as $field) {
                    if ($field->getDestinationField() == $relation->getIdentifier(true)) {
                        if (!isset($data[$field->getSourceField()])) {
                            return;
                        }
                        $mainEntityUUID = $data[$field->getSourceField()];
                        break;
                    }
                }

                /* get uuid of the currenty entity */
                $otherEntityUUID = $data[$this->mapping->getSourceIdentifier()];

                /* we know the 'other entity' exists - as we are handling it right now */
                if ($tableDir == 'right') {
                    $otherTable = $relation->getTableLeft();
                } else {
                    $otherTable = $relation->getTableRight();
                }

                /* so we just verify main entity exists as well before setting up the relation */
                $query  = "SELECT * FROM ". $otherTable ." WHERE id = '". $mainEntityUUID ."' ";

                $result = $this->pdo->query($query)->fetch(\PDO::FETCH_ASSOC);
                if (!isset($result['id'])) {
                    /* if other entity does not exist, return - there's nothing to do here yet */
                    return;
                }

                /* generate insert (ignore) query */
                $this->executeInsertRelationQuery($relation, $mainEntityUUID, $otherEntityUUID, $otherTable);
            } else {
                $table = null;

                /* generate query to select related entities from 'other' table */
                if ($tableDir == 'right') {
                    /* we keep the 'table' to more easily identify the belonging uuid later on */
                    $table = $relation->getTableLeft();
                } else {
                    $table = $relation->getTableRight();
                }

                $query = "SELECT id FROM ". $table ." WHERE ". $relation->getIdentifier(true) ." = '". $data['id'] ."'";

                /* fetch all related entities and create relationship for each */
                $result = $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    /* generate insert (ignore) query */
                    $this->executeInsertRelationQuery($relation, $row['id'], $data['id'], $table);
                }
            }
        }

    }

    private function executeInsertRelationQuery(MappingRelation $relation, $mainUUID, $otherUUID, $otherTable)
    {
        /* generate insert (ignore) query */
        $query  = "INSERT IGNORE INTO ". $relation->getTable() ."(";
        $exists = "SELECT id FROM ". $relation->getTable() ." WHERE ";

        $values = '';

        /* we iterate over the relationship fields and determine each fields value */
        /** @var MappingRelationField $field */
        foreach ($relation->getFields() as $field) {
            $value = null;

            if ($field->getValue() !== null) {
                /* ... that can be a fixed value stored in the relation definition */
                $value = $field->getValue();
            } else if ($field->getFunction() !== null) {
                /* ... or a function (we currently support uuid() and now()) */
                switch ($field->getFunction()) {
                    case 'uuid':
                        $value = $this->generateUUID();
                        break;
                    case 'now':
                        $value = date('Y-m-d H:i:s');
                        break;
                }
            } else if ($field->getSource() !== null) {
                $parts = explode('.', $field->getSource());

                if ($parts[0] == $this->mapping->getDestinationTable()) {
                    $value = $otherUUID;
                } else if ($parts[0] == $otherTable) {
                    $value = $mainUUID;
                }
            }

            if (!empty($value)) {
                $query .= $field->getField() .",";
                $values.= "'". $value ."',";

                if (!in_array($field->getField(), ['id','date_modified'])) {
                    $exists.= $field->getField() ." = '". $value ."' AND ";
                }
            }
        }

        /* remove last comma from query */
        $query  = substr($query, 0, -1);
        $query .= ") VALUES(". substr($values, 0, -1) .");";

        /* remove last AND from query */
        $exists = substr($exists, 0, -4);

        /* relation does exist already, return early */
        $result = $this->pdo->query($exists)->fetch(\PDO::FETCH_ASSOC);
        if (isset($result['id'])) {
            return;
        }

        $this->pdo->query($query);
    }

    /**
     * @return string
     */
    private function generateUUID()
    {
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(' ', $microTime);

        $dec_hex = dechex($a_dec * 1000000);
        $sec_hex = dechex($a_sec);

        $this->ensureLength($dec_hex, 5);
        $this->ensureLength($sec_hex, 6);

        $guid = '';
        $guid .= $dec_hex;
        $guid .= $this->generateUUIDSection(3);
        $guid .= '-';
        $guid .= $this->generateUUIDSection(4);
        $guid .= '-';
        $guid .= $this->generateUUIDSection(4);
        $guid .= '-';
        $guid .= $this->generateUUIDSection(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= $this->generateUUIDSection(6);

        return $guid;
    }

    /**
     * @param int $characters
     * @return string
     */
    private function generateUUIDSection($characters)
    {
        $return = '';
        for ($i = 0; $i < $characters; ++$i) {
            $return .= dechex(mt_rand(0, 15));
        }

        return $return;
    }

    /**
     * @param string $string
     * @param int $length
     */
    private function ensureLength(&$string, $length)
    {
        $strlen = strlen($string);
        if ($strlen < $length) {
            $string = str_pad($string, $length, '0');
        } elseif ($strlen > $length) {
            $string = substr($string, 0, $length);
        }
    }

    /**
     * @param array $data
     * @param string $field
     * @return bool|mixed|null
     */
    private function getArrayElement(array $data, $field)
    {
        $arrayIndexes = explode('.', $field);

        $lastElement = null;
        foreach ($arrayIndexes as $index => $key) {
            if ($index == 0) {
                if (isset($data[$key])) {
                    $lastElement = $data[$key];
                } else {
                    break;
                }
            } else {
                if (isset($lastElement[$key])) {
                    $lastElement = $lastElement[$key];
                } else {
                    break;
                }
            }
        }

        if (!is_array($lastElement)) {
            return $lastElement;
        }
        return false;
    }
}