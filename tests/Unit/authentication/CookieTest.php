<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Authentication\Cookie;
use System\Authentication\AuthenticationException;

class AuthenticationCookieTest extends TestCase
{
    /**
     * 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'name' => 'auth_cookie',
            'secret' => 'test_secret_key_123',
            'expires' => 3600
        ];
    }

    /**
     * 测试构造函数
     */
    public function testConstructor()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $this->assertInstanceOf(Cookie::class, $cookie);
    }

    /**
     * 测试 check() - Cookie 不存在时抛出异常
     */
    public function testCheckThrowsExceptionWhenCookieNotSet()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        // 清除 $_COOKIE
        unset($_COOKIE[$config['name']]);
        
        $this->expectException(AuthenticationException::class);
        $cookie->check();
    }

    /**
     * 测试 check() - Cookie 格式不正确（只有 1 部分）
     */
    public function testCheckThrowsExceptionWhenCookieFormatInvalid()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        // 设置格式不正确的 cookie
        $_COOKIE[$config['name']] = 'invalid_cookie';
        
        $this->expectException(AuthenticationException::class);
        $cookie->check();
    }

    /**
     * 测试 check() - Cookie 格式不正确（超过 2 部分）
     */
    public function testCheckThrowsExceptionWhenCookieHasMoreParts()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $_COOKIE[$config['name']] = 'part1.part2.part3';
        
        $this->expectException(AuthenticationException::class);
        $cookie->check();
    }

    /**
     * 测试 check() - 签名验证失败时抛出异常
     */
    public function testCheckThrowsExceptionWhenSignatureInvalid()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $payload = base64_encode(json_encode(['id' => 'test_id', 'exp' => time() + 3600, 'data' => []]));
        $_COOKIE[$config['name']] = $payload . '.invalid_signature';
        
        $this->expectException(AuthenticationException::class);
        $cookie->check();
    }

    /**
     * 测试 check() - Cookie 过期时抛出异常
     */
    public function testCheckThrowsExceptionWhenCookieExpired()
    {
        $config = $this->getDefaultConfig();
        $config['expire'] = 3600; // 启用过期检查
        $cookie = new Cookie($config);
        
        // 创建已过期的 payload
        $payload = base64_encode(json_encode(['id' => 'test_id', 'exp' => time() - 3600, 'data' => []]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $this->expectException(AuthenticationException::class);
        $cookie->check();
    }

    /**
     * 测试 check() - Cookie 过期但 expire 为 0（不检查过期）
     */
    public function testCheckDoesNotCheckExpireWhenExpireIsZero()
    {
        $config = $this->getDefaultConfig();
        $config['expires'] = 0; // 禁用过期检查
        $cookie = new Cookie($config);
        
        // 创建已过期的 payload，但 expire=0 不检查过期
        $payload = base64_encode(json_encode(['id' => 'test_id', 'exp' => time() - 3600, 'data' => []]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $result = $cookie->check();
        
        $this->assertTrue($result);
        $this->assertEquals('test_id', $cookie->id());
    }

    /**
     * 测试 check() - 签名验证成功
     */
    public function testCheckReturnsTrueWhenValid()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $cookieId = 'test_cookie_id';
        $expire = time() + 3600;
        $payload = base64_encode(json_encode(['id' => $cookieId, 'exp' => $expire, 'data' => ['user_id' => 1]]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $result = $cookie->check();
        
        $this->assertTrue($result);
        $this->assertEquals($cookieId, $cookie->id());
    }

    /**
     * 测试 data() - _data 未设置时返回 null
     */
    public function testDataReturnsNullWhenNotSet()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $result = $cookie->data();
        
        $this->assertNull($result);
    }

    /**
     * 测试 data() - 返回数组格式（$assoc = true）
     */
    public function testDataReturnsArrayFormat()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $cookieId = 'test_id';
        $payload = base64_encode(json_encode(['id' => $cookieId, 'exp' => time() + 3600, 'data' => ['user_id' => 1, 'username' => 'admin']]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $cookie->check();
        
        $result = $cookie->data(true);
        
        $this->assertIsArray($result);
        $this->assertEquals(['user_id' => 1, 'username' => 'admin'], $result);
    }

    /**
     * 测试 data() - 返回对象格式（$assoc = false）
     */
    public function testDataReturnsObjectFormat()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $cookieId = 'test_id';
        $payload = base64_encode(json_encode(['id' => $cookieId, 'exp' => time() + 3600, 'data' => ['user_id' => 2]]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $cookie->check();
        
        $result = $cookie->data();
        
        $this->assertIsObject($result);
        $this->assertEquals(2, $result->user_id);
    }

    /**
     * 测试 data() - 显式调用 $assoc = false
     */
    public function testDataWithExplicitFalseAssoc()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $cookieId = 'test_id';
        $payload = base64_encode(json_encode(['id' => $cookieId, 'exp' => time() + 3600, 'data' => ['user_id' => 3]]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $cookie->check();
        
        $result = $cookie->data(false);
        
        $this->assertIsObject($result);
        $this->assertEquals(3, $result->user_id);
    }

    /**
     * 测试 id() - 返回正确的 cookie id
     */
    public function testIdReturnsCookieId()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $cookieId = 'unique_test_id_123';
        $payload = base64_encode(json_encode(['id' => $cookieId, 'exp' => time() + 3600, 'data' => []]));
        $signature = hash_hmac('sha256', $payload, $config['secret']);
        $_COOKIE[$config['name']] = $payload . '.' . $signature;
        
        $cookie->check();
        
        $this->assertEquals($cookieId, $cookie->id());
    }

    /**
     * 测试 create() - expire > 0 时设置过期时间
     */
    public function testCreateWithExpire()
    {
        $config = $this->getDefaultConfig();
        $config['expires'] = 3600;
        $cookie = new Cookie($config);
        
        $result = $cookie->create(['user_id' => 1]);
        
        // 验证返回值格式正确
        $parts = explode('.', $result);
        $this->assertEquals(2, count($parts));
        
        // 验证签名正确
        $payload = $parts[0];
        $signature = $parts[1];
        $this->assertEquals($signature, hash_hmac('sha256', $payload, $config['secret']));
        
        // 验证数据正确
        $decoded = json_decode(base64_decode($payload));
        $this->assertNotNull($decoded->id);
        $this->assertEquals(time() + 3600, $decoded->exp, '', 1);
        $this->assertEquals(['user_id' => 1], (array) $decoded->data);
    }

    /**
     * 测试 create() - expire <= 0 时过期时间为 0
     */
    public function testCreateWithoutExpire()
    {
        $config = $this->getDefaultConfig();
        $config['expires'] = 0;
        $cookie = new Cookie($config);
        
        $result = $cookie->create();
        
        $parts = explode('.', $result);
        $payload = $parts[0];
        
        $decoded = json_decode(base64_decode($payload));
        $this->assertEquals(0, $decoded->exp);
    }

    /**
     * 测试 create() - 空数据参数
     */
    public function testCreateWithEmptyData()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $result = $cookie->create();
        
        $parts = explode('.', $result);
        $payload = $parts[0];
        
        $decoded = json_decode(base64_decode($payload));
        $this->assertEquals([], (array) $decoded->data);
    }

    /**
     * 测试 remove() - 返回布尔值
     */
    public function testRemoveReturnsBoolean()
    {
        $config = $this->getDefaultConfig();
        $cookie = new Cookie($config);
        
        $result = $cookie->remove();
        
        $this->assertIsBool($result);
    }
}