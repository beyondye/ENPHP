<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Output;

class OutputUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 定义 URL 常量（如果未定义）
        if (!defined('URL')) {
            define('URL', [
                'test' => [
                    '/' => '/test',
                    '/id' => '/test/{id}',
                    '/id/name' => '/test/{id}/{name}'
                ]
            ]);
        }
    }

    /**
     * 测试 url 方法 - 基本功能
     */
    public function testUrlBasic()
    {
        $result = Output::url('test');
        $this->assertEquals('/test', $result);
    }

    /**
     * 测试 url 方法 - 带单个参数
     */
    public function testUrlWithSingleParam()
    {
        // 确保参数值是字符串类型
        $result = Output::url('test', ['id' => '123']);
        $this->assertEquals('/test/123', $result);
    }

    /**
     * 测试 url 方法 - 带多个参数
     */
    public function testUrlWithMultipleParams()
    {
        // 确保参数值是字符串类型
        $result = Output::url('test', ['id' => '123', 'name' => 'test']);
        $this->assertEquals('/test/123/test', $result);
    }

    /**
     * 测试 url 方法 - 带锚点
     */
    public function testUrlWithAnchor()
    {
        $result = Output::url('test', [], 'section1');
        $this->assertEquals('/test#section1', $result);
    }

    /**
     * 测试 url 方法 - 带参数和锚点
     */
    public function testUrlWithParamsAndAnchor()
    {
        // 确保参数值是字符串类型
        $result = Output::url('test', ['id' => '123'], 'section1');
        $this->assertEquals('/test/123#section1', $result);
    }

    /**
     * 测试 url 方法 - 不存在的参数路径
     */
    public function testUrlNonExistentParamPath()
    {
        $result = Output::url('test', ['non_existent' => 'value']);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 url 方法 - 空参数
     */
    public function testUrlEmptyParams()
    {
        $result = Output::url('test', []);
        $this->assertEquals('/test', $result);
    }

    /**
     * 测试 url 方法 - 空锚点
     */
    public function testUrlEmptyAnchor()
    {
        $result = Output::url('test', [], '');
        $this->assertEquals('/test', $result);
    }
}