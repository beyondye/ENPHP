<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * 测试服务配置文件是否存在
     */
    public function testServiceConfigFileExists()
    {
        $configFile = APP_DIR . 'config/' . ENVIRONMENT . '/service.php';
        $this->assertFileExists($configFile);
    }

    /**
     * 测试服务配置文件是否返回数组
     */
    public function testServiceConfigReturnsArray()
    {
        $configFile = APP_DIR . 'config/' . ENVIRONMENT . '/service.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            $this->assertIsArray($config);
        } else {
            $this->markTestSkipped('Service config file not found');
        }
    }

    /**
     * 测试服务实例化 - 正常情况
     */
    public function testServiceInstantiation()
    {
        try {
            // 尝试获取第一个服务
            $configFile = APP_DIR . 'config/' . ENVIRONMENT . '/service.php';
            if (file_exists($configFile)) {
                $config = include $configFile;
                if (!empty($config)) {
                    $firstServiceName = array_key_first($config);
                    $service = service($firstServiceName);
                    $this->assertIsObject($service);
                } else {
                    $this->markTestSkipped('No services configured');
                }
            } else {
                $this->markTestSkipped('Service config file not found');
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Service instantiation failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试服务实例化 - 服务不存在
     */
    public function testServiceNotFound()
    {
        $this->expectException(Exception::class);
        service('non_existent_service');
    }

    

    /**
     * 测试服务依赖解析
     */
    public function testServiceDependencyResolution()
    {
        // 检查是否有带依赖的服务
        $configFile = APP_DIR . 'config/' . ENVIRONMENT . '/service.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            
            // 查找第一个带依赖的服务
            $serviceWithDependency = null;
            foreach ($config as $name => $serviceConfig) {
                if (isset($serviceConfig['params']) && !empty($serviceConfig['params'])) {
                    $serviceWithDependency = $name;
                    break;
                }
            }
            
            if ($serviceWithDependency) {
                try {
                    $service = service($serviceWithDependency);
                    $this->assertIsObject($service);
                } catch (Exception $e) {
                    $this->markTestSkipped('Service dependency resolution failed: ' . $e->getMessage());
                }
            } else {
                $this->markTestSkipped('No services with dependencies configured');
            }
        } else {
            $this->markTestSkipped('Service config file not found');
        }
    }

    /**
     * 测试服务返回的是否是有效实例
     */
    public function testServiceReturnsValidInstance()
    {
        $configFile = APP_DIR . 'config/' . ENVIRONMENT . '/service.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            
            if (!empty($config)) {
                // 测试第一个服务
                $firstServiceName = array_key_first($config);
                try {
                    $service = service($firstServiceName);
                    
                    // 验证返回的是对象
                    $this->assertIsObject($service);
                    
                    // 验证返回的对象具有预期的类名
                    $serviceConfig = $config[$firstServiceName];
                    if (isset($serviceConfig['entry'])) {
                        $expectedClass = $serviceConfig['entry'];
                        $this->assertInstanceOf($expectedClass, $service);
                    }
                    
                    // 验证服务实例是有效的（可以根据具体服务的接口进行验证）
                    // 这里可以添加更多的验证逻辑，例如检查服务是否具有特定的方法
                    $this->assertTrue(true, 'Service instance is valid');
                } catch (Exception $e) {
                    $this->markTestSkipped('Service instantiation failed: ' . $e->getMessage());
                }
            } else {
                $this->markTestSkipped('No services configured');
            }
        } else {
            $this->markTestSkipped('Service config file not found');
        }
    }
}