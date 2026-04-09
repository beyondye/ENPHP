<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;
use system\model\ModelException;

class SafeWhereTest extends TestCase
{
    /**
     * 测试 where 方法 - 基本查询条件验证
     */
    public function testWhereBasicCondition()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试基本的等于条件
        $wheres = ['id', '=', 1];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['id', '=', 1], $result[0]);

        // 测试大于条件
        $wheres = ['id', '>', 5];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['id', '>', 5], $result[0]);

        // 测试字符串条件
        $wheres = ['name', '=', 'Test'];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['name', '=', 'Test'], $result[0]);

        // 测试枚举条件
        $wheres = ['status', '=', 'active'];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['status', '=', 'active'], $result[0]);
    }

    /**
     * 测试 where 方法 - 'in' 操作符
     */
    public function testWhereInOperator()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        // 测试整数数组
        $wheres = ['id', 'in', [1, 2, 3]];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['id', 'in', [1, 2, 3]], $result[0]);

        // 测试字符串数组
        $wheres = ['name', 'in', ['Test1', 'Test2', 'Test3']];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['name', 'in', ['Test1', 'Test2', 'Test3']], $result[0]);
    }

    /**
     * 测试 where 方法 - 'between' 操作符
     */
    public function testWhereBetweenOperator()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        // 测试整数范围
        $wheres = ['id', 'between', [1, 10]];
        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertEquals(['id', 'between', [1, 10]], $result[0]);
    }

    /**
     * 测试 where 方法 - 多个条件
     */
    public function testWhereMultipleConditions()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        $wheres = [
            ['id', '>', 5],
            ['name', 'like', '%Test%'],
            ['status', '=', 'active']
        ];

        $result = Safe::where($wheres, $fields);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(['id', '>', 5, 'and'], $result[0]);
        $this->assertEquals(['name', 'like', '%Test%', 'and'], $result[1]);
        $this->assertEquals(['status', '=', 'active'], $result[2]);
    }

    /**
     * 测试 where 方法 - 错误处理（无效字段）
     */
    public function testWhereInvalidField()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        $wheres = ['invalid_field', '=', 1];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field Name:invalid_field');

        Safe::where($wheres, $fields);
    }

    /**
     * 测试 where 方法 - 错误处理（无效值）
     */
    public function testWhereInvalidValue()
    {
        $fields = [
            'id' => 'integer',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试整数字段传入非整数
        $wheres = ['id', '=', 'not_an_integer'];
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:not_an_integer');
        Safe::where($wheres, $fields);

        // 测试枚举字段传入不在选项中的值
        $wheres = ['status', '=', 'invalid_status'];
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field status Value Not Matched:invalid_status');
        Safe::where($wheres, $fields);
    }

    /**
     * 测试 where 方法 - 错误处理（'in' 操作符值不是数组）
     */
    public function testWhereInOperatorNonArrayValue()
    {
        $fields = [
            'id' => 'integer'
        ];

        $wheres = ['id', 'in', 'not_an_array'];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Value Must Be Array:not_an_array');

        Safe::where($wheres, $fields);
    }

    /**
     * 测试 where 方法 - 错误处理（'between' 操作符值不是数组）
     */
    public function testWhereBetweenOperatorNonArrayValue()
    {
        $fields = [
            'id' => 'integer'
        ];

        $wheres = ['id', 'between', 'not_an_array'];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Value Must Be Array:not_an_array');

        Safe::where($wheres, $fields);
    }

    /**
     * 测试 where 方法 - 错误处理（'in' 操作符数组中包含无效值）
     */
    public function testWhereInOperatorInvalidArrayValue()
    {
        $fields = [
            'id' => 'integer'
        ];

        $wheres = ['id', 'in', [1, 2, 'not_an_integer']];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:not_an_integer');

        Safe::where($wheres, $fields);
    }
}