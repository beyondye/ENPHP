<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelOrderByTest extends TestCase
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
                    'created_at' => 'datetime'
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
     * 测试 orderBy 方法 - 传递空数组
     */
    public function testOrderByWithEmptyArray()
    {
        // 测试传递空数组
        $result = $this->model->orderBy([]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 orders 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([], $conditions['orders']);
    }

    /**
     * 测试 orderBy 方法 - 传递非空数组
     */
    public function testOrderByWithNonEmptyArray()
    {
        // 测试传递非空数组
        $orders = ['created_at' => 'desc'];
        $result = $this->model->orderBy($orders);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 orders 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($orders, $conditions['orders']);
    }

    /**
     * 测试 orderBy 方法 - 链式调用
     */
    public function testOrderByChainedCalls()
    {
        // 测试链式调用
        $orders = ['created_at' => 'desc'];
        $result = $this->model->orderBy($orders)->where('id', 1);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 orders 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($orders, $conditions['orders']);
        $this->assertArrayHasKey('wheres', $conditions);
    }

    /**
     * 测试 orderBy 方法 - 多次调用
     */
    public function testOrderByMultipleCalls()
    {
        // 第一次调用 orderBy 方法
        $orders1 = ['created_at' => 'desc'];
        $this->model->orderBy($orders1);
        $conditions1 = $this->model->getConditions();
        $this->assertEquals($orders1, $conditions1['orders']);
        
        // 第二次调用 orderBy 方法，覆盖之前的排序
        $orders2 = ['name' => 'asc'];
        $this->model->orderBy($orders2);
        $conditions2 = $this->model->getConditions();
        $this->assertEquals($orders2, $conditions2['orders']);
    }

    /**
     * 测试 orderBy 方法 - 多个字段排序
     */
    public function testOrderByMultipleFields()
    {
        // 测试多个字段排序
        $orders = ['category_id' => 'asc', 'created_at' => 'desc'];
        $result = $this->model->orderBy($orders);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 orders 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($orders, $conditions['orders']);
    }

    /**
     * 测试 orderBy 方法 - 不同排序方向
     */
    public function testOrderByDifferentDirections()
    {
        // 测试不同排序方向
        $orders = [
            'created_at' => 'desc',
            'name' => 'asc'
        ];
        $result = $this->model->orderBy($orders);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 orders 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($orders, $conditions['orders']);
    }
}