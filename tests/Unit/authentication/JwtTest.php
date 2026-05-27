<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Authentication\Jwt;
use System\Authentication\AuthenticationException;

class JwtTest extends TestCase
{
    /**
     * 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'mode' => 'url',
            'name' => 'auth_token',
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
        $jwt = new Jwt($config);
        
        // 验证对象创建成功
        $this->assertInstanceOf(Jwt::class, $jwt);
    }

    /**
     * 测试 check() - header 模式下 JWT 不存在
     */
    public function testCheckThrowsExceptionWhenJwtNullInHeaderMode()
    {
        $config = $this->getDefaultConfig();
        $config['mode'] = 'header';
        $config['name'] = 'Authorization';
        
        unset($_SERVER['HTTP_AUTHORIZATION']);
        
        $jwt = new Jwt($config);
        
        $this->expectException(AuthenticationException::class);
        $jwt->check();
    }

    /**
     * 测试 check() - url 模式下 JWT 不存在
     */
    public function testCheckThrowsExceptionWhenJwtNullInUrlMode()
    {
        $config = $this->getDefaultConfig();
        $config['mode'] = 'url';
        $config['name'] = 'token';
        
        unset($_GET['token']);
        
        $jwt = new Jwt($config);
        
        $this->expectException(AuthenticationException::class);
        $jwt->check();
    }

    /**
     * 测试 check() - JWT 格式不正确（不是 3 部分）
     */
    public function testCheckThrowsExceptionWhenJwtFormatInvalid()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $_GET['token'] = 'invalid.jwt';  // 只有 2 部分
        
        $jwt = new Jwt($config);
        
        $this->expectException(AuthenticationException::class);
        $jwt->check();
    }

    /**
     * 测试 check() - JWT 签名验证失败
     */
    public function testCheckThrowsExceptionWhenSignatureInvalid()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test', 'exp' => time() + 3600, 'data' => []]));
        $invalidSignature = 'invalid_signature';
        
        $_GET['token'] = "$header.$payload.$invalidSignature";
        
        $jwt = new Jwt($config);
        
        $this->expectException(AuthenticationException::class);
        $jwt->check();
    }

    /**
     * 测试 check() - JWT 过期
     */
    public function testCheckThrowsExceptionWhenJwtExpired()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test', 'exp' => time() - 3600, 'data' => []]));
        $signature = hash_hmac('sha256', "$header.$payload", $config['secret']);
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        
        $this->expectException(AuthenticationException::class);
        $jwt->check();
    }

    /**
     * 测试 check() - header 模式验证成功
     */
    public function testCheckReturnsTrueInHeaderMode()
    {
        $config = $this->getDefaultConfig();
        $config['mode'] = 'header';
        $config['name'] = 'AUTH_TOKEN';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test_jti', 'exp' => time() + 3600, 'data' => ['user_id' => 1]]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_SERVER['HTTP_AUTH_TOKEN'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $result = $jwt->check();
        
        $this->assertTrue($result);
        $this->assertEquals('test_jti', $jwt->id());
    }

    /**
     * 测试 check() - url 模式验证成功
     */
    public function testCheckReturnsTrueInUrlMode()
    {
        $config = $this->getDefaultConfig();
        $config['mode'] = 'url';
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test_jti_url', 'exp' => time() + 3600, 'data' => []]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $result = $jwt->check();
        
        $this->assertTrue($result);
        $this->assertEquals('test_jti_url', $jwt->id());
    }

    /**
     * 测试 data() - _data 未设置时返回 null
     */
    public function testDataReturnsNullWhenNotSet()
    {
        $config = $this->getDefaultConfig();
        $jwt = new Jwt($config);
        
        $result = $jwt->data();
        
        $this->assertNull($result);
    }

    /**
     * 测试 data() - 返回数组格式（$assoc = true）
     */
    public function testDataReturnsArrayFormat()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test', 'exp' => time() + 3600, 'data' => ['user_id' => 1, 'username' => 'admin']]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $jwt->check();
        
        $result = $jwt->data(true);
        
        $this->assertIsArray($result);
        $this->assertEquals(['user_id' => 1, 'username' => 'admin'], $result);
    }

    /**
     * 测试 data() - 返回对象格式（$assoc = false）
     */
    public function testDataReturnsObjectFormat()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test', 'exp' => time() + 3600, 'data' => ['user_id' => 2]]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $jwt->check();
        
        $result = $jwt->data();
        
        $this->assertIsObject($result);
        $this->assertEquals(2, $result->user_id);
    }

    /**
     * 测试 data() - 显式调用 $assoc = false
     */
    public function testDataWithExplicitFalseAssoc()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'test', 'exp' => time() + 3600, 'data' => ['user_id' => 3]]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $jwt->check();
        
        $result = $jwt->data(false);
        
        $this->assertIsObject($result);
        $this->assertEquals(3, $result->user_id);
    }

    /**
     * 测试 id() - 返回正确的 jwt id
     */
    public function testIdReturnsJwtId()
    {
        $config = $this->getDefaultConfig();
        $config['name'] = 'token';
        
        $header = $this->base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64urlEncode(json_encode(['jti' => 'unique_jti_123', 'exp' => time() + 3600, 'data' => []]));
        $signature = $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true));
        
        $_GET['token'] = "$header.$payload.$signature";
        
        $jwt = new Jwt($config);
        $jwt->check();
        
        $this->assertEquals('unique_jti_123', $jwt->id());
    }

    /**
     * 测试 create() - 创建 JWT 令牌
     */
    public function testCreateJwtToken()
    {
        $config = $this->getDefaultConfig();
        $config['expire'] = 3600;
        
        $jwt = new Jwt($config);
        $result = $jwt->create(['user_id' => 1, 'role' => 'admin']);
        
        // 验证格式正确（3 部分）
        $parts = explode('.', $result);
        $this->assertEquals(3, count($parts));
        
        // 验证签名正确
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];
        $this->assertEquals($signature, $this->base64urlEncode(hash_hmac('sha256', "$header.$payload", $config['secret'], true)));
        
        // 验证 header 内容
        $decodedHeader = json_decode($this->base64urlDecode($header));
        $this->assertEquals('HS256', $decodedHeader->alg);
        $this->assertEquals('JWT', $decodedHeader->typ);
        
        // 验证 payload 内容
        $decodedPayload = json_decode($this->base64urlDecode($payload));
        $this->assertNotNull($decodedPayload->jti);
        $this->assertEquals(time() + 3600, $decodedPayload->exp, '', 1);
        $this->assertEquals(['user_id' => 1, 'role' => 'admin'], (array) $decodedPayload->data);
    }

    /**
     * 测试 create() - 空数据参数
     */
    public function testCreateWithEmptyData()
    {
        $config = $this->getDefaultConfig();
        $jwt = new Jwt($config);
        $result = $jwt->create();
        
        $parts = explode('.', $result);
        $payload = $parts[1];
        
        $decodedPayload = json_decode($this->base64urlDecode($payload));
        $this->assertEquals([], (array) $decodedPayload->data);
    }

    /**
     * 测试 remove() - 返回 true
     */
    public function testRemoveReturnsTrue()
    {
        $config = $this->getDefaultConfig();
        $jwt = new Jwt($config);
        $result = $jwt->remove();
        
        $this->assertTrue($result);
    }

    /**
     * 测试 base64url_encode() 和 base64url_decode()
     */
    public function testBase64UrlEncoding()
    {
        // 测试特殊字符转换
        $original = 'test+data/with=special';
        $encoded = $this->base64urlEncode($original);
        $decoded = $this->base64urlDecode($encoded);
        
        $this->assertEquals($original, $decoded);
        
        // 验证 URL 安全字符
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    /**
     * Base64URL 编码
     */
    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL 解码
     */
    private function base64urlDecode(string $data): string|false
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}