<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace pulledbits\ActiveRecord\SQL;

use PHPUnit\Framework\Error\Error;
use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;

class SchemaTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $pdo = \pulledbits\ActiveRecord\Test\createMockPDOMultiple([
            '/SELECT \* FROM MySchema.activiteit WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'BlaBla'
                ],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            ],
            '/SELECT \* FROM MySchema.activiteit$/' => [
                [
                    'werkvorm' => 'BlaBlaNoWhere'
                ],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            ],
            '/SELECT id AS _id, werkvorm AS _werkvorm FROM MySchema.activiteit WHERE id = :param1$/' => [
                [],
                [],
                [],
                [],
                []
            ],
            '/^SELECT id AS _id, werkvorm AS _werkvorm FROM MySchema.activiteit WHERE werkvorm = :\w+$/' => [
                []
            ],
            '/^UPDATE MySchema.activiteit SET werkvorm = :\w+ WHERE id = :\w+$/' => 1,
            '/^INSERT INTO MySchema.activiteit \(werkvorm, id\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/SELECT id, werkvorm FROM MySchema.activiteit WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
            ],
            '/SELECT id, werkvorm FROM MySchema.activiteit WHERE id = :\w+ AND foo = :\w+$/' => [],
            '/^DELETE FROM MySchema.activiteit WHERE id = :\w+$/' => 1,
            '/^DELETE FROM MySchema.activiteit WHERE sid = :\w+$/' => false,

            '/^INSERT INTO MySchema.activiteit \(name. foo2\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/^INSERT INTO MySchema.activiteit \(name. foo3, foo4\) VALUES \(:\w+, :\w+, :\w+\)$/' => 1,
            '/^CALL MySchema.missing_procedure\(:\w+, :\w+\)/' => false
        ]);
        $connection = new Connection($pdo);
        $this->object = new \pulledbits\ActiveRecord\SQL\Schema($connection, new QueryFactory(), 'MySchema');
    }

    protected function tearDown()
    {
        if (is_file(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php')) {
            unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php');
        }
    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $this->assertEquals(1, $this->object->update('activiteit', ['werkvorm' => 'My Name'], ['id' => '3']));
    }

    public function testInsertValue_When_DefaultState_Expect_SQLInsertQueryWithPreparedValues() {
        $this->assertEquals(1, $this->object->create('activiteit', ['werkvorm' => 'My Name', 'id' => '3']));
    }

    public function testSelectFrom_When_NoConditions_Expect_WhereLessSQL() {
        $records = $this->object->read('activiteit', [], []);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBlaNoWhere', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_NoColumnIdentifiers_Expect_SQLSelectAsteriskQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('activiteit', [], ['id' => '1']);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBla', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('activiteit', ['id', 'werkvorm'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->delete('activiteit', ['id' => '3']));
    }

    public function testDeleteFrom_When_Erroneous_Expect_Warning() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->assertEquals(0, $this->object->delete('activiteit', ['sid' => '3']));
    }

    public function testExecuteProcedure_When_ExistingProcedure_Expect_ProcedureToBeCalled() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->object->executeProcedure('missing_procedure', ['3', 'Foobar']);
    }
}