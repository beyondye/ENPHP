<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;

class CommonDeleteTest extends TestCase
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
     * 测试 delete 方法 - 空条件
     */
    public function testDeleteEmptyWhere()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Where Condition Is Empty.');

        // 调用 delete 方法
        $this->delete('test_table');
    }

    /**
     * 测试 delete 方法 - 正常情况
     */
    public function testDeleteSuccess()
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
        $mockStmt->expects($this->once())
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 delete 方法
        $result = $this->delete('test_table', ['id', '=', 1]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 delete 方法 - PDO 执行异常
     */
    public function testDeletePdoException()
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
        $mockStmt->expects($this->once())
            ->method('bindValue');

        // 期望调用 execute 方法并抛出异常
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('PDO Error'));

        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Execute Error :PDO Error');

        // 调用 delete 方法
        $this->delete('test_table', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 复杂 WHERE 条件
     */
    public function testDeleteWithComplexWhere()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 2 次（2个条件字段）
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 2
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(2);

        // 调用 delete 方法，使用复杂的 WHERE 条件
        $result = $this->delete('test_table', 
            ['id', '=', 1], 
            ['status', '!=', 'inactive']
        );

        // 验证返回结果
        $this->assertEquals(2, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(2, $this->effected);
    }

    /**
     * 测试 delete 方法 - 不同类型的 WHERE 条件值
     */
    public function testDeleteWithDifferentValueTypes()
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
        $mockStmt->expects($this->once())
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 delete 方法，使用不同类型的 WHERE 条件值
        $result = $this->delete('test_table', ['active', '=', true]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 delete 方法 - 字符串类型的 WHERE 条件值
     */
    public function testDeleteWithStringWhereValue()
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
        $mockStmt->expects($this->once())
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 3
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(3);

        // 调用 delete 方法，使用字符串类型的 WHERE 条件值
        $result = $this->delete('test_table', ['name', 'like', '%test%']);

        // 验证返回结果
        $this->assertEquals(3, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(3, $this->effected);
    }

    /**
     * 测试 delete 方法 - 浮点数类型的 WHERE 条件值
     */
    public function testDeleteWithFloatWhereValue()
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
        $mockStmt->expects($this->once())
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 delete 方法，使用浮点数类型的 WHERE 条件值
        $result = $this->delete('test_table', ['salary', '>', 5000.50]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }
}