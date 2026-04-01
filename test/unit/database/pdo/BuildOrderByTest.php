<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;
use system\database\DatabaseException;

class BuildOrderByTest extends TestCase
{
    /**
     * 测试 orderBy 方法 - 空数组
     */
    public function testOrderByEmpty()
    {
        // 调用 orderBy 方法并验证返回值
        $result = Build::orderBy([]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 orderBy 方法 - 单个排序字段（ASC）
     */
    public function testOrderBySingleFieldAsc()
    {
        // 调用 orderBy 方法并验证返回值
        $result = Build::orderBy(['id' => 'asc']);
        $this->assertEquals(' ORDER BY id asc', $result);
    }

    /**
     * 测试 orderBy 方法 - 单个排序字段（DESC）
     */
    public function testOrderBySingleFieldDesc()
    {
        // 调用 orderBy 方法并验证返回值
        $result = Build::orderBy(['id' => 'desc']);
        $this->assertEquals(' ORDER BY id desc', $result);
    }

    /**
     * 测试 orderBy 方法 - 多个排序字段
     */
    public function testOrderByMultipleFields()
    {
        // 调用 orderBy 方法并验证返回值
        $result = Build::orderBy(['id' => 'asc', 'name' => 'desc', 'age' => 'asc']);
        $this->assertEquals(' ORDER BY id asc,name desc,age asc', $result);
    }

    /**
     * 测试 orderBy 方法 - 大小写混合的排序方向
     */
    public function testOrderByMixedCaseDirection()
    {
        // 调用 orderBy 方法并验证返回值
        $result = Build::orderBy(['id' => 'ASC', 'name' => 'Desc']);
        $this->assertEquals(' ORDER BY id ASC,name Desc', $result);
    }

    /**
     * 测试 orderBy 方法 - 无效排序方向
     */
    public function testOrderByInvalidDirection()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. id');

        // 调用 orderBy 方法
        Build::orderBy(['id' => 'invalid']);
    }

    /**
     * 测试 orderBy 方法 - 空键
     */
    public function testOrderByEmptyKey()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. ');

        // 调用 orderBy 方法
        Build::orderBy(['' => 'asc']);
    }

    /**
     * 测试 orderBy 方法 - 空值
     */
    public function testOrderByEmptyValue()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. id');

        // 调用 orderBy 方法
        Build::orderBy(['id' => '']);
    }

    /**
     * 测试 orderBy 方法 - 非字符串键
     */
    public function testOrderByNonStringKey()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. 1');

        // 调用 orderBy 方法
        Build::orderBy([1 => 'asc']);
    }

    /**
     * 测试 orderBy 方法 - 非字符串值
     */
    public function testOrderByNonStringValue()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. id');

        // 调用 orderBy 方法
        Build::orderBy(['id' => 1]);
    }

    /**
     * 测试 orderBy 方法 - 边界测试：只包含空格的键
     */
    public function testOrderByWhitespaceKey()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc.   ');

        // 调用 orderBy 方法
        Build::orderBy(['   ' => 'asc']);
    }

    /**
     * 测试 orderBy 方法 - 边界测试：处理后为空的 orders 数组
     */
    public function testOrderByEmptyOrdersArray()
    {
        // 验证是否抛出 DatabaseException 异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. id');

        // 调用 orderBy 方法
        Build::orderBy(['id' => 'invalid']);
    }
}