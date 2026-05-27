<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Cookie;

class CookieTest extends TestCase
{
    /**
     * @var array 保存原始 $_COOKIE
     */
    private $originalCookie = [];

    /**
     * 在每个测试前执行
     */
    protected function setUp(): void
    {
        $this->originalCookie = $_COOKIE ?? [];
        $_COOKIE = [];
    }

    /**
     * 在每个测试后执行
     */
    protected function tearDown(): void
    {
        $_COOKIE = $this->originalCookie;
    }

    /**
     * 测试 get() - 不传参数返回全部 $_COOKIE
     */
    public function testGetAllCookies(): void
    {
        $_COOKIE = ['key1' => 'value1', 'key2' => 'value2'];
        
        $result = Cookie::get();
        
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $result);
    }

    /**
     * 测试 get() - 获取已存在的 cookie
     */
    public function testGetExistingCookie(): void
    {
        $_COOKIE['test_key'] = 'test_value';
        
        $result = Cookie::get('test_key');
        
        $this->assertEquals('test_value', $result);
    }

    /**
     * 测试 get() - 获取不存在的 cookie 返回 null
     */
    public function testGetNonExistingCookie(): void
    {
        $result = Cookie::get('non_existing_key');
        
        $this->assertNull($result);
    }

    /**
     * 测试 get() - 获取空字符串键
     */
    public function testGetEmptyStringKey(): void
    {
        $_COOKIE = ['key1' => 'value1'];
        
        $result = Cookie::get('');
        
        $this->assertEquals(['key1' => 'value1'], $result);
    }

    /**
     * 测试 set() - 名称为空字符串返回 false
     */
    public function testSetEmptyName(): void
    {
        $result = Cookie::set('', 'value');
        
        $this->assertFalse($result);
    }

    /**
     * 测试 set() - options 为整数 0（会话 cookie）
     */
    public function testSetWithZeroExpire(): void
    {
        $result = Cookie::set('test_key', 'test_value', 0);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 set() - options 为正整数（指定过期秒数）
     */
    public function testSetWithPositiveIntExpire(): void
    {
        $result = Cookie::set('test_key', 'test_value', 3600);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 set() - options 为数组，包含自定义选项
     */
    public function testSetWithArrayOptions(): void
    {
        $options = [
            'expires' => 3600,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        $result = Cookie::set('test_key', 'test_value', $options);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 set() - options 数组中 expires 为 0
     */
    public function testSetWithArrayZeroExpire(): void
    {
        $options = ['expires' => 0];
        
        $result = Cookie::set('test_key', 'test_value', $options);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 set() - options 数组中 expires 为正数
     */
    public function testSetWithArrayPositiveExpire(): void
    {
        $options = ['expires' => 7200];
        
        $result = Cookie::set('test_key', 'test_value', $options);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 many() - 设置多个 cookie
     */
    public function testManyWithData(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ];
        
        $result = Cookie::many($data);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 many() - 设置空数组
     */
    public function testManyWithEmptyArray(): void
    {
        $result = Cookie::many([]);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 many() - 设置多个 cookie 并传入 options
     */
    public function testManyWithOptions(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        
        $result = Cookie::many($data, 3600);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 delete() - 删除单个 cookie（字符串）
     */
    public function testDeleteSingleCookie(): void
    {
        $result = Cookie::delete('test_key');
        
        $this->assertTrue($result);
    }

    /**
     * 测试 delete() - 删除多个 cookie（数组）
     */
    public function testDeleteArrayOfCookies(): void
    {
        $result = Cookie::delete(['key1', 'key2', 'key3']);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 delete() - 删除空数组
     */
    public function testDeleteEmptyArray(): void
    {
        $result = Cookie::delete([]);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 delete() - 数组中包含非字符串值
     */
    public function testDeleteArrayWithNonStringValues(): void
    {
        $result = Cookie::delete(['key1', 123, null, 'key2']);
        
        $this->assertTrue($result);
    }

    /**
     * 测试 set() - 值为整数类型
     */
    public function testSetWithIntValue(): void
    {
        $result = Cookie::set('test_key', 12345, 3600);
        
        $this->assertTrue($result);
    }
}