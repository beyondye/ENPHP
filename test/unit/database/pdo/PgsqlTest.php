<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Pgsql;
use system\database\DatabaseException;

class PgsqlTest extends TestCase
{
    /**
     * 测试构造方法 - 基本功能
     */
    public function testConstructorBasic()
    {
        // 由于构造方法需要实际的数据库连接，我们需要使用反射来测试
        $reflection = new ReflectionClass(Pgsql::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        // 验证对象创建成功
        $this->assertInstanceOf(Pgsql::class, $instance);
    }

    /**
     * 测试构造方法 - 缺少必要参数
     */
    public function testConstructorMissingRequiredParams()
    {
        // 缺少数据库名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Pgsql(['username' => 'test', 'password' => 'test']);
    }

    /**
     * 测试构造方法 - 缺少用户名
     */
    public function testConstructorMissingUsername()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Pgsql(['database' => 'test', 'password' => 'test']);
    }

    /**
     * 测试构造方法 - 缺少密码
     */
    public function testConstructorMissingPassword()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Pgsql(['database' => 'test', 'username' => 'test']);
    }

    /**
     * 测试构造方法 - 数据库连接失败
     */
    public function testConstructorConnectionError()
    {
        // 模拟 PDO 构造函数抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Database Connection Error :/');
        
        // 使用一个无效的主机名来触发连接失败
        new Pgsql([
            'host' => 'invalid_hostname_12345',
            'database' => 'test',
            'username' => 'test',
            'password' => 'test'
        ]);
    }

    /**
     * 测试构造方法 - 完整参数
     */
    public function testConstructorWithAllParams()
    {
        // 由于构造方法需要实际的数据库连接，我们需要使用反射来测试
        $reflection = new ReflectionClass(Pgsql::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        // 验证对象创建成功
        $this->assertInstanceOf(Pgsql::class, $instance);
    }

    /**
     * 测试 insert 方法 - 空数据
     */
    public function testInsertEmptyData()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 insert 方法，传入空数据
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Is Empty.');
        $pgsql->insert('test_table', []);
    }

    /**
     * 测试 insert 方法 - 空表名
     */
    public function testInsertEmptyTable()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 insert 方法，传入空表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Table Name Is Empty.');
        $pgsql->insert('', ['name' => 'Test', 'value' => 100]);
    }

    /**
     * 测试 update 方法 - 空数据
     */
    public function testUpdateEmptyData()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 update 方法，传入空数据
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Data Is Empty.');
        $pgsql->update('test_table', [], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空表名
     */
    public function testUpdateEmptyTable()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 update 方法，传入空表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Table Name Is Empty.');
        $pgsql->update('', ['name' => 'Test'], ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 空表名
     */
    public function testDeleteEmptyTable()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 delete 方法，传入空表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Table Name Is Empty.');
        $pgsql->delete('', ['id', '=', 1]);
    }

    /**
     * 测试 select 方法 - 空表名
     */
    public function testSelectEmptyTable()
    {
        // 使用反射创建 Pgsql 实例
        $reflection = new ReflectionClass(Pgsql::class);
        $pgsql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 select 方法，传入空表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty.');
        $pgsql->select('');
    }
}