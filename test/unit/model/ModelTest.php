<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelTest extends TestCase
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var DatabaseAbstract|PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbMock;

    /**
     * @var ResultAbstract|PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建 ResultAbstract 模拟对象
        $this->resultMock = $this->createStub(ResultAbstract::class);

        // 创建数据库存根对象
        $this->dbMock = $this->createStub(DatabaseAbstract::class);
        $this->dbMock->method('select')
            ->willReturn($this->resultMock);
        $this->dbMock->method('delete')
            ->willReturn(1);
        $this->dbMock->method('update')
            ->willReturn(1);
        // 为 insert 方法设置一个变量来跟踪调用次数
        $insertCallCount = 0;
        $this->dbMock->method('insert')
            ->willReturnCallback(function () use (&$insertCallCount) {
                $insertCallCount++;
                // 第一次调用返回 1，第二次调用返回 2
                return $insertCallCount;
            });
        $this->dbMock->method('transaction')
            ->willReturn(true);
        $this->dbMock->method('commit')
            ->willReturn(true);
        $this->dbMock->method('rollback')
            ->willReturn(true);

        // 创建一个继承自 Model 的测试类
        $this->model = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'id' => 0,
                    'name' => '',
                    'email' => '',
                    'age' => 0,
                    'status' => 'active'
                ];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'age' => 'integer',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }

            // 暴露 protected 的 conditions 属性
            public function getConditions()
            {
                return $this->conditions;
            }

            // 暴露 protected 的 _bindWhere 方法
            public function bindWhere(float|int|string|array ...$wheres): array
            {
                return $this->_bindWhere(...$wheres);
            }

            // 暴露 protected 的 creating 方法
            public function callCreating()
            {
                $this->creating();
            }

            // 暴露 protected 的 updating 方法
            public function callUpdating()
            {
                $this->updating();
            }

            // 暴露 protected 的 deleting 方法
            public function callDeleting()
            {
                $this->deleting();
            }

            // 暴露 protected 的 db 属性
            public function getDb()
            {
                return $this->db;
            }
        };
        
        // 手动设置 db 属性为我们的模拟对象
        $reflection = new ReflectionProperty(get_class($this->model), 'db');
        $reflection->setAccessible(true);
        $reflection->setValue($this->model, $this->dbMock);
    }

    /**
     * 模拟 Database::instance 方法
     */
    protected function mockDatabaseInstance()
    {
        // 使用反射获取 Database 类
        $reflection = new ReflectionClass('system\Database');
        
        // 检查是否存在 instance 方法
        if ($reflection->hasMethod('instance')) {
            $instanceMethod = $reflection->getMethod('instance');
            $instanceMethod->setAccessible(true);
        }
    }

    /**
     * 测试构造函数 - 直接注入数据库实例
     */
    public function testConstructorWithDatabaseInstance()
    {
        // 测试直接注入数据库实例
        $model = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
            }

            // 暴露 protected 的 db 属性
            public function getDb()
            {
                return $this->db;
            }
        };
        
        // 手动设置 db 属性为我们的模拟对象
        $reflection = new ReflectionProperty(get_class($model), 'db');
        $reflection->setAccessible(true);
        $reflection->setValue($model, $this->dbMock);

        // 验证 db 属性被设置为传入的实例
        $this->assertEquals($this->dbMock, $model->getDb());
    }

    /**
     * 测试事件方法
     */
    public function testEventMethods()
    {
        // 测试 creating 方法
        $this->model->callCreating();
        // 测试 updating 方法
        $this->model->callUpdating();
        // 测试 deleting 方法
        $this->model->callDeleting();
        // 这些方法是空的，所以只需要确保它们可以被调用而不抛出异常
        $this->assertTrue(true);
    }

    /**
     * 测试 select() 方法
     */
    public function testSelect()
    {
        // 测试 select 方法
        $result = $this->model->select(['id', 'name']);
        
        // 验证返回值是模型实例
        $this->assertInstanceOf(Model::class, $result);
        // 验证条件被设置
        $conditions = $this->model->getConditions();
        $this->assertEquals(['id', 'name'], $conditions['fields']);
    }

    /**
     * 测试 where() 方法
     */
    public function testWhere()
    {
        // 测试 where 方法
        $result = $this->model->where('id', '=', 1);
        
        // 验证返回值是模型实例
        $this->assertInstanceOf(Model::class, $result);
        // 验证条件被设置
        $conditions = $this->model->getConditions();
        $this->assertNotEmpty($conditions['wheres']);
    }

    /**
     * 测试 groupBy() 方法
     */
    public function testGroupBy()
    {
        // 测试 groupBy 方法
        $result = $this->model->groupBy('status');
        
        // 验证返回值是模型实例
        $this->assertInstanceOf(Model::class, $result);
        // 验证条件被设置
        $conditions = $this->model->getConditions();
        $this->assertEquals(['status'], $conditions['groups']);
    }

    /**
     * 测试 having() 方法
     */
    public function testHaving()
    {
        // 测试 having 方法
        $result = $this->model->having(['age', '>', 18]);
        
        // 验证返回值是模型实例
        $this->assertInstanceOf(Model::class, $result);
        // 验证条件被设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([['age', '>', 18]], $conditions['havings']);
    }

    /**
     * 测试 orderBy() 方法
     */
    public function testOrderBy()
    {
        // 测试 orderBy 方法
        $result = $this->model->orderBy(['id' => 'desc']);
        
        // 验证返回值是模型实例
        $this->assertInstanceOf(Model::class, $result);
        // 验证条件被设置
        $conditions = $this->model->getConditions();
        $this->assertEquals(['id' => 'desc'], $conditions['orders']);
    }

    /**
     * 测试 delete() 方法 - 空条件
     */
    public function testDeleteWithEmptyConditions()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');

        $this->model->delete();
    }

    /**
     * 测试 delete() 方法 - 非空条件
     */
    public function testDeleteWithNonEmptyConditions()
    {
        // 模拟数据库方法
        $this->dbMock->method('delete')->willReturn(1);

        // 调用 delete 方法
        $result = $this->model->delete(1);

        // 验证结果
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete() 方法 - 数组条件
     */
    public function testDeleteWithArrayConditions()
    {
        // 模拟数据库方法
        $this->dbMock->method('delete')->willReturn(1);

        // 调用 delete 方法，使用数组条件
        $result = $this->model->delete(['id', '=', 1]);

        // 验证结果
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete() 方法 - 多条件
     */
    public function testDeleteWithMultipleConditions()
    {
        // 模拟数据库方法
        $this->dbMock->method('delete')->willReturn(1);

        // 调用 delete 方法，使用多条件
        $result = $this->model->delete(['id', '=', 1], ['name', '=', 'Test']);

        // 验证结果
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete() 方法 - 非空但绑定后为空的条件
     */
    public function testDeleteWithNonEmptyButBoundToEmptyConditions()
    {
        // 我们需要模拟 _bindWhere 方法返回空数组
        // 为此，我们需要创建一个新的测试模型，重写 _bindWhere 方法
        $modelWithEmptyBindWhere = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'id' => 0,
                    'name' => '',
                    'email' => '',
                    'age' => 0,
                    'status' => 'active'
                ];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'age' => 'integer',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }

            // 重写 _bindWhere 方法，返回空数组
            protected function _bindWhere(float|int|string|array ...$wheres): array
            {
                return [];
            }
        };

        // 手动设置 db 属性为我们的模拟对象
        $reflection = new ReflectionProperty(get_class($modelWithEmptyBindWhere), 'db');
        $reflection->setAccessible(true);
        $reflection->setValue($modelWithEmptyBindWhere, $this->dbMock);

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');

        // 调用 delete 方法，_bindWhere 会返回空数组
        $modelWithEmptyBindWhere->delete(1);
    }

    /**
     * 测试 update() 方法 - 空条件
     */
    public function testUpdateWithEmptyConditions()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Condition Cannot Be Empty.');

        $this->model->update(['name' => 'Test']);
    }

    /**
     * 测试 update() 方法 - 空数据
     */
    public function testUpdateWithEmptyData()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Data Cannot Be Empty.');

        $this->model->update([], 1);
    }

    /**
     * 测试 update() 方法 - 未定义 fillable
     */
    public function testUpdateWithoutFillable()
    {
        $modelWithoutFillable = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Fillable Array Not Defined.');

        $modelWithoutFillable->update(['name' => 'Test'], 1);
    }

    /**
     * 测试 update() 方法 - 未定义 schema
     */
    public function testUpdateWithoutSchema()
    {
        $modelWithoutSchema = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'name' => ''
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Schema Array Not Defined.');

        $modelWithoutSchema->update(['name' => 'Test'], 1);
    }

    /**
     * 测试 update() 方法 - 非空条件和数据
     */
    public function testUpdateWithNonEmptyConditionsAndData()
    {
        // 模拟数据库方法
        $this->dbMock->method('update')->willReturn(1);

        // 调用 update 方法
        $result = $this->model->update(['name' => 'Test'], 1);

        // 验证结果
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 update() 方法 - 非空但绑定后为空的条件
     */
    public function testUpdateWithNonEmptyButBoundToEmptyConditions()
    {
        // 我们需要模拟 _bindWhere 方法返回空数组
        // 为此，我们需要创建一个新的测试模型，重写 _bindWhere 方法
        $modelWithEmptyBindWhere = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'id' => 0,
                    'name' => '',
                    'email' => '',
                    'age' => 0,
                    'status' => 'active'
                ];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'age' => 'integer',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }

            // 重写 _bindWhere 方法，返回空数组
            protected function _bindWhere(float|int|string|array ...$wheres): array
            {
                return [];
            }
        };

        // 手动设置 db 属性为我们的模拟对象
        $reflection = new ReflectionProperty(get_class($modelWithEmptyBindWhere), 'db');
        $reflection->setAccessible(true);
        $reflection->setValue($modelWithEmptyBindWhere, $this->dbMock);

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Condition Cannot Be Empty.');

        // 调用 update 方法，_bindWhere 会返回空数组
        $modelWithEmptyBindWhere->update(['name' => 'Test'], 1);
    }

    /**
     * 测试 insert() 方法 - 空数据
     */
    public function testInsertWithEmptyData()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Insert Data Cannot Be Empty Or Not Array.');

        $this->model->insert([]);
    }

    /**
     * 测试 insert() 方法 - 未定义 fillable
     */
    public function testInsertWithoutFillable()
    {
        $modelWithoutFillable = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Fillable Array Not Defined.');

        $modelWithoutFillable->insert(['name' => 'Test']);
    }

    /**
     * 测试 insert() 方法 - 未定义 schema
     */
    public function testInsertWithoutSchema()
    {
        $modelWithoutSchema = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'name' => ''
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Schema Array Not Defined.');

        $modelWithoutSchema->insert(['name' => 'Test']);
    }

    /**
     * 测试 insert() 方法 - 非空数据
     */
    public function testInsertWithNonEmptyData()
    {
        // 模拟数据库方法
        $this->dbMock->method('insert')->willReturn(1);

        // 调用 insert 方法
        $result = $this->model->insert(['name' => 'Test']);

        // 验证结果
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 insert() 方法 - 多条数据
     */
    public function testInsertWithMultipleData()
    {
        // 调用 insert 方法，插入多条数据
        $result = $this->model->insert(
            ['name' => 'Test1'],
            ['name' => 'Test2']
        );

        // 验证结果 - 对于多条数据插入，返回最后插入的 ID
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 _bindWhere() 方法 - 单个数字值
     */
    public function testBindWhereWithSingleNumericValue()
    {
        $result = $this->model->bindWhere(1);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试 _bindWhere() 方法 - 单个字符串值
     */
    public function testBindWhereWithSingleStringValue()
    {
        $result = $this->model->bindWhere('1');
        $this->assertEquals([['id', '=', '1']], $result);
    }

    /**
     * 测试 _bindWhere() 方法 - 多个参数
     */
    public function testBindWhereWithMultipleParameters()
    {
        $result = $this->model->bindWhere('age', '>', 18);
        $this->assertEquals([['age', '>', 18]], $result);
    }

    /**
     * 测试 _bindWhere() 方法 - 数组参数
     */
    public function testBindWhereWithArrayParameters()
    {
        $result = $this->model->bindWhere(['id', '=', 1]);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试 first() 方法
     */
    public function testFirst()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法
        $result = $this->model->where('id', '=', 1)->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法
     */
    public function testAll()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法
        $result = $this->model->where('status', '=', 'active')->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 自定义 limit
     */
    public function testAllWithCustomLimit()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法，设置自定义 limit
        $result = $this->model->where('status', '=', 'active')->all(5);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法
     */
    public function testCount()
    {
        // 模拟返回值
        $expectedResult = ['total' => 5];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 count() 方法
        $result = $this->model->where('status', '=', 'active')->count();

        // 验证结果
        $this->assertEquals(5, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 无结果
     */
    public function testCountWithNoResults()
    {
        // 模拟返回值
        $this->resultMock->method('first')->willReturn(null);

        // 调用 count() 方法
        $result = $this->model->where('status', '=', 'active')->count();

        // 验证结果
        $this->assertEquals(0, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 find() 方法
     */
    public function testFind()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 find() 方法
        $result = $this->model->find(1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 exists() 方法
     */
    public function testExists()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 exists() 方法
        $result = $this->model->exists(1);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试 exists() 方法 - 不存在的记录
     */
    public function testExistsWithNonExistentRecord()
    {
        // 模拟返回值
        $this->resultMock->method('first')->willReturn(null);

        // 调用 exists() 方法
        $result = $this->model->exists(999);

        // 验证结果
        $this->assertFalse($result);
    }

    /**
     * 测试 rows() 方法
     */
    public function testRows()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法
        $result = $this->model->where('status', '=', 'active')->rows(2, 0);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 默认参数
     */
    public function testRowsWithDefaultParameters()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法，使用默认参数
        $result = $this->model->where('status', '=', 'active')->rows();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 自定义 offset
     */
    public function testRowsWithCustomOffset()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 3, 'name' => 'Test3'],
            (object) ['id' => 4, 'name' => 'Test4']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法，设置自定义 offset
        $result = $this->model->where('status', '=', 'active')->rows(2, 2);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 transaction() 方法 - 成功
     */
    public function testTransactionSuccess()
    {
        // 模拟数据库方法
        $this->dbMock->method('transaction')->willReturn(true);
        $this->dbMock->method('commit')->willReturn(true);
        $this->dbMock->method('rollback')->willReturn(true);

        // 测试事务成功
        $result = $this->model->transaction(function () {
            return 'Success';
        });

        // 验证结果
        $this->assertEquals('Success', $result);
    }

    /**
     * 测试 transaction() 方法 - 失败
     */
    public function testTransactionFailure()
    {
        // 模拟数据库方法
        $this->dbMock->method('transaction')->willReturn(true);
        $this->dbMock->method('rollback')->willReturn(true);

        // 测试事务失败
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Transaction Error: Test error');

        $this->model->transaction(function () {
            throw new \Exception('Test error');
        });
    }

    /**
     * 测试安全情况 - SQL 注入尝试
     */
    public function testSqlInjectionAttempt()
    {
        // 尝试 SQL 注入
        // 验证方法执行没有抛出异常
        try {
            $result = $this->model->where('name', '=', "' OR 1=1 --" )->first();
            // 测试通过，没有抛出异常
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // 如果抛出异常，测试失败
            $this->fail('SQL injection attempt caused an exception: ' . $e->getMessage());
        }
    }

    /**
     * 测试边界情况 - 负数 ID
     */
    public function testNegativeId()
    {
        // 测试负整数 ID
        $result = $this->model->bindWhere(-1);
        $this->assertEquals([['id', '=', -1]], $result);
    }

    /**
     * 测试边界情况 - 空字符串
     */
    public function testEmptyString()
    {
        // 测试空字符串
        // 注意：由于 schema 中 id 是 integer 类型，空字符串会被验证失败
        // 这里我们测试的是 bindWhere 方法的逻辑，所以我们需要捕获验证异常
        try {
            $result = $this->model->bindWhere('');
        } catch (ModelException $e) {
            // 验证异常消息
            $this->assertStringContainsString('Value Not Matched', $e->getMessage());
            return;
        }
        // 如果没有抛出异常，测试失败
        $this->fail('Expected ModelException was not thrown');
    }

    /**
     * 测试边界情况 - 大整数
     */
    public function testLargeInteger()
    {
        // 测试大整数
        $largeId = PHP_INT_MAX;
        $result = $this->model->bindWhere($largeId);
        $this->assertEquals([['id', '=', $largeId]], $result);
    }

    /**
     * 测试链式调用
     */
    public function testChainedCalls()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 测试链式调用
        $result = $this->model
            ->select(['id', 'name'])
            ->where('age', '>', 18)
            ->orderBy(['id' => 'desc'])
            ->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试链式调用 - 包含 groupBy 和 having
     */
    public function testChainedCallsWithGroupByAndHaving()
    {
        // 模拟返回值
        $expectedResult = (object) ['status' => 'active', 'total' => 5];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 测试链式调用，包含 groupBy 和 having
        $result = $this->model
            ->select(['status', 'count(*) as total'])
            ->groupBy('status')
            ->having(['total', '>', 0])
            ->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }
}