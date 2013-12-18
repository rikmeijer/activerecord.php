<?php

namespace Behavior\Annotated;

/**
 * Description of PHPClass
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class PHPClass extends \Behavior\Annotated
{
    /**
     *
     * @var Subroutine\Method[]
     */
    protected $methods = array();
    
    /**
     * 
     * @param \Behavior\Annotated\Factory $factory
     * @param \Reflector $reflector
     */
    public function __construct(Factory $factory, \Reflector $reflector)
    {
        parent::__construct($factory, $reflector);
        
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->methods[] = $factory->makeAnnotatedMethod($method);
        }
    }
    
    /**
     * 
     * @param \Behavior\Annotations\Factory $annotationsFactory
     * @return \Behavior\Annotations
     */
    protected function makeAnnotations(\Behavior\Annotations\Factory $annotationsFactory)
    {
        return $annotationsFactory->makeAnnotationsForReflectionClass($this->reflector);
    }
    
    /**
     * 
     * @param \Behavior\PHP\Factory $phpFactory
     * @return \Behavior\PHP\PHPClass
     */
    public function makeTest(\Behavior\PHP\Factory $phpFactory)
    {
        return $phpFactory->makePHPClass($this->reflector->getShortName() . 'Test');
    }

}
