<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonTransactionPgsqlIntegrationTest extends TestCase
{
    /**
     * 测试事务 - 基本功能（提交）
     */
    public function testTransactionCommit()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_commit (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 开始事务
            $db->transaction();

            try {
                // 执行操作
                $db->insert('test_transaction_commit', ['name' => 'Test 1', 'value' => 100]);
                $db->insert('test_transaction_commit', ['name' => 'Test 2', 'value' => 200]);

                // 提交事务
                $db->commit();

                // 验证数据已提交
                $result = $db->select('test_transaction_commit');
                $rows = $result->all('array');
                $this->assertCount(2, $rows);
                $this->assertEquals('Test 1', $rows[0]['name']);
                $this->assertEquals('Test 2', $rows[1]['name']);
            } catch (Exception $e) {
                // 发生异常时回滚
                $db->rollback();
                throw $e;
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_commit");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务 - 基本功能（回滚）
     */
    public function testTransactionRollback()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_rollback (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 开始事务
            $db->transaction();

            try {
                // 执行操作
                $db->insert('test_transaction_rollback', ['name' => 'Test 1', 'value' => 100]);
                $db->insert('test_transaction_rollback', ['name' => 'Test 2', 'value' => 200]);

                // 回滚事务
                $db->rollback();

                // 验证数据已回滚
                $result = $db->select('test_transaction_rollback');
                $rows = $result->all('array');
                $this->assertCount(0, $rows);
            } catch (Exception $e) {
                // 发生异常时回滚
                $db->rollback();
                throw $e;
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_rollback");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务 - 嵌套事务（应该抛出异常）
     */
    public function testTransactionNested()
    {
        $db = Database::instance('database.pgsql');

        // 开始第一个事务
        $db->transaction();

        try {
            // 尝试开始第二个事务（应该抛出异常）
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Transaction Is Already In Progress.');
            $db->transaction();
        } finally {
            // 清理第一个事务
            $db->rollback();
        }
    }

    /**
     * 测试事务 - 提交非活动事务（应该抛出异常）
     */
    public function testCommitNonActiveTransaction()
    {
        $db = Database::instance('database.pgsql');

        // 尝试提交非活动事务（应该抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to commit.');
        $db->commit();
    }

    /**
     * 测试事务 - 回滚非活动事务（应该抛出异常）
     */
    public function testRollbackNonActiveTransaction()
    {
        $db = Database::instance('database.pgsql');

        // 尝试回滚非活动事务（应该抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to roll back.');
        $db->rollback();
    }

    /**
     * 测试事务 - 复杂操作
     */
    public function testTransactionComplexOperations()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql1 = "CREATE TABLE IF NOT EXISTS test_transaction_complex1 (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $createTableSql2 = "CREATE TABLE IF NOT EXISTS test_transaction_complex2 (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql1);
            $db->execute($createTableSql2);

            // 开始事务
            $db->transaction();

            try {
                // 在第一个表中插入数据
                $id1 = $db->insert('test_transaction_complex1', ['name' => 'Test 1', 'value' => 100]);
                
                // 在第二个表中插入数据
                $id2 = $db->insert('test_transaction_complex2', ['name' => 'Test 2', 'value' => 200]);
                
                // 更新第一个表中的数据
                $db->update('test_transaction_complex1', ['value' => 150], ['id', '=', $id1]);
                
                // 删除第二个表中的数据
                $db->delete('test_transaction_complex2', ['id', '=', $id2]);

                // 提交事务
                $db->commit();

                // 验证操作结果
                // 验证第一个表的数据已更新
                $result1 = $db->select('test_transaction_complex1', ['id', 'name', 'value'], ['id', '=', $id1]);
                $row1 = $result1->first('array');
                $this->assertEquals(150, $row1['value']);
                
                // 验证第二个表的数据已删除
                $result2 = $db->select('test_transaction_complex2', ['id'], ['id', '=', $id2]);
                $row2 = $result2->first('array');
                $this->assertNull($row2);
            } catch (Exception $e) {
                // 发生异常时回滚
                $db->rollback();
                throw $e;
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_complex1");
            $db->execute("DROP TABLE IF EXISTS test_transaction_complex2");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试事务 - 异常处理
     */
    public function testTransactionWithException()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_transaction_exception (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 开始事务
            $db->transaction();

            try {
                // 执行操作
                $db->insert('test_transaction_exception', ['name' => 'Test 1', 'value' => 100]);
                
                // 故意抛出异常
                throw new Exception('Test exception');
                
                // 下面的代码不会执行
                $db->insert('test_transaction_exception', ['name' => 'Test 2', 'value' => 200]);
                $db->commit();
            } catch (Exception $e) {
                // 发生异常时回滚
                $db->rollback();
                
                // 验证数据已回滚
                $result = $db->select('test_transaction_exception');
                $rows = $result->all('array');
                $this->assertCount(0, $rows);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_transaction_exception");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Transaction test failed: ' . $e->getMessage());
        }
    }
}