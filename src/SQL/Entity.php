<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Schema;

final class Entity implements Record
{
    private $schema;

    private $entityTypeIdentifier;

    private $entityType;

    private $values;

    public function __construct(Schema $schema, string $entityTypeIdentifier, EntityType $entityType)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->entityType = $entityType;
        $this->values = [];
    }

    public function contains(array $values) {
        $this->values += $values;
    }

    public function __get($property)
    {
        if (array_key_exists($property, $this->values) === false) {
            return null;
        }
        return $this->values[$property];
    }

    public function read(string $entityTypeIdentifier, array $conditions) : array {
        return $this->schema->read($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    public function missesRequiredValues(): bool
    {
        return count($this->entityType->calculateMissingValues($this->values)) > 0;
    }

    public function __set($property, $value)
    {
        if ($this->missesRequiredValues()) {
            return 0;
        } elseif ($this->schema->update($this->entityTypeIdentifier, [$property => $value], $this->entityType->primaryKey($this->values)) > 0) {
            $this->values[$property] = $value;
        }
    }

    public function delete() : int
    {
        return $this->schema->delete($this->entityTypeIdentifier, $this->entityType->primaryKey($this->values));
    }

    public function create() : int
    {
        $missing = $this->entityType->calculateMissingValues($this->values);
        if (count($missing) === 0) {
            return $this->schema->create($this->entityTypeIdentifier, $this->values);
        }

        trigger_error('Required values are missing: ' . join(', ', $missing), E_USER_ERROR);
    }

    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    private function mergeConditionsWith__callCustomConditions(array $conditions, array $arguments) {
        if (count($arguments) === 1) {
            return array_merge($arguments[0], $conditions);
        }
        return $conditions;
    }

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->entityType->prepareReference(substr($method, 7));
            return $this->schema->read($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($this->fillConditions($reference['conditions']), $arguments));
        } elseif (substr($method, 0, 11) === 'referenceBy') {
            $reference = $this->entityType->prepareReference(substr($method, 11));
            $this->schema->create($reference['entityTypeIdentifier'], $this->mergeConditionsWith__callCustomConditions($this->fillConditions($reference['conditions']), $arguments));
            $records = $this->schema->read($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($this->fillConditions($reference['conditions']), $arguments));
            return $records[0];
        }
        return null;
    }
}