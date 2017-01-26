<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:19
 */

namespace ActiveRecord\SQL\Schema;

class Table implements \ActiveRecord\Schema\EntityType
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var \ActiveRecord\Schema
     */
    private $schema;

    public function __construct(string $identifier, \ActiveRecord\Schema $schema) {
        $this->identifier = $identifier;
        $this->schema = $schema;
    }

    public function executeEntityConfigurator(string $path, array $values) : \ActiveRecord\Entity
    {
        $configurator = require $path . DIRECTORY_SEPARATOR . $this->identifier . '.php';
        return $configurator($this, $values);
    }

    public function select(array $columnIdentifiers, array $whereParameters)
    {
        return $this->schema->selectFrom($this->identifier, $columnIdentifiers, $whereParameters, function(\Closure $recordConfigurator) {
            return $recordConfigurator($this);
        });
    }

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
    {
        return $this->schema->selectFrom($tableIdentifier, $columnIdentifiers, $whereParameters, function(\Closure $recordConfigurator) use ($tableIdentifier) {
            return $recordConfigurator(new Table($tableIdentifier, $this->schema));
        });
    }

    public function insert(array $values) {
        return $this->schema->insertValues($this->identifier, $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        return $this->schema->updateWhere($this->identifier, $setParameters, $whereParameters);
    }

    public function delete(array $whereParameters) {
        return $this->schema->deleteFrom($this->identifier , $whereParameters);
    }
}