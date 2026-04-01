<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;
use system\database\ResultAbstract;

class CommonExecuteTest extends TestCase
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
     * 测试 execute 方法 - SELECT 语句
     */
    public function testExecuteSelect()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM test')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 execute 方法
        $result = $this->execute('SELECT * FROM test');

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 execute 方法 - INSERT 语句
     */
    public function testExecuteInsert()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test (name) VALUES (:name)')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':name', 'test');

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 execute 方法
        $result = $this->execute('INSERT INTO test (name) VALUES (:name)', ['name' => 'test']);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
        // 验证 effected() 方法返回正确值
        $this->assertEquals(1, $this->effected());
    }

    /**
     * 测试 execute 方法 - UPDATE 语句
     */
    public function testExecuteUpdate()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('UPDATE test SET name = :name WHERE id = :id')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
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

        // 调用 execute 方法
        $result = $this->execute('UPDATE test SET name = :name WHERE id = :id', ['name' => 'test', 'id' => 1]);

        // 验证返回结果
        $this->assertEquals(2, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(2, $this->effected);
    }

    /**
     * 测试 execute 方法 - DELETE 语句
     */
    public function testExecuteDelete()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM test WHERE id = :id')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':id', 1);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 execute 方法
        $result = $this->execute('DELETE FROM test WHERE id = :id', ['id' => 1]);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 execute 方法 - 无参数
     */
    public function testExecuteNoParams()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test (name) VALUES (test)')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 execute 方法
        $result = $this->execute('INSERT INTO test (name) VALUES (test)');

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 execute 方法 - 空参数数组
     */
    public function testExecuteEmptyParams()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test (name) VALUES (test)')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 期望调用 rowCount 方法并返回 1
        $mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 调用 execute 方法
        $result = $this->execute('INSERT INTO test (name) VALUES (test)', []);

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 execute 方法 - PDO 执行异常
     */
    public function testExecutePdoException()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test (name) VALUES (:name)')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法
        $mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':name', 'test');

        // 期望调用 execute 方法并抛出异常
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('PDO Error'));

        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Statement Execute Error :PDO Error');

        // 调用 execute 方法
        $this->execute('INSERT INTO test (name) VALUES (:name)', ['name' => 'test']);
    }

    /**
     * 测试 execute 方法 - 不同类型的参数值
     */
    public function testExecuteWithDifferentValueTypes()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test (name, age, active, salary) VALUES (:name, :age, :active, :salary)')
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

        // 调用 execute 方法，包含不同类型的参数值
        $result = $this->execute(
            'INSERT INTO test (name, age, active, salary) VALUES (:name, :age, :active, :salary)', 
            [
                'name' => 'test',
                'age' => 25,
                'active' => true,
                'salary' => 1000.50
            ]
        );

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 execute 方法 - 包含 null 值的参数
     */
    public function testExecuteWithNullValue()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('UPDATE test SET name = :name WHERE id = :id')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 2 次
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

        // 调用 execute 方法，包含 null 值的参数
        $result = $this->execute(
            'UPDATE test SET name = :name WHERE id = :id', 
            ['name' => null, 'id' => 1]
        );

        // 验证返回结果
        $this->assertEquals(1, $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 execute 方法 - 带空格的 SELECT 语句
     */
    public function testExecuteSelectWithSpaces()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('   SELECT * FROM test   ')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 execute 方法
        $result = $this->execute('   SELECT * FROM test   ');

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }

    /**
     * 测试 execute 方法 - 小写的 select 语句
     */
    public function testExecuteSelectLowercase()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('select * from test')
            ->willReturn($mockStmt);

        // 期望调用 execute 方法
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // 调用 execute 方法
        $result = $this->execute('select * from test');

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
        // 验证 effected 属性未被修改
        $this->assertEquals(0, $this->effected);
    }
}