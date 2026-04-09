<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\database\DatabaseAbstract;
use system\database\ResultAbstract;

class ModelExistsTest extends TestCase
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
     * 测试 exists() 方法 - 整数 ID 存在
     */
    public function testExistsWithIntegerIdExists()
    {
        // 模拟返回值（存在）
        $this->resultMock->method('first')->willReturn((object) ['id' => 1, 'name' => 'Test']);

        // 调用 exists() 方法
        $result = $this->model->exists(1);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试 exists() 方法 - 整数 ID 不存在
     */
    public function testExistsWithIntegerIdNotExists()
    {
        // 模拟返回值（不存在）
        $this->resultMock->method('first')->willReturn(null);

        // 调用 exists() 方法
        $result = $this->model->exists(999);

        // 验证结果
        $this->assertFalse($result);
    }

    /**
     * 测试 exists() 方法 - 字符串 ID 存在
     */
    public function testExistsWithStringIdExists()
    {
        // 模拟返回值（存在）
        $this->resultMock->method('first')->willReturn((object) ['id' => '1', 'name' => 'Test']);

        // 调用 exists() 方法
        $result = $this->model->exists('1');

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试 exists() 方法 - 字符串 ID 不存在
     */
    public function testExistsWithStringIdNotExists()
    {
        // 模拟返回值（不存在）
        $this->resultMock->method('first')->willReturn(null);

        // 调用 exists() 方法
        $result = $this->model->exists('999');

        // 验证结果
        $this->assertFalse($result);
    }

    /**
     * 测试 exists() 方法 - 多次调用
     */
    public function testExistsMultipleCalls()
    {
        // 模拟返回值
        $this->resultMock->method('first')
            ->willReturnOnConsecutiveCalls(
                (object) ['id' => 1, 'name' => 'Test1'], // 存在
                null, // 不存在
                (object) ['id' => 3, 'name' => 'Test3'] // 存在
            );

        // 第一次调用
        $result1 = $this->model->exists(1);
        $this->assertTrue($result1);

        // 第二次调用
        $result2 = $this->model->exists(2);
        $this->assertFalse($result2);

        // 第三次调用
        $result3 = $this->model->exists(3);
        $this->assertTrue($result3);
    }

    /**
     * 测试 exists() 方法 - 字符串主键
     */
    public function testExistsWithStringPrimaryKey()
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

        // 测试存在和不存在的情况
        $this->resultMock->method('first')
            ->willReturnOnConsecutiveCalls(
                (object) ['uuid' => 'test-uuid', 'name' => 'Test'], // 存在
                null // 不存在
            );
        
        // 测试存在的情况
        $result1 = $stringPrimaryModel->exists('test-uuid');
        $this->assertTrue($result1);

        // 测试不存在的情况
        $result2 = $stringPrimaryModel->exists('non-existent-uuid');
        $this->assertFalse($result2);
    }
}