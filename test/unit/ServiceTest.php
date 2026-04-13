<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Config;

class ServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // 清空配置
        Config::flush();
    }

    protected function tearDown(): void
    {
        // 清空配置
        Config::flush();
    }

    /**
     * 测试服务实例化 - 正常情况
     */
    public function testServiceInstantiation()
    {
        // 使用 eval 创建测试类，添加唯一后缀
        $className = 'TestService_' . uniqid();
        eval("class $className { public function getMessage() { return \"Hello from service\"; } }");

        // 设置服务配置 - 注意：现在需要设置为服务数组格式
        $servicesConfig = [
            'test_service' => [
                'entry' => $className,
                'params' => []
            ]
        ];
        // 配置键名应该与服务名相同
        Config::set('test_service', $servicesConfig);

        // 测试服务实例化
        $service = service('test_service');
        $this->assertIsObject($service);
        $this->assertInstanceOf($className, $service);
    }

    /**
     * 测试服务实例化 - 带依赖
     */
    public function testServiceWithDependency()
    {
        // 使用 eval 创建测试类，添加唯一后缀
        $dependencyClassName = 'TestDependency_' . uniqid();
        $serviceClassName = 'TestServiceWithDependency_' . uniqid();
        eval("class $dependencyClassName { public function getMessage() { return 'Hello from dependency'; } }");
        eval("class $serviceClassName { public \$dependency; public function __construct($dependencyClassName \$dependency) { \$this->dependency = \$dependency; } public function getDependencyMessage() { return \$this->dependency->getMessage(); } }");

        // 设置服务配置
        $servicesConfig = [
            'test_service_with_dependency' => [
                'entry' => $serviceClassName,
                'params' => [
                    [
                        'type' => 'class',
                        'value' => $dependencyClassName,
                        'params' => []
                    ]
                ]
            ]
        ];
        Config::set('test_service_with_dependency', $servicesConfig);

        // 测试服务实例化
        $service = service('test_service_with_dependency');
        $this->assertIsObject($service);
        $this->assertInstanceOf($serviceClassName, $service);
        $this->assertIsObject($service->dependency);
        $this->assertInstanceOf($dependencyClassName, $service->dependency);
        $this->assertEquals('Hello from dependency', $service->getDependencyMessage());
    }

    /**
     * 测试服务实例化 - 带基本类型参数
     */
    public function testServiceWithBasicTypeParams()
    {
        // 使用 eval 创建测试类，添加唯一后缀
        $className = 'TestServiceWithParams_' . uniqid();
        eval("class $className { public \$stringParam; public \$intParam; public \$floatParam; public \$boolParam; public function __construct(\$stringParam, \$intParam, \$floatParam, \$boolParam) { \$this->stringParam = \$stringParam; \$this->intParam = \$intParam; \$this->floatParam = \$floatParam; \$this->boolParam = \$boolParam; } }");

        // 设置服务配置
        $servicesConfig = [
            'test_service_with_params' => [
                'entry' => $className,
                'params' => [
                    'string_param',
                    123,
                    123.45,
                    true
                ]
            ]
        ];
        Config::set('test_service_with_params', $servicesConfig);

        // 测试服务实例化
        $service = service('test_service_with_params');
        $this->assertIsObject($service);
        $this->assertInstanceOf($className, $service);
        $this->assertEquals('string_param', $service->stringParam);
        $this->assertEquals(123, $service->intParam);
        $this->assertEquals(123.45, $service->floatParam);
        $this->assertEquals(true, $service->boolParam);
    }

    /**
     * 测试服务实例化 - 服务不存在
     */
    public function testServiceNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service config not found:[non_existent_service]');
        service('non_existent_service');
    }

    /**
     * 测试服务实例化 - 服务配置格式错误
     */
    public function testServiceConfigFormatError()
    {
        // 设置服务配置为非数组格式
        Config::set('test_service_invalid_config', 'not_an_array');

        $this->expectException(Exception::class);
        service('test_service_invalid_config');
    }

    /**
     * 测试服务实例化 - 服务配置缺少 entry 键
     */
    public function testServiceConfigMissingEntry()
    {
        // 设置服务配置，缺少 entry 键
        $servicesConfig = [
            'test_service_missing_entry' => [
                'params' => []
            ]
        ];
        Config::set('test_service_missing_entry', $servicesConfig);

        $this->expectException(Exception::class);
        service('test_service_missing_entry');
    }

    /**
     * 测试服务实例化 - 非 class 类型参数
     */
    public function testServiceWithNonClassTypeParams()
    {
        // 使用 eval 创建测试类，添加唯一后缀
        $className = 'TestServiceWithParams_' . uniqid();
        eval("class $className { public \$stringParam; public \$intParam; public \$floatParam; public \$boolParam; public function __construct(\$stringParam, \$intParam, \$floatParam, \$boolParam) { \$this->stringParam = \$stringParam; \$this->intParam = \$intParam; \$this->floatParam = \$floatParam; \$this->boolParam = \$boolParam; } }");

        // 设置服务配置，包含非 class 类型的参数
        $servicesConfig = [
            'test_service_with_non_class_params' => [
                'entry' => $className,
                'params' => [
                    [
                        'value' => 'string_value'
                        // 注意：这里没有 'type' 键，应该直接返回 value
                    ],
                    [
                        'type' => 'string',
                        'value' => '123'
                        // 注意：这里 type 不是 'class'，应该直接返回 value
                    ],
                    123.45, // 直接传递基本类型
                    true // 直接传递基本类型
                ]
            ]
        ];
        Config::set('test_service_with_non_class_params', $servicesConfig);

        // 测试服务实例化
        $service = service('test_service_with_non_class_params');
        $this->assertIsObject($service);
        $this->assertInstanceOf($className, $service);
        $this->assertEquals('string_value', $service->stringParam);
        $this->assertEquals('123', $service->intParam);
        $this->assertEquals(123.45, $service->floatParam);
        $this->assertEquals(true, $service->boolParam);
    }
}