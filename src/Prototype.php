<?php
namespace ActiveRecord;

class Prototype
{
	/**
	 * 
	 * @var PHPClass
	 */
	private $class;
	
	public function __construct(PHPClass $class)
	{
		$this->class = $class;
	}
	
	/**
	 * 
	 * @param string $propertyIdentifier
	 */
	public function addProperty($propertyIdentifier)
	{
		$this->class->addPrivateInstanceVariable($propertyIdentifier);
		$this->class->addPublicMethod('get' . ucfirst($propertyIdentifier), "\t\treturn \$this->{$propertyIdentifier};");
	}
	
	/**
	 * 
	 * @param resource $stream
	 * @return integer
	 */
	public function writeOut($stream)
	{
		return fwrite($stream, $this->class->generate());
	}
}