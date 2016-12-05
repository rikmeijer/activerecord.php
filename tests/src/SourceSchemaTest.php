<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_Default_Expect_ArrayWithClasses()
    {
        $schema = new SourceSchema(new class() extends \Doctrine\DBAL\Schema\MySqlSchemaManager {
            public function __construct() {}

            public function listTables() {
                return [
                    new class extends \Doctrine\DBAL\Schema\Table
                    {
                        public function __construct() {}
                        public function getName() {
                            return 'MyTable';
                        }
                    }
                ];
            }
        });

        $schemaDescription = $schema->describe('\\Database');

        $this->assertCount(1, $schemaDescription['tableClasses']);
        $this->assertEquals('\\Database\\Table\\MyTable', $schemaDescription['tableClasses']['MyTable']['identifier']);
    }

}