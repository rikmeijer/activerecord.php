<?php


namespace pulledbits\ActiveRecord\SQL\Query;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Connection;

class Select
{
    private $entityTypeIdentifier;
    private $attributeIdentifiers;

    /**
     * @var Where
     */
    private $where;

    public function __construct($entityTypeIdentifier, array $attributeIdentifiers)
    {
        if (count($attributeIdentifiers) === 0) {
            $attributeIdentifiers[] = '*';
        }
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->attributeIdentifiers = $attributeIdentifiers;
    }

    public function where(Where $where)
    {
        $this->where = $where;
    }

    public function execute(Connection $connection) : Result
    {
        return $connection->query($this->entityTypeIdentifier,"SELECT " . join(', ', $this->attributeIdentifiers) . " FROM " . $this->entityTypeIdentifier . $this->where, $this->where->parameters());
    }
}