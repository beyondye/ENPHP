<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;

class ModelMysqlIntegrationTest extends TestCase
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * 测试表名
     */
    protected string $testTable = 'test_users';

    protected function setUp(): void
    {
        parent::setUp();

        // 创建一个继承自 Model 的测试类
        $this->model = new class extends Model {
            public function __construct()
            {
                parent::__construct('default');
                $this->table = 'test_users';
                $this->primary = 'id';
                $this->fillable = [
                    'id' => 0,
                    'name' => '',
                    'email' => '',
                    'age' => 0,
                    'status' => 'active'
                ];
                $this->schema = [
                    'id' => 'integer',
                    'name' => 'varchar',
                    'email' => 'varchar',
                    'age' => 'integer',
                    'status' => ['enum', 'options' => ['active', 'inactive']]
                ];
            }

            // 暴露 protected 的 conditions 属性
            public function getConditions()
            {
                return $this->conditions;
            }

            // 暴露 protected 的 _bindWhere 方法
            public function bindWhere(float|int|string|array ...$wheres): array
            {
                return $this->_bindWhere(...$wheres);
            }

            // 暴露 protected 的 creating 方法
            public function callCreating()
            {
                $this->creating();
            }

            // 暴露 protected 的 updating 方法
            public function callUpdating()
            {
                $this->updating();
            }

            // 暴露 protected 的 deleting 方法
            public function callDeleting()
            {
                $this->deleting();
            }

            // 暴露 protected 的 db 属性
            public function getDb()
            {
                return $this->db;
            }
        };

        // 创建测试表
        $this->createTestTable();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // 删除测试表
        $this->dropTestTable();
    }

    /**
     * 创建测试表
     */
    protected function createTestTable()
    {
        $db = $this->model->getDb();
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->testTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                age INT NOT NULL,
                status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $db->execute($sql);
    }

    /**
     * 删除测试表
     */
    protected function dropTestTable()
    {
        $db = $this->model->getDb();
        $sql = "DROP TABLE IF EXISTS {$this->testTable}";
        $db->execute($sql);
    }

    /**
     * 插入测试数据
     */
    protected function insertTestData()
    {
        $testData = [
            ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25, 'status' => 'active'],
            ['name' => 'Bob', 'email' => 'bob@example.com', 'age' => 30, 'status' => 'active'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com', 'age' => 35, 'status' => 'inactive'],
            ['name' => 'David', 'email' => 'david@example.com', 'age' => 40, 'status' => 'active'],
            ['name' => 'Eve', 'email' => 'eve@example.com', 'age' => 45, 'status' => 'inactive']
        ];

        foreach ($testData as $data) {
            $this->model->insert($data);
        }
    }

    /**
     * 测试 insert() 方法
     */
    public function testInsert()
    {
        // 插入一条记录
        $id = $this->model->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 28,
            'status' => 'active'
        ]);

        // 验证插入成功
        $this->assertIsScalar($id);
        $this->assertGreaterThan(0, $id);

        // 验证记录存在
        $user = $this->model->find($id);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals(28, $user->age);
        $this->assertEquals('active', $user->status);
    }

    /**
     * 测试 insert() 方法 - 多条数据
     */
    public function testInsertMultiple()
    {
        // 插入多条记录
        $id = $this->model->insert(
            ['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 20, 'status' => 'active'],
            ['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 21, 'status' => 'active']
        );

        // 验证插入成功
        $this->assertIsScalar($id);
        $this->assertGreaterThan(0, $id);

        // 验证至少有两条记录被插入
        $users = $this->model->all();
        $this->assertIsArray($users);
        $this->assertGreaterThanOrEqual(2, count($users));

        // 查找并验证 User 1
        $user1 = $this->model->where('email', '=', 'user1@example.com')->first();
        $this->assertNotNull($user1);
        $this->assertEquals('User 1', $user1->name);
        $this->assertEquals(20, $user1->age);

        // 查找并验证 User 2
        $user2 = $this->model->where('email', '=', 'user2@example.com')->first();
        $this->assertNotNull($user2);
        $this->assertEquals('User 2', $user2->name);
        $this->assertEquals(21, $user2->age);
    }

    /**
     * 测试 update() 方法
     */
    public function testUpdate()
    {
        // 先插入一条记录
        $id = $this->model->insert([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 25,
            'status' => 'active'
        ]);

        // 更新记录
        $affectedRows = $this->model->update(
            ['name' => 'Updated Name', 'email' => 'updated@example.com', 'age' => 30],
            $id
        );

        // 验证更新成功
        $this->assertEquals(1, $affectedRows);

        // 验证记录已更新
        $user = $this->model->find($id);
        $this->assertNotNull($user);
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
        $this->assertEquals(30, $user->age);
    }

    /**
     * 测试 delete() 方法
     */
    public function testDelete()
    {
        // 先插入一条记录
        $id = $this->model->insert([
            'name' => 'To Be Deleted',
            'email' => 'delete@example.com',
            'age' => 25,
            'status' => 'active'
        ]);

        // 验证记录存在
        $user = $this->model->find($id);
        $this->assertNotNull($user);

        // 删除记录
        $affectedRows = $this->model->delete($id);

        // 验证删除成功
        $this->assertEquals(1, $affectedRows);

        // 验证记录不存在
        $user = $this->model->find($id);
        $this->assertNull($user);
    }

    /**
     * 测试 first() 方法
     */
    public function testFirst()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 first() 方法
        $user = $this->model->where('status', '=', 'active')->first();

        // 验证结果
        $this->assertNotNull($user);
        $this->assertEquals('active', $user->status);
    }

    /**
     * 测试 all() 方法
     */
    public function testAll()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 all() 方法
        $users = $this->model->where('status', '=', 'active')->all();

        // 验证结果
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
        foreach ($users as $user) {
            $this->assertEquals('active', $user->status);
        }
    }

    /**
     * 测试 all() 方法 - 自定义 limit
     */
    public function testAllWithLimit()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 all() 方法，设置 limit
        $users = $this->model->where('status', '=', 'active')->all(2);

        // 验证结果
        $this->assertIsArray($users);
        $this->assertEquals(2, count($users));
        foreach ($users as $user) {
            $this->assertEquals('active', $user->status);
        }
    }

    /**
     * 测试 count() 方法
     */
    public function testCount()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 count() 方法
        $count = $this->model->where('status', '=', 'active')->count();

        // 验证结果
        $this->assertIsInt($count);
        $this->assertEquals(3, $count); // 3 个 active 用户
    }

    /**
     * 测试 find() 方法
     */
    public function testFind()
    {
        // 先插入一条记录
        $id = $this->model->insert([
            'name' => 'Find Me',
            'email' => 'find@example.com',
            'age' => 25,
            'status' => 'active'
        ]);

        // 测试 find() 方法
        $user = $this->model->find($id);

        // 验证结果
        $this->assertNotNull($user);
        $this->assertEquals('Find Me', $user->name);
        $this->assertEquals('find@example.com', $user->email);
        $this->assertEquals(25, $user->age);
        $this->assertEquals('active', $user->status);
    }

    /**
     * 测试 exists() 方法
     */
    public function testExists()
    {
        // 先插入一条记录
        $id = $this->model->insert([
            'name' => 'Exists Test',
            'email' => 'exists@example.com',
            'age' => 25,
            'status' => 'active'
        ]);

        // 测试 exists() 方法 - 存在的记录
        $exists = $this->model->exists($id);
        $this->assertTrue($exists);

        // 测试 exists() 方法 - 不存在的记录
        $exists = $this->model->exists(999999);
        $this->assertFalse($exists);
    }

    /**
     * 测试 rows() 方法
     */
    public function testRows()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 rows() 方法
        $users = $this->model->where('status', '=', 'active')->rows(2, 1);

        // 验证结果
        $this->assertIsArray($users);
        $this->assertEquals(2, count($users));
        foreach ($users as $user) {
            $this->assertEquals('active', $user->status);
        }
    }

    /**
     * 测试 transaction() 方法
     */
    public function testTransaction()
    {
        // 测试事务成功
        $result = $this->model->transaction(function () {
            // 插入一条记录
            $id1 = $this->model->insert([
                'name' => 'Transaction User 1',
                'email' => 'trans1@example.com',
                'age' => 25,
                'status' => 'active'
            ]);

            // 插入另一条记录
            $id2 = $this->model->insert([
                'name' => 'Transaction User 2',
                'email' => 'trans2@example.com',
                'age' => 30,
                'status' => 'active'
            ]);

            return ['id1' => $id1, 'id2' => $id2];
        });

        // 验证事务成功
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id1', $result);
        $this->assertArrayHasKey('id2', $result);

        // 验证记录存在
        $user1 = $this->model->find($result['id1']);
        $this->assertNotNull($user1);

        $user2 = $this->model->find($result['id2']);
        $this->assertNotNull($user2);
    }

    /**
     * 测试 transaction() 方法 - 失败
     */
    public function testTransactionFailure()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Transaction Error: Test transaction failure');

        // 测试事务失败
        $this->model->transaction(function () {
            // 插入一条记录
            $id = $this->model->insert([
                'name' => 'Transaction User',
                'email' => 'trans@example.com',
                'age' => 25,
                'status' => 'active'
            ]);

            // 抛出异常，事务应该回滚
            throw new \Exception('Test transaction failure');
        });
    }

    /**
     * 测试 transaction() 方法 - 复杂操作
     */
    public function testTransactionWithComplexOperations()
    {
        // 测试复杂事务操作
        $result = $this->model->transaction(function () {
            // 插入第一条记录
            $id1 = $this->model->insert([
                'name' => 'Complex User 1',
                'email' => 'complex1@example.com',
                'age' => 25,
                'status' => 'active'
            ]);

            // 更新第一条记录
            $this->model->update(
                ['name' => 'Complex User 1 Updated'],
                $id1
            );

            // 插入第二条记录
            $id2 = $this->model->insert([
                'name' => 'Complex User 2',
                'email' => 'complex2@example.com',
                'age' => 30,
                'status' => 'active'
            ]);

            // 删除第二条记录
            $this->model->delete($id2);

            return ['id1' => $id1, 'id2' => $id2];
        });

        // 验证事务成功
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id1', $result);
        $this->assertArrayHasKey('id2', $result);

        // 验证第一条记录存在且已更新
        $user1 = $this->model->find($result['id1']);
        $this->assertNotNull($user1);
        $this->assertEquals('Complex User 1 Updated', $user1->name);

        // 验证第二条记录已被删除
        $user2 = $this->model->find($result['id2']);
        $this->assertNull($user2);
    }

    /**
     * 测试 transaction() 方法 - 带验证的事务
     */
    public function testTransactionWithValidation()
    {
        // 测试带验证的事务操作
        $result = $this->model->transaction(function () {
            // 插入一条有效记录
            $id1 = $this->model->insert([
                'name' => 'Valid User',
                'email' => 'valid@example.com',
                'age' => 25,
                'status' => 'active'
            ]);

            // 尝试插入一条无效记录（年龄为非数字字符串）
            try {
                $this->model->insert([
                    'name' => 'Invalid User',
                    'email' => 'invalid@example.com',
                    'age' => 'not a number', // 无效的年龄值
                    'status' => 'active'
                ]);
                return ['id1' => $id1, 'status' => 'error: should have thrown exception'];
            } catch (ModelException $e) {
                // 预期会抛出异常，继续执行
                return ['id1' => $id1, 'status' => 'success'];
            }
        });

        // 验证事务成功
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id1', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);

        // 验证有效记录存在
        $user = $this->model->find($result['id1']);
        $this->assertNotNull($user);
        $this->assertEquals('Valid User', $user->name);
    }

    /**
     * 测试 transaction() 方法 - 事务回滚
     */
    public function testTransactionRollback()
    {
        // 测试事务回滚
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Transaction Error: Test rollback');

        // 执行事务，中间会抛出异常
        $this->model->transaction(function () {
            // 插入第一条记录
            $id1 = $this->model->insert([
                'name' => 'Rollback Test User 1',
                'email' => 'rollback1@example.com',
                'age' => 25,
                'status' => 'active'
            ]);

            // 插入第二条记录
            $id2 = $this->model->insert([
                'name' => 'Rollback Test User 2',
                'email' => 'rollback2@example.com',
                'age' => 30,
                'status' => 'active'
            ]);

            // 抛出异常，事务应该回滚
            throw new \Exception('Test rollback');
        });

        // 验证事务回滚后，两条记录都不存在
        $user1 = $this->model->where('email', '=', 'rollback1@example.com')->first();
        $this->assertNull($user1);

        $user2 = $this->model->where('email', '=', 'rollback2@example.com')->first();
        $this->assertNull($user2);
    }

    /**
     * 测试 transaction() 方法 - 批量操作
     */
    public function testTransactionWithBulkOperations()
    {
        // 测试批量操作事务
        $result = $this->model->transaction(function () {
            // 批量插入多条记录
            $ids = [];
            for ($i = 1; $i <= 5; $i++) {
                $id = $this->model->insert([
                    'name' => "Bulk User {$i}",
                    'email' => "bulk{$i}@example.com",
                    'age' => 20 + $i,
                    'status' => 'active'
                ]);
                $ids[] = $id;
            }

            // 批量更新记录
            foreach ($ids as $id) {
                $this->model->update(
                    ['status' => 'inactive'],
                    $id
                );
            }

            return $ids;
        });

        // 验证事务成功
        $this->assertIsArray($result);
        $this->assertEquals(5, count($result));

        // 验证所有记录都已更新
        foreach ($result as $id) {
            $user = $this->model->find($id);
            $this->assertNotNull($user);
            $this->assertEquals('inactive', $user->status);
        }
    }

    /**
     * 测试链式调用 - 基本查询
     */
    public function testChainedCallsBasic()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试基本链式调用
        $user = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('age', '>', 30)
            ->where('status', '=', 'active')
            ->orderBy(['age' => 'desc'])
            ->first();

        // 验证结果
        $this->assertNotNull($user);
        $this->assertGreaterThan(30, $user->age);
        $this->assertEquals('active', $user->status);
    }

    /**
     * 测试链式调用 - 多条件查询
     */
    public function testChainedCallsMultipleConditions()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试多条件链式调用
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where(['age', '>=', 25])
            ->where(['age', '<=', 40])
            ->where(['status', '=', 'active'])
            ->orderBy(['age' => 'asc'])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(40, $user->age);
            $this->assertEquals('active', $user->status);
        }
    }

    /**
     * 测试链式调用 - 分组聚合
     */
    public function testChainedCallsGroupBy()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试分组聚合链式调用
        $results = $this->model
            ->select(['status', 'COUNT(*) as user_count'])
            ->groupBy('status')
            ->orderBy(['user_count' => 'desc'])
            ->all();

        // 验证结果
        $this->assertIsArray($results);
        $this->assertEquals(2, count($results));
        foreach ($results as $result) {
            $this->assertTrue(property_exists($result, 'status'));
            $this->assertTrue(property_exists($result, 'user_count'));
            $this->assertIsInt($result->user_count);
        }
    }

    /**
     * 测试链式调用 - 分页查询
     */
    public function testChainedCallsPagination()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试分页链式调用
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('status', '=', 'active')
            ->orderBy(['age' => 'asc'])
            ->rows(2, 0);

        // 验证结果
        $this->assertIsArray($users);
        $this->assertEquals(2, count($users));
        foreach ($users as $user) {
            $this->assertEquals('active', $user->status);
        }
    }

    /**
     * 测试链式调用 - 计数查询
     */
    public function testChainedCallsCount()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试计数链式调用
        $count = $this->model
            ->where('age', '>', 30)
            ->where('status', '=', 'active')
            ->count();

        // 验证结果
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * 测试链式调用 - 复杂排序
     */
    public function testChainedCallsComplexOrderBy()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试复杂排序链式调用
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('status', '=', 'active')
            ->orderBy(['age' => 'desc', 'name' => 'asc'])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
    }

    /**
     * 测试链式调用 - 组合查询
     */
    public function testChainedCallsCombined()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试组合链式调用
        $user = $this->model
            ->select(['id', 'name', 'email'])
            ->where('age', '>', 25)
            ->where('status', '=', 'active')
            ->orderBy(['age' => 'desc'])
            ->first();

        // 验证结果
        $this->assertNotNull($user);
        $this->assertTrue(property_exists($user, 'id'));
        $this->assertTrue(property_exists($user, 'name'));
        $this->assertTrue(property_exists($user, 'email'));
    }

    /**
     * 测试 where 方法 - 复杂条件
     */
    public function testWhereWithComplexConditions()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试复杂 where 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('age', '>=', 25)
            ->where('age', '<=', 40)
            ->where('status', '=', 'active')
            ->where('name', 'like', '%a%')
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(40, $user->age);
            $this->assertEquals('active', $user->status);
            $this->assertStringContainsString('a', strtolower($user->name));
        }
    }

    /**
     * 测试 where 方法 - 数组条件
     */
    public function testWhereWithArrayConditions()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试数组形式的 where 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where(['age', '>', 30])
            ->where(['status', '=', 'active'])
            ->where(['name', 'like', '%e%'])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertGreaterThan(30, $user->age);
            $this->assertEquals('active', $user->status);
            $this->assertStringContainsString('e', strtolower($user->name));
        }
    }

    /**
     * 测试 where 方法 - 多条件逻辑
     */
    public function testWhereWithLogicalConditions()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试带有逻辑连接符的 where 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where(['age', '>', 30, 'or'])
            ->where(['status', '=', 'inactive'])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertTrue($user->age > 30 || $user->status == 'inactive');
        }
    }

    /**
     * 测试 where 方法 - IN 条件
     */
    public function testWhereWithInCondition()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 IN 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('age', 'in', [25, 30, 35])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertContains($user->age, [25, 30, 35]);
        }
    }

    /**
     * 测试 where 方法 - BETWEEN 条件
     */
    public function testWhereWithBetweenCondition()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 BETWEEN 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('age', 'between', [25, 35])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(35, $user->age);
        }
    }

    /**
     * 测试 where 方法 - NOT 条件
     */
    public function testWhereWithNotCondition()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试 NOT 条件
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('status', '<>', 'active')
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertEquals('inactive', $user->status);
        }
    }

    /**
     * 测试 where 方法 - 多次调用 with 一位数组参数
     */
    public function testWhereWithMultipleArrayParameters()
    {
        // 插入测试数据
        $this->insertTestData();

        // 测试多次调用 where 方法，每次使用一位数组参数
        $users = $this->model
            ->select(['id', 'name', 'email', 'age', 'status'])
            ->where('age', '>=', 25, 'and')
            ->where(['age', '<=', 40],['status', '=', 'active', 'and'], ['name', 'like', '%a%'])
            ->all();

        // 验证结果
        $this->assertIsArray($users);
        foreach ($users as $user) {
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(40, $user->age);
            $this->assertEquals('active', $user->status);
            $this->assertStringContainsString('a', strtolower($user->name));
        }
    }

    /**
     * 测试边界情况 - 空数据插入
     */
    public function testInsertWithEmptyData()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Insert Data Cannot Be Empty Or Not Array.');

        $this->model->insert([]);
    }

    /**
     * 测试边界情况 - 空数据更新
     */
    public function testUpdateWithEmptyData()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Data Cannot Be Empty.');

        // 先插入一条记录
        $id = $this->model->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 25,
            'status' => 'active'
        ]);

        $this->model->update([], $id);
    }

    /**
     * 测试边界情况 - 空条件删除
     */
    public function testDeleteWithEmptyConditions()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Delete Condition Cannot Be Empty.');

        $this->model->delete();
    }

    /**
     * 测试边界情况 - 空条件更新
     */
    public function testUpdateWithEmptyConditions()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Update Condition Cannot Be Empty.');

        $this->model->update(['name' => 'Test']);
    }
}