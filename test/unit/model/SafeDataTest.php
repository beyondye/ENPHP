<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;
use system\model\ModelException;

class SafeDataTest extends TestCase
{
    /**
     * 测试 data 方法 - 有效的数据
     */
    public function testDataValid()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试有效的数据
        $data = [
            'id' => 1,
            'name' => 'Test',
            'status' => 'active'
        ];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::data($data, $fields);
    }

    /**
     * 测试 data 方法 - 无效的数据
     */
    public function testDataInvalid()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试无效的整数
        $data = [
            'id' => 'not_an_integer',
            'name' => 'Test',
            'status' => 'active'
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:not_an_integer');
        Safe::data($data, $fields);

        // 测试无效的枚举值
        $data = [
            'id' => 1,
            'name' => 'Test',
            'status' => 'invalid_status'
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field status Value Not Matched:invalid_status');
        Safe::data($data, $fields);
    }

    /**
     * 测试 data 方法 - 空数据
     */
    public function testDataEmpty()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        // 测试空数据
        $data = [];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::data($data, $fields);
    }

    /**
     * 测试 data 方法 - 不存在的字段
     */
    public function testDataNonExistentField()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar'
        ];

        // 测试包含不存在字段的数据
        $data = [
            'id' => 1,
            'name' => 'Test',
            'non_existent_field' => 'test'
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field Name:non_existent_field');
        Safe::data($data, $fields);
    }

    /**
     * 测试 data 方法 - 部分有效数据
     */
    public function testDataPartiallyValid()
    {
        $fields = [
            'id' => 'integer',
            'name' => 'varchar',
            'status' => ['enum', 'options' => ['active', 'inactive']]
        ];

        // 测试部分有效数据
        $data = [
            'id' => 1,
            'name' => 'Test',
            'status' => 'active'
        ];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::data($data, $fields);
    }
}