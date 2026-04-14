<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;
use system\Config;

class ConfigTest extends TestCase
{
    /**
     * 测试设置和获取简单值
     */
    public function testSetAndGetSimpleValue()
    {
        // 测试设置和获取字符串
        Config::set('test_key', 'test_value');
        $this->assertEquals('test_value', Config::get('test_key'));
        
        // 测试设置和获取数字
        Config::set('test_number', 123);
        $this->assertEquals(123, Config::get('test_number'));
        
        // 测试设置和获取布尔值
        Config::set('test_boolean', true);
        $this->assertEquals(true, Config::get('test_boolean'));
        
        // 测试设置和获取数组
        $testArray = ['a' => 1, 'b' => 2];
        Config::set('test_array', $testArray);
        $this->assertEquals($testArray, Config::get('test_array'));
        
        // 测试设置和获取对象
        $testObject = new \stdClass();
        $testObject->property = 'value';
        Config::set('test_object', $testObject);
        $this->assertEquals($testObject, Config::get('test_object'));
    }
    
    /**
     * 测试使用点号分隔的键路径
     */
    public function testSetAndGetWithDotNotation()
    {
        // 测试设置和获取嵌套值
        Config::set('database1.host', 'localhost');
        $this->assertEquals('localhost', Config::get('database1.host'));
        
        // 测试设置和获取更深层次的嵌套值
        Config::set('database1.credentials.username', 'root');
        $this->assertEquals('root', Config::get('database1.credentials.username'));
        
        // 测试设置和获取整个嵌套数组
        $databaseConfig = [
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'test'
        ];
        Config::set('database1', $databaseConfig);
        $this->assertEquals($databaseConfig, Config::get('database1'));
        $this->assertEquals('localhost', Config::get('database1.host'));
        $this->assertEquals(3306, Config::get('database1.port'));
        $this->assertEquals('test', Config::get('database1.database'));
    }
    
    /**
     * 测试获取不存在的键
     */
    public function testGetNonExistentKey()
    {
        // 测试获取不存在的键，应该返回默认值
        $this->assertEquals('default', Config::get('non_existent_key', 'default'));
        
        // 测试获取不存在的嵌套键，应该返回默认值
        $this->assertEquals('default', Config::get('database1.non_existent_key', 'default'));
    }
    
    /**
     * 测试空键
     */
    public function testEmptyKey()
    {
        // 测试设置空键，应该不做任何操作
        Config::set('', 'value');
        $this->assertNull(Config::get(''));
        
        // 测试获取空键，应该返回默认值
        $this->assertEquals('default', Config::get('', 'default'));
    }
    
    /**
     * 测试覆盖已有值
     */
    public function testOverrideValue()
    {
        // 设置初始值
        Config::set('test_key', 'initial_value');
        $this->assertEquals('initial_value', Config::get('test_key'));
        
        // 覆盖值
        Config::set('test_key', 'new_value');
        $this->assertEquals('new_value', Config::get('test_key'));
        
        // 覆盖嵌套值
        Config::set('database1.host', 'localhost');
        $this->assertEquals('localhost', Config::get('database1.host'));
        
        Config::set('database1.host', '127.0.0.1');
        $this->assertEquals('127.0.0.1', Config::get('database1.host'));
    }
    
    /**
     * 测试 flush 方法
     */
    public function testFlush()
    {
        // 设置值
        Config::set('test_key', 'test_value');
        Config::set('database1.host', 'localhost');
        
        // 确保值存在
        $this->assertEquals('test_value', Config::get('test_key'));
        $this->assertEquals('localhost', Config::get('database1.host'));
        
        // 清空单个键
        Config::flush('test_key');
        $this->assertNull(Config::get('test_key'));
        $this->assertEquals('localhost', Config::get('database1.host'));
        
        // 清空另一个键
        Config::flush('database1');
        $this->assertNull(Config::get('database1.host'));
    }
    
    /**
     * 测试初始化空配置
     */
    public function testGetWithEmptyConfig()
    {
        // 清空所有配置
        Config::flush('test_key');
        Config::flush('database1');
        
        // 测试获取值，应该返回默认值
        $this->assertEquals('default', Config::get('test_key', 'default'));
    }
    
    /**
     * 测试设置和获取 null 值
     */
    public function testSetAndGetNullValue()
    {
        // 设置 null 值
        Config::set('test_null', null);
        $this->assertNull(Config::get('test_null'));
        
        // 设置嵌套的 null 值
        Config::set('database1.null_value', null);
        $this->assertNull(Config::get('database1.null_value'));
    }
    
    /**
     * 测试设置和获取空字符串
     */
    public function testSetAndGetEmptyString()
    {
        // 设置空字符串
        Config::set('test_empty', '');
        $this->assertEquals('', Config::get('test_empty'));
        
        // 设置嵌套的空字符串
        Config::set('database1.empty_value', '');
        $this->assertEquals('', Config::get('database1.empty_value'));
    }
    
    /**
     * 测试设置和获取数字 0
     */
    public function testSetAndGetZeroValue()
    {
        // 设置数字 0
        Config::set('test_zero', 0);
        $this->assertEquals(0, Config::get('test_zero'));
        
        // 设置嵌套的数字 0
        Config::set('database1.zero_value', 0);
        $this->assertEquals(0, Config::get('database1.zero_value'));
    }
    
    /**
     * 测试设置和获取布尔值 false
     */
    public function testSetAndGetFalseValue()
    {
        // 设置布尔值 false
        Config::set('test_false', false);
        $this->assertFalse(Config::get('test_false'));
        
        // 设置嵌套的布尔值 false
        Config::set('database1.false_value', false);
        $this->assertFalse(Config::get('database1.false_value'));
    }
}