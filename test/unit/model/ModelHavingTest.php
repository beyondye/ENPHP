<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelHavingTest extends TestCase
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
                    'category_id' => 'integer',
                    'count' => 'integer'
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
     * 测试 having 方法 - 传递空数组
     */
    public function testHavingWithEmptyArray()
    {
        // 测试传递空数组
        $result = $this->model->having([]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 havings 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([], $conditions['havings']);
    }

    /**
     * 测试 having 方法 - 传递非空数组
     */
    public function testHavingWithNonEmptyArray()
    {
        // 测试传递非空数组
        $havings = [['count', '>', 5]];
        $result = $this->model->having($havings);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 havings 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($havings, $conditions['havings']);
    }

    /**
     * 测试 having 方法 - 链式调用
     */
    public function testHavingChainedCalls()
    {
        // 测试链式调用
        $havings = [['count', '>', 5]];
        $result = $this->model->having($havings)->groupBy(['category_id']);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 havings 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($havings, $conditions['havings']);
        $this->assertArrayHasKey('groups', $conditions);
    }

    /**
     * 测试 having 方法 - 多次调用
     */
    public function testHavingMultipleCalls()
    {
        // 第一次调用 having 方法
        $havings1 = [['count', '>', 5]];
        $this->model->having($havings1);
        $conditions1 = $this->model->getConditions();
        $this->assertEquals($havings1, $conditions1['havings']);
        
        // 第二次调用 having 方法，覆盖之前的条件
        $havings2 = [['count', '<', 10]];
        $this->model->having($havings2);
        $conditions2 = $this->model->getConditions();
        $this->assertEquals($havings2, $conditions2['havings']);
    }


    /**
     * 测试 having 方法 - 复杂条件
     */
    public function testHavingComplexCondition()
    {
        // 测试复杂条件
        $havings = [['count', 'in', [5, 6, 7, 8, 9]]];
        $result = $this->model->having($havings);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 havings 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($havings, $conditions['havings']);
    }
}