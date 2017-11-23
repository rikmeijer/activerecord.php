<?php

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use pulledbits\ActiveRecord\RecordConfigurator;

final class WrappedEntity implements RecordConfigurator
{
    private $wrappedEntityConfigurator;

    public function __construct(\pulledbits\ActiveRecord\RecordConfigurator $wrappedEntityConfigurator)
    {
        $this->wrappedEntityConfigurator = $wrappedEntityConfigurator;
    }

    public function configure() : \pulledbits\ActiveRecord\Record
    {
        return $this->wrappedEntityConfigurator->configure();
    }
}