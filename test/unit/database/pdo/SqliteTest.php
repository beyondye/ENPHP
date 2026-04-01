<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class SqliteTest extends TestCase
{
    /**
     * 测试 Sqlite 构造函数 - 正常情况
     */
    public function testConstructorSuccess()
    {
        // 使用内存数据库进行测试
        $config = [
            'database' => ':memory:'
        ];

        // 验证构造函数不会抛出异常
        $this->assertInstanceOf(Sqlite::class, new Sqlite($config));
    }

    /**
     * 测试 Sqlite 构造函数 - 空数据库名称
     */
    public function testConstructorEmptyDatabase()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLite Database Name Is Required.');

        // 使用空数据库名称
        $config = [
            'database' => ''
        ];

        new Sqlite($config);
    }

    /**
     * 测试 Sqlite 构造函数 - 完整配置
     */
    public function testConstructorWithFullConfig()
    {
        // 使用完整配置
        $config = [
            'persistent' => true,
            'username' => 'test',
            'password' => 'test123',
            'database' => ':memory:',
            'host' => 'localhost'
        ];

        // 验证构造函数不会抛出异常
        $this->assertInstanceOf(Sqlite::class, new Sqlite($config));
    }

    /**
     * 测试 Sqlite 构造函数 - 默认配置
     */
    public function testConstructorWithDefaultConfig()
    {
        // 不提供配置，使用默认值
        // 验证构造函数不会抛出异常
        $this->assertInstanceOf(Sqlite::class, new Sqlite());
    }

    /**
     * 测试 Sqlite 构造函数 - PDO 连接失败
     */
    public function testConstructorPdoConnectionError()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLite Database Connection Error');

        // 使用无效的数据库路径，应该导致 PDO 连接失败
        $config = [
            'database' => '/path/to/nonexistent/database.db'
        ];

        new Sqlite($config);
    }
}