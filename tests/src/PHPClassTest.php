<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGeneratePHPClassGivesEmptyPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
	

	public function testPHPClassWithPrivateInstanceVariable()
	{
		$class = new PHPClass("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tprivate \$name;" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
	

	public function testPHPClassWithDependency()
	{
		$class = new PHPClass("PersonRecord");
		$class->dependsOn("repository");
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tpublic function __construct(\$repository)" . PHP_EOL . 
				"\t{" . PHP_EOL .
				"\t\$this->repository = \$repository;" . PHP_EOL .
				"\t}" . PHP_EOL .
				"\tprivate \$repository;" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
}