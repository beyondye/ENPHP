<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Config;
use system\SysException;

class ServiceFuncTest extends TestCase
{
    /**
     * 测试 service 函数 - 正常情况：服务配置是闭包
     */
    public function testServiceWithClosure()
    {
        // 设置测试配置
        Config::set('test_closure', function () {
            return new class {
                public $name = 'test_service';
            };
        });
        
        // 调用 service 函数
        $service = service('test_closure');
        
        // 验证返回的是对象
        $this->assertIsObject($service);
        $this->assertEquals('test_service', $service->name);
    }

    /**
     * 测试 service 函数 - 正常情况：服务配置是类名字符串
     */
    public function testServiceWithClassName()
    {
        // 设置测试配置
        Config::set('test_class', stdClass::class);
        
        // 调用 service 函数
        $service = service('test_class');
        
        // 验证返回的是对象
        $this->assertIsObject($service);
        $this->assertInstanceOf(stdClass::class, $service);
    }

    /**
     * 测试 service 函数 - 服务配置不存在
     */
    public function testServiceNotFound()
    {
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config not found:[non_existent_service]');
        
        // 尝试获取不存在的服务
        service('non_existent_service');
    }

    /**
     * 测试 service 函数 - 服务配置不是可调用的
     */
    public function testServiceNotCallable()
    {
        // 设置不可调用的配置
        Config::set('test_not_callable', 'invalid_service');
        
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config must be callable:[test_not_callable]');
        
        // 尝试获取服务
        service('test_not_callable');
    }

    /**
     * 测试 service 函数 - 服务配置是数组但不可调用
     */
    public function testServiceArrayNotCallable()
    {
        // 设置数组配置（但不是有效的回调数组）
        Config::set('test_array', ['key' => 'value']);
        
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config must be callable:[test_array]');
        
        // 尝试获取服务
        service('test_array');
    }
}