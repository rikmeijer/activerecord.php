<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

class ConfiguratorFactory
{
    private $generatorGeneratorFactory;
    private $path;

    public function __construct(\pulledbits\ActiveRecord\Source\ConfiguratorGeneratorFactory $generatorGeneratorFactory, string $path)
    {
        $this->generatorGeneratorFactory = $generatorGeneratorFactory;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate(string $entityTypeIdentifier) : callable
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generator = $this->generatorGeneratorFactory->makeConfiguratorGenerator($entityTypeIdentifier);
            $generator->generateConfigurator(\GuzzleHttp\Psr7\stream_for(fopen($configuratorPath, 'w')));
        }
        return require $configuratorPath;
    }
}