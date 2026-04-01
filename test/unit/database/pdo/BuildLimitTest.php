<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;
use system\database\DatabaseException;

class BuildLimitTest extends TestCase
{
    /**
     * 测试 limit 方法 - 空值
     */
    public function testLimitEmpty()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit([]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 limit 方法 - 整数参数
     */
    public function testLimitInteger()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit(10);
        $this->assertEquals(' LIMIT 10', $result);
    }

    /**
     * 测试 limit 方法 - 单元素数组
     */
    public function testLimitSingleElementArray()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit([10]);
        $this->assertEquals(' LIMIT 10', $result);
    }

    /**
     * 测试 limit 方法 - 双元素数组（带 OFFSET）
     */
    public function testLimitTwoElementArray()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit([10, 20]);
        $this->assertEquals(' LIMIT 10 OFFSET 20', $result);
    }

    /**
     * 测试 limit 方法 - 边界测试：零值
     */
    public function testLimitZero()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit(0);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 limit 方法 - 边界测试：负数
     */
    public function testLimitNegative()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit(-10);
        $this->assertEquals(' LIMIT -10', $result);
    }

    /**
     * 测试 limit 方法 - 边界测试：大整数
     */
    public function testLimitLargeInteger()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit(999999999);
        $this->assertEquals(' LIMIT 999999999', $result);
    }


    /**
     * 测试 limit 方法 - 无效参数：非整数数组元素
     */
    public function testLimitNonIntegerArrayElement()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');

        // 调用 limit 方法
        Build::limit(['10']);
    }

    /**
     * 测试 limit 方法 - 无效参数：超过两个元素的数组
     */
    public function testLimitMoreThanTwoElements()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');

        // 调用 limit 方法
        Build::limit([10, 20, 30]);
    }

    /**
     * 测试 limit 方法 - 无效参数：空数组
     */
    public function testLimitEmptyArray()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit([]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 limit 方法 - 无效参数：null
     */
    public function testLimitNull()
    {
        // 调用 limit 方法并验证返回值
        $result = Build::limit([]);
        $this->assertEquals('', $result);
    }
}