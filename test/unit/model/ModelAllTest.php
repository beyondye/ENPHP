<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelAllTest extends TestCase
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

        // 创建数据库模拟对象
        $this->dbMock = $this->createStub(DatabaseAbstract::class);
        $this->dbMock->method('select')
            ->willReturn($this->resultMock);

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
                    'age' => 'integer',
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
     * 测试 all() 方法 - 基本用法（默认 limit）
     */
    public function testAllWithDefaultLimit()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（默认 limit）
        $result = $this->model->all();

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
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 3, 'name' => 'Test3']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（自定义 limit）
        $result = $this->model->all(50);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 带 where 条件
     */
    public function testAllWithWhereCondition()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（带 where 条件）
        $result = $this->model->where('age', '>', 18)->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 带 select 字段
     */
    public function testAllWithSelectFields()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（带 select 字段）
        $result = $this->model->select(['id', 'name'])->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 带 order by
     */
    public function testAllWithOrderBy()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 1, 'name' => 'Test1']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（带 order by）
        $result = $this->model->orderBy(['id' => 'desc'])->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 带 group by
     */
    public function testAllWithGroupBy()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['name' => 'Test', 'count' => 2]
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（带 group by）
        $result = $this->model->select(['name', 'count(*) as count'])->groupBy(['name'])->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 带 having
     */
    public function testAllWithHaving()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['name' => 'Test', 'count' => 5]
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（带 having）
        $result = $this->model
            ->select(['name', 'count(*) as count'])
            ->groupBy(['name'])
            ->having(['count', '>', 3])
            ->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 链式调用
     */
    public function testAllWithChainedCalls()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 1, 'name' => 'Test1']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（链式调用）
        $result = $this->model
            ->select(['id', 'name'])
            ->where('age', '>', 18)
            ->orderBy(['created_at' => 'desc'])
            ->all(10);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 多次调用
     */
    public function testAllMultipleCalls()
    {
        // 模拟返回值
        $expectedResult1 = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $expectedResult2 = [
            (object) ['id' => 3, 'name' => 'Test3']
        ];
        
        $this->resultMock->method('all')
            ->willReturnOnConsecutiveCalls($expectedResult1, $expectedResult2);

        // 第一次调用
        $result1 = $this->model->where('age', '>', 18)->all();
        $this->assertEquals($expectedResult1, $result1);
        $this->assertEquals([], $this->model->getConditions());

        // 第二次调用
        $result2 = $this->model->where('age', '<', 18)->all(5);
        $this->assertEquals($expectedResult2, $result2);
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 边界测试（空条件）
     */
    public function testAllWithEmptyConditions()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 3, 'name' => 'Test3']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（无任何条件）
        $result = $this->model->all();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 all() 方法 - 边界测试（limit 为 0）
     */
    public function testAllWithLimitZero()
    {
        // 模拟返回值
        $expectedResult = [];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 all() 方法（limit 为 0）
        $result = $this->model->all(0);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }
}