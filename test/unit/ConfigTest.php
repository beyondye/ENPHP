<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Config;

class ConfigTest extends TestCase
{
    private string $testConfigDir;

    protected function setUp(): void
    {
        // 创建临时测试配置目录
        $this->testConfigDir = sys_get_temp_dir() . '/enphp_config_test';
        if (!is_dir($this->testConfigDir)) {
            mkdir($this->testConfigDir, 0755, true);
        }

        // 清空配置
        Config::flush();
    }

    protected function tearDown(): void
    {
        // 清理临时文件
        if (is_dir($this->testConfigDir)) {
            $files = glob($this->testConfigDir . '/*.php');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->testConfigDir);
        }

        // 清空配置
        Config::flush();
    }

    /**
     * 测试初始化配置 - 正常情况
     */
    public function testInitWithValidConfigDir()
    {
        // 创建测试配置文件
        $configContent1 = <<<'PHP'
<?php
return [
    'host' => 'localhost',
    'port' => 3306
];
PHP;
        file_put_contents($this->testConfigDir . '/database.php', $configContent1);

        $configContent2 = <<<'PHP'
<?php
return [
    'name' => 'ENPHP',
    'version' => '1.0.0'
];
PHP;
        file_put_contents($this->testConfigDir . '/app.php', $configContent2);

        // 初始化配置
        Config::init($this->testConfigDir);

        // 验证配置是否正确加载
        $this->assertEquals('localhost', Config::get('database.host'));
        $this->assertEquals(3306, Config::get('database.port'));
        $this->assertEquals('ENPHP', Config::get('app.name'));
        $this->assertEquals('1.0.0', Config::get('app.version'));
    }

    /**
     * 测试初始化配置 - 配置目录不存在
     */
    public function testInitWithInvalidConfigDir()
    {
        $this->expectException(Exception::class);
        Config::init('/non/existent/directory');
    }

    /**
     * 测试初始化配置 - 空配置目录
     */
    public function testInitWithEmptyConfigDir()
    {
        // 初始化空配置目录
        Config::init($this->testConfigDir);

        // 验证配置是否为空
        $this->assertNull(Config::get('test'));
    }

    /**
     * 测试初始化配置 - 包含非数组返回值的文件
     */
    public function testInitWithNonArrayConfigFile()
    {
        // 创建非数组返回值的配置文件
        $configContent = <<<'PHP'
<?php
return 'string value';
PHP;
        file_put_contents($this->testConfigDir . '/invalid.php', $configContent);

        // 初始化配置
        Config::init($this->testConfigDir);

        // 验证配置是否为空（非数组文件应该被忽略）
        $this->assertNull(Config::get('invalid'));
    }

    /**
     * 测试获取配置 - 存在的配置
     */
    public function testGetExistingConfig()
    {
        // 设置测试配置
        Config::set('test', 'value');

        // 获取配置
        $result = Config::get('test');

        // 验证结果
        $this->assertEquals('value', $result);
    }

    /**
     * 测试获取配置 - 不存在的配置
     */
    public function testGetNonExistingConfig()
    {
        // 获取不存在的配置
        $result = Config::get('non_existent');

        // 验证结果
        $this->assertNull($result);
    }

    /**
     * 测试获取配置 - 不存在的配置带默认值
     */
    public function testGetNonExistingConfigWithDefault()
    {
        // 获取不存在的配置，带默认值
        $result = Config::get('non_existent', 'default_value');

        // 验证结果
        $this->assertEquals('default_value', $result);
    }

    /**
     * 测试获取配置 - 嵌套配置
     */
    public function testGetNestedConfig()
    {
        // 设置测试配置
        Config::set('database', [
            'host' => 'localhost',
            'port' => 3306,
            'credentials' => [
                'username' => 'root',
                'password' => 'password'
            ]
        ]);

        // 获取嵌套配置
        $host = Config::get('database.host');
        $port = Config::get('database.port');
        $username = Config::get('database.credentials.username');
        $password = Config::get('database.credentials.password');
        $nonExistent = Config::get('database.credentials.non_existent', 'default');

        // 验证结果
        $this->assertEquals('localhost', $host);
        $this->assertEquals(3306, $port);
        $this->assertEquals('root', $username);
        $this->assertEquals('password', $password);
        $this->assertEquals('default', $nonExistent);
    }

    /**
     * 测试清空配置
     */
    public function testFlush()
    {
        // 设置测试配置
        Config::set('test', 'value');
        $this->assertEquals('value', Config::get('test'));

        // 清空配置
        Config::flush();

        // 验证配置是否已清空
        $this->assertNull(Config::get('test'));
    }

    /**
     * 测试设置配置
     */
    public function testSet()
    {
        // 设置配置
        Config::set('test', 'value');

        // 验证配置是否已设置
        $this->assertEquals('value', Config::get('test'));

        // 更新配置
        Config::set('test', 'new_value');
        $this->assertEquals('new_value', Config::get('test'));
    }

    /**
     * 测试设置和获取复杂配置
     */
    public function testSetAndGetComplexConfig()
    {
        // 设置复杂配置
        Config::set('app', [
            'name' => 'ENPHP',
            'version' => '1.0.0',
            'debug' => true,
            'paths' => [
                'base' => '/var/www/enphp',
                'app' => '/var/www/enphp/application',
                'system' => '/var/www/enphp/system'
            ]
        ]);

        // 获取配置
        $appName = Config::get('app.name');
        $appVersion = Config::get('app.version');
        $appDebug = Config::get('app.debug');
        $basePath = Config::get('app.paths.base');
        $appPath = Config::get('app.paths.app');
        $systemPath = Config::get('app.paths.system');
        $nonExistent = Config::get('app.paths.non_existent', '/default/path');

        // 验证结果
        $this->assertEquals('ENPHP', $appName);
        $this->assertEquals('1.0.0', $appVersion);
        $this->assertTrue($appDebug);
        $this->assertEquals('/var/www/enphp', $basePath);
        $this->assertEquals('/var/www/enphp/application', $appPath);
        $this->assertEquals('/var/www/enphp/system', $systemPath);
        $this->assertEquals('/default/path', $nonExistent);
    }
}