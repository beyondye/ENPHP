<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;

class ModelSelectTest extends TestCase
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
     * 测试 select 方法 - 不传递参数
     */
    public function testSelectWithoutParams()
    {
        // 测试不传递参数
        $result = $this->model->select([]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 fields 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([], $conditions['fields']);
    }

    /**
     * 测试 select 方法 - 传递空数组
     */
    public function testSelectWithEmptyArray()
    {
        // 测试传递空数组
        $result = $this->model->select([]);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 fields 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals([], $conditions['fields']);
    }

    /**
     * 测试 select 方法 - 传递非空数组
     */
    public function testSelectWithNonEmptyArray()
    {
        // 测试传递非空数组
        $fields = ['id', 'name', 'email'];
        $result = $this->model->select($fields);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 fields 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($fields, $conditions['fields']);
    }

    /**
     * 测试 select 方法 - 链式调用
     */
    public function testSelectChainedCalls()
    {
        // 测试链式调用
        $fields = ['id', 'name'];
        $result = $this->model->select($fields)->where('id', 1);
        
        // 验证结果是 Model 对象（支持链式调用）
        $this->assertInstanceOf(Model::class, $result);
        
        // 验证 conditions 中的 fields 是否正确设置
        $conditions = $this->model->getConditions();
        $this->assertEquals($fields, $conditions['fields']);
        $this->assertArrayHasKey('wheres', $conditions);
    }

    /**
     * 测试 select 方法 - 多次调用
     */
    public function testSelectMultipleCalls()
    {
        // 第一次调用 select 方法
        $fields1 = ['id', 'name'];
        $this->model->select($fields1);
        $conditions1 = $this->model->getConditions();
        $this->assertEquals($fields1, $conditions1['fields']);
        
        // 第二次调用 select 方法，覆盖之前的字段
        $fields2 = ['id', 'email'];
        $this->model->select($fields2);
        $conditions2 = $this->model->getConditions();
        $this->assertEquals($fields2, $conditions2['fields']);
    }
}