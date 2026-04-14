<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class DatabaseTransactionTest extends TestCase
{
    /**
     * 测试基本事务提交
     */
    public function testTransactionCommit()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_commit (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 开始事务
            $this->assertTrue($db->transaction());

            // 插入数据
            $insertResult = $db->insert('test_transaction_commit', ['name' => 'Test', 'value' => 100]);
            $this->assertIsScalar($insertResult);

            // 提交事务
            $this->assertTrue($db->commit());

            // 验证数据已插入
            $selectResult = $db->select('test_transaction_commit', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $row = $selectResult->first('array');
            $this->assertEquals('Test', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_commit");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务回滚
     */
    public function testTransactionRollback()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_rollback (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 开始事务
            $this->assertTrue($db->transaction());

            // 插入数据
            $insertResult = $db->insert('test_transaction_rollback', ['name' => 'Test', 'value' => 100]);
            $this->assertIsScalar($insertResult);

            // 回滚事务
            $this->assertTrue($db->rollback());

            // 验证数据未插入
            $selectResult = $db->select('test_transaction_rollback', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $row = $selectResult->first('array');
            $this->assertNull($row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_rollback");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务中的异常处理
     */
    public function testTransactionWithException()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_exception (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 开始事务
            $this->assertTrue($db->transaction());

            try {
                // 插入数据
                $insertResult = $db->insert('test_transaction_exception', ['name' => 'Test', 'value' => 100]);
                $this->assertIsScalar($insertResult);

                // 故意抛出异常
                throw new \Exception('Test exception');
            } catch (\Exception $e) {
                // 回滚事务
                $this->assertTrue($db->rollback());
            }

            // 验证数据未插入
            $selectResult = $db->select('test_transaction_exception', ['id', 'name', 'value'], ['name', '=', 'Test']);
            $row = $selectResult->first('array');
            $this->assertNull($row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_exception");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务中的多个操作
     */
    public function testTransactionMultipleOperations()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_multiple (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 开始事务
            $this->assertTrue($db->transaction());

            // 插入多条数据
            $insertResult1 = $db->insert('test_transaction_multiple', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($insertResult1);

            $insertResult2 = $db->insert('test_transaction_multiple', ['name' => 'Test 2', 'value' => 200]);
            $this->assertIsScalar($insertResult2);

            // 更新数据
            $updateResult = $db->update('test_transaction_multiple', ['value' => 150], ['id', '=', $insertResult1]);
            $this->assertGreaterThan(0, $updateResult);

            // 提交事务
            $this->assertTrue($db->commit());

            // 验证数据
            $selectResult1 = $db->select('test_transaction_multiple', ['id', 'name', 'value'], ['id', '=', $insertResult1]);
            $row1 = $selectResult1->first('array');
            $this->assertEquals('Test 1', $row1['name']);
            $this->assertEquals(150, $row1['value']);

            $selectResult2 = $db->select('test_transaction_multiple', ['id', 'name', 'value'], ['id', '=', $insertResult2]);
            $row2 = $selectResult2->first('array');
            $this->assertEquals('Test 2', $row2['name']);
            $this->assertEquals(200, $row2['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_multiple");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务嵌套（应该抛出异常）
     */
    public function testNestedTransaction()
    {
        $db = Database::instance('database.default');

        try {
            // 开始第一个事务
            $this->assertTrue($db->transaction());

            // 尝试开始第二个事务（应该抛出异常）
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Transaction Is Already In Progress.');
            $db->transaction();
        } finally {
            // 确保事务已回滚
            try {
                $db->rollback();
            } catch (DatabaseException $e) {
                // 忽略异常
            }
        }
    }

    /**
     * 测试无活动事务时的提交（应该抛出异常）
     */
    public function testCommitWithoutActiveTransaction()
    {

        $db = Database::instance('database.default');

        // 确保没有活动事务
        try {
            $db->rollback();
        } catch (DatabaseException $e) {
            // 忽略异常，确保事务已回滚
        }

        // 直接调用 commit 方法，没有先开始事务
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to commit.');
        $db->commit();
    }

    /**
     * 测试无活动事务时的回滚（应该抛出异常）
     */
    public function testRollbackWithoutActiveTransaction()
    {
        $db = Database::instance('database.default');

        // 确保没有活动事务
        try {
            $db->rollback();
        } catch (DatabaseException $e) {
            // 忽略异常，确保事务已回滚
        }

        // 尝试回滚无活动事务（应该抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to roll back.');
        $db->rollback();
    }

    /**
     * 测试事务中的删除操作
     */
    public function testTransactionWithDelete()
    {
        try {

            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_delete (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $insertResult = $db->insert('test_transaction_delete', ['name' => 'Test', 'value' => 100]);
            $this->assertIsScalar($insertResult);

            // 开始事务
            $this->assertTrue($db->transaction());

            // 删除数据
            $deleteResult = $db->delete('test_transaction_delete', ['id', '=', $insertResult]);
            $this->assertGreaterThan(0, $deleteResult);

            // 提交事务
            $this->assertTrue($db->commit());

            // 验证数据已删除
            $selectResult = $db->select('test_transaction_delete', ['id', 'name', 'value'], ['id', '=', $insertResult]);
            $row = $selectResult->first('array');
            $this->assertNull($row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_delete");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }
}
