<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class DatabaseTest extends TestCase
{
    /**
     * 测试 Database 类的方法存在性
     */
    public function testDatabaseMethodsExist()
    {
        $reflection = new ReflectionClass(Database::class);
        
        // 验证 instance 方法存在
        $this->assertTrue($reflection->hasMethod('instance'));
        
        // 验证 instance 方法是静态的
        $method = $reflection->getMethod('instance');
        $this->assertTrue($method->isStatic());
        
        // 验证方法参数
        $parameters = $method->getParameters();
        $this->assertEquals(1, count($parameters));
        $this->assertEquals('service', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        
        // 验证返回类型
        $returnType = $method->getReturnType();
        $this->assertEquals('system\\database\\DatabaseAbstract', (string) $returnType);
    }

    /**
     * 测试 instance 方法 - 服务名称参数边界测试
     */
    public function testInstanceServiceNameBoundaries()
    {
        // 测试空字符串服务名称
        $this->expectException(DatabaseException::class);
        Database::instance('');
    }

    /**
     * 测试 instance 方法 - 特殊字符服务名称
     */
    public function testInstanceServiceNameSpecialCharacters()
    {
        // 测试包含特殊字符的服务名称
        $this->expectException(DatabaseException::class);
        Database::instance('service@#$%');
    }

    /**
     * 测试 instance 方法 - 长服务名称
     */
    public function testInstanceServiceNameLong()
    {
        // 测试长服务名称
        $longServiceName = str_repeat('a', 1000);
        $this->expectException(DatabaseException::class);
        Database::instance($longServiceName);
    }

    /**
     * 测试 Database 类的异常处理
     */
    public function testDatabaseExceptionHandling()
    {
        // 测试无效的服务名称
        $this->expectException(DatabaseException::class);
        Database::instance('non_existent_service');
    }

    /**
     * 测试 Database 类的安全性
     */
    public function testDatabaseSecurity()
    {
        // 测试 SQL 注入尝试
        $sqlInjectionAttempt = "'; DROP TABLE users; --";
        $this->expectException(DatabaseException::class);
        Database::instance($sqlInjectionAttempt);
    }
}