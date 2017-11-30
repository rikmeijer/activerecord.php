<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\SQL\Connection;
use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $schema;

    protected function setUp()
    {
        $this->connection = new Connection(createMockPDOMultiple([]));
        $this->schema = $this->connection->schema();
    }

    public function testConstructor_When_Default_Expect_ArrayWithRecordConfigurators()
    {
        $myTable = new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]);
        $anotherTable = new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ]);

        $sourceSchema = new Schema($this->schema, [
            'MyTable' => $myTable,
            'AnotherTable' => $anotherTable
        ]);

        $this->assertEquals($this->schema->makeRecordType('MyTable', $myTable), $sourceSchema->describeTable('MyTable'));
        $this->assertEquals($this->schema->makeRecordType('AnotherTable', $anotherTable), $sourceSchema->describeTable('AnotherTable'));
    }


    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->schema, [
            'MyView' => new TableDescription()
        ]);

        $tableDescription = $schema->describeTable('MyView');

        $this->assertEquals($this->schema->makeRecordType('MyView', new TableDescription()), $tableDescription);
    }


    public function testDescribe_When_ViewWithUnderscoreNoExistingTableAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->schema,  ['MyView_bla' => new TableDescription()]);

        $tableDescription = $schema->describeTable('MyView_bla');

        $this->assertEquals($this->schema->makeRecordType('MyView_bla', new TableDescription()), $tableDescription);
    }

    public function testDescribe_When_ViewUsedWithExistingTableIdentifier_Expect_EntityTypeIdentifier()
    {

        $myTable = new TableDescription(['name', 'birthdate'], [], [
            'FkOthertableRole' => [
                'table' => 'OtherTable',
                'where' => [
                    'id' => 'role_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AntoherTable',
                'where' => [
                    'id' => 'role2_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]);

        $schema = new Schema($this->schema, [
            'MyTable' => $myTable,
            'MyTable_today' => $myTable
        ]);

        $tableDescription = $schema->describeTable('MyTable_today');

        $this->assertEquals($this->schema->makeRecordType('MyTable_today', $myTable), $tableDescription);
    }
}
