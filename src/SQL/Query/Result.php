<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\SQL\Schema;

class Result implements \Countable
{

    private $statement;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, RecordFactory $recordFactory = null)
    {
        $this->statement = $statement;
        $this->recordFactory = $recordFactory;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    private function makeRecord(Schema $schema, string $entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($schema, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    public function fetchAllAs(Schema $schema, string $entityTypeIdentifier) : array
    {
        return array_map(function(array $values) use ($schema, $entityTypeIdentifier) {
            return $this->makeRecord($schema, $entityTypeIdentifier, $values);
        }, $this->statement->fetchAll());
    }
}