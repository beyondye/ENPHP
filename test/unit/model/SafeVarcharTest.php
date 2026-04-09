<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;

class SafeVarcharTest extends TestCase
{
    /**
     * 测试 varchar 方法 - 有效的字符串
     */
    public function testVarcharValidString()
    {
        // 测试空字符串
        $this->assertTrue(Safe::varchar(''));
        
        // 测试普通字符串
        $this->assertTrue(Safe::varchar('test'));
        $this->assertTrue(Safe::varchar('Hello, world!'));
        
        // 测试包含特殊字符的字符串
        $this->assertTrue(Safe::varchar('Test with special chars: !@#$%^&*()'));
        
        // 测试包含中文字符的字符串
        $this->assertTrue(Safe::varchar('测试中文字符'));
    }

    /**
     * 测试 varchar 方法 - 有效的字符串（指定长度）
     */
    public function testVarcharValidStringWithLength()
    {
        // 测试长度刚好等于限制的字符串
        $this->assertTrue(Safe::varchar(str_repeat('a', 10), 10));
        
        // 测试长度小于限制的字符串
        $this->assertTrue(Safe::varchar('test', 10));
        $this->assertTrue(Safe::varchar('hello', 10));
    }

    /**
     * 测试 varchar 方法 - 无效的字符串（长度超过限制）
     */
    public function testVarcharInvalidStringTooLong()
    {
        // 测试长度超过限制的字符串
        $this->assertFalse(Safe::varchar(str_repeat('a', 11), 10));
        $this->assertFalse(Safe::varchar('Hello, world!', 10));
    }

   

    /**
     * 测试 varchar 方法 - 边界情况
     */
    public function testVarcharBoundary()
    {
        // 测试空字符串
        $this->assertTrue(Safe::varchar(''));
        $this->assertTrue(Safe::varchar('', 0)); // 长度为 0 的字符串
        
        // 测试最大长度
        $maxLength = 255;
        $this->assertTrue(Safe::varchar(str_repeat('a', $maxLength)));
        $this->assertFalse(Safe::varchar(str_repeat('a', $maxLength + 1)));
    }
}