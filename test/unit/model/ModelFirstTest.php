<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelFirstTest extends TestCase
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
     * 测试 first() 方法 - 基本用法
     */
    public function testFirstBasicUsage()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法
        $result = $this->model->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 无结果时返回 null
     */
    public function testFirstReturnsNullWhenNoResults()
    {
        // 模拟返回 null
        $this->resultMock->method('first')->willReturn(null);

        // 调用 first() 方法
        $result = $this->model->first();

        // 验证结果
        $this->assertNull($result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 带 where 条件
     */
    public function testFirstWithWhereCondition()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（带 where 条件）
        $result = $this->model->where('id', '=', 1)->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 带 select 字段
     */
    public function testFirstWithSelectFields()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（带 select 字段）
        $result = $this->model->select(['id', 'name'])->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 带 order by
     */
    public function testFirstWithOrderBy()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（带 order by）
        $result = $this->model->orderBy(['id' => 'desc'])->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 带 group by
     */
    public function testFirstWithGroupBy()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（带 group by）
        $result = $this->model->groupBy(['name'])->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 带 having
     */
    public function testFirstWithHaving()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（带 having）
        $result = $this->model->having(['age', '>', 18])->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 链式调用
     */
    public function testFirstWithChainedCalls()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（链式调用）
        $result = $this->model
            ->select(['id', 'name'])
            ->where('age', '>', 18)
            ->orderBy(['created_at' => 'desc'])
            ->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 绑定单个主键值
     */
    public function testFirstWithBoundPrimaryKey()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（绑定单个主键值）
        $result = $this->model->where(1)->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 多次调用
     */
    public function testFirstMultipleCalls()
    {
        // 模拟返回值
        $expectedResult1 = (object) ['id' => 1, 'name' => 'Test1'];
        $expectedResult2 = (object) ['id' => 2, 'name' => 'Test2'];
        
        $this->resultMock->method('first')
            ->willReturnOnConsecutiveCalls($expectedResult1, $expectedResult2);

        // 第一次调用
        $result1 = $this->model->where('id', 1)->first();
        $this->assertEquals($expectedResult1, $result1);
        $this->assertEquals([], $this->model->getConditions());

        // 第二次调用
        $result2 = $this->model->where('id', 2)->first();
        $this->assertEquals($expectedResult2, $result2);
        $this->assertEquals([], $this->model->getConditions());
    }

   
    /**
     * 测试 first() 方法 - 边界测试（空条件）
     */
    public function testFirstWithEmptyConditions()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（无任何条件）
        $result = $this->model->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 边界测试（大量条件）
     */
    public function testFirstWithMultipleConditions()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 first() 方法（多个条件）
        $result = $this->model
            ->where('age', '>', 18)
            ->where('name', 'like', '%Test%')
            ->where('email', '!=', 'test@example.com')
            ->orderBy(['created_at' => 'desc'])
            ->groupBy(['name'])
            ->first();

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        // 验证条件被重置
        $this->assertEquals([], $this->model->getConditions());
    }

    /**
     * 测试 first() 方法 - 验证异常捕获
     */
    public function testFirstWithValidationException()
    {
        // 测试向整数字段传递字符串值时应该抛出异常
        $this->expectException(system\model\ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:123abc');

        // 尝试向整数字段传递字符串值
        $this->model->where('id', '=', '123abc')->first();
    }
}