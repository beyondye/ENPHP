<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonRollbackTest extends TestCase
{
    /**
     * 测试 rollback 方法 - 基本功能
     */
    public function testRollbackBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 开始事务
        $sqlite->transaction();

        // 执行 rollback 方法
        $result = $sqlite->rollback();

        // 验证返回值
        $this->assertTrue($result);
    }

    /**
     * 测试 rollback 方法 - 在事务中回滚
     */
    public function testRollbackInTransaction()
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
        $sqlite->transaction();

        // 插入数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 回滚事务
        $result = $sqlite->rollback();
        $this->assertTrue($result);

        // 验证数据未被插入
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(0, $rows);
    }

    /**
     * 测试 rollback 方法 - 多次回滚
     */
    public function testRollbackMultipleTimes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 开始事务
        $sqlite->transaction();

        // 第一次回滚
        $result1 = $sqlite->rollback();
        $this->assertTrue($result1);

        // 第二次回滚（在没有活动事务时会抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to roll back.');
        $sqlite->rollback();
    }

    /**
     * 测试 rollback 方法 - 关闭连接后回滚
     */
    public function testRollbackAfterClose()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 关闭连接
        $sqlite->close();

        // 尝试回滚，应该抛出异常
        $this->expectException(Error::class);
        $sqlite->rollback();
    }
}