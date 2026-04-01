<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Result;

class ResultFirstTest extends TestCase
{
    /**
     * 测试 first 方法 - 默认类型（object）
     */
    public function testFirstDefaultType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回对象
        $mockResult = (object) ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_CLASS)
            ->willReturn($mockResult);

        // 期望调用 closeCursor 方法
        $mockStmt->expects($this->once())
            ->method('closeCursor');

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 first 方法
        $actualResult = $result->first();

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);
    }

    /**
     * 测试 first 方法 - 指定类型为 object
     */
    public function testFirstObjectType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回对象
        $mockResult = (object) ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_CLASS)
            ->willReturn($mockResult);

        // 期望调用 closeCursor 方法
        $mockStmt->expects($this->once())
            ->method('closeCursor');

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 first 方法
        $actualResult = $result->first('object');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);
    }

    /**
     * 测试 first 方法 - 指定类型为 array
     */
    public function testFirstArrayType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回关联数组
        $mockResult = ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        // 期望调用 closeCursor 方法
        $mockStmt->expects($this->once())
            ->method('closeCursor');

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 first 方法
        $actualResult = $result->first('array');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);
    }

    /**
     * 测试 first 方法 - 空结果
     */
    public function testFirstEmptyResult()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回 null
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_CLASS)
            ->willReturn(null);

        // 期望调用 closeCursor 方法
        $mockStmt->expects($this->once())
            ->method('closeCursor');

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 first 方法
        $actualResult = $result->first();

        // 验证返回结果
        $this->assertNull($actualResult);
    }

    /**
     * 测试 first 方法 - 其他类型参数
     */
    public function testFirstOtherType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回关联数组
        $mockResult = ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        // 期望调用 closeCursor 方法
        $mockStmt->expects($this->once())
            ->method('closeCursor');

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 first 方法
        $actualResult = $result->first('other');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);
    }
}