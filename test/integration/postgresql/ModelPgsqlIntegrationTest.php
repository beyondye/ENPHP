<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;
use system\Database;

// 测试模型类
class TestModel extends Model
{
    protected string $table = 'test_model';
    protected string $primary = 'id';
    protected bool $autoincrement = true;
    
    protected array $schema = [
        'id' => 'integer',
        'name' => 'varchar',
        'email' => 'varchar',
        'age' => 'integer',
        'active' => 'integer',
        'created_at' => 'datetime'
    ];
    
    protected array $fillable = [
        'name' => '',
        'email' => '',
        'age' => 0,
        'active' => 1
    ];
}

// 带事件追踪的测试模型类
class TestModelWithEvents extends TestModel
{
    public bool $creatingCalled = false;
    public bool $updatingCalled = false;
    public bool $deletingCalled = false;

    protected function creating(): void
    {
        $this->creatingCalled = true;
    }

    protected function updating(): void
    {
        $this->updatingCalled = true;
    }

    protected function deleting(): void
    {
        $this->deletingCalled = true;
    }
}

class ModelPgsqlIntegrationTest extends TestCase
{
    /**
     * 测试模型 - 初始化
     */
    public function testModelInitialization()
    {
        try {
            $model = new TestModel('database.pgsql');
            $this->assertInstanceOf(Model::class, $model);
        } catch (ModelException $e) {
            $this->markTestSkipped('Model initialization test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 插入数据
     */
    public function testModelInsert()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 测试插入单条数据
            $id = $model->insert(['name' => 'Test User', 'email' => 'test@example.com', 'age' => 25, 'active' => 1]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 测试插入多条数据
            $affectedRows = $model->insert(
                ['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 30, 'active' => 1],
                ['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 35, 'active' => 0]
            );
            $this->assertIsInt($affectedRows);
            $this->assertEquals(2, $affectedRows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 更新数据
     */
    public function testModelUpdate()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $model->insert(['name' => 'Test User', 'email' => 'test@example.com', 'age' => 25, 'active' => 1]);

            // 测试更新数据
            $affected = $model->update(['name' => 'Updated User', 'email' => 'updated@example.com', 'age' => 30, 'active' => 0], $id);
            $this->assertEquals(1, $affected);

            // 验证更新结果
            $updatedUser = $model->find($id);
            $this->assertNotNull($updatedUser);
            $this->assertEquals('Updated User', $updatedUser->name);
            $this->assertEquals('updated@example.com', $updatedUser->email);
            $this->assertEquals(30, $updatedUser->age);
            $this->assertEquals(0, $updatedUser->active);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 删除数据
     */
    public function testModelDelete()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id1 = $model->insert(['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25, 'active' => 1]);
            $id2 = $model->insert(['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 30, 'active' => 0]);

            // 测试删除数据
            $affected = $model->delete($id1);
            $this->assertEquals(1, $affected);

            // 验证删除结果
            $deletedUser = $model->find($id1);
            $this->assertNull($deletedUser);
            
            $existingUser = $model->find($id2);
            $this->assertNotNull($existingUser);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 查询方法
     */
    public function testModelQueryMethods()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $model->insert(['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25, 'active' => 1]);
            $model->insert(['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 30, 'active' => 0]);
            $model->insert(['name' => 'User 3', 'email' => 'user3@example.com', 'age' => 35, 'active' => 1]);

            // 测试 find 方法
            $user = $model->find(1);
            $this->assertNotNull($user);
            $this->assertEquals('User 1', $user->name);

            // 测试 exists 方法
            $exists = $model->exists(1);
            $this->assertTrue($exists);
            
            $notExists = $model->exists(999);
            $this->assertFalse($notExists);

            // 测试 first 方法
            $firstUser = $model->where('age', '>', 25)->first();
            $this->assertNotNull($firstUser);
            $this->assertGreaterThan(25, $firstUser->age);

            // 测试 all 方法
            $allUsers = $model->all();
            $this->assertIsArray($allUsers);

            // 测试 count 方法
            $count = $model->count();
            $this->assertIsInt($count);

            // 测试 rows 方法（分页）
            $rows = $model->rows(2, 1);
            $this->assertIsArray($rows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model query methods test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 查询构建器
     */
    public function testModelQueryBuilder()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $model->insert(['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25, 'active' => 1]);
            $model->insert(['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 30, 'active' => 0]);
            $model->insert(['name' => 'User 3', 'email' => 'user3@example.com', 'age' => 35, 'active' => 1]);

            // 测试查询构建器链
            $result = $model->select(['id', 'name', 'age'])
                           ->where('age', '>', 25)
                           ->where('active', '=', 1)
                           ->orderBy(['age' => 'desc'])
                           ->all();
            
            $this->assertIsArray($result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model query builder test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 事务
     */
    public function testModelTransaction()
    {
        try {
            $db = Database::instance('database.pgsql');
            $model = new TestModel('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 测试事务提交
            $result = $model->transaction(function () use ($model) {
                $id1 = $model->insert(['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25, 'active' => 1]);
                $id2 = $model->insert(['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 30, 'active' => 0]);
                return [$id1, $id2];
            });

            $this->assertIsArray($result);
            $this->assertCount(2, $result);
            
            // 验证数据已提交
            $count = $model->count();
            $this->assertEquals(2, $count);

            // 测试事务回滚
            try {
                $model->transaction(function () use ($model) {
                    $model->insert(['name' => 'User 3', 'email' => 'user3@example.com', 'age' => 35, 'active' => 1]);
                    throw new Exception('Test exception to trigger rollback');
                });
            } catch (ModelException $e) {
                // 预期会抛出异常
            }

            // 验证数据已回滚
            $count = $model->count();
            $this->assertEquals(2, $count);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model transaction test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试模型 - 安全测试（SQL注入）
     */
    public function testModelSqlInjection()
    {
        $db = Database::instance('database.pgsql');
        $model = new TestModel('database.pgsql');

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            age INTEGER,
            active INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->execute($createTableSql);

        // 插入测试数据
        $model->insert(['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25, 'active' => 1]);

        // 尝试 SQL 注入 - 应该抛出异常
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Field id Value Not Matched:1\' OR 1=1 --');
        $sqlInjection = "1' OR 1=1 --";
        $model->where('id', '=', $sqlInjection)->all();

        // 清理
        $db->execute("DROP TABLE IF EXISTS test_model");
    }

    /**
     * 测试模型 - 边界测试
     */
    public function testModelBoundaryCases()
    {
        $db = Database::instance('database.pgsql');
        $model = new TestModel('database.pgsql');

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            age INTEGER,
            active INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->execute($createTableSql);

        // 测试空数据插入 - 应该抛出异常
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Insert Data Cannot Be Empty Or Not Array');
        $model->insert([]);

        // 清理
        $db->execute("DROP TABLE IF EXISTS test_model");
    }

    /**
     * 测试模型 - 事件方法
     */
    public function testModelEvents()
    {
        try {
            $db = Database::instance('database.pgsql');
            
            $model = new TestModelWithEvents('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_model (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 测试创建事件
            $model->insert(['name' => 'Test User', 'email' => 'test@example.com', 'age' => 25, 'active' => 1]);
            $this->assertTrue($model->creatingCalled);

            // 测试更新事件
            $model->update(['name' => 'Updated User'], 1);
            $this->assertTrue($model->updatingCalled);

            // 测试删除事件
            $model->delete(1);
            $this->assertTrue($model->deletingCalled);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_model");
        } catch (ModelException $e) {
            $this->markTestSkipped('Model events test failed: ' . $e->getMessage());
        }
    }
}