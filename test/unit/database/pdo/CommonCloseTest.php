<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonCloseTest extends TestCase
{
    /**
     * 测试 close 方法 - 基本功能
     */
    public function testCloseBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 执行 close 方法
        $result = $sqlite->close();

        // 验证返回值
        $this->assertTrue($result);
    }

    /**
     * 测试 close 方法 - 多次调用
     */
    public function testCloseMultipleTimes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 第一次调用
        $result1 = $sqlite->close();
        $this->assertTrue($result1);

        // 第二次调用
        $result2 = $sqlite->close();
        $this->assertTrue($result2);

        // 第三次调用
        $result3 = $sqlite->close();
        $this->assertTrue($result3);
    }

    /**
     * 测试 close 方法 - 关闭后尝试执行操作
     */
    public function testCloseAndTryToExecute()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 关闭连接
        $sqlite->close();

        // 尝试执行操作，应该抛出异常
        $this->expectException(Error::class);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
    }
}