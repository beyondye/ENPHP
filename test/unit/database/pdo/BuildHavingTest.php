<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;

class BuildHavingTest extends TestCase
{
    /**
     * 测试 having 方法 - 空条件
     */
    public function testHavingEmpty()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having();
        $this->assertEquals('', $result);


    }

    /**
     * 测试 having 方法 - 单个条件（字段、操作符和值）
     */
    public function testHavingSingleCondition()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count', '>', 5]]);
        $this->assertEquals(' HAVING count>:where_count', $result);

        $result = Build::having([['id', '=', 1]]);
        $this->assertEquals(' HAVING id=:where_id', $result);

        $result = Build::having([['sum(id)', '>', 1]]);
        $this->assertEquals(' HAVING sum(id)>:where_sum(id)', $result);
    }

    /**
     * 测试 having 方法 - IN 条件
     */
    public function testHavingInCondition()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count(id)', 'in', [1, 2, 3]]]);
        $this->assertEquals(' HAVING count(id) IN (:count(id)_0,:count(id)_1,:count(id)_2)', $result);
    }

    /**
     * 测试 having 方法 - BETWEEN 条件
     */
    public function testHavingBetweenCondition()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count', 'between', [1, 10]]]);
        $this->assertEquals(' HAVING count BETWEEN (:count_0,:count_1)', $result);
    }

    /**
     * 测试 having 方法 - LIKE 条件
     */
    public function testHavingLikeCondition()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['name', 'like', '%test%']]);
        $this->assertEquals(' HAVING name LIKE :where_name', $result);
    }

    /**
     * 测试 having 方法 - 多个条件（默认 AND 逻辑）
     */
    public function testHavingMultipleConditions()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count', '>', 5, 'and'], ['sum', '<', 100]]);
        $this->assertEquals(' HAVING count>:where_count AND sum<:where_sum', $result);
    }

    /**
     * 测试 having 方法 - 多个条件（OR 逻辑）
     */
    public function testHavingMultipleConditionsWithOr()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count', '>', 5, 'or'], ['sum', '<', 100]]);
        $this->assertEquals(' HAVING count>:where_count OR sum<:where_sum', $result);
    }

    /**
     * 测试 having 方法 - 混合条件类型
     */
    public function testHavingMixedConditions()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having([['count', '>', 5, 'and'], ['sum', 'in', [10, 20, 30], 'and'], ['avg', 'between', [1, 10]]]);
        $this->assertEquals(' HAVING count>:where_count AND sum IN (:sum_0,:sum_1,:sum_2) AND avg BETWEEN (:avg_0,:avg_1)', $result);
    }

    /**
     * 测试 having 方法 - 边界测试：空数组条件
     */
    public function testHavingEmptyArrayCondition()
    {
        // 调用 having 方法并验证返回值
        $result = Build::having();
        $this->assertEquals('', $result);
    }

}