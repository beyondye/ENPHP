<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Input;

class InputFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 清空 $_GET 数组，避免测试之间的干扰
        $_GET = [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // 清空 $_GET 数组
        $_GET = [];
    }

    /**
     * 测试 filter 方法 - 没有 filter 参数
     */
    public function testFilterNoParameter()
    {
        $_GET = [];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - filter 参数为空字符串
     */
    public function testFilterEmptyString()
    {
        $_GET = ['filter' => ''];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - filter 参数为空数组
     */
    public function testFilterEmptyArray()
    {
        $_GET = ['filter' => []];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - filter 参数格式不正确（少于 3 个部分）
     */
    public function testFilterInvalidFormatLessThanThreeParts()
    {
        $_GET = ['filter' => urlencode('field:=value')];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - filter 参数格式不正确（多于 3 个部分）
     */
    public function testFilterInvalidFormatMoreThanThreeParts()
    {
        $_GET = ['filter' => urlencode('field:=value:extra')];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - filter 参数包含空部分
     */
    public function testFilterEmptyParts()
    {
        // 字段名为空
        $_GET = ['filter' => urlencode(':==value')];
        $result = Input::filter();
        $this->assertEquals([], $result);

        // 操作符为空
        $_GET = ['filter' => urlencode('field::value')];
        $result = Input::filter();
        $this->assertEquals([], $result);

        // 值为空
        $_GET = ['filter' => urlencode('field:=')];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - 字段名不符合正则表达式
     */
    public function testFilterInvalidFieldName()
    {
        // 包含特殊字符
        $_GET = ['filter' => urlencode('field.name:=value')];
        $result = Input::filter();
        $this->assertEquals([], $result);

        // 包含空格
        $_GET = ['filter' => urlencode('field name:=value')];
        $result = Input::filter();
        $this->assertEquals([], $result);

        // 包含特殊符号
        $_GET = ['filter' => urlencode('field@name:=value')];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - 操作符不在允许列表中
     */
    public function testFilterInvalidOperator()
    {
        $_GET = ['filter' => urlencode('field:===:value')];
        $result = Input::filter();
        $this->assertEquals([], $result);

        $_GET = ['filter' => urlencode('field:unknown:value')];
        $result = Input::filter();
        $this->assertEquals([], $result);
    }

    /**
     * 测试 filter 方法 - 单个有效的 filter 参数
     */
    public function testFilterSingleValidCondition()
    {
        $_GET = ['filter' => urlencode('id:=:1')];
        $result = Input::filter();
        $this->assertEquals(['id' => ['id', '=', '1']], $result);
    }

    /**
     * 测试 filter 方法 - 多个有效的 filter 参数
     */
    public function testFilterMultipleValidConditions()
    {
        $_GET = ['filter' => urlencode('id:=:1|name:like:%test%|status:>:0')];
        $result = Input::filter();
        
        $expected = [
            'id' => ['id', '=', '1'],
            'name' => ['name', 'like', '%test%'],
            'status' => ['status', '>', '0']
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 filter 方法 - 混合有效和无效的条件
     */
    public function testFilterMixedValidAndInvalidConditions()
    {
        $_GET = ['filter' => urlencode('id:=:1|invalid::|name:like:%test%')];
        $result = Input::filter();
        
        $expected = [
            'id' => ['id', '=', '1'],
            'name' => ['name', 'like', '%test%']
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 filter 方法 - 使用自定义参数名
     */
    public function testFilterCustomParameterName()
    {
        $_GET = ['custom_filter' => urlencode('id:=:1')];
        $result = Input::filter('custom_filter');
        $this->assertEquals(['id' => ['id', '=', '1']], $result);
    }

    /**
     * 测试 filter 方法 - 自定义参数名不存在
     */
    public function testFilterCustomParameterNameNotExist()
    {
        $_GET = ['filter' => urlencode('id:=:1')];
        $result = Input::filter('custom_filter');
        $this->assertEquals([], $result);
    }
}