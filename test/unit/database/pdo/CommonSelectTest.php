<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;
use system\database\ResultAbstract;

class CommonSelectTest extends TestCase
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
     * 测试 select 方法 - 空表名
     */
    public function testSelectEmptyTable()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty');

        // 调用 select 方法
        $this->select('');
    }

    /**
     * 测试 select 方法 - 只包含空格的表名
     */
    public function testSelectWhitespaceTable()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty');

        // 调用 select 方法
        $this->select('   ');
    }

    /**
     * 测试 select 方法 - 正常情况
     */
    public function testSelectSuccess()
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

        // 调用 select 方法
        $result = $this->select(
            'test_table',
            ['id', 'name'],
            [['id', '=', 1], ['name', 'like', '%test%']],
            ['age'],
            [['count', '>', 5], ['sum', '<', 100]],
            ['id' => 'desc', 'name' => 'asc'],
            [10, 20]
        );

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 最小参数
     */
    public function testSelectMinimal()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table');

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定字段
     */
    public function testSelectOnlyFields()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', ['id', 'name', 'age']);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定 WHERE 条件
     */
    public function testSelectOnlyWhere()
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

        // 调用 select 方法
        $result = $this->select('test_table', [], [['id', '=', 1]]);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定 GROUP BY
     */
    public function testSelectOnlyGroupBy()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', [], [], ['age']);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定 HAVING
     */
    public function testSelectOnlyHaving()
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

        // 调用 select 方法
        $result = $this->select('test_table', [], [], [], [['count', '>', 5]]);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定 ORDER BY
     */
    public function testSelectOnlyOrderBy()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', [], [], [], [], ['id' => 'desc', 'name' => 'asc']);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 只指定 LIMIT
     */
    public function testSelectOnlyLimit()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', [], [], [], [], [], [10, 20]);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - PDO 执行异常
     */
    public function testSelectPdoException()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法并抛出异常
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('PDO Error'));

        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Execute Error :PDO Error');

        // 调用 select 方法
        $this->select('test_table', [], [['id', '=', 1]]);
    }

    /**
     * 测试 select 方法 - 复杂 WHERE 条件
     */
    public function testSelectComplexWhere()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 3 次
        $mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', [], [
            ['id', '=', 1],
            ['name', 'like', '%test%'],
            ['age', '>', 20]
        ]);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 复杂 HAVING 条件
     */
    public function testSelectComplexHaving()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 2 次
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法
        $result = $this->select('test_table', [], [], ['age'], [
            ['count', '>', 5],
            ['avg', '>', 25]
        ]);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 select 方法 - 整数类型的 LIMIT
     */
    public function testSelectWithIntegerLimit()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 select 方法，使用整数类型的 LIMIT
        $result = $this->select('test_table', [], [], [], [], [], 10);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }
}