<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelFindTest extends TestCase
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
     * 测试 find() 方法 - 整数 ID
     */
    public function testFindWithIntegerId()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => 1, 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 find() 方法
        $result = $this->model->find(1);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 find() 方法 - 字符串 ID
     */
    public function testFindWithStringId()
    {
        // 模拟返回值
        $expectedResult = (object) ['id' => '1', 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 find() 方法
        $result = $this->model->find('1');

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * 测试 find() 方法 - 无结果时返回 null
     */
    public function testFindReturnsNullWhenNoResults()
    {
        // 模拟返回 null
        $this->resultMock->method('first')->willReturn(null);

        // 调用 find() 方法
        $result = $this->model->find(999);

        // 验证结果
        $this->assertNull($result);
    }

    /**
     * 测试 find() 方法 - 多次调用
     */
    public function testFindMultipleCalls()
    {
        // 模拟返回值
        $expectedResult1 = (object) ['id' => 1, 'name' => 'Test1'];
        $expectedResult2 = (object) ['id' => 2, 'name' => 'Test2'];
        
        $this->resultMock->method('first')
            ->willReturnOnConsecutiveCalls($expectedResult1, $expectedResult2);

        // 第一次调用
        $result1 = $this->model->find(1);
        $this->assertEquals($expectedResult1, $result1);

        // 第二次调用
        $result2 = $this->model->find(2);
        $this->assertEquals($expectedResult2, $result2);
    }

    /**
     * 测试 find() 方法 - 字符串主键
     */
    public function testFindWithStringPrimaryKey()
    {
        // 创建一个带有字符串主键的测试模型
        $stringPrimaryModel = new class($this->dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'uuid';
                $this->schema = [
                    'uuid' => 'varchar',
                    'name' => 'varchar'
                ];
            }
        };

        // 模拟返回值
        $expectedResult = (object) ['uuid' => 'test-uuid', 'name' => 'Test'];
        $this->resultMock->method('first')->willReturn($expectedResult);

        // 调用 find() 方法
        $result = $stringPrimaryModel->find('test-uuid');

        // 验证结果
        $this->assertEquals($expectedResult, $result);
    }
}