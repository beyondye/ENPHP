<?php

declare(strict_types=1);

namespace system\tests\authentication;

use PHPUnit\Framework\TestCase;
use system\authentication\Session;
use system\authentication\AuthenticationException;

class SessionTest extends TestCase
{
    /**
     * 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'name' => 'auth_session'
        ];
    }

    /**
     * 测试构造函数
     */
    public function testConstructor()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->assertInstanceOf(Session::class, $session);
    }

    /**
     * 测试 check() - Session 不存在时抛出异常
     */
    public function testCheckThrowsExceptionWhenSessionNull()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 确保 Session::get 返回 null
        $this->mockSessionGet($config['name'], null);
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - Session 数据不是有效 JSON 时抛出异常
     */
    public function testCheckThrowsExceptionWhenJsonInvalid()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 设置无效的 JSON 数据
        $this->mockSessionGet($config['name'], 'invalid_json');
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - 缺少 status 字段时抛出异常
     */
    public function testCheckThrowsExceptionWhenStatusMissing()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 设置缺少 status 字段的 JSON
        $this->mockSessionGet($config['name'], json_encode(['data' => []]));
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - 缺少 data 字段时抛出异常
     */
    public function testCheckThrowsExceptionWhenDataMissing()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 设置缺少 data 字段的 JSON
        $this->mockSessionGet($config['name'], json_encode(['status' => 'ok']));
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - data 字段为 null 时抛出异常
     */
    public function testCheckThrowsExceptionWhenDataIsNull()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 设置 data 字段为 null
        $this->mockSessionGet($config['name'], json_encode(['status' => 'ok', 'data' => null]));
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - status 不为 'ok' 时抛出异常
     */
    public function testCheckThrowsExceptionWhenStatusNotOk()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 设置 status 不为 'ok'
        $this->mockSessionGet($config['name'], json_encode(['status' => 'invalid', 'data' => []]));
        
        $this->expectException(AuthenticationException::class);
        $session->check();
    }

    /**
     * 测试 check() - 验证成功
     */
    public function testCheckReturnsTrueWhenValid()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        // 确保会话已启动
        $this->ensureSessionStarted();
        
        // 设置有效数据
        $validSession = json_encode(['status' => 'ok', 'data' => ['user_id' => 1]]);
        $this->mockSessionGet($config['name'], $validSession);
        
        $result = $session->check();
        
        $this->assertTrue($result);
        $this->assertEquals(session_id(), $session->id());
    }

    /**
     * 测试 data() - _data 未设置时返回 null
     */
    public function testDataReturnsNullWhenNotSet()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $result = $session->data();
        
        $this->assertNull($result);
    }

    /**
     * 测试 data() - 返回数组格式（$assoc = true）
     */
    public function testDataReturnsArrayFormat()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        
        $validSession = json_encode(['status' => 'ok', 'data' => ['user_id' => 1, 'username' => 'admin']]);
        $this->mockSessionGet($config['name'], $validSession);
        
        $session->check();
        
        $result = $session->data(true);
        
        $this->assertIsArray($result);
        $this->assertEquals(['user_id' => 1, 'username' => 'admin'], $result);
    }

    /**
     * 测试 data() - 返回对象格式（$assoc = false）
     */
    public function testDataReturnsObjectFormat()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        
        $validSession = json_encode(['status' => 'ok', 'data' => ['user_id' => 2]]);
        $this->mockSessionGet($config['name'], $validSession);
        
        $session->check();
        
        $result = $session->data();
        
        $this->assertIsObject($result);
        $this->assertEquals(2, $result->user_id);
    }

    /**
     * 测试 data() - 显式调用 $assoc = false
     */
    public function testDataWithExplicitFalseAssoc()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        
        $validSession = json_encode(['status' => 'ok', 'data' => ['user_id' => 3]]);
        $this->mockSessionGet($config['name'], $validSession);
        
        $session->check();
        
        $result = $session->data(false);
        
        $this->assertIsObject($result);
        $this->assertEquals(3, $result->user_id);
    }

    /**
     * 测试 id() - 返回正确的 session id
     */
    public function testIdReturnsSessionId()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        $expectedId = session_id();
        
        $validSession = json_encode(['status' => 'ok', 'data' => []]);
        $this->mockSessionGet($config['name'], $validSession);
        
        $session->check();
        
        $this->assertEquals($expectedId, $session->id());
    }

    /**
     * 测试 create() - 创建认证数据并返回 session id
     */
    public function testCreate()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        $expectedId = session_id();
        
        $this->mockSessionSet($config['name']);
        
        $result = $session->create(['user_id' => 1, 'role' => 'admin']);
        
        $this->assertEquals($expectedId, $result);
        $this->assertEquals($expectedId, $session->id());
    }

    /**
     * 测试 create() - 空数据参数
     */
    public function testCreateWithEmptyData()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->ensureSessionStarted();
        
        $this->mockSessionSet($config['name']);
        
        $result = $session->create();
        
        $this->assertEquals(session_id(), $result);
    }

    /**
     * 测试 remove() - 返回 true
     */
    public function testRemoveReturnsTrue()
    {
        $config = $this->getDefaultConfig();
        $session = new Session($config);
        
        $this->mockSessionDelete($config['name']);
        
        $result = $session->remove();
        
        $this->assertTrue($result);
    }

    /**
     * 确保会话已启动
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * 模拟 Session::get 方法
     */
    private function mockSessionGet(string $key, $returnValue): void
    {
        // 使用全局变量模拟
        $GLOBALS['_SESSION_DATA'][$key] = $returnValue;
        
        // 如果 system\Session 使用 $_SESSION
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
        if ($returnValue === null) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $returnValue;
        }
    }

    /**
     * 模拟 Session::set 方法
     */
    private function mockSessionSet(string $key): void
    {
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    /**
     * 模拟 Session::delete 方法
     */
    private function mockSessionDelete(string $key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}