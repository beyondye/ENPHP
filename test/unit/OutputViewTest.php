<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Output;

class OutputViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 确保 fixtures 目录存在
        $fixturesDir = __DIR__ . '/../fixtures/';
        if (!is_dir($fixturesDir)) {
            mkdir($fixturesDir, 0755, true);
        }
        
        // 定义常量（如果未定义）
        if (!defined('TEMPLATE_DIR')) {
            define('TEMPLATE_DIR', $fixturesDir);
        }
        if (!defined('EXT')) {
            define('EXT', '.php');
        }
        if (!defined('CHARSET')) {
            define('CHARSET', 'UTF-8');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 清理测试文件
        $testFiles = [
            TEMPLATE_DIR . 'test.php',
            TEMPLATE_DIR . 'test_compress.php',
            TEMPLATE_DIR . 'test_static.php',
            TEMPLATE_DIR . 'test_empty.php',
            TEMPLATE_DIR . 'test_buffer.php',
            TEMPLATE_DIR . 'test_coverage.php',
            TEMPLATE_DIR . 'test_view.php',
            TEMPLATE_DIR . 'test_view_compress.php'
        ];
        
        foreach ($testFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * 测试 view 方法 - 基本功能（直接输出）
     */
    public function testViewBasic()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, <?php echo $name; ?>!';
        file_put_contents(TEMPLATE_DIR . 'test.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 捕获输出
        ob_start();
        try {
            Output::view('test', ['name' => 'World']);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('Hello, World!', $output);
    }

    /**
     * 测试 view 方法 - 返回内容
     */
    public function testViewReturn()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, <?php echo $name; ?>!';
        file_put_contents(TEMPLATE_DIR . 'test.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 测试返回内容
        $result = Output::view('test', ['name' => 'World'], true);
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('Hello, World!', $result);
    }

    /**
     * 测试 view 方法 - 压缩内容
     */
    public function testViewCompress()
    {
        // 创建测试模板文件（包含多余空白字符和注释）
        $templateContent = "
            <div>
                <!-- This is a comment -->
                <p>Hello, <?php echo \$name; ?>!</p>
            </div>
        ";
        file_put_contents(TEMPLATE_DIR . 'test_compress.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 测试返回压缩内容
        $result = Output::view('test_compress', ['name' => 'World'], true, true);
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('<div><p>Hello, World!</p></div>', $result);
    }

    /**
     * 测试 view 方法 - 静态变量累积
     */
    public function testViewStaticVars()
    {
        // 创建测试模板文件，使用 isset 检查变量是否存在
        $templateContent = 'Hello, <?php echo isset($name) ? $name : ""; ?>! Your age is <?php echo isset($age) ? $age : ""; ?>.';
        file_put_contents(TEMPLATE_DIR . 'test_static.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 第一次调用，设置 name 变量
        $result1 = Output::view('test_static', ['name' => 'World'], true);
        // 第二次调用，设置 age 变量，应该保留 name 变量
        $result2 = Output::view('test_static', ['age' => 30], true);
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('Hello, World! Your age is .', $result1);
        $this->assertEquals('Hello, World! Your age is 30.', $result2);
    }

    /**
     * 测试 view 方法 - 空数据
     */
    public function testViewEmptyData()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, World!';
        file_put_contents(TEMPLATE_DIR . 'test_empty.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 测试空数据
        $result = Output::view('test_empty', [], true);
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('Hello, World!', $result);
    }

    /**
     * 测试 view 方法 - 输出缓冲级别
     */
    public function testViewOutputBufferLevel()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, World!';
        file_put_contents(TEMPLATE_DIR . 'test_buffer.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 测试直接输出（无额外缓冲）
        ob_start();
        try {
            Output::view('test_buffer');
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('Hello, World!', $output);
    }

    /**
     * 测试 view 方法 - 输出缓冲级别大于 2 的情况
     * 覆盖第 147-154 行代码
     */
    public function testViewOutputBufferLevelGreaterThanTwo()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, World!';
        file_put_contents(TEMPLATE_DIR . 'test_coverage.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 开启足够的缓冲级别，确保 Output::view 内部的 ob_get_level() > 2
        for ($i = 0; $i < 3; $i++) {
            ob_start();
        }
        
        try {
            // 调用 view 方法，应该触发 ob_end_flush() 路径
            Output::view('test_coverage');
        } finally {
            // 确保清理所有我们创建的缓冲
            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
    }

    /**
     * 测试 view 方法 - 输出缓冲级别小于等于 2 的情况
     * 覆盖第 147-154 行代码
     */
    public function testViewOutputBufferLevelLessThanOrEqualToTwo()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, World!';
        file_put_contents(TEMPLATE_DIR . 'test_coverage.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 开启一级缓冲
        ob_start();
        
        try {
            // 调用 view 方法，应该触发 ob_get_contents() 和 ob_end_clean() 路径
            Output::view('test_coverage');
        } finally {
            // 确保清理所有我们创建的缓冲
            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
    }

    /**
     * 测试 view 方法 - 输出缓冲级别小于等于 2 且启用压缩的情况
     * 覆盖第 147-154 行代码
     */
    public function testViewOutputBufferLevelLessThanOrEqualToTwoWithCompress()
    {
        // 创建测试模板文件（包含多余空白字符）
        $templateContent = "
            <div>
                <p>Hello, World!</p>
            </div>
        ";
        file_put_contents(TEMPLATE_DIR . 'test_coverage.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 测试返回压缩内容（更可靠的测试方式）
        $result = Output::view('test_coverage', [], true, true);
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        $this->assertEquals('<div><p>Hello, World!</p></div>', $result);
    }

    /**
     * 测试 view 方法 - 直接输出并启用压缩
     */
    public function testViewOutputWithCompress()
    {
        // 创建测试模板文件（包含多余空白字符）
        $templateContent = "
            <div>
                <p>Hello, World!</p>
            </div>
        ";
        file_put_contents(TEMPLATE_DIR . 'test_view_compress.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 捕获输出
        ob_start();
        try {
            // 调用 view 方法，启用压缩
            Output::view('test_view_compress', [], false, true);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
        // 验证输出内容
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Hello, World!', $output);
    }

    /**
     * 测试 view 方法 - 两级输出缓冲
     */
    public function testViewWithTwoOutputBuffers()
    {
        // 创建测试模板文件
        $templateContent = 'Hello, World!';
        file_put_contents(TEMPLATE_DIR . 'test_view.php', $templateContent);
        
        // 记录初始缓冲级别
        $initialBufferLevel = ob_get_level();
        
        // 开启两级输出缓冲
        ob_start();
        ob_start();
        
        try {
            // 调用 view 方法
            Output::view('test_view');
        } finally {
            // 确保清理所有我们创建的缓冲
            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }
        }
        
        // 验证缓冲级别恢复正常
        $this->assertEquals($initialBufferLevel, ob_get_level());
    }
}