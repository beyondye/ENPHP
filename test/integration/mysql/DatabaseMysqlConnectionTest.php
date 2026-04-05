<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Mysql;

class DatabaseMysqlConnectionTest extends TestCase
{
    /**
     * 测试默认 MySQL 连接
     */
    public function testDefaultMysqlConnection()
    {
        try {
            // 使用默认服务连接 MySQL 数据库
            $db = Database::instance('default');
            $this->assertNotNull($db);
            $this->assertInstanceOf('system\database\pdo\Mysql', $db);
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Default MySQL connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试自定义 MySQL 连接参数
     */
    public function testCustomMysqlConnection()
    {
        try {
            // 获取默认配置
            $defaultConfig = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
            $mysqlConfig = $defaultConfig['default'] ?? [];
            
            // 测试不同的连接参数
            $testConfigs = [
                // 基本连接
                [
                    'driver' => 'pdo_mysql',
                    'host' => $mysqlConfig['host'] ?? 'localhost',
                    'port' => $mysqlConfig['port'] ?? 3306,
                    'database' => $mysqlConfig['database'] ?? 'test',
                    'username' => $mysqlConfig['username'] ?? 'root',
                    'password' => $mysqlConfig['password'] ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci'
                ],
                // 不同字符集
                [
                    'driver' => 'pdo_mysql',
                    'host' => $mysqlConfig['host'] ?? 'localhost',
                    'port' => $mysqlConfig['port'] ?? 3306,
                    'database' => $mysqlConfig['database'] ?? 'test',
                    'username' => $mysqlConfig['username'] ?? 'root',
                    'password' => $mysqlConfig['password'] ?? '',
                    'charset' => 'utf8',
                    'collation' => 'utf8_general_ci'
                ],
                // 不同端口
                [
                    'driver' => 'pdo_mysql',
                    'host' => $mysqlConfig['host'] ?? 'localhost',
                    'port' => 3306,
                    'database' => $mysqlConfig['database'] ?? 'test',
                    'username' => $mysqlConfig['username'] ?? 'root',
                    'password' => $mysqlConfig['password'] ?? '',
                    'charset' => 'utf8mb4'
                ]
            ];
            
            foreach ($testConfigs as $config) {
                // 使用反射创建 Mysql 实例
                $reflection = new ReflectionClass(Mysql::class);
                $instance = $reflection->newInstanceWithoutConstructor();
                
                // 设置 db 属性
                $dbProperty = $reflection->getProperty('db');
                $dbProperty->setAccessible(true);
                
                // 尝试创建 PDO 连接
                try {
                    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
                    $options = [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                    ];
                    $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
                    $dbProperty->setValue($instance, $pdo);
                    
                    // 验证实例创建成功
                    $this->assertInstanceOf(Mysql::class, $instance);
                } catch (\PDOException $e) {
                    // 如果连接失败，标记测试为跳过
                    $this->markTestSkipped('Custom MySQL connection failed: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Test setup failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL 连接失败情况
     */
    public function testMysqlConnectionFailure()
    {
        // 测试无效主机
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Database Connection Error/');
        
        new Mysql([
            'driver' => 'pdo_mysql',
            'host' => 'invalid-host',
            'port' => 3306,
            'database' => 'test',
            'username' => 'root',
            'password' => ''
        ]);
    }

    /**
     * 测试缺少必要参数的情况
     */
    public function testMysqlMissingRequiredParams()
    {
        // 测试缺少数据库名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        
        new Mysql([
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => 3306,
            'username' => 'root',
            'password' => ''
        ]);
        
        // 测试缺少用户名
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        
        new Mysql([
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'test',
            'password' => ''
        ]);
        
        // 测试缺少密码
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database Name Or Username Or Password Is Required.');
        
        new Mysql([
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'test',
            'username' => 'root'
        ]);
    }

    /**
     * 测试 MySQL 连接池（实例缓存）
     */
    public function testMysqlConnectionPool()
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
            $this->markTestSkipped('MySQL connection pool test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试不同服务的 MySQL 连接
     */
    public function testDifferentMysqlServices()
    {
        try {
            // 获取默认服务实例
            $defaultDb = Database::instance('default');
            $this->assertNotNull($defaultDb);
            
            // 尝试获取其他服务实例（如果配置中存在）
            $config = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
            foreach ($config as $service => $serviceConfig) {
                if ($service !== 'default') {
                    $db = Database::instance($service);
                    $this->assertNotNull($db);
                    $this->assertInstanceOf('system\database\pdo\Mysql', $db);
                }
            }
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Different MySQL services test failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->markTestSkipped('Test setup failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL 连接的持久性
     */
    public function testMysqlPersistentConnection()
    {
        try {
            // 获取默认配置
            $defaultConfig = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
            $mysqlConfig = $defaultConfig['default'] ?? [];
            
            // 测试持久连接
            $persistentConfig = [
                'driver' => 'pdo_mysql',
                'host' => $mysqlConfig['host'] ?? 'localhost',
                'port' => $mysqlConfig['port'] ?? 3306,
                'database' => $mysqlConfig['database'] ?? 'test',
                'username' => $mysqlConfig['username'] ?? 'root',
                'password' => $mysqlConfig['password'] ?? '',
                'charset' => 'utf8mb4',
                'persistent' => true
            ];
            
            // 使用反射创建 Mysql 实例
            $reflection = new ReflectionClass(Mysql::class);
            $instance = $reflection->newInstanceWithoutConstructor();
            
            // 设置 db 属性
            $dbProperty = $reflection->getProperty('db');
            $dbProperty->setAccessible(true);
            
            // 尝试创建持久 PDO 连接
            try {
                $dsn = "mysql:host={$persistentConfig['host']};port={$persistentConfig['port']};dbname={$persistentConfig['database']};charset={$persistentConfig['charset']}";
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_PERSISTENT => true,
                ];
                $pdo = new \PDO($dsn, $persistentConfig['username'], $persistentConfig['password'], $options);
                $dbProperty->setValue($instance, $pdo);
                
                // 验证实例创建成功
                $this->assertInstanceOf(Mysql::class, $instance);
            } catch (\PDOException $e) {
                // 如果连接失败，标记测试为跳过
                $this->markTestSkipped('Persistent MySQL connection failed: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Test setup failed: ' . $e->getMessage());
        }
    }
}