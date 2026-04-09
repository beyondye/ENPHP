<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\model\ModelException;

class ModelDeleteTest extends TestCase
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var DatabaseAbstract|PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建数据库模拟对象
        $this->dbMock = $this->createStub(DatabaseAbstract::class);

        // 创建一个继承自 Model 的测试类
        $this->model = new class($this->dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'age' => 'integer'
                ];
            }

            // 暴露 protected 的 conditions 属性
            public function getConditions()
            {
                return $this->conditions;
            }
        };
    }

    /**
     * 测试 delete() 方法 - 基本用法（单个主键值）
     */
    public function testDeleteWithSinglePrimaryKey()
    {
        // 模拟返回值
        $expectedResult = 1;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（单个主键值）
        $result = $this->model->delete(1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 字符串主键值
     */
    public function testDeleteWithStringPrimaryKey()
    {
        // 模拟返回值
        $expectedResult = 1;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（字符串主键值）
        $result = $this->model->delete('1');

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 带字段名和值
     */
    public function testDeleteWithFieldAndValue()
    {
        // 模拟返回值
        $expectedResult = 2;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（带字段名和值）
        $result = $this->model->delete('id', 1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 带完整条件
     */
    public function testDeleteWithCompleteCondition()
    {
        // 模拟返回值
        $expectedResult = 3;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（带完整条件）
        $result = $this->model->delete('id', '=', 1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 带 in 条件
     */
    public function testDeleteWithInCondition()
    {
        // 模拟返回值
        $expectedResult = 5;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（带 in 条件）
        $result = $this->model->delete('id', 'in', [1, 2, 3, 4, 5]);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 带 between 条件
     */
    public function testDeleteWithBetweenCondition()
    {
        // 模拟返回值
        $expectedResult = 10;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（带 between 条件）
        $result = $this->model->delete('age', 'between', [18, 25]);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 多个条件（AND）
     */
    public function testDeleteWithMultipleConditionsAnd()
    {
        // 模拟返回值
        $expectedResult = 2;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（多个条件）
        $result = $this->model->delete(['id', '>', 1, 'and'], ['age', '<', 30]);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 多个条件（OR）
     */
    public function testDeleteWithMultipleConditionsOr()
    {
        // 模拟返回值
        $expectedResult = 5;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 调用 delete() 方法（多个条件）
        $result = $this->model->delete(['id', '>', 5, 'or'], ['age', '<', 18]);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 空条件抛出异常
     */
    public function testDeleteWithEmptyConditionThrowsException()
    {
        // 测试空条件
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');

        // 调用 delete() 方法（空条件）
        $this->model->delete();
    }

    /**
     * 测试 delete() 方法 - 处理后为空的条件抛出异常
     */
    public function testDeleteWithProcessedEmptyConditionThrowsException()
    {
        // 测试处理后为空的条件
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');

        // 调用 delete() 方法（空数组条件）
        $this->model->delete([]);
    }

    /**
     * 测试 delete() 方法 - 安全测试（防止 SQL 注入）
     */
    public function testDeleteSecurity()
    {
        // 模拟返回值
        $expectedResult = 1;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 测试潜在的 SQL 注入攻击（使用字符串字段）
        $maliciousInput = "Test' OR 1=1 --";
        $result = $this->model->delete('name', '=', $maliciousInput);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 delete() 方法 - 验证异常捕获（无效字段类型）
     */
    public function testDeleteWithValidationException()
    {
        // 测试向整数字段传递字符串值时应该抛出异常
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:123abc');

        // 尝试向整数字段传递字符串值
        $this->model->delete('id', '=', '123abc');
    }

    /**
     * 测试 delete() 方法 - 调用 deleting() 方法
     */
    public function testDeleteCallsDeletingMethod()
    {
        // 模拟返回值
        $expectedResult = 1;
        $this->dbMock->method('delete')->willReturn($expectedResult);

        // 创建一个继承自 Model 的测试类，重写 deleting() 方法
        $deletingCalled = false;
        $testModel = new class($this->dbMock) extends Model {
            public $deletingCalled = false;
            
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = ['id' => 'integer'];
            }

            protected function deleting(): void {
                $this->deletingCalled = true;
            }
        };

        // 调用 delete() 方法
        $result = $testModel->delete(1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($testModel->deletingCalled);
    }



    /**
     * 测试 delete 方法 - 有效的条件
     */
    public function testDeleteValidCondition()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 delete 方法，返回删除的记录数
        $dbMock->expects($this->once())
            ->method('delete')
            ->with('test_table', ...[['id', '=', 1]])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试删除数据
        $result = $model->delete(1);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete 方法 - 空条件
     */
    public function testDeleteEmptyCondition()
    {
        // 创建数据库存根对象
        $dbMock = $this->createStub(DatabaseAbstract::class);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');
        $model->delete();
    }

    /**
     * 测试 delete 方法 - 复杂的条件
     */
    public function testDeleteComplexCondition()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 delete 方法，返回删除的记录数
        $dbMock->expects($this->once())
            ->method('delete')
            ->with('test_table', ...[['id', 'in', [1, 2, 3]]])
            ->willReturn(3);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试使用复杂条件删除数据
        $result = $model->delete('id', [1, 2, 3]);
        $this->assertEquals(3, $result);
    }

    /**
     * 测试 delete 方法 - 多个条件
     */
    public function testDeleteMultipleConditions()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 delete 方法，返回删除的记录数
        $dbMock->expects($this->once())
            ->method('delete')
            ->with('test_table', ['id', '=', 1, 'and'], ['name', '=', 'Test'])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试使用多个条件删除数据
        $result = $model->delete(['id', '=', 1], ['name', '=', 'Test']);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete 方法 - 边界值测试
     */
    public function testDeleteBoundaryValues()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 delete 方法，返回删除的记录数
        $dbMock->expects($this->once())
            ->method('delete')
            ->with('test_table', ...[['id', '=', 0]])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试使用边界值作为条件删除数据
        $result = $model->delete(0);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 delete 方法 - 不同类型的参数
     */
    public function testDeleteDifferentTypes()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 delete 方法，返回删除的记录数
        $dbMock->expects($this->exactly(2))
            ->method('delete')
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'price' => 'decimal'
                ];
            }
        };

        // 测试使用不同类型的参数
        $model->delete(1); // 整数
        $model->delete('1'); // 字符串
       // $model->delete(1.5); // 浮点数
    }
}