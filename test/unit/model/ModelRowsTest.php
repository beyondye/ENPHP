<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelRowsTest extends TestCase
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
     * 测试 rows() 方法 - 基本用法（默认参数）
     */
    public function testRowsWithDefaultParameters()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 3, 'name' => 'Test3']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（默认参数）
        $result = $this->model->rows();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 自定义 limit
     */
    public function testRowsWithCustomLimit()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（自定义 limit）
        $result = $this->model->rows(2);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 自定义 limit 和 offset
     */
    public function testRowsWithCustomLimitAndOffset()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 3, 'name' => 'Test3'],
            (object) ['id' => 4, 'name' => 'Test4']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（自定义 limit 和 offset）
        $result = $this->model->rows(2, 2);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 带 where 条件
     */
    public function testRowsWithWhereCondition()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（带 where 条件）
        $result = $this->model->where('age', '>', 18)->rows(10);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 带 select 字段
     */
    public function testRowsWithSelectFields()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（带 select 字段）
        $result = $this->model->select(['id', 'name'])->rows(5);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 带 order by
     */
    public function testRowsWithOrderBy()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 3, 'name' => 'Test3'],
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 1, 'name' => 'Test1']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（带 order by）
        $result = $this->model->orderBy(['id' => 'desc'])->rows(3);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 带 group by
     */
    public function testRowsWithGroupBy()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['age' => 20, 'count' => 2],
            (object) ['age' => 25, 'count' => 3]
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（带 group by）
        $result = $this->model->select(['age', 'count(*) as count'])->groupBy(['age'])->rows(5);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 带 having
     */
    public function testRowsWithHaving()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['age' => 25, 'count' => 3]
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（带 having）
        $result = $this->model
            ->select(['age', 'count(*) as count'])
            ->groupBy(['age'])
            ->having(['age', '>', 20])
            ->rows(5);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 链式调用
     */
    public function testRowsWithChainedCalls()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 2, 'name' => 'Test2'],
            (object) ['id' => 1, 'name' => 'Test1']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（链式调用）
        $result = $this->model
            ->select(['id', 'name'])
            ->where('age', '>', 18)
            ->orderBy(['created_at' => 'desc'])
            ->rows(2, 0);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 多次调用
     */
    public function testRowsMultipleCalls()
    {
        // 模拟返回值
        $expectedResult1 = [
            (object) ['id' => 1, 'name' => 'Test1'],
            (object) ['id' => 2, 'name' => 'Test2']
        ];
        $expectedResult2 = [
            (object) ['id' => 3, 'name' => 'Test3'],
            (object) ['id' => 4, 'name' => 'Test4']
        ];
        
        $this->resultMock->method('all')
            ->willReturnOnConsecutiveCalls($expectedResult1, $expectedResult2);

        // 第一次调用
        $result1 = $this->model->where('age', '>', 18)->rows(2);
        $this->assertEquals($expectedResult1, $result1);
        $this->assertEquals([], $this->model->getConditions());

        // 第二次调用
        $result2 = $this->model->where('age', '<', 18)->rows(2, 0);
        $this->assertEquals($expectedResult2, $result2);
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 边界测试（limit 为 0）
     */
    public function testRowsWithLimitZero()
    {
        // 模拟返回值
        $expectedResult = [];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（limit 为 0）
        $result = $this->model->rows(0);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 rows() 方法 - 边界测试（offset 大于 0）
     */
    public function testRowsWithOffsetGreaterThanZero()
    {
        // 模拟返回值
        $expectedResult = [
            (object) ['id' => 3, 'name' => 'Test3'],
            (object) ['id' => 4, 'name' => 'Test4']
        ];
        $this->resultMock->method('all')->willReturn($expectedResult);

        // 调用 rows() 方法（offset 大于 0）
        $result = $this->model->rows(2, 2);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }
}