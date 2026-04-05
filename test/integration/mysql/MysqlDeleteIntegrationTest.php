<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Mysql;
use system\database\DatabaseException;

class MysqlDeleteIntegrationTest extends TestCase
{
    private ?Mysql $mysql = null;
    private string $testTable = 'test_delete_integration';

    /**
     * 测试前准备
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 配置 MySQL 连接信息
        $config = [
            'host' => 'localhost',
            'database' => 'test_db', // 确保这个数据库存在
            'username' => 'root',
            'password' => 'a12345678', // 根据实际情况修改
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ];

        try {
            // 创建 Mysql 实例
            $this->mysql = new Mysql($config);

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS {$this->testTable} (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT,
                status VARCHAR(50)
            )";
            $this->mysql->execute($createTableSql);

            // 清空测试表
            $truncateSql = "TRUNCATE TABLE {$this->testTable}";
            $this->mysql->execute($truncateSql);

            // 插入测试数据
            for ($i = 1; $i <= 10; $i++) {
                $this->mysql->insert($this->testTable, [
                    'name' => "Test {$i}",
                    'value' => $i * 100,
                    'status' => $i % 2 === 0 ? 'active' : 'inactive'
                ]);
            }
        } catch (DatabaseException $e) {
            // 如果数据库连接失败，跳过测试
            $this->markTestSkipped('MySQL database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试后清理
     */
    protected function tearDown(): void
    {
        if ($this->mysql) {
            // 删除测试表
            $dropTableSql = "DROP TABLE IF EXISTS {$this->testTable}";
            $this->mysql->execute($dropTableSql);
            $this->mysql->close();
            $this->mysql = null;
        }
        parent::tearDown();
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 正常删除操作
     */
    public function testDeleteNormal()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 验证初始数据量
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(10, $rows);

        // 测试删除一条记录
        $affected = $this->mysql->delete($this->testTable, ['id', '=', 1]);
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $this->mysql->effected());

        // 验证数据已删除
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(9, $rows);

        // 验证 ID 为 1 的记录不存在
        $result = $this->mysql->select($this->testTable, ['id'], ['id', '=', 1]);
        $row = $result->first('array');
        $this->assertNull($row);
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 删除不存在的记录
     */
    public function testDeleteNonExistent()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试删除不存在的记录
        $affected = $this->mysql->delete($this->testTable, ['id', '=', 999]);
        $this->assertEquals(0, $affected);
        $this->assertEquals(0, $this->mysql->effected());

        // 验证数据量不变
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(10, $rows);
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 带复杂条件的删除操作
     */
    public function testDeleteWithComplexCondition()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试删除 value > 500 的记录
        $affected = $this->mysql->delete($this->testTable, ['value', '>', 500]);
        $this->assertEquals(5, $affected); // 应该删除 6,7,8,9,10 共 5 条记录
        $this->assertEquals(5, $this->mysql->effected());

        // 验证数据已删除
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(5, $rows);

        // 验证所有记录的 value 都 <= 500
        foreach ($rows as $row) {
            $this->assertLessThanOrEqual(500, $row['value']);
        }
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 带多个条件的删除操作
     */
    public function testDeleteWithMultipleConditions()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试删除 status = 'active' 且 value > 300 的记录
        $affected = $this->mysql->delete($this->testTable, 
            ['status', '=', 'active', 'and'],
            ['value', '>', 300]
        );

        // 应该删除 4,6,8,10 共 4 条记录
        $this->assertEquals(4, $affected);
        $this->assertEquals(4, $this->mysql->effected());

        // 验证数据已删除
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(6, $rows);

        // 验证所有记录都不满足删除条件
        foreach ($rows as $row) {
            $this->assertTrue($row['status'] !== 'active' || $row['value'] <= 300);
        }
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 空条件删除（应该抛出异常）
     */
    public function testDeleteEmptyWhere()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试空条件删除，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Where Condition Is Empty.');
        $this->mysql->delete($this->testTable);
    }

    /**
     * 测试 Mysql 类的 delete 方法 - 使用 IN 操作符的删除操作
     */
    public function testDeleteWithInOperator()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试使用 IN 操作符删除多条记录
        $affected = $this->mysql->delete($this->testTable, ['id', 'in', [1, 3, 5, 7, 9]]);
        $this->assertEquals(5, $affected);
        $this->assertEquals(5, $this->mysql->effected());

        // 验证数据已删除
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(5, $rows);

        // 验证 ID 为 1,3,5,7,9 的记录不存在
        $result = $this->mysql->select($this->testTable, ['id'], ['id', 'in', [1, 3, 5, 7, 9]]);
        $rows = $result->all('array');
        $this->assertCount(0, $rows);
    }
}