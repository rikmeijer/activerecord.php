<?php
namespace ActiveRecord\Source;

final class Table
{

    /**
     * @var string
     */
    private $namespace;

    /**
     * Table constructor.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    private function describeMethod(bool $static, array $parameters, array $body) : array {
        return [
            'static' => $static,
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    private function describeBodySelect(array $fields, string $from, array $where) : array {
        $whereParameters = [];
        foreach ($where as $referencedColumnName => $parameterIdentifier) {
            $whereParameters[] = $this->makeArrayMappingToProperty($referencedColumnName, $parameterIdentifier);
        }

        return ['return $this->schema->select("' . $from . '", [\'' . join('\', \'', $fields) . '\'], [', join(',' . PHP_EOL, $whereParameters), ']);'];
    }

    private function makeArrayMapping(string $keyIdentifier, string $variableIdentifier) : string {
        return '\'' . $keyIdentifier . '\' => ' . $variableIdentifier;
    }
    private function makeArrayMappingToProperty(string $keyIdentifier, string $propertyIdentifier) {
        return $this->makeArrayMapping($keyIdentifier, '$this->__get(\'' . $propertyIdentifier . '\')');
    }
    
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        if (substr($this->namespace, -1) != "\\") {
            $this->namespace .= "\\";
        }

        $columnIdentifiers = array_keys($dbalSchemaTable->getColumns());

        $tableIdentifier = $dbalSchemaTable->getName();

        $methods = [
            '__construct' => $this->describeMethod(false, ["schema" => '\ActiveRecord\Schema'], ['$this->schema = $schema;']),
            'fetchAll' => $this->describeMethod(false, [], $this->describeBodySelect($columnIdentifiers, $tableIdentifier, []))
        ];

        $primaryKeyDefaultValue = $primaryKeyWhere = $defaultUpdateValues = [];
        $properties = [
            'schema' => ['\ActiveRecord\Schema', ['static' => false, 'value' => null]]
        ];
        foreach ($columnIdentifiers as $columnIdentifier) {
            $properties['_' . $columnIdentifier] = ['string', ['static' => false, 'value' => null]];
            $defaultUpdateValues[] = $this->makeArrayMappingToProperty($columnIdentifier, $columnIdentifier);

            if ($dbalSchemaTable->hasPrimaryKey() === false) {
                // no primary key
            } elseif (in_array($columnIdentifier, $dbalSchemaTable->getPrimaryKeyColumns())) {
                $primaryKeyDefaultValue[] = $columnIdentifier;
                $primaryKeyWhere[] = $this->makeArrayMappingToProperty($columnIdentifier, $columnIdentifier);
            }
        }
        $methods['wherePrimaryKey'] = $this->describeMethod(true, ['values' => 'array'], [
            '$wherePrimaryKey = [];',
            'foreach ([\''.join('\', \'', $primaryKeyDefaultValue).'\'] as $primaryKeyColumnIdentifier) {',
            '    if (array_key_exists($values, $primaryKeyColumnIdentifier)) {',
            '        $wherePrimaryKey[$primaryKeyColumnIdentifier] = $values[$primaryKeyColumnIdentifier];',
            '    }',
            '}',
            'return $wherePrimaryKey;'
        ]);

        $methods['__set'] = $this->describeMethod(false, ["property" => 'string', "value" => 'string'], [
            'if (property_exists($this, $property)) {',
            '$this->{$this->schema->transformColumnToProperty($property)} = $value;',
            '$this->schema->update("' . $tableIdentifier . '", [' . join(',' . PHP_EOL, $defaultUpdateValues) . '], [' . join(',' . PHP_EOL, $primaryKeyWhere) . ']);',
            '}'
        ]);
        $methods['__get'] = $this->describeMethod(false, ["property" => 'string'], [
            'return $this->{$this->schema->transformColumnToProperty($property)};'
        ]);

        $methods['delete'] = $this->describeMethod(false, [], [
            'return $this->schema->delete("' . $tableIdentifier . '", [' . join(',' . PHP_EOL, $primaryKeyWhere) . ']);'
        ]);

        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);

            $fkLocalColumns = $foreignKey->getLocalColumns();
            $where = array_combine($foreignKey->getForeignColumns(), $fkLocalColumns);
            $query = $this->describeBodySelect($foreignKey->getForeignColumns(), $foreignKey->getForeignTableName(), $where);
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeMethod(false, [], $query);
        }
        
        return [
            'identifier' => $this->namespace . $tableIdentifier,

            'properties' => $properties,
            'methods' => $methods
        ];
    }
    
}