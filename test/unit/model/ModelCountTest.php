<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelCountTest extends TestCase
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
     * 测试 count() 方法 - 基本用法（无条件）
     */
    public function testCountWithNoConditions()
    {
        // 模拟返回值
        $expectedResult = 5;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法
        $result = $this->model->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 无结果时返回 0
     */
    public function testCountReturnsZeroWhenNoResults()
    {
        // 模拟返回 null
        $this->resultMock->method('first')
            ->willReturn(null);

        // 调用 count() 方法
        $result = $this->model->count();

        // 验证结果
        $this->assertEquals(0, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 带 where 条件
     */
    public function testCountWithWhereCondition()
    {
        // 模拟返回值
        $expectedResult = 3;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（带 where 条件）
        $result = $this->model->where('age', '>', 18)->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 带 group by
     */
    public function testCountWithGroupBy()
    {
        // 模拟返回值
        $expectedResult = 2;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（带 group by）
        $result = $this->model->groupBy(['age'])->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 带 having
     */
    public function testCountWithHaving()
    {
        // 模拟返回值
        $expectedResult = 1;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（带 having）
        $result = $this->model->groupBy(['age'])->having(['age', '>', 25])->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 带 order by
     */
    public function testCountWithOrderBy()
    {
        // 模拟返回值
        $expectedResult = 5;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（带 order by）
        $result = $this->model->orderBy(['created_at' => 'desc'])->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 链式调用
     */
    public function testCountWithChainedCalls()
    {
        // 模拟返回值
        $expectedResult = 2;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（链式调用）
        $result = $this->model
            ->where('age', '>', 18)
            ->where('name', 'like', '%Test%')
            ->orderBy(['created_at' => 'desc'])
            ->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 多次调用
     */
    public function testCountMultipleCalls()
    {
        // 模拟返回值
        $expectedResult1 = 5;
        $expectedResult2 = 3;
        $expectedResult3 = 0;
        
        $this->resultMock->method('first')
            ->willReturnOnConsecutiveCalls(
                ['total' => $expectedResult1],
                ['total' => $expectedResult2],
                null
            );

        // 第一次调用
        $result1 = $this->model->count();
        $this->assertEquals($expectedResult1, $result1);
        $this->assertEquals([], $this->model->getConditions());

        // 第二次调用
        $result2 = $this->model->where('age', '>', 18)->count();
        $this->assertEquals($expectedResult2, $result2);
        $this->assertEquals([], $this->model->getConditions());

        // 第三次调用
        $result3 = $this->model->where('id', 999)->count();
        $this->assertEquals($expectedResult3, $result3);
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 count() 方法 - 边界测试（空条件）
     */
    public function testCountWithEmptyConditions()
    {
        // 模拟返回值
        $expectedResult = 10;
        $this->resultMock->method('first')
            ->willReturn(['total' => $expectedResult]);

        // 调用 count() 方法（无任何条件）
        $result = $this->model->count();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }
}