<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use system\database\pdo\Result;

class ResultTest extends TestCase
{


    /**
     * 测试 all 方法 - 默认类型（object）
     */
    public function testAllWithObjectType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2']
        ];
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 all 方法
        $actualResult = $result->all();
        
        // 验证结果
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试 all 方法 - array 类型
     */
    public function testAllWithArrayType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [
            ['id' => 1, 'name' => 'Test 1'],
            ['id' => 2, 'name' => 'Test 2']
        ];
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_ASSOC)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 all 方法
        $actualResult = $result->all('array');
        
        // 验证结果
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试 all 方法 - 空结果
     */
    public function testAllWithEmptyResult()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [];
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 all 方法
        $actualResult = $result->all();
        
        // 验证结果
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(0, $result->count());
    }

    /**
     * 测试 count 方法
     */
    public function testCount()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2']
        ];
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 all 方法来设置 count
        $result->all();
        
        // 执行 count 方法
        $count = $result->count();
        
        // 验证结果
        $this->assertEquals(2, $count);
    }

    /**
     * 测试 first 方法 - 默认类型（object）
     */
    public function testFirstWithObjectType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = (object) ['id' => 1, 'name' => 'Test 1'];
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($expectedResult);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 first 方法
        $actualResult = $result->first();
        
        // 验证结果
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(1, $result->count());
    }

    /**
     * 测试 first 方法 - array 类型
     */
    public function testFirstWithArrayType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = ['id' => 1, 'name' => 'Test 1'];
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_ASSOC)
                 ->willReturn($expectedResult);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 first 方法
        $actualResult = $result->first('array');
        
        // 验证结果
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(1, $result->count());
    }

    /**
     * 测试 first 方法 - 无结果
     */
    public function testFirstWithNoResult()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn(false);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 first 方法
        $actualResult = $result->first();
        
        // 验证结果
        $this->assertNull($actualResult);
        $this->assertEquals(0, $result->count());
    }

    /**
     * 测试 raw 方法
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testRaw()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 raw 方法
        $actualStmt = $result->raw();
        
        // 验证结果
        $this->assertSame($mockStmt, $actualStmt);
    }

    /**
     * 测试边界情况 - 多次调用 all 方法
     */
    public function testMultipleAllCalls()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2']
        ];
        $mockStmt->expects($this->exactly(2))
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 第一次调用 all 方法
        $actualResult1 = $result->all();
        $this->assertEquals($expectedResult, $actualResult1);
        $this->assertEquals(2, $result->count());
        
        // 第二次调用 all 方法
        $actualResult2 = $result->all();
        $this->assertEquals($expectedResult, $actualResult2);
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试边界情况 - 先调用 all 再调用 first
     */
    public function testAllThenFirst()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $allResult = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2']
        ];
        $firstResult = (object) ['id' => 1, 'name' => 'Test 1'];
        
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($allResult);
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($firstResult);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 先调用 all 方法
        $actualAllResult = $result->all();
        $this->assertEquals($allResult, $actualAllResult);
        $this->assertEquals(2, $result->count());
        
        // 再调用 first 方法
        $actualFirstResult = $result->first();
        $this->assertEquals($firstResult, $actualFirstResult);
        $this->assertEquals(1, $result->count());
    }

    /**
     * 测试边界情况 - 先调用 first 再调用 all
     */
    public function testFirstThenAll()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $firstResult = (object) ['id' => 1, 'name' => 'Test 1'];
        $allResult = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2']
        ];
        
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($firstResult);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_OBJ)
                 ->willReturn($allResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 先调用 first 方法
        $actualFirstResult = $result->first();
        $this->assertEquals($firstResult, $actualFirstResult);
        $this->assertEquals(1, $result->count());
        
        // 再调用 all 方法
        $actualAllResult = $result->all();
        $this->assertEquals($allResult, $actualAllResult);
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试边界情况 - 无效的类型参数
     */
    public function testAllWithInvalidType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = [
            ['id' => 1, 'name' => 'Test 1'],
            ['id' => 2, 'name' => 'Test 2']
        ];
        $mockStmt->expects($this->once())
                 ->method('fetchAll')
                 ->with(PDO::FETCH_ASSOC)
                 ->willReturn($expectedResult);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 all 方法，使用无效的类型参数
        $actualResult = $result->all('invalid');
        
        // 验证结果（应该默认使用 array 类型）
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(2, $result->count());
    }

    /**
     * 测试边界情况 - first 方法使用无效的类型参数
     */
    public function testFirstWithInvalidType()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // 设置模拟对象的行为
        $expectedResult = ['id' => 1, 'name' => 'Test 1'];
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_ASSOC)
                 ->willReturn($expectedResult);
        $mockStmt->expects($this->once())
                 ->method('closeCursor')
                 ->willReturn(true);
        
        // 创建 Result 对象
        $result = new Result($mockStmt);
        
        // 执行 first 方法，使用无效的类型参数
        $actualResult = $result->first('invalid');
        
        // 验证结果（应该默认使用 array 类型）
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(1, $result->count());
    }
}