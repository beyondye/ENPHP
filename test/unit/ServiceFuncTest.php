<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;
use system\Config;
use system\SysException;

class ServiceFuncTest extends TestCase
{
    /**
     * 测试开始前初始化配置
     */
    protected function setUp(): void
    {
       
            // 直接设置测试所需的配置数据，按照 service() 函数的实际实现结构
            Config::set('test', [
                'entry' => 'app\service\Test',
                'params' => [
                    'test' => [
                        'type' => 'class',
                        'value' => 'app\model\Test',
                        'params' => [
                            'db' => ['value' => 'default']
                        ],
                    ],
                ]
            ]);
        
    }

    /**
     * 测试正常情况下获取服务实例
     */
    public function testServiceNormalCase()
    {
        // 确保服务配置存在
        $config = Config::get('test');
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('entry', $config);
        
        // 调用 service 函数获取服务实例
        $service = service('test');
        
        // 验证返回的是对象
        $this->assertIsObject($service);
        // 验证返回的是正确的类实例
        $this->assertInstanceOf('app\service\Test', $service);
    }
    
    /**
     * 测试服务配置不存在的情况
     */
    public function testServiceConfigNotFound()
    {
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config not found:[non_existent_service]');
        
        // 尝试获取不存在的服务
        service('non_existent_service');
    }
    
    /**
     * 测试服务配置不是数组的情况
     */
    public function testServiceConfigNotArray()
    {
        // 临时修改配置为非数组
        Config::set('test', 'not an array');
        
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config must be an array:[test]');
        
        // 尝试获取服务
        service('test');
        
        // 恢复配置
        Config::set('test', [
            'entry' => 'service\Test',
            'params' => []
        ]);
    }
    
    /**
     * 测试服务配置缺少 entry 键的情况
     */
    public function testServiceMissingEntry()
    {
        // 临时修改配置，移除 entry 键
        Config::set('test', []);
        
        $this->expectException(SysException::class);
        $this->expectExceptionMessage('Service config missing entry:[test]');
        
        // 尝试获取服务
        service('test');
    }
    
    
    /**
     * 测试服务参数的递归构建功能
     */
    public function testServiceParamsRecursiveBuild()
    {
        // 确保服务配置存在且包含参数
        $config = Config::get('test');
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('params', $config);
        
        // 调用 service 函数获取服务实例
        $service = service('test');
        
        // 验证返回的是对象
        $this->assertIsObject($service);
        // 验证返回的是正确的类实例
        $this->assertInstanceOf('app\service\Test', $service);
    }
    
}