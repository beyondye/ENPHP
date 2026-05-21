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
     * 测试单个null参数
     */
    public function testWhereSingleNull()
    {
        $result = Util::where(null);
        $this->assertEquals([['id', '=', null]], $result);
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
     * 测试五参数抛出异常
     */
    public function testWhereFiveParamsThrowsException()
    {
        $this->expectException(DatabaseException::class);
        Util::where('id', '=', 1, 'and', 'extra');
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
     * 测试所有条件都带连接符的情况
     */
    public function testWhereAllConditionsWithOperators()
    {
        $result = Util::where(
            ['id', '>', 10, 'and'],
            ['name', 'like', '%test%', 'or'],
            ['status', '=', 'active', 'and']
        );
        $this->assertEquals([
            ['id', '>', 10, 'and'],
            ['name', 'like', '%test%', 'or'],
            ['status', '=', 'active']
        ], $result);
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
     * 测试边界情况 - 纯数字字段名
     */
    public function testWhereNumericFieldName()
    {
        $result = Util::where('1', '=', 100);
        $this->assertEquals([['1', '=', 100]], $result);
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
     * 测试非数组参数情况（触发异常）
     */
    public function testWhereNonArrayParameter()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Not Support Where Condition Format,Please Check The Format.');
        Util::where([object::class => new \DateTime()]);
    }

    /**
     * 测试嵌套空数组情况
     */
    public function testWhereNestedEmptyArray()
    {
        $result = Util::where([[]]);
        $this->assertEquals([], $result);
    }

    /**
     * 测试关联数组参数 - 字段+值格式
     */
    public function testWhereAssociativeArrayFieldAndValue()
    {
        $result = Util::where(['status' => 'active', 'id' => 1]);
        $this->assertEquals([['status', '=', 'active', 'and'], ['id', '=', 1]], $result);
    }

    /**
     * 测试关联数组参数 - 字段+数组格式（IN查询）
     */
    public function testWhereAssociativeArrayFieldAndArray()
    {
        $result = Util::where(['id' => [1, 2, 3], 'status' => 'active']);
        $this->assertEquals([['id', 'in', [1, 2, 3], 'and'], ['status', '=', 'active']], $result);
    }

    /**
     * 测试关联数组参数 - 包含null值
     */
    public function testWhereAssociativeArrayWithNull()
    {
        $result = Util::where(['deleted_at' => null]);
        $this->assertEquals([['deleted_at', '=', null]], $result);
    }

    /**
     * 测试关联数组参数 - 值为对象时抛出异常
     */
    public function testWhereAssociativeArrayWithObjectValue()
    {
        $this->expectException(DatabaseException::class);
        Util::where(['date' => new \DateTime()]);
    }
/**
     * 测试纯关联数组参数（不含数字索引）
     */
    public function testWherePureAssociativeArray()
    {
        // 使用纯字符串键的关联数组
        $result = Util::where(['status' => 'active', 'name' => 'test']);
        $this->assertEquals([['status', '=', 'active', 'and'], ['name', '=', 'test']], $result);
    }

    /**
     * 测试关联数组中数字索引的数组元素被递归处理（覆盖第 113-115 行）
     */
    public function testWhereAssociativeArrayWithNumericIndexArray()
    {
        $result = Util::where(['status' => 'active', 0 => ['id', '=', 1]]);
        $this->assertEquals([['status', '=', 'active', 'and'], ['id', '=', 1]], $result);
    }

    /**
     * 测试关联数组中数字索引的对象元素抛出异常（覆盖第 117-118 行）
     */
    public function testWhereAssociativeArrayWithNumericIndexObject()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Not Support Non-Array Parameter.');
        Util::where(['status' => 'active', 0 => new \stdClass()]);
    }

}