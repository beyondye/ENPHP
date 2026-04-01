<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;
use system\database\pdo\Build;
use system\database\Util;

class CommonUpdateTest extends TestCase
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
     * 测试 update 方法 - 空数据
     */
    public function testUpdateEmptyData()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Data Is Empty.');

        // 调用 update 方法
        $this->update('test_table', [], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空条件
     */
    public function testUpdateEmptyWhere()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Where Condition Is Empty.');

        // 调用 update 方法
        $this->update('test_table', ['name' => 'test']);
    }

    /**
     * 测试 update 方法 - 正常情况
     */
    public function testUpdateSuccess()
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

        // 调用 update 方法
        $result = $this->update('test_table', ['name' => 'test'], ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 update 方法 - PDO 执行异常
     */
    public function testUpdatePdoException()
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
        $this->expectExceptionMessage('Update Execute Error :PDO Error');

        // 调用 update 方法
        $this->update('test_table', ['name' => 'test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 不同类型的值
     */
    public function testUpdateWithDifferentValueTypes()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 5 次（4个数据字段 + 1个条件字段）
        $mockStmt->expects($this->exactly(5))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 2
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(2);

        // 调用 update 方法，包含不同类型的值
        $result = $this->update('test_table', [
            'name' => 'test',
            'age' => 25,
            'active' => true,
            'salary' => 1000.50
        ], ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(2, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(2, $this->effected);
    }

    /**
     * 测试 update 方法 - 单字段
     */
    public function testUpdateSingleField()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 2 次（1个数据字段 + 1个条件字段）
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

        // 调用 update 方法，只有一个字段
        $result = $this->update('test_table', ['name' => 'test'], ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 update 方法 - 多个字段
     */
    public function testUpdateMultipleFields()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 6 次（5个数据字段 + 1个条件字段）
        $mockStmt->expects($this->exactly(6))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 3
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(3);

        // 调用 update 方法，包含多个字段
        $result = $this->update('test_table', [
            'name' => 'test',
            'age' => 25,
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St'
        ], ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(3, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(3, $this->effected);
    }

    /**
     * 测试 update 方法 - 复杂 WHERE 条件
     */
    public function testUpdateWithComplexWhere()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 4 次（2个数据字段 + 2个条件字段）
        $mockStmt->expects($this->exactly(4))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 2
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(2);

        // 调用 update 方法，使用复杂的 WHERE 条件
        $result = $this->update('test_table', 
            ['name' => 'test', 'age' => 25], 
            ['id', '=', 1], 
            ['status', '!=', 'inactive']
        );

        // 验证返回结果
        $this->assertEquals(2, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(2, $this->effected);
    }

    /**
     * 测试 update 方法 - 包含 null 值
     */
    public function testUpdateWithNullValues()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 2 次（1个数据字段 + 1个条件字段）
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

        // 调用 update 方法，包含 null 值
        $result = $this->update('test_table', ['name' => null], ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }
}