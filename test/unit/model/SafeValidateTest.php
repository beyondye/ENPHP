<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;
use system\model\ModelException;

class SafeValidateTest extends TestCase
{
    /**
     * 测试 validate 方法 - 有效的整数字段
     */
    public function testValidateValidInteger()
    {
        $fields = [
            'id' => 'integer'
        ];

        // 测试有效的整数
        $this->assertTrue(Safe::validate('id', 123, $fields));
        $this->assertTrue(Safe::validate('id', -123, $fields));
        $this->assertTrue(Safe::validate('id', 0, $fields));
    }

    /**
     * 测试 validate 方法 - 无效的整数字段
     */
    public function testValidateInvalidInteger()
    {
        $fields = [
            'id' => 'integer'
        ];

        // 测试非整数
        $this->assertTrue(Safe::validate('id', '123', $fields));
        $this->assertFalse(Safe::validate('id', 123.45, $fields));
        $this->assertFalse(Safe::validate('id', true, $fields));
    }

    /**
     * 测试 validate 方法 - 有效的字符串字段
     */
    public function testValidateValidVarchar()
    {
        $fields = [
            'name' => 'varchar'
        ];

        // 测试有效的字符串
        $this->assertTrue(Safe::validate('name', 'Test', $fields));
        $this->assertTrue(Safe::validate('name', '', $fields));
    }

    /**
     * 测试 validate 方法 - 无效的字符串字段
     */
    public function testValidateInvalidVarchar()
    {
        $fields = [
            'name' => 'varchar'
        ];

        // 测试非字符串
        $this->assertFalse(Safe::validate('name', 123, $fields));
        $this->assertFalse(Safe::validate('name', 123.45, $fields));
        $this->assertFalse(Safe::validate('name', true, $fields));
    }

    /**
     * 测试 validate 方法 - 有效的日期时间字段
     */
    public function testValidateValidDatetime()
    {
        $fields = [
            'created_at' => 'datetime'
        ];

        // 测试有效的日期时间
        $this->assertTrue(Safe::validate('created_at', '2023-12-31', $fields));
        $this->assertTrue(Safe::validate('created_at', '2023-12-31 23:59:59', $fields));
    }

    /**
     * 测试 validate 方法 - 无效的日期时间字段
     */
    public function testValidateInvalidDatetime()
    {
        $fields = [
            'created_at' => 'datetime'
        ];

        // 测试非字符串
        $this->assertFalse(Safe::validate('created_at', 1234567890, $fields));
        
        // 测试无效的日期时间格式
        $this->assertFalse(Safe::validate('created_at', 'invalid', $fields));
    }

    /**
     * 测试 validate 方法 - 有效的枚举字段
     */
    public function testValidateValidEnum()
    {
        $fields = [
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试有效的枚举值
        $this->assertTrue(Safe::validate('status', 'active', $fields));
        $this->assertTrue(Safe::validate('status', 'inactive', $fields));
    }

    /**
     * 测试 validate 方法 - 无效的枚举字段
     */
    public function testValidateInvalidEnum()
    {
        $fields = [
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试无效的枚举值
        $this->assertFalse(Safe::validate('status', 'invalid', $fields));
    }

    /**
     * 测试 validate 方法 - 有效的小数字段
     */
    public function testValidateValidDecimal()
    {
        $fields = [
            'price' => 'decimal'
        ];

        // 测试有效的小数
        $this->assertFalse(Safe::validate('price', '123.45', $fields));
        $this->assertTrue(Safe::validate('price', '123', $fields));
    }

    /**
     * 测试 validate 方法 - 无效的小数字段
     */
    public function testValidateInvalidDecimal()
    {
        $fields = [
            'price' => ['decimal', 'precision' => 5, 'scale' => 2]
        ];

        // 测试无效的小数
        $this->assertTrue(Safe::validate('price', 123.45, $fields));
        $this->assertFalse(Safe::validate('price', 'invalid', $fields));
    }

    /**
     * 测试 validate 方法 - 带规则的字段
     */
    public function testValidateWithRules()
    {
        $fields = [
            'id' => ['integer', 'min' => 1, 'max' => 100, 'unsigned' => true],
            'name' => ['varchar', 'length' => 10],
            'price' => ['decimal', 'precision' => 5, 'scale' => 2]
        ];

        // 测试有效的值
        $this->assertTrue(Safe::validate('id', 50, $fields));
        $this->assertTrue(Safe::validate('name', 'Test', $fields));
        $this->assertTrue(Safe::validate('price', '12.34', $fields));

        // 测试无效的值
        $this->assertFalse(Safe::validate('id', 0, $fields)); // 小于最小值
        $this->assertFalse(Safe::validate('id', 101, $fields)); // 大于最大值
        $this->assertFalse(Safe::validate('id', -1, $fields)); // 负数
        $this->assertFalse(Safe::validate('name', 'This is too long', $fields)); // 长度超过限制
        $this->assertFalse(Safe::validate('price', '12003.45', $fields)); // 整数部分过长
    }

    /**
     * 测试 validate 方法 - 无效的字段名
     */
    public function testValidateInvalidFieldName()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        // 测试不存在的字段
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field Name:non_existent');
        Safe::validate('non_existent', 123, $fields);
    }

    /**
     * 测试 validate 方法 - 无效的字段配置
     */
    public function testValidateInvalidFieldConfig()
    {
        $fields = [
            'id' => 123 // 字段配置不是字符串或数组
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Validation Value for Field id, Must Be String or Array Containing Type and Rules.');
        Safe::validate('id', 123, $fields);
    }

    /**
     * 测试 validate 方法 - 无效的字段类型
     */
    public function testValidateInvalidFieldType()
    {
        $fields = [
            'id' => 'invalid_type' // 无效的字段类型
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Validation Type for Field id, Must Be One of [integer,varchar,datetime,enum,decimal,text].');
        Safe::validate('id', 123, $fields);
    }
}