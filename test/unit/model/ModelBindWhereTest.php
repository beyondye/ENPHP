<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelBindWhereTest extends TestCase
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

        // 创建一个继承自 Model 的测试类，暴露 _bindWhere 方法
        $this->model = new class($this->dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar'
                ];
            }

            // 暴露 protected 的 _bindWhere 方法
        public function bindWhere(float|int|string|array ...$wheres): array
        {
            return $this->_bindWhere(...$wheres);
        }

            // 暴露 protected 的 conditions 属性
            public function getConditions()
            {
                return $this->conditions;
            }
        };
    }

    /**
     * 测试 _bindWhere 方法 - 单个数字值（绑定到主键）
     */
    public function testBindWhereWithSingleNumericValue()
    {
        // 调用 bindWhere 方法（单个数字值）
        $result = $this->model->bindWhere(1);

        // 验证结果
        $expected = [['id', '=', 1]];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 单个字符串值（绑定到主键）
     */
    public function testBindWhereWithSingleStringValue()
    {
        // 调用 bindWhere 方法（单个字符串值）
        $result = $this->model->bindWhere('1');

        // 验证结果
        $expected = [['id', '=', '1']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 单个浮点数（绑定到非主键字段）
     */
    public function testBindWhereWithSingleFloatValue()
    {
        // 修改模型，添加一个浮点数字段
        $testModel = new class($this->dbMock) extends Model {
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

            // 暴露 protected 的 _bindWhere 方法
        public function bindWhere(float|int|string|array ...$wheres): array
        {
            return $this->_bindWhere(...$wheres);
        }
        };

        // 测试多个参数形式的浮点数
        $result = $testModel->bindWhere('price', '=', 1.5);

        // 验证结果
        $expected = [['price', '=', 1.5]];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 多个参数（不绑定到主键）
     */
    public function testBindWhereWithMultipleParameters()
    {
        // 调用 bindWhere 方法（多个参数）
        $result = $this->model->bindWhere('name', '=', 'Test');

        // 验证结果
        $expected = [['name', '=', 'Test']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 数组参数（不绑定到主键）
     */
    public function testBindWhereWithArrayParameter()
    {
        // 调用 bindWhere 方法（数组参数）
        $result = $this->model->bindWhere(['name', '=', 'Test']);

        // 验证结果
        $expected = [['name', '=', 'Test']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 多个数组参数
     */
    public function testBindWhereWithMultipleArrayParameters()
    {
        // 调用 bindWhere 方法（多个数组参数）
        $result = $this->model->bindWhere(['id', '>', 1], ['name', 'like', '%Test%']);

        // 验证结果
        $expected = [['id', '>', 1, 'and'], ['name', 'like', '%Test%']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 带逻辑运算符的条件
     */
    public function testBindWhereWithLogicalOperator()
    {
        // 调用 bindWhere 方法（带逻辑运算符）
        $result = $this->model->bindWhere(['id', '>', 1, 'and'], ['name', '=', 'Test']);

        // 验证结果
        $expected = [['id', '>', 1, 'and'], ['name', '=', 'Test']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 带 in 条件
     */
    public function testBindWhereWithInCondition()
    {
        // 调用 bindWhere 方法（带 in 条件）
        $result = $this->model->bindWhere('id', 'in', [1, 2, 3]);

        // 验证结果
        $expected = [['id', 'in', [1, 2, 3]]];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 带 between 条件
     */
    public function testBindWhereWithBetweenCondition()
    {
        // 调用 bindWhere 方法（带 between 条件）
        $result = $this->model->bindWhere('id', 'between', [1, 10]);

        // 验证结果
        $expected = [['id', 'between', [1, 10]]];
        $this->assertEquals($expected, $result);
    }



    /**
     * 测试 _bindWhere 方法 - 第一个参数是字符串但有多个参数（不绑定到主键）
     */
    public function testBindWhereWithStringFirstParamAndMultipleParams()
    {
        // 调用 bindWhere 方法（第一个参数是字符串，有多个参数）
        $result = $this->model->bindWhere('name', '=', 'Test');

        // 验证结果 - 不应该绑定到主键，应该保持原样
        $expected = [['name', '=', 'Test']];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 数字作为字段值（不是字段名）
     */
    public function testBindWhereWithNumericValue()
    {
        // 调用 bindWhere 方法（数字作为字段值）
        $result = $this->model->bindWhere('id', '=', 123);

        // 验证结果
        $expected = [['id', '=', 123]];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 参数为1（绑定到主键）
     */
    public function testBindWhereWithParameterOne()
    {
        // 调用 bindWhere 方法（参数为1）
        $result = $this->model->bindWhere(1);

        // 验证结果 - 应该绑定到主键
        $expected = [['id', '=', 1]];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 _bindWhere 方法 - 确保第255行代码被覆盖
     */
    public function testBindWhereWithSingleValueBindsToPrimaryKey()
    {
        // 测试单个数字值（整数主键）
        $result1 = $this->model->bindWhere(42);
        $this->assertEquals([['id', '=', 42]], $result1);
        
        // 测试单个字符串值（字符串主键）
        $stringPrimaryModel = new class($this->dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'uuid';
                $this->schema = [
                    'uuid' => 'varchar'
                ];
            }

            // 暴露 protected 的 _bindWhere 方法
        public function bindWhere(float|int|string|array ...$wheres): array
        {
            return $this->_bindWhere(...$wheres);
        }
        };
        
        $result2 = $stringPrimaryModel->bindWhere('test-uuid');
        $this->assertEquals([['uuid', '=', 'test-uuid']], $result2);
        
        // 测试单个浮点数值（decimal主键）
        $decimalPrimaryModel = new class($this->dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'price';
                $this->schema = [
                    'price' => 'decimal'
                ];
            }

            // 暴露 protected 的 _bindWhere 方法
            public function bindWhere(float|int|string|array ...$wheres): array
            {
                return $this->_bindWhere(...$wheres);
            }
        };
        
        $result3 = $decimalPrimaryModel->bindWhere(3.14);
        $this->assertEquals([['price', '=', 3.14]], $result3);
    }
}