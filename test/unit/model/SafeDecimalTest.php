<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;

class SafeDecimalTest extends TestCase
{
    /**
     * 测试 decimal 方法 - 有效的小数（整数，字符串类型）
     */
    public function testDecimalValidIntegerString()
    {
        // 测试正整数
        $this->assertTrue(Safe::decimal('123'));
        $this->assertTrue(Safe::decimal('123', 5));
        $this->assertTrue(Safe::decimal('123', 3));
        
        // 测试负整数
        $this->assertTrue(Safe::decimal('-123'));
        $this->assertTrue(Safe::decimal('-123', 5));
        
        // 测试零
        $this->assertTrue(Safe::decimal('0'));
        $this->assertTrue(Safe::decimal('0', 1));
    }

    /**
     * 测试 decimal 方法 - 有效的小数（小数，字符串类型）
     */
    public function testDecimalValidDecimalString()
    {
        // 测试正小数
        $this->assertFalse(Safe::decimal('123.45'));
        $this->assertTrue(Safe::decimal('123.45', 5, 2));
        
        // 测试负小数
        $this->assertFalse(Safe::decimal('-123.45'));
        $this->assertTrue(Safe::decimal('-123.45', 5, 2));
        
        // 测试零小数
        $this->assertTrue(Safe::decimal('0.0', 3, 1));
        $this->assertTrue(Safe::decimal('0.00', 3, 2));
    }

    /**
     * 测试 decimal 方法 - 有效的小数（整数，整数类型）
     */
    public function testDecimalValidIntegerInt()
    {
        // 测试正整数
        $this->assertTrue(Safe::decimal(123));
        $this->assertTrue(Safe::decimal(123, 5));
        $this->assertTrue(Safe::decimal(123, 3));
        
        // 测试负整数
        $this->assertTrue(Safe::decimal(-123));
        $this->assertTrue(Safe::decimal(-123, 5));
        
        // 测试零
        $this->assertTrue(Safe::decimal(0));
        $this->assertTrue(Safe::decimal(0, 1));
    }

    /**
     * 测试 decimal 方法 - 有效的小数（小数，浮点数类型）
     */
    public function testDecimalValidDecimalFloat()
    {
        // 测试正小数
        $this->assertTrue(Safe::decimal(123.45));
        $this->assertTrue(Safe::decimal(123.45, 5, 2));
        
        // 测试负小数
        $this->assertTrue(Safe::decimal(-123.45));
        $this->assertTrue(Safe::decimal(-123.45, 5, 2));
        
        // 测试零小数
        $this->assertTrue(Safe::decimal(0.0));
        $this->assertTrue(Safe::decimal(0.0, 1));
    }

    /**
     * 测试 decimal 方法 - 无效的小数（格式错误）
     */
    public function testDecimalInvalidFormat()
    {
        // 测试多个小数点
        $this->assertFalse(Safe::decimal('123.45.67'));
        
        // 测试只有小数点
        $this->assertFalse(Safe::decimal('.'));
        
        // 测试小数点前为空
        $this->assertFalse(Safe::decimal('.45'));
        
        // 测试小数点后为空
        $this->assertFalse(Safe::decimal('123.'));
        
        // 测试非数字字符
        $this->assertFalse(Safe::decimal('123abc'));
        $this->assertFalse(Safe::decimal('abc123'));
        $this->assertFalse(Safe::decimal('12a3.45'));
    }

    /**
     * 测试 decimal 方法 - 无效的小数（精度不足）
     */
    public function testDecimalInvalidPrecision()
    {
        // 测试整数部分长度超过精度
        $this->assertFalse(Safe::decimal('12345', 3));
        
        // 测试整数部分长度加上小数部分长度超过精度
        $this->assertFalse(Safe::decimal('123.45', 4, 2));
    }

    /**
     * 测试 decimal 方法 - 无效的小数（小数位数超过限制）
     */
    public function testDecimalInvalidScale()
    {
        // 测试小数位数超过指定限制
        $this->assertFalse(Safe::decimal('123.456', 6, 2));
    }

    /**
     * 测试 decimal 方法 - 无效的参数
     */
    public function testDecimalInvalidParameters()
    {
        // 测试精度为0
        $this->assertFalse(Safe::decimal('123', 0));
        
        // 测试精度为负数
        $this->assertFalse(Safe::decimal('123', -1));
        
        // 测试小数位数为负数
        $this->assertFalse(Safe::decimal('123', 5, -1));
        
        // 测试小数位数大于精度
        $this->assertFalse(Safe::decimal('123.45', 3, 2));
    }

   

    /**
     * 测试 decimal 方法 - 边界情况
     */
    public function testDecimalBoundary()
    {
        // 测试刚好达到精度限制
        $this->assertTrue(Safe::decimal('12345.67', 7, 2)); // 5整数位 + 2小数位 = 7精度
        
        // 测试刚好达到小数位数限制
        $this->assertTrue(Safe::decimal('123.45', 5, 2)); // 小数位数为2，刚好达到限制
        
        // 测试整数部分刚好达到长度限制
        $this->assertTrue(Safe::decimal('123', 3)); // 整数部分长度为3，刚好达到限制
    }
}