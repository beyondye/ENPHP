<?php

declare(strict_types=1);

namespace system\tests;

use PHPUnit\Framework\TestCase;
use system\Session;

class SessionTest extends TestCase
{
    /**
     * 测试 start() - 首次调用时启动会话
     * @runInSeparateProcess
     */
    public function testStartFirstCall(): void
    {
        Session::start();
        
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals(SESSION_COOKIE_NAME, session_name());
    }

    /**
     * 测试 start() - 多次调用不会重复启动
     * @runInSeparateProcess
     */
    public function testStartMultipleCalls(): void
    {
        Session::start();
        $firstSessionId = session_id();
        
        // 第二次调用
        Session::start();
        
        // 会话ID应该保持不变
        $this->assertEquals($firstSessionId, session_id());
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    /**
     * 测试 set() - 设置非空值
     * @runInSeparateProcess
     */
    public function testSetWithValue(): void
    {
        $result = Session::set('test_key', 'test_value');
        
        $this->assertTrue($result);
        $this->assertEquals('test_value', $_SESSION['test_key']);
    }

    /**
     * 测试 set() - 设置空字符串会删除会话数据
     * @runInSeparateProcess
     */
    public function testSetWithEmptyString(): void
    {
        $_SESSION['test_key'] = 'existing_value';
        
        $result = Session::set('test_key', '');
        
        $this->assertTrue($result);
        $this->assertFalse(isset($_SESSION['test_key']));
    }

    /**
     * 测试 set() - 设置 null 会删除会话数据
     * @runInSeparateProcess
     */
    public function testSetWithNull(): void
    {
        $_SESSION['test_key'] = 'existing_value';
        
        $result = Session::set('test_key', null);
        
        $this->assertTrue($result);
        $this->assertFalse(isset($_SESSION['test_key']));
    }

    /**
     * 测试 get() - 获取已存在的会话数据
     * @runInSeparateProcess
     */
    public function testGetExistingKey(): void
    {
        $_SESSION['test_key'] = 'test_value';
        
        $result = Session::get('test_key');
        
        $this->assertEquals('test_value', $result);
    }

    /**
     * 测试 get() - 获取不存在的会话数据返回 null
     * @runInSeparateProcess
     */
    public function testGetNonExistingKey(): void
    {
        $result = Session::get('non_existing_key');
        
        $this->assertNull($result);
    }

    /**
     * 测试 flash() - 获取并删除已存在的会话数据
     * @runInSeparateProcess
     */
    public function testFlashExistingKey(): void
    {
        $_SESSION['flash_key'] = 'flash_value';
        
        $result = Session::flash('flash_key');
        
        $this->assertEquals('flash_value', $result);
        $this->assertFalse(isset($_SESSION['flash_key']));
    }

    /**
     * 测试 flash() - 获取不存在的会话数据返回 null
     * @runInSeparateProcess
     */
    public function testFlashNonExistingKey(): void
    {
        $result = Session::flash('non_existing_key');
        
        $this->assertNull($result);
    }

    /**
     * 测试 delete() - 删除单个会话键
     * @runInSeparateProcess
     */
    public function testDeleteSingleKey(): void
    {
        $_SESSION['key1'] = 'value1';
        $_SESSION['key2'] = 'value2';
        
        Session::delete('key1');
        
        $this->assertFalse(isset($_SESSION['key1']));
        $this->assertTrue(isset($_SESSION['key2']));
    }

    /**
     * 测试 delete() - 删除不存在的键不会报错
     * @runInSeparateProcess
     */
    public function testDeleteNonExistingKey(): void
    {
        // 不会抛出异常
        Session::delete('non_existing_key');
        
        $this->assertTrue(true);
    }

    /**
     * 测试 delete() - 删除数组中的多个键
     * @runInSeparateProcess
     */
    public function testDeleteArrayOfKeys(): void
    {
        $_SESSION['key1'] = 'value1';
        $_SESSION['key2'] = 'value2';
        $_SESSION['key3'] = 'value3';
        
        Session::delete(['key1', 'key2']);
        
        $this->assertFalse(isset($_SESSION['key1']));
        $this->assertFalse(isset($_SESSION['key2']));
        $this->assertTrue(isset($_SESSION['key3']));
    }

    /**
     * 测试 delete() - 删除空数组
     * @runInSeparateProcess
     */
    public function testDeleteEmptyArray(): void
    {
        $_SESSION['key1'] = 'value1';
        
        Session::delete([]);
        
        $this->assertTrue(isset($_SESSION['key1']));
    }

    /**
     * 测试 regenerate() - 默认参数（不销毁旧会话）
     * @runInSeparateProcess
     */
    public function testRegenerateDefault(): void
    {
        Session::start();
        $oldSessionId = session_id();
        
        $result = Session::regenerate();
        
        $this->assertTrue($result);
        $this->assertNotEquals($oldSessionId, session_id());
    }

    /**
     * 测试 regenerate() - 销毁旧会话
     * @runInSeparateProcess
     */
    public function testRegenerateWithDestroy(): void
    {
        Session::start();
        $oldSessionId = session_id();
        
        $result = Session::regenerate(true);
        
        $this->assertTrue($result);
        $this->assertNotEquals($oldSessionId, session_id());
    }

    /**
     * 测试 destroy() - 销毁会话
     * @runInSeparateProcess
     */
    public function testDestroy(): void
    {
        $_SESSION['test_key'] = 'test_value';
        
        $result = Session::destroy();
        
        $this->assertTrue($result);
        $this->assertEquals([], $_SESSION);
    }
}