<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonTransactionTest extends TestCase
{
    /**
     * 测试 transaction 方法 - 基本功能
     */
    public function testTransactionBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 执行 transaction 方法
        $result = $sqlite->transaction();

        // 验证返回值
        $this->assertTrue($result);
    }

    /**
     * 测试 transaction 方法 - 多次调用
     */
    public function testTransactionMultipleTimes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 第一次调用
        $result1 = $sqlite->transaction();
        $this->assertTrue($result1);

        // 第二次调用（在已有活动事务时会抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Transaction Is Already In Progress.');
        $sqlite->transaction();
    }

    /**
     * 测试 transaction 方法 - 关闭连接后调用
     */
    public function testTransactionAfterClose()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 关闭连接
        $sqlite->close();

        // 尝试调用 transaction，应该抛出异常
        $this->expectException(Error::class);
        $sqlite->transaction();
    }

    /**
     * 测试 transaction 方法 - 与 commit 配合使用
     */
    public function testTransactionWithCommit()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 开始事务
        $result = $sqlite->transaction();
        $this->assertTrue($result);

        // 插入数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 提交事务
        $sqlite->commit();

        // 验证数据已被插入
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 transaction 方法 - 与 rollback 配合使用
     */
    public function testTransactionWithRollback()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 开始事务
        $result = $sqlite->transaction();
        $this->assertTrue($result);

        // 插入数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 回滚事务
        $sqlite->rollback();

        // 验证数据未被插入
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(0, $rows);
    }
}