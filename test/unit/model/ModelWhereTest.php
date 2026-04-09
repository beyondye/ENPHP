<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelWhereTest extends TestCase
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
                    'email' => 'varchar'
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
     * 测试 where 方法 - 单个数字参数
     */
    public function testWhereSingleNumericParam()
    {
        // 测试单个数字参数
        $result = $this->model->where(1);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', '=', 1]], $conditions['wheres']);
    }

    /**
     * 测试 where 方法 - 单个字符串参数
     */
    public function testWhereSingleStringParam()
    {
        // 测试单个字符串参数
        $result = $this->model->where('1');
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', '=', '1']], $conditions['wheres']);
    }

    /**
     * 测试 where 方法 - 两个参数（字段名和值）
     */
    public function testWhereTwoParams()
    {
        // 测试两个参数（字段名和值）
        $result = $this->model->where('name', 'Test');
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['name', '=', 'Test']], $conditions['wheres']);
    }

    /**
     * 测试 where 方法 - 三个参数（字段名、操作符和值）
     */
    public function testWhereThreeParams()
    {
        // 测试三个参数（字段名、操作符和值）
        $result = $this->model->where('id', '>', 5);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', '>', 5]], $conditions['wheres']);
    }

    /**
     * 测试 where 方法 - 数组参数
     */
    public function testWhereArrayParam()
    {
        // 测试数组参数
        $result = $this->model->where(['id', '=', 1]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', '=', 1]], $conditions['wheres']);
    }

   

    /**
     * 测试 where 方法 - 多次调用
     */
    public function testWhereMultipleCalls()
    {
        // 第一次调用 where 方法
        $this->model->where('id', 1);
        $conditions1 = $this->model->getConditions();
        $this->assertEquals([['id', '=', 1]], $conditions1['wheres']);
        
        // 第二次调用 where 方法，覆盖之前的条件
        $this->model->where('name', 'Test');
        $conditions2 = $this->model->getConditions();
        $this->assertEquals([['name', '=', 'Test']], $conditions2['wheres']);
    }

    /**
     * 测试 where 方法 - 复杂条件
     */
    public function testWhereComplexCondition()
    {
        // 测试复杂条件（in 操作符）
        $result = $this->model->where('id', 'in', [1, 2, 3]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', 'in', [1, 2, 3]]], $conditions['wheres']);
    }

    /**
     * 测试 where 方法 - between 操作符
     */
    public function testWhereWithBetween()
    {
        // 测试 between 操作符
        $result = $this->model->where('id', 'between', [1, 10]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 wheres 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertArrayHasKey('wheres', $conditions);
        $this->assertEquals([['id', 'between', [1, 10]]], $conditions['wheres']);
    }
}