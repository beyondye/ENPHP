<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\DatabaseAbstract;

class DatabaseTest extends TestCase
{
    /**
     * 测试默认服务的单例模式
     */
    public function testDefaultServiceSingleton()
    {
        // 第一次调用，应该创建新实例
        $db1 = Database::instance();
        // 第二次调用，应该返回相同实例
        $db2 = Database::instance();
        
        $this->assertSame($db1, $db2, "Database::instance() 应该返回相同实例");
    }

    /**
     * 测试指定服务的单例模式
     */
    public function testNamedServiceSingleton()
    {
        // 测试 'test' 服务
        $db1 = Database::instance('test');
        $db2 = Database::instance('test');
        
        $this->assertSame($db1, $db2, "Database::instance('test') 应该返回相同实例");
    }

    /**
     * 测试不同服务返回不同实例
     */
    public function testDifferentServices()
    {
        $dbDefault = Database::instance('default');
        $dbTest = Database::instance('test');
        
        $this->assertNotSame($dbDefault, $dbTest, "不同服务应该返回不同实例");
    }

    /**
     * 测试不存在的服务抛出异常
     */
    public function testNonExistentService()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(" 'non_existent' Config Not Exist,Please Check Database Config File In 'development' Directory.");
        
        Database::instance('non_existent');
    }

    public function testDefaultService()
    {
        $db = Database::instance();
        $this->assertInstanceOf(DatabaseAbstract::class, $db);
    }

    /**
     * 测试指定服务的实例
     */
    public function testNamedService()
    {
        $db = Database::instance('test');
        $this->assertInstanceOf(DatabaseAbstract::class, $db);
    }


    public function testUnsupportedDriver()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("'unsupported' Driver Not Support.");
        
        Database::instance('unsupported');
    }

    /**
     * 测试 SQLite 驱动
     */
    public function testSqliteDriver()
    {
        // 假设配置文件中存在一个使用 SQLite 驱动的服务
        $db = Database::instance('sqlite');
        $this->assertInstanceOf(DatabaseAbstract::class, $db);
    }
   
}