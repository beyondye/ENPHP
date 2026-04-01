<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;

class CommonUpsertTest extends TestCase
{
    use Common;

    protected $db;

    /**
     * 测试前的准备工作
     */
    protected function setUp(): void
    {
        // 初始化 effected 属性
        $this->effected = 0;
    }

    /**
     * 测试 upsert 方法 - 空数据
     */
    public function testUpsertEmptyData()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Data Is Empty.');

        // 调用 upsert 方法
        $this->upsert('test_table', []);
    }

    /**
     * 测试 upsert 方法 - 正常情况
     */
    public function testUpsertSuccess()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 期望调用 lastInsertId 方法并返回 1
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        // 调用 upsert 方法
        $result = $this->upsert('test_table', ['name' => 'test', 'age' => 25]);

        // 验证返回结果
        $this->assertEquals('1', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 upsert 方法 - 包含 null 值
     */
    public function testUpsertWithNullValues()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 期望调用 lastInsertId 方法并返回 1
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        // 调用 upsert 方法
        $result = $this->upsert('test_table', ['name' => 'test', 'age' => null]);

        // 验证返回结果
        $this->assertEquals('1', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 upsert 方法 - PDO 执行异常
     */
    public function testUpsertPdoException()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 期望调用 execute 方法并抛出异常
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('PDO Error'));

        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Execute Error :PDO Error');

        // 调用 upsert 方法
        $this->upsert('test_table', ['name' => 'test', 'age' => 25]);
    }

    /**
     * 测试 upsert 方法 - 不同类型的值
     */
    public function testUpsertWithDifferentValueTypes()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 4 次
        $mockStmt->expects($this->exactly(4))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 期望调用 lastInsertId 方法并返回 '123'
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('123');

        // 调用 upsert 方法，包含不同类型的值
        $result = $this->upsert('test_table', [
            'name' => 'test',
            'age' => 25,
            'active' => true,
            'salary' => 1000.50
        ]);

        // 验证返回结果
        $this->assertEquals('123', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 upsert 方法 - 单字段
     */
    public function testUpsertSingleField()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 1 次
        $mockStmt->expects($this->exactly(1))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 期望调用 lastInsertId 方法并返回 '456'
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('456');

        // 调用 upsert 方法，只有一个字段
        $result = $this->upsert('test_table', ['name' => 'test']);

        // 验证返回结果
        $this->assertEquals('456', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 upsert 方法 - 大量字段
     */
    public function testUpsertMultipleFields()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 6 次
        $mockStmt->expects($this->exactly(6))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 期望调用 lastInsertId 方法并返回 '789'
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('789');

        // 调用 upsert 方法，包含多个字段
        $result = $this->upsert('test_table', [
            'id' => 1,
            'name' => 'test',
            'age' => 25,
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St'
        ]);

        // 验证返回结果
        $this->assertEquals('789', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }
}