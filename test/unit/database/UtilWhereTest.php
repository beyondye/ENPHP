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
        $this->expectExceptionMessage('Not Support Where Condition Format,Please Check The Format.');
        Util::where(1, 2);
    }

    /**
     * 测试带 'not in' 操作符的情况
     */
    public function testWhereNotInOperator()
    {
        $result = Util::where('id', 'not in', [1, 2, 3]);
        $this->assertEquals([['id', 'not in', [1, 2, 3]]], $result);
    }

    /**
     * 测试带 'between' 操作符的情况
     */
    public function testWhereBetweenOperator()
    {
        $result = Util::where('age', 'between', [18, 30]);
        $this->assertEquals([['age', 'between', [18, 30]]], $result);
    }

    /**
     * 测试带 'not between' 操作符的情况
     */
    public function testWhereNotBetweenOperator()
    {
        $result = Util::where('age', 'not between', [18, 30]);
        $this->assertEquals([['age', 'not between', [18, 30]]], $result);
    }

    /**
     * 测试边界情况 - 空字符串参数
     */
    public function testWhereEmptyStringParam()
    {
        $result = Util::where('');
        $this->assertEquals([], $result);
    }

    /**
     * 测试边界情况 - 特殊字符字段名
     */
    public function testWhereSpecialCharacterFieldName()
    {
        $result = Util::where('user_id', '=', 1);
        $this->assertEquals([['user_id', '=', 1]], $result);
    }


    /**
     * 测试边界情况 - 大型数组参数
     */
    public function testWhereLargeArrayParam()
    {
        $largeArray = range(1, 100);
        $result = Util::where('id', 'in', $largeArray);
        $this->assertEquals([['id', 'in', $largeArray]], $result);
    }

    /**
     * 测试边界情况 - 多个逻辑运算符
     */
    public function testWhereMultipleLogicalOperators()
    {
        $result = Util::where(['id', '=', 1, 'or'], ['name', 'like', '%test%', 'and'], ['age', '>', 18]);
        $this->assertEquals([['id', '=', 1, 'or'], ['name', 'like', '%test%', 'and'], ['age', '>', 18]], $result);
    }

    /**
     * 测试边界情况 - 带 'not' 逻辑运算符的情况
     */
    public function testWhereNotLogicalOperator()
    {
        $result = Util::where(['id', '=', 1, 'not'], ['name', '=', 'test']);
        $this->assertEquals([['id', '=', 1, 'not'], ['name', '=', 'test']], $result);
    }

    /**
     * 测试边界情况 - 带浮点数的情况
     */
    public function testWhereFloatValue()
    {
        $result = Util::where('price', '=', 19.99);
        $this->assertEquals([['price', '=', 19.99]], $result);
    }

    /**
     * 测试非数组参数的情况
     */
    public function testWhereNonArrayParam()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('If First Parameter Is Array,Other Parameters Must Be Array.' . json_encode([['id', '=', 1], 'invalid_param']));
        Util::where(['id', '=', 1], 'invalid_param');
    }

        /**
     * 测试非数组参数情况（触发 'Not Support Non-Array Parameter' 异常）
     */
    public function testWhereNonArrayParameter()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Not Support Non-Array Parameter.');
        // 传入包含布尔值的数组，布尔值既不是字符串/数字也不是数组
        Util::where([true]);
    }

        /**
     * 测试嵌套空数组情况（覆盖 lines 48-50 的 empty($wheres) 检查）
     */
    public function testWhereNestedEmptyArray()
    {
        // 传入包含空数组的参数，应该返回空数组
        $result = Util::where([[]]);
        $this->assertEquals([], $result);
    }
}
