<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;

class SafeIntegerTest extends TestCase
{
    /**
     * 测试 integer 方法 - 有效的整数（默认参数）
     */
    public function testIntegerValidDefault()
    {
        // 测试正整数
        $this->assertTrue(Safe::integer(123));
        
        // 测试负整数
        $this->assertTrue(Safe::integer(-123));
        
        // 测试零
        $this->assertTrue(Safe::integer(0));
    }

    /**
     * 测试 integer 方法 - 有效的整数（指定范围）
     */
    public function testIntegerValidWithRange()
    {
        // 测试在范围内的整数
        $this->assertTrue(Safe::integer(5, 1, 10));
        $this->assertTrue(Safe::integer(1, 1, 10)); // 最小值
        $this->assertTrue(Safe::integer(10, 1, 10)); // 最大值
        
        // 测试负数范围
        $this->assertTrue(Safe::integer(-5, -10, 0));
        $this->assertTrue(Safe::integer(-10, -10, 0)); // 最小值
        $this->assertTrue(Safe::integer(0, -10, 0)); // 最大值
    }

    /**
     * 测试 integer 方法 - 有效的无符号整数
     */
    public function testIntegerValidUnsigned()
    {
        // 测试正整数
        $this->assertTrue(Safe::integer(123, 0, PHP_INT_MAX, true));
        
        // 测试零
        $this->assertTrue(Safe::integer(0, 0, PHP_INT_MAX, true));
    }

    /**
     * 测试 integer 方法 - 无效的整数（超出范围）
     */
    public function testIntegerInvalidOutOfRange()
    {
        // 测试小于最小值
        $this->assertFalse(Safe::integer(0, 1, 10));
        
        // 测试大于最大值
        $this->assertFalse(Safe::integer(11, 1, 10));
        
        // 测试负数范围
        $this->assertFalse(Safe::integer(-11, -10, 0));
        $this->assertFalse(Safe::integer(1, -10, 0));
    }

    /**
     * 测试 integer 方法 - 无效的无符号整数
     */
    public function testIntegerInvalidUnsigned()
    {
        // 测试负整数
        $this->assertFalse(Safe::integer(-1, 0, PHP_INT_MAX, true));
    }

    /**
     * 测试 integer 方法 - 边界情况
     */
    public function testIntegerBoundary()
    {
        // 测试 PHP_INT_MIN
        $this->assertTrue(Safe::integer(PHP_INT_MIN));
        
        // 测试 PHP_INT_MAX
        $this->assertTrue(Safe::integer(PHP_INT_MAX));
        
        // 测试刚好在范围内
        $this->assertTrue(Safe::integer(PHP_INT_MIN, PHP_INT_MIN, PHP_INT_MAX));
        $this->assertTrue(Safe::integer(PHP_INT_MAX, PHP_INT_MIN, PHP_INT_MAX));
    }
}