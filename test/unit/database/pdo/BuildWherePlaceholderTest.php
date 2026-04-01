<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;
use system\database\DatabaseException;

class BuildWherePlaceholderTest extends TestCase
{
    /**
     * 测试t
     WherePlaceholder 方法 - 基本条件
     */
    public function testWherePlaceholderBasicCondition()
    {
        $result = Build::wherePlaceholder(['id', '=', 1]);
        $this->assertEquals('id=:where_id', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 带逻辑操作符的条件
     */
    public function testWherePlaceholderWithLogicOperator()
    {
        $result = Build::wherePlaceholder(['id', '=', 1, 'and'], ['name', 'like', '%test%', 'or']);
        $this->assertEquals('id=:where_id AND name LIKE :where_name OR', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - IN 条件
     */
    public function testWherePlaceholderInCondition()
    {
        $result = Build::wherePlaceholder(['id', 'in', [1, 2, 3]]);
        $this->assertEquals('id IN (:id_0,:id_1,:id_2)', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - BETWEEN 条件
     */
    public function testWherePlaceholderBetweenCondition()
    {
        $result = Build::wherePlaceholder(['age', 'between', [18, 30]]);
        $this->assertEquals('age BETWEEN (:age_0,:age_1)', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 空条件
     */
    public function testWherePlaceholderEmpty()
    {
        $result = Build::wherePlaceholder([], [], []);
        $this->assertEquals('', $result);
    }


    /**
     * 测试t
     WherePlaceholder 方法 - 条件格式错误（元素数量不足）
     */
    public function testWherePlaceholderInvalidFormatTooShort()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Condition Format Is Wrong.');
        Build::wherePlaceholder(['id', '=']);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 条件格式错误（元素数量过多）
     */
    public function testWherePlaceholderInvalidFormatTooLong()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Condition Format Is Wrong.');
        Build::wherePlaceholder(['id', '=', 1, 'and', 'extra']);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 字段名不是字符串
     */
    public function testWherePlaceholderNonStringField()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Key Must Be String. 0');
        Build::wherePlaceholder([1, '=', 1]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 操作符不是字符串
     */
    public function testWherePlaceholderNonStringOperator()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Operator Must Be String. id');
        Build::wherePlaceholder(['id', 1, 1]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 值类型错误
     */
    public function testWherePlaceholderInvalidValueType()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Value Condition Format Is Wrong. id');
        Build::wherePlaceholder(['id', '=', new stdClass()]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 逻辑操作符错误
     */
    public function testWherePlaceholderInvalidLogicOperator()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Logic Condition Format Is Wrong. id');
        Build::wherePlaceholder(['id', '=', 1, 'invalid']);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 操作符错误
     */
    public function testWherePlaceholderInvalidOperator()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Operator Is Wrong. id');
        Build::wherePlaceholder(['id', 'invalid', 1]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - IN 条件值不是数组
     */
    public function testWherePlaceholderInNonArrayValue()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere In Or Between Value Must Be Array. id');
        Build::wherePlaceholder(['id', 'in', 1]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - BETWEEN 条件值不是数组
     */
    public function testWherePlaceholderBetweenNonArrayValue()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere In Or Between Value Must Be Array. age');
        Build::wherePlaceholder(['age', 'between', 18]);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 空 IN 数组
     */
    public function testWherePlaceholderEmptyInArray()
    {
        $result = Build::wherePlaceholder(['id', 'in', []]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 空 BETWEEN 数组
     */
    public function testWherePlaceholderEmptyBetweenArray()
    {
        $result = Build::wherePlaceholder(['age', 'between', []]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 数组中包含非标量值
     */
    public function testWherePlaceholderNonScalarInArray()
    {
        $result = Build::wherePlaceholder(['id', 'in', [1, new stdClass(), 3]]);
        $this->assertEquals('id IN (:id_0,:id_2)', $result);
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 支持的操作符
     */
    public function testWherePlaceholderSupportedOperators()
    {
        $operators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'like', 'ilike'];
        foreach ($operators as $operator) {
            $result = Build::wherePlaceholder(['id', $operator, 1]);
            if($operator === 'like' || $operator === 'ilike') {
                $operator=strtoupper($operator);
                $this->assertEquals("id {$operator} :where_id", $result);
            } else {
                $this->assertEquals("id{$operator}:where_id", $result);
            }
        }
    }

    /**
     * 测试t
     WherePlaceholder 方法 - 逻辑操作符 'not'
     */
    public function testWherePlaceholderWithNotLogic()
    {
        $result = Build::wherePlaceholder(['id', '=', 1, 'not']);
        $this->assertEquals('id=:where_id NOT', $result);
    }
}