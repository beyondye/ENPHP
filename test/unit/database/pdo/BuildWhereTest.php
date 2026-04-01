<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;

class BuildWhereTest extends TestCase
{
    /**
     * 测试 where 方法 - 空条件
     */
    public function testWhereEmpty()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where();
        $this->assertEquals('', $result);
    }


    /**
     * 测试 where 方法 - 单个条件（只提供值）
     */
    public function testWhereSingleValue()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);

        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);

        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);

        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);
        
        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);

        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);

        $result = Build::where([['id', 'in', [1, 'not']]]);
        $this->assertEquals(' WHERE id IN (:id_0,:id_1)', $result);
    }

    /**
     * 测试 where 方法 - 单个条件（字段和值）
     */
    public function testWhereFieldAndValue()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['name', '=', 'test']]);
        $this->assertEquals(' WHERE name=:where_name', $result);
    }

    /**
     * 测试 where 方法 - 单个条件（字段、操作符和值）
     */
    public function testWhereFieldOperatorAndValue()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['age', '>', 18]]);
        $this->assertEquals(' WHERE age>:where_age', $result);
    }

    /**
     * 测试 where 方法 - IN 条件
     */
    public function testWhereInCondition()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', 'in', [1, 2, 3]]]);
        $this->assertEquals(' WHERE id IN (:id_0,:id_1,:id_2)', $result);
    }

    /**
     * 测试 where 方法 - BETWEEN 条件
     */
    public function testWhereBetweenCondition()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['age', 'between', [18, 30]]]);
        $this->assertEquals(' WHERE age BETWEEN (:age_0,:age_1)', $result);
    }

    /**
     * 测试 where 方法 - LIKE 条件
     */
    public function testWhereLikeCondition()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['name', 'like', '%test%']]);
        $this->assertEquals(' WHERE name LIKE :where_name', $result);
    }

    /**
     * 测试 where 方法 - 多个条件（默认 AND 逻辑）
     */
    public function testWhereMultipleConditions()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1, 'and'], ['name', '=', 'test']]);
        $this->assertEquals(' WHERE id=:where_id AND name=:where_name', $result);
    }

    /**
     * 测试 where 方法 - 多个条件（显式 AND 逻辑）
     */
    public function testWhereMultipleConditionsWithAnd()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1, 'and'], ['name', '=', 'test']]);
        $this->assertEquals(' WHERE id=:where_id AND name=:where_name', $result);
    }

    /**
     * 测试 where 方法 - 多个条件（OR 逻辑）
     */
    public function testWhereMultipleConditionsWithOr()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1, 'or'], ['name', '=', 'test']]);
        $this->assertEquals(' WHERE id=:where_id OR name=:where_name', $result);
    }

    /**
     * 测试 where 方法 - 多个条件（NOT 逻辑）
     */
    public function testWhereMultipleConditionsWithNot()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1, 'not'], ['name', '=', 'test']]);
        $this->assertEquals(' WHERE id=:where_id NOT name=:where_name', $result);
    }

    /**
     * 测试 where 方法 - 混合条件类型
     */
    public function testWhereMixedConditions()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', 'in', [1, 2, 3], 'and'], ['age', '>', 18, 'and'], ['name', 'like', '%test%']]);
        $this->assertEquals(' WHERE id IN (:id_0,:id_1,:id_2) AND age>:where_age AND name LIKE :where_name', $result);
    }

    /**
     * 测试 where 方法 - 数组形式的单个条件
     */
    public function testWhereArraySingleCondition()
    {
        // 调用 where 方法并验证返回值
        $result = Build::where([['id', '=', 1]]);
        $this->assertEquals(' WHERE id=:where_id', $result);
    }
}