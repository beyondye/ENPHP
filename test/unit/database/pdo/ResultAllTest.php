<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Result;

class ResultAllTest extends TestCase
{
    /**
     * 测试 all 方法 - 默认类型（object）
     */
    public function testAllDefaultType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetchAll 方法返回对象数组
        $mockResult = [(object) ['id' => 1, 'name' => 'test1'], (object) ['id' => 2, 'name' => 'test2']];
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS)
            ->willReturn($mockResult);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 all 方法
        $actualResult = $result->all();

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试 all 方法 - 指定类型为 object
     */
    public function testAllObjectType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetchAll 方法返回对象数组
        $mockResult = [(object) ['id' => 1, 'name' => 'test1']];
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS)
            ->willReturn($mockResult);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 all 方法
        $actualResult = $result->all('object');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(1, $result->count());
    }

    /**
     * 测试 all 方法 - 指定类型为 array
     */
    public function testAllArrayType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetchAll 方法返回关联数组
        $mockResult = [['id' => 1, 'name' => 'test1'], ['id' => 2, 'name' => 'test2']];
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 all 方法
        $actualResult = $result->all('array');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试 all 方法 - 空结果
     */
    public function testAllEmptyResult()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetchAll 方法返回空数组
        $mockResult = [];
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS)
            ->willReturn($mockResult);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 all 方法
        $actualResult = $result->all();

        // 验证返回结果
        $this->assertEquals([], $actualResult);

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(0, $result->count());
    }

    /**
     * 测试 all 方法 - 其他类型参数
     */
    public function testAllOtherType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetchAll 方法返回关联数组
        $mockResult = [['id' => 1, 'name' => 'test1']];
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 调用 all 方法
        $actualResult = $result->all('other');

        // 验证返回结果
        $this->assertEquals($mockResult, $actualResult);

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(1, $result->count());
    }
}