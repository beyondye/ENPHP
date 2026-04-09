<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;

class SafeEnumTest extends TestCase
{
    /**
     * 测试 enum 方法 - 有效的枚举值
     */
    public function testEnumValidValue()
    {
        $options = ['active', 'inactive', 'pending'];
        
        // 测试有效的枚举值
        $this->assertTrue(Safe::enum('active', $options));
        $this->assertTrue(Safe::enum('inactive', $options));
        $this->assertTrue(Safe::enum('pending', $options));
    }

    /**
     * 测试 enum 方法 - 无效的枚举值
     */
    public function testEnumInvalidValue()
    {
        $options = ['active', 'inactive', 'pending'];
        
        // 测试无效的枚举值
        $this->assertFalse(Safe::enum('invalid', $options));
        $this->assertFalse(Safe::enum('Active', $options)); // 大小写不匹配
        $this->assertFalse(Safe::enum('', $options)); // 空字符串
        $this->assertFalse(Safe::enum(null, $options)); // null
    }

    /**
     * 测试 enum 方法 - 不同类型的枚举值
     */
    public function testEnumDifferentTypes()
    {
        // 测试整数枚举
        $integerOptions = [1, 2, 3];
        $this->assertTrue(Safe::enum(1, $integerOptions));
        $this->assertFalse(Safe::enum(4, $integerOptions));
        
        // 测试混合类型枚举
        $mixedOptions = [1, 'two', 3.0, true];
        $this->assertTrue(Safe::enum(1, $mixedOptions));
        $this->assertTrue(Safe::enum('two', $mixedOptions));
        $this->assertTrue(Safe::enum(3.0, $mixedOptions));
        $this->assertTrue(Safe::enum(true, $mixedOptions));
        $this->assertFalse(Safe::enum('1', $mixedOptions)); // 字符串 '1' 与整数 1 不匹配
    }

    /**
     * 测试 enum 方法 - 空选项数组
     */
    public function testEnumEmptyOptions()
    {
        $emptyOptions = [];
        
        // 空选项数组中没有任何有效值
        $this->assertFalse(Safe::enum('active', $emptyOptions));
        $this->assertFalse(Safe::enum('', $emptyOptions));
        $this->assertFalse(Safe::enum(null, $emptyOptions));
    }

    /**
     * 测试 enum 方法 - 边界情况
     */
    public function testEnumBoundary()
    {
        $options = ['', null, 0, false];
        
        // 测试边界值
        $this->assertTrue(Safe::enum('', $options));
        $this->assertTrue(Safe::enum(null, $options));
        $this->assertTrue(Safe::enum(0, $options));
        $this->assertTrue(Safe::enum(false, $options));
    }
}