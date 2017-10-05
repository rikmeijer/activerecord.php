<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $sourceSchema;
    private $path;

    public function __construct(Source\Schema $sourceSchema, string $path)
    {
        $this->sourceSchema = $sourceSchema;
        $this->path = $path;
    }

    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generatorGeneratorFactory = new Source\GeneratorGeneratorFactory();
            $recordClassDescription = $this->sourceSchema->describeTable(new \pulledbits\ActiveRecord\SQL\Source\Table(), $entityTypeIdentifier);
            $generator = $generatorGeneratorFactory->makeGeneratorGenerator($recordClassDescription);
            file_put_contents($configuratorPath, $generator->generate());
        }

        $configurator = require $configuratorPath;
        return $configurator($schema, $entityTypeIdentifier);
    }
}