<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Common;
use system\database\DatabaseException;
use system\database\ResultAbstract;

class CommonTest extends TestCase
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
     * 测试 transaction 方法 - 正常情况
     */
    public function testTransactionSuccess()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 beginTransaction 方法并返回 true
        $this->db->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);

        // 调用 transaction 方法
        $result = $this->transaction();

        // 验证返回结果
        $this->assertTrue($result);
    }

    /**
     * 测试 transaction 方法 - 失败情况
     */
    public function testTransactionFailure()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 beginTransaction 方法并返回 false
        $this->db->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(false);

        // 调用 transaction 方法
        $result = $this->transaction();

        // 验证返回结果
        $this->assertFalse($result);
    }

    /**
     * 测试 commit 方法 - 正常情况
     */
    public function testCommitSuccess()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 commit 方法并返回 true
        $this->db->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        // 调用 commit 方法
        $result = $this->commit();

        // 验证返回结果
        $this->assertTrue($result);
    }

    /**
     * 测试 commit 方法 - 失败情况
     */
    public function testCommitFailure()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 commit 方法并返回 false
        $this->db->expects($this->once())
            ->method('commit')
            ->willReturn(false);

        // 调用 commit 方法
        $result = $this->commit();

        // 验证返回结果
        $this->assertFalse($result);
    }

    /**
     * 测试 rollback 方法 - 正常情况
     */
    public function testRollbackSuccess()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 rollBack 方法并返回 true
        $this->db->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);

        // 调用 rollback 方法
        $result = $this->rollback();

        // 验证返回结果
        $this->assertTrue($result);
    }

    /**
     * 测试 rollback 方法 - 失败情况
     */
    public function testRollbackFailure()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 rollBack 方法并返回 false
        $this->db->expects($this->once())
            ->method('rollBack')
            ->willReturn(false);

        // 调用 rollback 方法
        $result = $this->rollback();

        // 验证返回结果
        $this->assertFalse($result);
    }

    /**
     * 测试 close 方法
     */
    public function testClose()
    {
        // 创建 PDO 存根对象
        $this->db = $this->createStub(PDO::class);
        // 调用 close 方法
        $result = $this->close();

        // 验证返回结果
        $this->assertTrue($result);
        // 验证 db 属性被设置为 null
        $this->assertNull($this->db);
    }

    /**
     * 测试 lastid 方法
     */
    public function testLastid()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 期望调用 lastInsertId 方法并返回 1
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        // 调用 lastid 方法
        $result = $this->lastid();

        // 验证返回结果
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 effected 方法 - 初始值
     */
    public function testEffectedInitial()
    {
        // 验证初始值
        $this->assertEquals(0, $this->effected);
        // 调用 effected 方法
        $result = $this->effected();
        // 验证返回结果
        $this->assertEquals(0, $result);
    }

    /**
     * 测试 effected 方法 - 非初始值
     */
    public function testEffectedNonInitial()
    {
        // 设置 effected 属性
        $this->effected = 5;
        // 调用 effected 方法
        $result = $this->effected();
        // 验证返回结果
        $this->assertEquals(5, $result);
    }

    /**
     * 测试 insert 方法 - 空数据
     */
    public function testInsertEmptyData()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Is Empty.');

        // 调用 insert 方法
        $this->insert('test_table', []);
    }

    /**
     * 测试 insert 方法 - 正常情况
     */
    public function testInsertSuccess()
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

        // 调用 insert 方法
        $result = $this->insert('test_table', ['name' => 'test', 'age' => 25]);

        // 验证返回结果
        $this->assertEquals('1', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
    }

    /**
     * 测试 insert 方法 - 批量插入
     */
    public function testInsertBatch()
    {
        // 创建 PDO 模拟对象
        $this->db = $this->createMock(PDO::class);
        // 创建 PDOStatement 模拟对象
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 prepare 方法
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // 期望调用 bindValue 方法 4 次（2条记录，每条2个字段）
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

        // 期望调用 lastInsertId 方法并返回 1
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        // 调用 insert 方法
        $result = $this->insert('test_table', ['name' => 'test1', 'age' => 25], ['name' => 'test2', 'age' => 30]);

        // 验证返回结果
        $this->assertEquals('1', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(2, $this->effected);
    }

    /**
     * 测试 insert 方法 - 包含 null 值
     */
    public function testInsertWithNullValues()
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

        // 调用 insert 方法
        $result = $this->insert('test_table', ['name' => 'test', 'age' => null]);

        // 验证返回结果
        $this->assertEquals('1', $result);
        // 验证 effected 属性被更新
        $this->assertEquals(1, $this->effected);
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
        $result = $this->select('test_table', []);

        // 验证返回结果是 ResultAbstract 实例
        $this->assertInstanceOf(ResultAbstract::class, $result);
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
}
