<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;

class BuildGroupByTest extends TestCase
{
    /**
     * 测试 groupBy 方法 - 空分组数组
     */
    public function testGroupByEmpty()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy([], []);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 groupBy 方法 - 单个分组字段
     */
    public function testGroupBySingleField()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['id'], []);
        $this->assertEquals(' GROUP BY id', $result);
    }

    /**
     * 测试 groupBy 方法 - 多个分组字段
     */
    public function testGroupByMultipleFields()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['id', 'name', 'age'], []);
        $this->assertEquals(' GROUP BY id,name,age', $result);
    }

    /**
     * 测试 groupBy 方法 - 带有 HAVING 条件
     */
    public function testGroupByWithHaving()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['id'], [['count', '>', 5]]);
        $this->assertEquals(' GROUP BY id HAVING count>:where_count', $result);
    }

    /**
     * 测试 groupBy 方法 - 带有多个 HAVING 条件
     */
    public function testGroupByWithMultipleHaving()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['id', 'name'], [['count', '>', 5, 'and'    ], ['sum', '<', 100]]);
        $this->assertEquals(' GROUP BY id,name HAVING count>:where_count AND sum<:where_sum', $result);
    }

    /**
     * 测试 groupBy 方法 - 带有空 HAVING 条件
     */
    public function testGroupByWithEmptyHaving()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['id'], []);
        $this->assertEquals(' GROUP BY id', $result);
    }

    /**
     * 测试 groupBy 方法 - 边界测试：单个元素的分组数组
     */
    public function testGroupBySingleElementArray()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['name'], []);
        $this->assertEquals(' GROUP BY name', $result);
    }

    /**
     * 测试 groupBy 方法 - 边界测试：包含特殊字符的字段名
     */
    public function testGroupBySpecialCharacters()
    {
        // 调用 groupBy 方法并验证返回值
        $result = Build::groupBy(['user_id', 'user_name'], []);
        $this->assertEquals(' GROUP BY user_id,user_name', $result);
    }
}