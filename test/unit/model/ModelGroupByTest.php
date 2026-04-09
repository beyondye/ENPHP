<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelGroupByTest extends TestCase
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
                    'category_id' => 'integer'
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
     * 测试 groupBy 方法 - 传递空数组
     */
    public function testGroupByWithEmptyArray()
    {
        // 测试传递空数组
        $result = $this->model->groupBy([]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 groups 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([], $conditions['groups']);
    }

    /**
     * 测试 groupBy 方法 - 传递非空数组
     */
    public function testGroupByWithNonEmptyArray()
    {
        // 测试传递非空数组
        $groups = ['category_id', 'name'];
        $result = $this->model->groupBy($groups);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 groups 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($groups, $conditions['groups']);
    }

    /**
     * 测试 groupBy 方法 - 链式调用
     */
    public function testGroupByChainedCalls()
    {
        // 测试链式调用
        $groups = ['category_id'];
        $result = $this->model->groupBy($groups)->where('id', 1);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 groups 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($groups, $conditions['groups']);
        $this->assertArrayHasKey('wheres', $conditions);
    }

    /**
     * 测试 groupBy 方法 - 多次调用
     */
    public function testGroupByMultipleCalls()
    {
        // 第一次调用 groupBy 方法
        $groups1 = ['category_id'];
        $this->model->groupBy($groups1);
        $conditions1 = $this->model->getConditions();
        $this->assertEquals($groups1, $conditions1['groups']);
        
        // 第二次调用 groupBy 方法，覆盖之前的分组
        $groups2 = ['name'];
        $this->model->groupBy($groups2);
        $conditions2 = $this->model->getConditions();
        $this->assertEquals($groups2, $conditions2['groups']);
    }

    /**
     * 测试 groupBy 方法 - 单个字段分组
     */
    public function testGroupBySingleField()
    {
        // 测试单个字段分组
        $groups = ['category_id'];
        $result = $this->model->groupBy($groups);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 groups 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($groups, $conditions['groups']);
    }

    /**
     * 测试 groupBy 方法 - 多个字段分组
     */
    public function testGroupByMultipleFields()
    {
        // 测试多个字段分组
        $groups = ['category_id', 'name'];
        $result = $this->model->groupBy($groups);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 groups 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($groups, $conditions['groups']);
    }
}