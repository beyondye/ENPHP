<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;

class BuildWherePlaceholderValuesTest extends TestCase
{
    /**
     * 测试 wherePlaceholderValues 方法 - 空条件
     */
    public function testWherePlaceholderValuesEmpty()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望不调用 bindValue 方法
        $mockStmt->expects($this->never())->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 基本条件
     */
    public function testWherePlaceholderValuesBasicCondition()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法一次
        $mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':where_id', 1);

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['id', '=', 1]);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 多个基本条件
     */
    public function testWherePlaceholderValuesMultipleBasicConditions()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法两次
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['id', '=', 1], ['name', '=', 'test']);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - IN 条件
     */
    public function testWherePlaceholderValuesInCondition()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法三次
        $mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['id', 'in', [1, 2, 3]]);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - BETWEEN 条件
     */
    public function testWherePlaceholderValuesBetweenCondition()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法两次
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['age', 'between', [18, 30]]);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 混合条件
     */
    public function testWherePlaceholderValuesMixedConditions()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法多次
        $mockStmt->expects($this->exactly(4))
            ->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues(
            $mockStmt,
            ['id', '=', 1],
            ['name', 'in', ['test1', 'test2']],
            ['age', '=', 25]
        );
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 数组中包含非字符串和非数值类型
     */
    public function testWherePlaceholderValuesNonStringNumericInArray()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望调用 bindValue 方法两次（忽略非字符串和非数值类型）
        $mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['id', 'in', [1, true, 3]]);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 空 IN 数组
     */
    public function testWherePlaceholderValuesEmptyInArray()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望不调用 bindValue 方法
        $mockStmt->expects($this->never())->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['id', 'in', []]);
        $this->assertTrue($result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 空 BETWEEN 数组
     */
    public function testWherePlaceholderValuesEmptyBetweenArray()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 期望不调用 bindValue 方法
        $mockStmt->expects($this->never())->method('bindValue');

        // 调用 wherePlaceholderValues 方法并验证返回值
        $result = Build::wherePlaceholderValues($mockStmt, ['age', 'between', []]);
        $this->assertTrue($result);
    }
}