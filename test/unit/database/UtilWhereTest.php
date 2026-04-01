<?php

declare(strict_types=1);

namespace system\database\tests;

use PHPUnit\Framework\TestCase;
use system\database\Util;
use system\database\DatabaseException;

class UtilWhereTest extends TestCase
{
    /**
     * 测试空参数情况
     */
    public function testWhereEmpty()
    {
        $result = Util::where();
        $this->assertEquals([], $result);
    }

    /**
     * 测试单个数字参数
     */
    public function testWhereSingleNumber()
    {
        $result = Util::where(1);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试单个字符串参数
     */
    public function testWhereSingleString()
    {
        $result = Util::where('1');
        $this->assertEquals([['id', '=', '1']], $result);
    }

    /**
     * 测试两个参数 - 字段名和值
     */
    public function testWhereTwoParamsFieldAndValue()
    {
        $result = Util::where('id', 1);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试两个参数 - 字段名和数组
     */
    public function testWhereTwoParamsFieldAndArray()
    {
        $result = Util::where('id', [1, 2, 3]);
        $this->assertEquals([['id', 'in', [1, 2, 3]]], $result);
    }

    /**
     * 测试三个参数
     */
    public function testWhereThreeParams()
    {
        $result = Util::where('id', '=', 1);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试四个参数
     */
    public function testWhereFourParams()
    {
        $result = Util::where('id', '=', 1, 'and');
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试单个数组参数
     */
    public function testWhereSingleArray()
    {
        $result = Util::where(['id', '=', 1]);
        $this->assertEquals([['id', '=', 1]], $result);
    }

    /**
     * 测试多个数组参数
     */
    public function testWhereMultipleArrays()
    {
        $result = Util::where(['id', '=', 1], ['name', '=', '张三']);
        $this->assertEquals([['id', '=', 1, 'and'], ['name', '=', '张三']], $result);
    }

    /**
     * 测试带逻辑运算符的数组参数
     */
    public function testWhereArraysWithLogicalOperator()
    {
        $result = Util::where(['id', '=', 1, 'or'], ['name', '=', '张三']);
        $this->assertEquals([['id', '=', 1, 'or'], ['name', '=', '张三']], $result);
    }

    /**
     * 测试最后一个条件带有逻辑运算符的情况
     */
    public function testWhereLastConditionWithLogicalOperator()
    {
        $result = Util::where(['id', '=', 1], ['name', '=', '张三', 'and']);
        $this->assertEquals([['id', '=', 1, 'and'], ['name', '=', '张三']], $result);
    }

    /**
     * 测试无效参数情况
     */
    public function testWhereInvalidParams()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('build where Array condition error.');
        Util::where(1, 2);
    }
}