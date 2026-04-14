<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;
use system\Input;

class InputTest extends TestCase
{
    /**
     * 测试 get 方法 - 获取所有 GET 参数
     */
    public function testGetAll()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据
        $_GET = ['id' => '123', 'name' => 'test'];
        
        // 测试获取所有 GET 参数
        $result = Input::get();
        $this->assertIsArray($result);
        $this->assertEquals('123', $result['id']);
        $this->assertEquals('test', $result['name']);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 获取指定 GET 参数
     */
    public function testGetSpecific()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据
        $_GET = ['id' => '123', 'name' => 'test'];
        
        // 测试获取存在的参数
        $result = Input::get('id');
        $this->assertEquals('123', $result);
        
        $result = Input::get('name');
        $this->assertEquals('test', $result);
        
        // 测试获取不存在的参数，使用默认值
        $result = Input::get('non_existent', 'default');
        $this->assertEquals('default', $result);
        
        // 测试获取不存在的参数，不使用默认值
        $result = Input::get('non_existent');
        $this->assertNull($result);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 空字符串参数
     */
    public function testGetEmptyString()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据
        $_GET = ['id' => '123', 'name' => 'test'];
        
        // 测试空字符串参数
        $result = Input::get('');
        $this->assertIsArray($result);
        $this->assertEquals('123', $result['id']);
        $this->assertEquals('test', $result['name']);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 处理数组参数
     */
    public function testGetArray()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据
        $_GET = ['ids' => ['1', '2', '3']];
        
        // 测试获取数组参数
        $result = Input::get('ids');
        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result);
        
        // 测试获取所有参数（包含数组）
        $result = Input::get();
        $this->assertIsArray($result);
        $this->assertIsArray($result['ids']);
        $this->assertEquals(['1', '2', '3'], $result['ids']);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 处理空字符串值
     */
    public function testGetEmptyStringValue()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据 - 空字符串值
        $_GET = ['empty' => ''];
        
        // 测试获取空字符串值，应该返回默认值
        $result = Input::get('empty', 'default');
        $this->assertEquals('default', $result);
        
        // 测试获取空字符串值，不使用默认值
        $result = Input::get('empty');
        $this->assertNull($result);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 边界测试：长参数名
     */
    public function testGetLongParameterName()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 创建一个非常长的参数名
        $longParamName = str_repeat('a', 1000);
        
        // 设置测试数据
        $_GET = [$longParamName => 'test_value'];
        
        // 测试获取长参数名的值
        $result = Input::get($longParamName);
        $this->assertEquals('test_value', $result);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 get 方法 - 安全测试：XSS 攻击
     */
    public function testGetXssAttack()
    {
        // 保存原始 $_GET
        $originalGet = $_GET;
        
        // 设置测试数据 - XSS 攻击代码
        $xssCode = '<script>alert("XSS")</script>';
        $_GET = ['xss' => $xssCode];
        
        // 测试获取 XSS 代码
        $result = Input::get('xss');
        $this->assertEquals($xssCode, $result);
        
        // 恢复原始 $_GET
        $_GET = $originalGet;
    }
    
    /**
     * 测试 post 方法 - 边界测试：长参数名
     */
    public function testPostLongParameterName()
    {
        // 保存原始 $_POST
        $originalPost = $_POST;
        
        // 创建一个非常长的参数名
        $longParamName = str_repeat('a', 1000);
        
        // 设置测试数据
        $_POST = [$longParamName => 'test_value'];
        
        // 测试获取长参数名的值
        $result = Input::post($longParamName);
        $this->assertEquals('test_value', $result);
        
        // 恢复原始 $_POST
        $_POST = $originalPost;
    }
    
    /**
     * 测试 post 方法 - 安全测试：XSS 攻击
     */
    public function testPostXssAttack()
    {
        // 保存原始 $_POST
        $originalPost = $_POST;
        
        // 设置测试数据 - XSS 攻击代码
        $xssCode = '<script>alert("XSS")</script>';
        $_POST = ['xss' => $xssCode];
        
        // 测试获取 XSS 代码
        $result = Input::post('xss');
        $this->assertEquals($xssCode, $result);
        
        // 恢复原始 $_POST
        $_POST = $originalPost;
    }
    
    /**
     * 测试 ip 方法 - 边界测试：无效 IP 地址
     */
    public function testIpInvalidAddress()
    {
        // 保存原始环境变量
        $originalEnv = $_ENV;
        
        // 测试无效 IP 地址
        putenv('HTTP_CLIENT_IP=999.999.999.999');
        $result = Input::ip();
        // 应该返回默认值，因为 IP 无效
        $this->assertEquals('0.0.0.0', $result);
        
        // 测试格式错误的 IP 地址
        putenv('HTTP_CLIENT_IP=invalid_ip');
        $result = Input::ip();
        // 应该返回默认值，因为 IP 格式错误
        $this->assertEquals('0.0.0.0', $result);
        
        // 恢复原始环境变量
        $_ENV = $originalEnv;
    }
    
    /**
     * 测试 method 方法 - 边界测试：不存在的 REQUEST_METHOD
     */
    public function testMethodNonExistent()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 移除 REQUEST_METHOD
        unset($_SERVER['REQUEST_METHOD']);
        
        // 测试获取不存在的 REQUEST_METHOD
        $result = Input::method();
        $this->assertEquals('', $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 referer 方法 - 边界测试：不存在的 HTTP_REFERER
     */
    public function testRefererNonExistent()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 移除 HTTP_REFERER
        unset($_SERVER['HTTP_REFERER']);
        
        // 测试获取不存在的 HTTP_REFERER
        $result = Input::referer();
        $this->assertEquals('', $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 host 方法 - 边界测试：不存在的 HTTP_HOST
     */
    public function testHostNonExistent()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 移除 HTTP_HOST
        unset($_SERVER['HTTP_HOST']);
        
        // 测试获取不存在的 HTTP_HOST
        $result = Input::host();
        $this->assertEquals('', $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 method 方法
     */
    public function testMethod()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 测试 GET 方法
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = Input::method();
        $this->assertEquals('GET', $result);
        
        // 测试 POST 方法
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $result = Input::method();
        $this->assertEquals('POST', $result);
        
        // 测试 PUT 方法
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $result = Input::method();
        $this->assertEquals('PUT', $result);
        
        // 测试 DELETE 方法
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $result = Input::method();
        $this->assertEquals('DELETE', $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 referer 方法
     */
    public function testReferer()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 设置测试数据
        $referer = 'https://example.com';
        $_SERVER['HTTP_REFERER'] = $referer;
        
        // 测试获取 referer
        $result = Input::referer();
        $this->assertEquals($referer, $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 body 方法
     */
    public function testBody()
    {
        // 由于 body 方法使用 file_get_contents('php://input')，在测试环境中可能无法直接测试
        // 这里我们只测试方法是否存在且返回字符串
        $result = Input::body();
        $this->assertIsString($result);
    }
    
    /**
     * 测试 post 方法 - 获取所有 POST 参数
     */
    public function testPostAll()
    {
        // 保存原始 $_POST
        $originalPost = $_POST;
        
        // 设置测试数据
        $_POST = ['id' => '123', 'name' => 'test'];
        
        // 测试获取所有 POST 参数
        $result = Input::post();
        $this->assertIsArray($result);
        $this->assertEquals('123', $result['id']);
        $this->assertEquals('test', $result['name']);
        
        // 恢复原始 $_POST
        $_POST = $originalPost;
    }
    
    /**
     * 测试 post 方法 - 获取指定 POST 参数
     */
    public function testPostSpecific()
    {
        // 保存原始 $_POST
        $originalPost = $_POST;
        
        // 设置测试数据
        $_POST = ['id' => '123', 'name' => 'test'];
        
        // 测试获取存在的参数
        $result = Input::post('id');
        $this->assertEquals('123', $result);
        
        $result = Input::post('name');
        $this->assertEquals('test', $result);
        
        // 测试获取不存在的参数
        $result = Input::post('non_existent');
        $this->assertNull($result);
        
        // 恢复原始 $_POST
        $_POST = $originalPost;
    }
    
    /**
     * 测试 post 方法 - 处理数组参数
     */
    public function testPostArray()
    {
        // 保存原始 $_POST
        $originalPost = $_POST;
        
        // 设置测试数据
        $_POST = ['ids' => ['1', '2', '3']];
        
        // 测试获取数组参数
        $result = Input::post('ids');
        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result);
        
        // 测试获取所有参数（包含数组）
        $result = Input::post();
        $this->assertIsArray($result);
        $this->assertIsArray($result['ids']);
        $this->assertEquals(['1', '2', '3'], $result['ids']);
        
        // 恢复原始 $_POST
        $_POST = $originalPost;
    }
    
    /**
     * 测试 isAjax 方法 - AJAX 请求
     */
    public function testIsAjaxTrue()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 设置测试数据
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        
        // 测试 AJAX 请求
        $result = Input::isAjax();
        $this->assertTrue($result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 isAjax 方法 - 非 AJAX 请求
     */
    public function testIsAjaxFalse()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 移除 HTTP_X_REQUESTED_WITH
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        
        // 测试非 AJAX 请求
        $result = Input::isAjax();
        $this->assertFalse($result);
        
        // 设置非 XMLHttpRequest 值
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'other';
        $result = Input::isAjax();
        $this->assertFalse($result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
    
    /**
     * 测试 ip 方法 - 各种 IP 来源
     */
    public function testIp()
    {
        // 保存原始环境变量
        $originalEnv = $_ENV;
        
        // 测试 HTTP_CLIENT_IP
        putenv('HTTP_CLIENT_IP=192.168.1.1');
        $result = Input::ip();
        $this->assertEquals('192.168.1.1', $result);
        
        // 测试 HTTP_X_FORWARDED_FOR
        putenv('HTTP_CLIENT_IP=');
        putenv('HTTP_X_FORWARDED_FOR=192.168.1.2');
        $result = Input::ip();
        $this->assertEquals('192.168.1.2', $result);
        
        // 测试 HTTP_X_FORWARDED
        putenv('HTTP_X_FORWARDED_FOR=');
        putenv('HTTP_X_FORWARDED=192.168.1.3');
        $result = Input::ip();
        $this->assertEquals('192.168.1.3', $result);
        
        // 测试 HTTP_FORWARDED_FOR
        putenv('HTTP_X_FORWARDED=');
        putenv('HTTP_FORWARDED_FOR=192.168.1.4');
        $result = Input::ip();
        $this->assertEquals('192.168.1.4', $result);
        
        // 测试 HTTP_FORWARDED
        putenv('HTTP_FORWARDED_FOR=');
        putenv('HTTP_FORWARDED=192.168.1.5');
        $result = Input::ip();
        $this->assertEquals('192.168.1.5', $result);
        
        // 测试 REMOTE_ADDR
        putenv('HTTP_FORWARDED=');
        putenv('REMOTE_ADDR=192.168.1.6');
        $result = Input::ip();
        $this->assertEquals('192.168.1.6', $result);
        
        // 测试默认值
        putenv('REMOTE_ADDR=');
        $result = Input::ip();
        $this->assertEquals('0.0.0.0', $result);
        
        // 恢复原始环境变量
        $_ENV = $originalEnv;
    }
    
    /**
     * 测试 ip 方法 - 处理多个 IP 地址
     */
    public function testIpWithMultipleAddresses()
    {
        // 保存原始环境变量
        $originalEnv = $_ENV;
        
        // 测试 HTTP_X_FORWARDED_FOR 多个 IP 地址
        putenv('HTTP_CLIENT_IP=');
        putenv('HTTP_X_FORWARDED_FOR=192.168.1.10, 192.168.1.11, 192.168.1.12');
        $result = Input::ip();
        $this->assertEquals('192.168.1.10', $result);
        
        // 测试 HTTP_FORWARDED_FOR 多个 IP 地址
        putenv('HTTP_X_FORWARDED_FOR=');
        putenv('HTTP_FORWARDED_FOR=192.168.1.20, 192.168.1.21');
        $result = Input::ip();
        $this->assertEquals('192.168.1.20', $result);
        
        // 恢复原始环境变量
        $_ENV = $originalEnv;
    }
    
    /**
     * 测试 host 方法
     */
    public function testHost()
    {
        // 保存原始 $_SERVER
        $originalServer = $_SERVER;
        
        // 设置测试数据
        $host = 'example.com';
        $_SERVER['HTTP_HOST'] = $host;
        
        // 测试获取 host
        $result = Input::host();
        $this->assertEquals($host, $result);
        
        // 恢复原始 $_SERVER
        $_SERVER = $originalServer;
    }
}