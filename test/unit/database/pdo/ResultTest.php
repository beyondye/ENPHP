<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Result;
use system\database\ResultAbstract;

class ResultTest extends TestCase
{
    /**
     * 测试 Result 类继承关系
     */
    public function testResultInheritance()
    {
        // 检查 Result 类是否继承自 ResultAbstract
        $this->assertTrue(is_subclass_of(Result::class, ResultAbstract::class));
    }

    /**
     * 测试 Result 类构造函数
     */
    public function testResultConstructor()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createStub(PDOStatement::class);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 验证实例创建成功
        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * 测试 Result::count 方法
     */
    public function testResultCount()
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

        // 调用 all 方法，这会设置 num 属性
        $result->all();

        // 验证 count 方法返回正确的结果数量
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试 Result::count 方法 - 初始状态
     */
    public function testResultCountInitial()
    {
        // 创建模拟的 PDOStatement，使用 createStub 而不是 createMock
        // 这样就不需要设置期望
        $mockStmt = $this->createStub(PDOStatement::class);

        // 创建 Result 实例
        $result = new Result($mockStmt);

        // 验证 count 方法在初始状态下返回 0
        $this->assertEquals(0, $result->count());
    }

    /**
     * 测试 Result::all 方法 - 默认类型（object）
     */
    public function testResultAllDefaultType()
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
    }

    /**
     * 测试 Result::all 方法 - 指定类型为 array
     */
    public function testResultAllArrayType()
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
    }

    /**
     * 测试 Result::first 方法 - 默认类型（object）
     */
    public function testResultFirstDefaultType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回对象
        $mockResult = (object) ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_CLASS)
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
     * 测试 Result::first 方法 - 指定类型为 array
     */
    public function testResultFirstArrayType()
    {
        // 创建模拟的 PDOStatement
        $mockStmt = $this->createMock(PDOStatement::class);

        // 模拟 fetch 方法返回关联数组
        $mockResult = ['id' => 1, 'name' => 'test'];
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
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
}
