<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;
use system\database\DatabaseAbstract;

class ModelInsertComplexTest extends TestCase
{
    /**
     * 测试 insert 方法 - 有效的单个数据插入
     */
    public function testInsertValidSingleData()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 insert 方法，返回插入的 ID
        $dbMock->expects($this->once())
            ->method('insert')
            ->with('test_table', ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com', 'status' => 'active'])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => '', 'status' => 'active'];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }
        };

        // 测试插入单个数据
        $result = $model->insert(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com']);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 insert 方法 - 批量数据插入
     */
    public function testInsertBatchData()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 insert 方法，返回插入的 ID
        $dbMock->expects($this->once())
            ->method('insert')
            ->with('test_table', 
                ['id' => 1, 'name' => 'Test1', 'email' => 'test1@example.com', 'status' => 'active'],
                ['id' => 2, 'name' => 'Test2', 'email' => 'test2@example.com', 'status' => 'active'],
                ['id' => 3, 'name' => 'Test3', 'email' => 'test3@example.com', 'status' => 'inactive']
            )
            ->willReturn(3);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => '', 'status' => 'active'];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }
        };

        // 测试批量插入
        $result = $model->insert(
            ['id' => 1, 'name' => 'Test1', 'email' => 'test1@example.com'],
            ['id' => 2, 'name' => 'Test2', 'email' => 'test2@example.com'],
            ['id' => 3, 'name' => 'Test3', 'email' => 'test3@example.com', 'status' => 'inactive']
        );
        $this->assertEquals(3, $result);
    }

    /**
     * 测试 insert 方法 - 带默认值的插入
     */
    public function testInsertWithDefaultValues()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 insert 方法，返回插入的 ID
        $dbMock->expects($this->once())
            ->method('insert')
            ->with('test_table', ['id' => 1, 'name' => 'Test', 'email' => '', 'status' => 'active'])
            ->willReturn(1);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->fillable = ['id' => 0, 'name' => '', 'email' => '', 'status' => 'active'];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }
        };

        // 测试只提供部分字段，其他字段使用默认值
        $result = $model->insert(['id' => 1, 'name' => 'Test']);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 insert 方法 - 空数据插入
     */
    public function testInsertEmptyData()
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
        $this->expectExceptionMessage('Insert Data Cannot Be Empty Or Not Array.');
        $model->insert([]);
    }

    /**
     * 测试 insert 方法 - 未定义 fillable 的情况
     */
    public function testInsertWithoutFillable()
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
        $modelWithoutFillable->insert(['id' => 1, 'name' => 'Test']);
    }

    /**
     * 测试 insert 方法 - 未定义 schema 的情况
     */
    public function testInsertWithoutSchema()
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
        $modelWithoutSchema->insert(['id' => 1, 'name' => 'Test']);
    }

    /**
     * 测试 insert 方法 - 无效的填充字段
     */
    public function testInsertInvalidFillableField()
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
        $model->insert(['id' => 1, 'name' => 'Test', 'invalid_field' => 'value']);
    }

    /**
     * 测试 insert 方法 - 无效的数据字段
     */
    public function testInsertInvalidDataField()
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
        $model->insert(['id' => 'not_an_integer', 'name' => 'Test', 'email' => 'test@example.com']);
    }

    /**
     * 测试 insert 方法 - 复杂的字段类型和验证规则
     */
    public function testInsertComplexFieldTypes()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 insert 方法，返回插入的 ID
        $dbMock->expects($this->once())
            ->method('insert')
            ->with('test_table', [
                'id' => 1,
                'name' => 'Test Product',
                'price' => '199.99',
                'stock' => 100,
                'status' => 'active',
                'created_at' => '2023-12-31 23:59:59'
            ])
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
                    'status' => 'inactive',
                    'created_at' => ''
                ];
                $this->schema = [
                    'id' => ['integer', 'min' => 1, 'max' => 1000, 'unsigned' => true],
                    'name' => ['varchar', 'length' => 100],
                    'price' => ['decimal', 'precision' => 10, 'scale' => 2],
                    'stock' => ['integer', 'min' => 0, 'unsigned' => true],
                    'status' => ['enum', 'options' => ['active', 'inactive']],
                    'created_at' => 'datetime'
                ];
            }
        };

        // 测试插入包含复杂字段类型的数据
        $result = $model->insert([
            'id' => 1,
            'name' => 'Test Product',
            'price' => '199.99',
            'stock' => 100,
            'status' => 'active',
            'created_at' => '2023-12-31 23:59:59'
        ]);
        $this->assertEquals(1, $result);
    }

    /**
     * 测试 insert 方法 - 边界值测试
     */
    public function testInsertBoundaryValues()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 insert 方法，返回插入的 ID
        $dbMock->expects($this->once())
            ->method('insert')
            ->with('test_table', [
                'id' => 1,
                'name' => str_repeat('a', 100), // 最大长度
                'price' => '99999.99', // 最大精度
                'stock' => 0, // 最小值
                'status' => 'active',
                'created_at' => '2023-02-29' // 闰年
            ])
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
                    'status' => 'inactive',
                    'created_at' => ''
                ];
                $this->schema = [
                    'id' => ['integer', 'min' => 1, 'max' => 1000, 'unsigned' => true],
                    'name' => ['varchar', 'length' => 100],
                    'price' => ['decimal', 'precision' => 8, 'scale' => 2],
                    'stock' => ['integer', 'min' => 0, 'unsigned' => true],
                    'status' => ['enum', 'options' => ['active', 'inactive']],
                    'created_at' => 'datetime'
                ];
            }
        };

        // 测试插入包含边界值的数据
        $result = $model->insert([
            'id' => 1,
            'name' => str_repeat('a', 100), // 最大长度
            'price' => '99999.99', // 最大精度
            'stock' => 0, // 最小值
            'status' => 'active',
            'created_at' => '2023-02-29' // 闰年
        ]);
        $this->assertEquals(1, $result);
    }
}