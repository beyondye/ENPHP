<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;
use system\Context;

class ContextTest extends TestCase
{
    /**
     * 测试设置和获取值
     */
    public function testSetAndGet()
    {
        // 测试设置和获取字符串
        Context::set('test_key', 'test_value');
        $this->assertEquals('test_value', Context::get('test_key'));
        
        // 测试设置和获取数字
        Context::set('test_number', 123);
        $this->assertEquals(123, Context::get('test_number'));
        
        // 测试设置和获取数组
        $testArray = ['a' => 1, 'b' => 2];
        Context::set('test_array', $testArray);
        $this->assertEquals($testArray, Context::get('test_array'));
        
        // 测试设置和获取对象
        $testObject = new \stdClass();
        $testObject->property = 'value';
        Context::set('test_object', $testObject);
        $this->assertEquals($testObject, Context::get('test_object'));
    }
    
    /**
     * 测试获取不存在的键
     */
    public function testGetNonExistentKey()
    {
        // 确保键不存在
        if (Context::has('non_existent_key')) {
            Context::remove('non_existent_key');
        }
        
        // 测试获取不存在的键，应该返回 null
        $this->assertNull(Context::get('non_existent_key'));
    }
    
    /**
     * 测试检查键是否存在
     */
    public function testHas()
    {
        // 测试存在的键
        Context::set('existing_key', 'value');
        $this->assertTrue(Context::has('existing_key'));
        
        // 测试不存在的键
        if (Context::has('non_existent_key')) {
            Context::remove('non_existent_key');
        }
        $this->assertFalse(Context::has('non_existent_key'));
    }
    
    /**
     * 测试删除键
     */
    public function testRemove()
    {
        // 设置一个键
        Context::set('key_to_remove', 'value');
        $this->assertTrue(Context::has('key_to_remove'));
        
        // 删除键
        Context::remove('key_to_remove');
        $this->assertFalse(Context::has('key_to_remove'));
        $this->assertNull(Context::get('key_to_remove'));
        
        // 测试删除不存在的键（应该不会报错）
        Context::remove('non_existent_key');
        $this->assertFalse(Context::has('non_existent_key'));
    }
    
    /**
     * 测试清空所有数据
     */
    public function testClear()
    {
        // 设置多个键
        Context::set('key1', 'value1');
        Context::set('key2', 'value2');
        Context::set('key3', 'value3');
        
        // 确保键存在
        $this->assertTrue(Context::has('key1'));
        $this->assertTrue(Context::has('key2'));
        $this->assertTrue(Context::has('key3'));
        
        // 清空所有数据
        Context::clear();
        
        // 确保所有键都不存在
        $this->assertFalse(Context::has('key1'));
        $this->assertFalse(Context::has('key2'));
        $this->assertFalse(Context::has('key3'));
    }
    
    /**
     * 测试覆盖已有值
     */
    public function testOverrideValue()
    {
        // 设置初始值
        Context::set('test_key', 'initial_value');
        $this->assertEquals('initial_value', Context::get('test_key'));
        
        // 覆盖值
        Context::set('test_key', 'new_value');
        $this->assertEquals('new_value', Context::get('test_key'));
    }
    
    /**
     * 测试上下文的静态特性
     */
    public function testStaticNature()
    {
        // 设置一个值
        Context::set('static_key', 'static_value');
        
        // 测试在不同的测试方法中值仍然存在
        $this->assertEquals('static_value', Context::get('static_key'));
    }
    
    /**
     * 测试空值
     */
    public function testNullValue()
    {
        // 设置 null 值
        Context::set('null_key', null);
        $this->assertNull(Context::get('null_key'));
        $this->assertTrue(Context::has('null_key'));
    }
    
    /**
     * 测试空字符串
     */
    public function testEmptyString()
    {
        // 设置空字符串
        Context::set('empty_key', '');
        $this->assertEquals('', Context::get('empty_key'));
        $this->assertTrue(Context::has('empty_key'));
    }
    
    /**
     * 测试数字 0
     */
    public function testZeroValue()
    {
        // 设置数字 0
        Context::set('zero_key', 0);
        $this->assertEquals(0, Context::get('zero_key'));
        $this->assertTrue(Context::has('zero_key'));
    }
    
    /**
     * 测试布尔值 false
     */
    public function testFalseValue()
    {
        // 设置布尔值 false
        Context::set('false_key', false);
        $this->assertFalse(Context::get('false_key'));
        $this->assertTrue(Context::has('false_key'));
    }
}