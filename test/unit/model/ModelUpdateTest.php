<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;
use system\database\DatabaseAbstract;

class ModelUpdateTest extends TestCase
{
    /**
     * 测试 update 方法 - 有效的数据和条件
     */
    public function testUpdateValidDataAndCondition()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 update 方法，返回更新的记录数
        $dbMock->expects($this->once())
            ->method('update')
            ->with('test_table', ['name' => 'Updated Test'], ...[['id', '=', 1]])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试更新数据
        $result = $model->update(['name' => 'Updated Test'], 1);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 update 方法 - 空数据
     */
    public function testUpdateEmptyData()
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
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Data Cannot Be Empty.');
        $model->update([], 1);
    }

    /**
     * 测试 update 方法 - 空条件
     */
    public function testUpdateEmptyCondition()
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
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Condition Cannot Be Empty.');
        $model->update(['name' => 'Updated Test']);
    }

    /**
     * 测试 update 方法 - 未定义 fillable
     */
    public function testUpdateWithoutFillable()
    {
        // 创建数据库存根对象
        $dbMock = $this->createStub(DatabaseAbstract::class);

        // 创建一个没有 fillable 的模型
        $modelWithoutFillable = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                // 不设置 fillable
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Fillable Array Not Defined.');
        $modelWithoutFillable->update(['name' => 'Updated Test'], 1);
    }

    /**
     * 测试 update 方法 - 未定义 schema
     */
    public function testUpdateWithoutSchema()
    {
        // 创建数据库存根对象
        $dbMock = $this->createStub(DatabaseAbstract::class);

        // 创建一个没有 schema 的模型
        $modelWithoutSchema = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => ''];
                // 不设置 schema
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Schema Array Not Defined.');
        $modelWithoutSchema->update(['name' => 'Updated Test'], 1);
    }

    /**
     * 测试 update 方法 - 无效的填充字段
     */
    public function testUpdateInvalidFillableField()
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
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field invalid_field,Only Allowed Fields:id,name,email');
        $model->update(['name' => 'Updated Test', 'invalid_field' => 'value'], 1);
    }

    /**
     * 测试 update 方法 - 无效的数据字段
     */
    public function testUpdateInvalidDataField()
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
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:not_an_integer');
        $model->update(['id' => 'not_an_integer', 'name' => 'Updated Test'], 1);
    }

    /**
     * 测试 update 方法 - 复杂的条件
     */
    public function testUpdateComplexCondition()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 update 方法，返回更新的记录数
        $dbMock->expects($this->once())
            ->method('update')
            ->with('test_table', ['name' => 'Updated Test'], ...[['id', 'in', [1, 2, 3]]])
            ->willReturn(3);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }
        };

        // 测试使用复杂条件更新数据
        $result = $model->update(['name' => 'Updated Test'], 'id', [1, 2, 3]);
        $this->assertEquals(3, $result);
    }

    /**
     * 测试 update 方法 - 边界值测试
     */
    public function testUpdateBoundaryValues()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 update 方法，返回更新的记录数
        $dbMock->expects($this->once())
            ->method('update')
            ->with('test_table', [
                'name' => str_repeat('a', 100), // 最大长度
                'price' => '99999.99', // 最大精度
                'stock' => 0, // 最小值
                'status' => 'active'
            ], ...[['id', '=', 1]])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = [
                    'id' => 0,
                    'name' => '',
                    'price' => '0.00',
                    'stock' => 0,
                    'status' => 'inactive'
                ];
                $this->schema = [
                    'id' => ['integer', 'min' => 1, 'max' => 1000, 'unsigned' => true],
                    'name' => ['varchar', 'length' => 100],
                    'price' => ['decimal', 'precision' => 8, 'scale' => 2],
                    'stock' => ['integer', 'min' => 0, 'unsigned' => true],
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }
        };

        // 测试更新包含边界值的数据
        $result = $model->update([
            'name' => str_repeat('a', 100), // 最大长度
            'price' => '99999.99', // 最大精度
            'stock' => 0, // 最小值
            'status' => 'active'
        ], 1);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 update 方法 - where 方法返回空数组
     */
    public function testUpdateWhereReturnsEmpty()
    {
        // 创建数据库存根对象
        $dbMock = $this->createStub(DatabaseAbstract::class);

        // 创建一个继承自 Model 的测试类，重写 where 方法返回空数组
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => ''];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar'
                ];
            }

            // 重写 where 方法，返回空数组
            protected function _bindWhere(float|int|string|array ...$wheres): array
            {
                return [];
            }
        };

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Condition Cannot Be Empty.');
        $model->update(['name' => 'Updated Test'], 1);
    }
}