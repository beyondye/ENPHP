<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;
use system\model\ModelException;

class SafeValidateTest extends TestCase
{
    /**
     * 测试 validate() 方法 - 无效字段名
     */
    public function testValidateWithInvalidFieldName()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field Name:invalid_field');

        $fields = ['id' => 'integer', 'name' => 'varchar'];
        Safe::validate('invalid_field', 1, $fields);
    }

    /**
     * 测试 validate() 方法 - 无效字段配置类型
     */
    public function testValidateWithInvalidFieldConfigType()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Validation Value for Field id, Must Be String or Array Containing Type and Rules.');

        $fields = ['id' => 123]; // 字段配置不是字符串或数组
        Safe::validate('id', 1, $fields);
    }

    /**
     * 测试 validate() 方法 - 无效验证类型
     */
    public function testValidateWithInvalidValidationType()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Validation Type for Field id, Must Be One of [integer,varchar,datetime,enum,decimal,text].');

        $fields = ['id' => 'invalid_type'];
        Safe::validate('id', 1, $fields);
    }

    /**
     * 测试 validate() 方法 - 整数类型验证（有效）
     */
    public function testValidateWithValidInteger()
    {
        $fields = ['id' => 'integer'];
        $result = Safe::validate('id', 123, $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 整数类型验证（无效）
     */
    public function testValidateWithInvalidInteger()
    {
        $fields = ['id' => 'integer'];
        $result = Safe::validate('id', '123abc', $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 整数类型验证（字符串数字）
     */
    public function testValidateWithStringInteger()
    {
        $fields = ['id' => 'integer'];
        $result = Safe::validate('id', '123', $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 整数类型验证（带 min/max 规则）
     */
    public function testValidateWithIntegerWithMinMax()
    {
        $fields = ['id' => ['integer', 'min' => 1, 'max' => 100]];
        
        // 有效范围
        $result1 = Safe::validate('id', 50, $fields);
        $this->assertTrue($result1);
        
        // 小于最小值
        $result2 = Safe::validate('id', 0, $fields);
        $this->assertFalse($result2);
        
        // 大于最大值
        $result3 = Safe::validate('id', 101, $fields);
        $this->assertFalse($result3);
    }

    /**
     * 测试 validate() 方法 - 整数类型验证（无符号）
     */
    public function testValidateWithUnsignedInteger()
    {
        $fields = ['id' => ['integer', 'unsigned' => true]];
        
        // 正数有效
        $result1 = Safe::validate('id', 1, $fields);
        $this->assertTrue($result1);
        
        // 负数无效
        $result2 = Safe::validate('id', -1, $fields);
        $this->assertFalse($result2);
    }

    /**
     * 测试 validate() 方法 - 字符串类型验证（有效）
     */
    public function testValidateWithValidVarchar()
    {
        $fields = ['name' => 'varchar'];
        $result = Safe::validate('name', 'Test', $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 字符串类型验证（无效）
     */
    public function testValidateWithInvalidVarchar()
    {
        $fields = ['name' => 'varchar'];
        $result = Safe::validate('name', 123, $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 字符串类型验证（带长度限制）
     */
    public function testValidateWithVarcharWithLength()
    {
        $fields = ['name' => ['varchar', 'length' => 5]];
        
        // 长度有效
        $result1 = Safe::validate('name', 'Test', $fields);
        $this->assertTrue($result1);
        
        // 长度无效
        $result2 = Safe::validate('name', 'Testing', $fields);
        $this->assertFalse($result2);
    }

    /**
     * 测试 validate() 方法 - 文本类型验证
     */
    public function testValidateWithText()
    {
        $fields = ['content' => 'text'];
        $result = Safe::validate('content', 'This is a test', $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 日期时间类型验证（有效）
     */
    public function testValidateWithValidDatetime()
    {
        $fields = ['created_at' => 'datetime'];
        $result = Safe::validate('created_at', '2023-12-31 23:59:59', $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 日期时间类型验证（无效）
     */
    public function testValidateWithInvalidDatetime()
    {
        $fields = ['created_at' => 'datetime'];
        $result = Safe::validate('created_at', 'invalid-date', $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 枚举类型验证（有效）
     */
    public function testValidateWithValidEnum()
    {
        $fields = ['status' => ['enum', 'options' => ['active', 'inactive']]];
        $result = Safe::validate('status', 'active', $fields);
        $this->assertTrue($result);
    }

    /**
     * 测试 validate() 方法 - 枚举类型验证（无效）
     */
    public function testValidateWithInvalidEnum()
    {
        $fields = ['status' => ['enum', 'options' => ['active', 'inactive']]];
        $result = Safe::validate('status', 'invalid', $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 枚举类型验证（空选项）
     */
    public function testValidateWithEnumEmptyOptions()
    {
        $fields = ['status' => ['enum']]; // 没有选项
        $result = Safe::validate('status', 'active', $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 小数类型验证（有效）
     */
    public function testValidateWithValidDecimal()
    {
        $fields = ['price' => 'decimal'];
        
        // 整数
        $result1 = Safe::validate('price', 100, $fields);
        $this->assertFalse($result1);
        
        // 浮点数
        $result2 = Safe::validate('price', 100.99, $fields);
        $this->assertTrue($result2);

 // 浮点数
        $result2 = Safe::validate('price', 100000000000.99, $fields);
        $this->assertFalse($result2);

        
        // 字符串
        $result3 = Safe::validate('price', '100.99', $fields);
        $this->assertTrue($result3);
    }

    /**
     * 测试 validate() 方法 - 小数类型验证（无效）
     */
    public function testValidateWithInvalidDecimal()
    {
        $fields = ['price' => 'decimal'];
        $result = Safe::validate('price', '100.99.99', $fields);
        $this->assertFalse($result);
    }

    /**
     * 测试 validate() 方法 - 小数类型验证（带精度和小数位）
     */
    public function testValidateWithDecimalWithPrecisionAndScale()
    {
        $fields = ['price' => ['decimal', 'precision' => 5, 'scale' => 2]];
        
        // 有效
        $result1 = Safe::validate('price', 123.45, $fields);
        $this->assertTrue($result1);
        
        // 小数位过多
        $result2 = Safe::validate('price', 123.456, $fields);
        $this->assertFalse($result2);
        
        // 整数部分过长
        $result3 = Safe::validate('price', 12345.67, $fields);
        $this->assertFalse($result3);
    }

    /**
     * 测试 validate() 方法 - 数组类型配置（默认类型）
     */
    public function testValidateWithArrayConfigDefaultType()
    {
        $fields = ['name' => []]; // 空数组，默认类型为 varchar
        $result = Safe::validate('name', 'Test', $fields);
        $this->assertTrue($result);
    }
}