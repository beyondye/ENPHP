<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\model\Safe;
use system\model\ModelException;

class SafeFillableTest extends TestCase
{
    /**
     * 测试 fillable 方法 - 有效字段
     */
    public function testFillableValidFields()
    {
        $fillable = [
            'name' => '',
            'value' => 0,
            'status' => 'active'
        ];

        // 测试只包含可填充字段的数据
        $data = [
            'name' => 'Test',
            'value' => 100,
            'status' => 'active'
        ];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::fillable($data, $fillable);

        // 测试只包含部分可填充字段的数据
        $data = [
            'name' => 'Test'
        ];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::fillable($data, $fillable);
    }

    /**
     * 测试 fillable 方法 - 无效字段
     */
    public function testFillableInvalidField()
    {
        $fillable = [
            'name' => '',
            'value' => 0,
            'status' => 'active'
        ];

        // 测试包含无效字段的数据
        $data = [
            'name' => 'Test',
            'value' => 100,
            'invalid_field' => 'test'
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field invalid_field,Only Allowed Fields:name,value,status');

        Safe::fillable($data, $fillable);
    }

    /**
     * 测试 fillable 方法 - 空数据
     */
    public function testFillableEmptyData()
    {
        $fillable = [
            'name' => '',
            'value' => 0,
            'status' => 'active'
        ];

        // 测试空数据
        $data = [];

        // 应该不会抛出异常
        $this->expectNotToPerformAssertions();
        Safe::fillable($data, $fillable);
    }

    /**
     * 测试 fillable 方法 - 多个无效字段
     */
    public function testFillableMultipleInvalidFields()
    {
        $fillable = [
            'name' => '',
            'value' => 0
        ];

        // 测试包含多个无效字段的数据
        $data = [
            'name' => 'Test',
            'invalid_field1' => 'test1',
            'invalid_field2' => 'test2'
        ];

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Invalid Field invalid_field1,Only Allowed Fields:name,value');

        Safe::fillable($data, $fillable);
    }
}