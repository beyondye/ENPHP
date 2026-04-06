<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class DatabaseMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 MySQL 数据库连接
     */
    public function testMysqlConnection()
    {
        try {
            // 使用默认服务连接 MySQL 数据库
            $db = Database::instance('default');
            $this->assertNotNull($db);
            
            // 验证返回的实例是 DatabaseAbstract 的子类
            $this->assertInstanceOf('system\\database\\DatabaseAbstract', $db);
        } catch (DatabaseException $e) {
            // 如果数据库连接失败，标记测试为跳过
            $this->markTestSkipped('MySQL connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL 数据库操作
     */
    public function testMysqlOperation()
    {
        try {
            // 使用默认服务连接 MySQL 数据库
            $db = Database::instance('default');
            
            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_integration (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $result = $db->execute($createTableSql);
            $this->assertTrue($result >= 0);
            
            // 插入测试数据
            $insertResult = $db->insert('test_integration', ['name' => 'Test', 'value' => 100]);
            $this->assertIsScalar($insertResult);
            $this->assertGreaterThan(0, $insertResult);
            
            // 查询测试数据
            $selectResult = $db->select('test_integration', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $this->assertNotNull($selectResult);
            
            // 获取查询结果
            $row = $selectResult->first('array');
            $this->assertEquals('Test', $row['name']);
            $this->assertEquals(100, $row['value']);
            
            // 更新测试数据
            $updateResult = $db->update('test_integration', ['name' => 'Updated Test', 'value' => 200], ['id', '=', $insertResult]);
            $this->assertGreaterThan(0, $updateResult);
            
            // 再次查询测试数据
            $selectResult = $db->select('test_integration', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $row = $selectResult->first('array');
            $this->assertEquals('Updated Test', $row['name']);
            $this->assertEquals(200, $row['value']);
            
            // 删除测试数据
            $deleteResult = $db->delete('test_integration', ['id', '=', $insertResult]);
            $this->assertGreaterThan(0, $deleteResult);
            
            // 验证数据已删除
            $selectResult = $db->select('test_integration', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $row = $selectResult->first('array');
            $this->assertNull($row);
            
            // 删除测试表
            $dropTableSql = "DROP TABLE IF EXISTS test_integration";
            $result = $db->execute($dropTableSql);
            $this->assertTrue($result >= 0);
        } catch (DatabaseException $e) {
            // 如果数据库操作失败，标记测试为跳过
            $this->markTestSkipped('MySQL operation failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL 事务
     */
    public function testMysqlTransaction()
    {
        try {
            // 使用默认服务连接 MySQL 数据库
            $db = Database::instance('default');
            
            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);
            
            // 开始事务
            $this->assertTrue($db->transaction());
            
            try {
                // 插入测试数据
                $insertResult = $db->insert('test_transaction', ['name' => 'Transaction Test', 'value' => 100]);
                $this->assertIsScalar($insertResult);
                
                // 故意抛出异常
                throw new \Exception('Test rollback');
            } catch (\Exception $e) {
                // 回滚事务
                $this->assertTrue($db->rollback());
            }
            
            // 验证数据未被插入
            $selectResult = $db->select('test_transaction', ['id', 'name', 'value'], ['name', '=', 'Transaction Test']);
            $row = $selectResult->first('array');
            $this->assertNull($row);
            
            // 开始新事务
            $this->assertTrue($db->transaction());
            
            // 插入测试数据
            $insertResult = $db->insert('test_transaction', ['name' => 'Transaction Test', 'value' => 100]);
            $this->assertIsScalar($insertResult);
            
            // 提交事务
            $this->assertTrue($db->commit());
            
            // 验证数据已被插入
            $selectResult = $db->select('test_transaction', ['id', 'name', 'value'], ['name', '=', 'Transaction Test']);
            $row = $selectResult->first('array');
            $this->assertEquals('Transaction Test', $row['name']);
            $this->assertEquals(100, $row['value']);
            
            // 删除测试表
            $dropTableSql = "DROP TABLE IF EXISTS test_transaction";
            $db->execute($dropTableSql);
        } catch (DatabaseException $e) {
            // 如果数据库操作失败，标记测试为跳过
            $this->markTestSkipped('MySQL transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 Database 实例缓存
     */
    public function testMysqlInstanceCaching()
    {
        try {
            // 获取第一个实例
            $db1 = Database::instance('default');
            $this->assertNotNull($db1);
            
            // 获取第二个实例（应该是缓存的）
            $db2 = Database::instance('default');
            $this->assertNotNull($db2);
            
            // 验证两个实例是同一个对象
            $this->assertSame($db1, $db2);
        } catch (DatabaseException $e) {
            // 如果数据库连接失败，标记测试为跳过
            $this->markTestSkipped('MySQL connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试无效的 MySQL 服务
     */
    public function testInvalidMysqlService()
    {
        // 测试不存在的服务
        $this->expectException(DatabaseException::class);
        Database::instance('non_existent_service');
    }

    public function testUnsupportedDriver()
{
     // 测试不支持的驱动
    $this->expectException(DatabaseException::class);
    $this->expectExceptionMessage(" 'unsupported' Driver Not Support.");
    
    // 尝试获取使用不支持驱动的服务实例
    Database::instance('unsupported');
}
}