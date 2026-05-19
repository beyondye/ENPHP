<?php

declare(strict_types=1);

namespace system\tests;

use PHPUnit\Framework\TestCase;
use system\Authentication;
use system\authentication\AuthenticationException;
use system\authentication\Jwt;
use system\authentication\Cookie;
use system\authentication\Session;

class AuthenticationTest extends TestCase
{
    /**
     * 默认配置数据
     */
    private function getJwtConfig(): array
    {
        return [
            'mode' => 'header',
            'name' => 'Authorization',
            'secret' => 'test_secret',
            'expire' => 3600
        ];
    }

    private function getCookieConfig(): array
    {
        return [
            'name' => 'auth_cookie',
            'secret' => 'test_secret',
            'expire' => 3600
        ];
    }

    private function getSessionConfig(): array
    {
        return [
            'name' => 'auth_session'
        ];
    }

    /**
     * 每个测试前重置状态
     */
    protected function setUp(): void
    {
        parent::setUp();
        Authentication::reset();
        $this->setupConfigMocks();
    }

    /**
     * 设置配置模拟
     */
    private function setupConfigMocks(): void
    {
        // 使用全局变量存储配置映射
        $GLOBALS['_AUTH_CONFIG_MAP'] = [
            'authentication.jwt' => $this->getJwtConfig(),
            'authentication.cookie' => $this->getCookieConfig(),
            'authentication.session' => $this->getSessionConfig(),
            // 为无效类型也提供配置，以便到达 default 分支
            'authentication.invalid_type' => ['name' => 'test'],
        ];
    }

    /**
     * 测试 instance() - 默认类型（session）创建成功
     */
    public function testInstanceWithDefaultType()
    {
        $instance = Authentication::instance();
        
        $this->assertInstanceOf(Session::class, $instance);
    }

    /**
     * 测试 instance() - jwt 类型创建成功
     */
    public function testInstanceWithJwtType()
    {
        Authentication::reset();
        $instance = Authentication::instance('authentication.jwt');
        
        $this->assertInstanceOf(Jwt::class, $instance);
    }

    /**
     * 测试 instance() - cookie 类型创建成功
     */
    public function testInstanceWithCookieType()
    {
        Authentication::reset();
        $instance = Authentication::instance('authentication.cookie');
        
        $this->assertInstanceOf(Cookie::class, $instance);
    }

    /**
     * 测试 instance() - 单例模式验证
     */
    public function testInstanceReturnsSameInstance()
    {
        $instance1 = Authentication::instance('authentication.jwt');
        $instance2 = Authentication::instance('authentication.jwt');
        
        $this->assertSame($instance1, $instance2);
    }

    /**
     * 测试 instance() - 配置为 null 时抛出异常
     */
    public function testInstanceThrowsExceptionWhenConfigNull()
    {
        Authentication::reset();
        
        // 确保此类型没有配置
        unset($GLOBALS['_AUTH_CONFIG_MAP']['authentication.no_config']);
        
        $this->expectException(AuthenticationException::class);
        Authentication::instance('authentication.no_config');
    }

    /**
     * 测试 instance() - 配置不是数组时抛出异常
     */
    public function testInstanceThrowsExceptionWhenConfigNotArray()
    {
        Authentication::reset();
        
        // 设置非数组配置
        $GLOBALS['_AUTH_CONFIG_MAP']['authentication.not_array'] = 'string_config';
        
        $this->expectException(AuthenticationException::class);
        Authentication::instance('authentication.not_array');
    }

    /**
     * 测试 instance() - 无效类型（但有配置）时抛出异常
     * 覆盖 default 分支（第 48-49 行）
     */
    public function testInstanceThrowsExceptionWhenInvalidTypeWithConfig()
    {
        Authentication::reset();
        
        // 确保无效类型有数组配置，这样才能到达 default 分支
        $GLOBALS['_AUTH_CONFIG_MAP']['authentication.invalid_type'] = ['name' => 'test'];
        
        $this->expectException(AuthenticationException::class);
        Authentication::instance('authentication.invalid_type');
    }

    /**
     * 测试 instance() - 切换类型时返回新实例
     */
    public function testInstanceWithDifferentTypeReturnsNewInstance()
    {
        // 先获取 jwt 实例
        $jwtInstance = Authentication::instance('authentication.jwt');
        
        // 重置后获取 cookie 实例
        Authentication::reset();
        $cookieInstance = Authentication::instance('authentication.cookie');
        
        $this->assertNotSame($jwtInstance, $cookieInstance);
        $this->assertInstanceOf(Jwt::class, $jwtInstance);
        $this->assertInstanceOf(Cookie::class, $cookieInstance);
    }
}