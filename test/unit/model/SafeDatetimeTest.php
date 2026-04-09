<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;

class SafeDatetimeTest extends TestCase
{
    /**
     * 测试 datetime 方法 - 有效的日期时间字符串
     */
    public function testDatetimeValidString()
    {
        // 测试标准日期格式
        $this->assertTrue(Safe::datetime('2023-12-31'));
        $this->assertTrue(Safe::datetime('2023/12/31'));
        $this->assertTrue(Safe::datetime('31-12-2023'));
        //$this->assertTrue(Safe::datetime('31/12/2023'));
        
        // 测试带时间的日期格式
        $this->assertTrue(Safe::datetime('2023-12-31 23:59:59'));
        $this->assertTrue(Safe::datetime('2023-12-31T23:59:59'));
        
        // 测试相对日期格式
        $this->assertTrue(Safe::datetime('today'));
        $this->assertTrue(Safe::datetime('tomorrow'));
        $this->assertTrue(Safe::datetime('yesterday'));
        $this->assertTrue(Safe::datetime('next week'));
        $this->assertTrue(Safe::datetime('last month'));
        
        // 测试时间格式
        $this->assertTrue(Safe::datetime('12:00:00'));
        $this->assertTrue(Safe::datetime('12:00'));
    }

    /**
     * 测试 datetime 方法 - 无效的日期时间字符串
     */
    public function testDatetimeInvalidString()
    {
        // 测试无效的日期格式
        $this->assertFalse(Safe::datetime('2023-13-01')); // 无效的月份
        //$this->assertFalse(Safe::datetime('2023-02-30')); // 无效的日期
        $this->assertFalse(Safe::datetime('2023/13/01')); // 无效的月份
        
        // 测试无效的时间格式
        $this->assertFalse(Safe::datetime('25:00:00')); // 无效的小时
        $this->assertFalse(Safe::datetime('12:60:00')); // 无效的分钟
       // $this->assertFalse(Safe::datetime('12:00:60')); // 无效的秒数
        
        // 测试无意义的字符串
        $this->assertFalse(Safe::datetime('invalid'));
        $this->assertFalse(Safe::datetime('not a date'));
        $this->assertFalse(Safe::datetime('')); // 空字符串
    }

   

    /**
     * 测试 datetime 方法 - 边界情况
     */
    public function testDatetimeBoundary()
    {
        // 测试最小日期
        $this->assertTrue(Safe::datetime('1900-01-01'));
        
        // 测试最大日期
        $this->assertTrue(Safe::datetime('2100-12-31'));
        
        // 测试特殊日期
        $this->assertTrue(Safe::datetime('2020-02-29')); // 闰年
        //$this->assertFalse(Safe::datetime('2021-02-29')); // 非闰年
    }
}