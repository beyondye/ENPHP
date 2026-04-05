<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Mysql;
use system\database\DatabaseException;

class MysqlIntegrationTest extends TestCase
{
    private ?Mysql $mysql = null;
    private string $testTable = 'test_upsert_integration';

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
                name VARCHAR(255) NOT NULL UNIQUE,
                value INT,
                description TEXT
            )";
            $this->mysql->execute($createTableSql);

            // 清空测试表
            $truncateSql = "TRUNCATE TABLE {$this->testTable}";
            $this->mysql->execute($truncateSql);
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
     * 测试 Mysql 类的 upsert 方法 - 插入新记录
     */
    public function testUpsertInsert()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试插入新记录
        $id = $this->mysql->upsert($this->testTable, [
            'name' => 'Test Insert',
            'value' => 100,
            'description' => 'Test insert operation'
        ]);

        $this->assertIsScalar($id);
        $this->assertGreaterThan(0, $id);
        $this->assertEquals(1, $this->mysql->effected());

        // 验证数据已插入
        $result = $this->mysql->select($this->testTable, ['id', 'name', 'value', 'description'], ['name', '=', 'Test Insert']);
        $row = $result->first('array');
        $this->assertEquals('Test Insert', $row['name']);
        $this->assertEquals(100, $row['value']);
        $this->assertEquals('Test insert operation', $row['description']);
    }

    /**
     * 测试 Mysql 类的 upsert 方法 - 更新现有记录
     */
    public function testUpsertUpdate()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 先插入一条记录
        $this->mysql->upsert($this->testTable, [
            'name' => 'Test Update',
            'value' => 100,
            'description' => 'Original description'
        ]);

        // 测试更新现有记录
        $id = $this->mysql->upsert($this->testTable, [
            'name' => 'Test Update', // 相同的唯一键
            'value' => 200, // 更新的值
            'description' => 'Updated description' // 更新的值
        ]);

        $this->assertIsScalar($id);
        $this->assertGreaterThan(0, $id);
        $this->assertEquals(2, $this->mysql->effected()); // MySQL 中 ON DUPLICATE KEY UPDATE 会返回 2

        // 验证数据已更新
        $result = $this->mysql->select($this->testTable, ['id', 'name', 'value', 'description'], ['name', '=', 'Test Update']);
        $row = $result->first('array');
        $this->assertEquals('Test Update', $row['name']);
        $this->assertEquals(200, $row['value']);
        $this->assertEquals('Updated description', $row['description']);
    }

    /**
     * 测试 Mysql 类的 upsert 方法 - 批量操作
     */
    public function testUpsertMultiple()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 测试插入多条记录
        $this->mysql->upsert($this->testTable, [
            'name' => 'Test 1',
            'value' => 100,
            'description' => 'Test 1 description'
        ]);

        $this->mysql->upsert($this->testTable, [
            'name' => 'Test 2',
            'value' => 200,
            'description' => 'Test 2 description'
        ]);

        // 测试更新其中一条记录
        $this->mysql->upsert($this->testTable, [
            'name' => 'Test 1',
            'value' => 150,
            'description' => 'Updated Test 1 description'
        ]);

        // 验证数据
        $result = $this->mysql->select($this->testTable);
        $rows = $result->all('array');
        $this->assertCount(2, $rows);

        // 查找 Test 1 记录
        $test1Row = null;
        $test2Row = null;
        foreach ($rows as $row) {
            if ($row['name'] === 'Test 1') {
                $test1Row = $row;
            } elseif ($row['name'] === 'Test 2') {
                $test2Row = $row;
            }
        }

        $this->assertNotNull($test1Row);
        $this->assertEquals(150, $test1Row['value']);
        $this->assertEquals('Updated Test 1 description', $test1Row['description']);

        $this->assertNotNull($test2Row);
        $this->assertEquals(200, $test2Row['value']);
        $this->assertEquals('Test 2 description', $test2Row['description']);
    }

    /**
     * 测试 Mysql 类的 upsert 方法 - 基于主键的更新操作
     */
    public function testUpsertWithPrimaryKey()
    {
        if (!$this->mysql) {
            $this->markTestSkipped('MySQL database not available');
        }

        // 先插入一条记录
        $id = $this->mysql->upsert($this->testTable, [
            'name' => 'Test Primary Key',
            'value' => 100,
            'description' => 'Original description'
        ]);

        $this->assertIsScalar($id);
        $this->assertGreaterThan(0, $id);

        // 测试基于主键的更新操作
        $updatedId = $this->mysql->upsert($this->testTable, [
            'id' => $id, // 指定主键值
            'name' => 'Test Primary Key Updated',
            'value' => 200,
            'description' => 'Updated description'
        ]);

        $this->assertIsScalar($updatedId);
        $this->assertEquals($id, $updatedId); // 应该返回相同的 ID
        $this->assertEquals(2, $this->mysql->effected()); // MySQL 中 ON DUPLICATE KEY UPDATE 会返回 2

        // 验证数据已更新
        $result = $this->mysql->select($this->testTable, ['id', 'name', 'value', 'description'], ['id', '=', $id]);
        $row = $result->first('array');
        $this->assertEquals($id, $row['id']);
        $this->assertEquals('Test Primary Key Updated', $row['name']);
        $this->assertEquals(200, $row['value']);
        $this->assertEquals('Updated description', $row['description']);
    }
}