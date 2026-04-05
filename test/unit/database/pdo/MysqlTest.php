<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Mysql;
use system\database\DatabaseException;

class MysqlTest extends TestCase
{
    /**
     * 测试构造方法 - 基本功能
     */
    public function testConstructorBasic()
    {
        // 由于构造方法需要实际的数据库连接，我们需要使用反射来测试
        $reflection = new ReflectionClass(Mysql::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        // 验证对象创建成功
        $this->assertInstanceOf(Mysql::class, $instance);
    }

    /**
     * 测试构造方法 - 缺少必要参数
     */
    public function testConstructorMissingRequiredParams()
    {
        // 缺少数据库名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Mysql(['username' => 'test', 'password' => 'test']);
    }

    /**
     * 测试构造方法 - 缺少用户名
     */
    public function testConstructorMissingUsername()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Mysql(['database' => 'test', 'password' => 'test']);
    }

    /**
     * 测试构造方法 - 缺少密码
     */
    public function testConstructorMissingPassword()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        new Mysql(['database' => 'test', 'username' => 'test']);
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
        new Mysql([
            'host' => 'invalid_hostname_12345',
            'database' => 'test',
            'username' => 'test',
            'password' => 'test'
        ]);
    }

    /**
     * 测试 upsert 方法 - 空数据
     */
    public function testUpsertEmptyData()
    {
        // 使用反射创建 Mysql 实例
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 upsert 方法，传入空数据
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Data Is Empty.');
        $mysql->upsert('test_table', []);
    }

    /**
     * 测试 upsert 方法 - 空表名
     */
    public function testUpsertEmptyTable()
    {
        // 使用反射创建 Mysql 实例
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 upsert 方法，传入空表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Table Name Is Required.');
        $mysql->upsert('', ['name' => 'Test', 'value' => 100]);
    }

    /**
     * 测试 upsert 方法 - 只包含空白字符的表名
     */
    public function testUpsertWhitespaceTable()
    {
        // 使用反射创建 Mysql 实例
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        // 测试 upsert 方法，传入只包含空白字符的表名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Table Name Is Required.');
        $mysql->upsert('   ', ['name' => 'Test', 'value' => 100]);
    }

    /**
     * 测试 upsert 方法 - 基本功能
     */
    public function testUpsertBasic()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法
        $result = $mysql->upsert('test_table', ['name' => 'Test', 'value' => 100]);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 执行错误
     */
    public function testUpsertExecuteError()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willThrowException(new PDOException('Test error'));
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Execute Error :Test error');
        $mysql->upsert('test_table', ['name' => 'Test', 'value' => 100]);
    }

    /**
     * 测试 upsert 方法 - 特殊字符
     */
    public function testUpsertWithSpecialCharacters()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用包含特殊字符的数据
        $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
        $result = $mysql->upsert('test_table', ['name' => $specialName, 'value' => 100]);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - SQL 注入尝试
     */
    public function testUpsertSqlInjectionAttempt()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用 SQL 注入尝试
        $sqlInjectionAttempt = "Test'; DROP TABLE test_table; --";
        $result = $mysql->upsert('test_table', ['name' => $sqlInjectionAttempt, 'value' => 100]);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 不同类型的数据
     */
    public function testUpsertWithDifferentDataTypes()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(4))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用不同类型的数据
        $data = [
            'name' => 'Test Product',
            'value' => 100,
            'price' => 99.99,
            'active' => 1
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 空字符串参数
     */
    public function testUpsertWithEmptyStringParams()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用空字符串参数
        $result = $mysql->upsert('test_table', ['name' => '', 'value' => 100]);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 复杂参数样例 1：大量字段
     */
    public function testUpsertWithManyFields()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(10))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用大量字段
        $data = [
            'field1' => 'value1',
            'field2' => 123,
            'field3' => 123.45,
            'field4' => true,
            'field5' => 'value5',
            'field6' => 678,
            'field7' => 678.90,
            'field8' => false,
            'field9' => 'value9',
            'field10' => 987
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 复杂参数样例 2：带有特殊字符的字段名
     */
    public function testUpsertWithSpecialFieldNames()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(3))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用带有下划线的字段名
        $data = [
            'user_name' => 'test_user',
            'user_age' => 25,
            'user_email' => 'test@example.com'
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 复杂参数样例 3：带有 null 值的数据
     */
    public function testUpsertWithNullValues()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(3))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用带有 null 值的数据
        $data = [
            'name' => 'Test',
            'value' => null,
            'active' => 1
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 复杂参数样例 4：带有非常长的字符串
     */
    public function testUpsertWithLongString()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用非常长的字符串
        $longString = str_repeat('x', 1000);
        $data = [
            'name' => 'Test',
            'long_text' => $longString
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }

    /**
     * 测试 upsert 方法 - 复杂参数样例 5：混合数据类型
     */
    public function testUpsertWithMixedDataTypes()
    {
        // 创建一个模拟的 PDO 对象
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->willReturn($mockStmt);
        
        $mockPdo->expects($this->once())
                ->method('lastInsertId')
                ->willReturn('1');
        
        $mockStmt->expects($this->exactly(5))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1);
        
        // 使用反射创建 Mysql 实例并设置 db 属性
        $reflection = new ReflectionClass(Mysql::class);
        $mysql = $reflection->newInstanceWithoutConstructor();
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($mysql, $mockPdo);
        
        // 测试 upsert 方法，使用混合数据类型
        $data = [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 99.99,
            'is_active' => true,
            'description' => 'This is a test product'
        ];
        $result = $mysql->upsert('test_table', $data);
        
        // 验证结果
        $this->assertIsScalar($result);
        $this->assertEquals('1', $result);
    }
}