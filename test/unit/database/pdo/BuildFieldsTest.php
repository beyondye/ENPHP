<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;

class BuildFieldsTest extends TestCase
{
    /**
     * 测试 fields 方法 - 空字段数组
     */
    public function testFieldsEmpty()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望不调用 quote 方法
        $mockDb->expects($this->never())->method('quote');

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb);
        $this->assertEquals('*', $result);
    }

    /**
     * 测试 fields 方法 - 单个字段
     */
    public function testFieldsSingleField()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with('id')
            ->willReturn("'id'");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, 'id');
        $this->assertEquals("'id'", $result);
    }

    /**
     * 测试 fields 方法 - 多个字段
     */
    public function testFieldsMultipleFields()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望调用 quote 方法三次
        $mockDb->expects($this->exactly(3))
            ->method('quote')
            ->willReturnMap([
                ['id', "'id'"],
                ['name', "'name'"],
                ['age', "'age'"]
            ]);

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, 'id', 'name', 'age');
        $this->assertEquals("'id','name','age'", $result);
    }

    /**
     * 测试 fields 方法 - 包含特殊字符的字段名
     */
    public function testFieldsSpecialCharacters()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with('user_id')
            ->willReturn("'user_id'");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, 'user_id');
        $this->assertEquals("'user_id'", $result);
    }

    /**
     * 测试 fields 方法 - 空字符串字段名
     */
    public function testFieldsEmptyString()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with('')
            ->willReturn("''");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, '');
        $this->assertEquals("''", $result);
    }

    /**
     * 测试 fields 方法 - 包含空格的字段名
     */
    public function testFieldsSpaces()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with('user name')
            ->willReturn("'user name'");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, 'user name');
        $this->assertEquals("'user name'", $result);
    }

    /**
     * 测试 fields 方法 - 非常长的字段名
     */
    public function testFieldsLongFieldName()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 生成一个长字段名
        $longFieldName = str_repeat('a', 100);

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with($longFieldName)
            ->willReturn("'" . $longFieldName . "'");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, $longFieldName);
        $this->assertEquals("'" . $longFieldName . "'", $result);
    }

    /**
     * 测试 fields 方法 - 包含 SQL 注入风险的字段名
     */
    public function testFieldsSqlInjectionRisk()
    {
        // 创建模拟的 PDO 对象
        $mockDb = $this->createMock(PDO::class);

        // 包含 SQL 注入风险的字段名
        $riskyFieldName = "id'; DROP TABLE users; --";

        // 期望调用 quote 方法一次
        $mockDb->expects($this->once())
            ->method('quote')
            ->with($riskyFieldName)
            ->willReturn("'id\'; DROP TABLE users; --'");

        // 调用 fields 方法并验证返回值
        $result = Build::fields($mockDb, $riskyFieldName);
        $this->assertEquals("'id\'; DROP TABLE users; --'", $result);
    }
}