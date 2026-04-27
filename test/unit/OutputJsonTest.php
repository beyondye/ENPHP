<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Output;

class OutputJsonTest extends TestCase
{
    /**
     * 测试 json 方法 - 基本功能
     */
    public function testJsonBasic()
    {
        $status = 200;
        $message = 'Success';
        $data = ['key' => 'value'];
        
        $result = Output::json($status, $message, $data, true);
        $decoded = json_decode($result, true);
        
        $this->assertEquals($status, $decoded['status']);
        $this->assertEquals($message, $decoded['message']);
        $this->assertEquals($data, $decoded['data']);
    }

    /**
     * 测试 json 方法 - 空数据
     */
    public function testJsonEmptyData()
    {
        $status = 200;
        $message = 'Success';
        $data = [];
        
        $result = Output::json($status, $message, $data, true);
        $decoded = json_decode($result, true);
        
        $this->assertEquals($status, $decoded['status']);
        $this->assertEquals($message, $decoded['message']);
        $this->assertEquals($data, $decoded['data']);
    }

    /**
     * 测试 json 方法 - 非数组数据
     */
    public function testJsonNonArrayData()
    {
        $status = 200;
        $message = 'Success';
        $data = 'string data';
        
        $result = Output::json($status, $message, $data, true);
        $decoded = json_decode($result, true);
        
        $this->assertEquals($status, $decoded['status']);
        $this->assertEquals($message, $decoded['message']);
        $this->assertEquals($data, $decoded['data']);
    }

    /**
     * 测试 json 方法 - 错误状态
     */
    public function testJsonErrorStatus()
    {
        $status = 404;
        $message = 'Not Found';
        $data = ['error' => 'Resource not found'];
        
        $result = Output::json($status, $message, $data, true);
        $decoded = json_decode($result, true);
        
        $this->assertEquals($status, $decoded['status']);
        $this->assertEquals($message, $decoded['message']);
        $this->assertEquals($data, $decoded['data']);
    }

    /**
     * 测试 json 方法 - 直接输出
     */
    public function testJsonDirectOutput()
    {
        $status = 200;
        $message = 'Success';
        $data = ['key' => 'value'];
        
        // 捕获输出
        ob_start();
        try {
            Output::json($status, $message, $data, false);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        
        $decoded = json_decode($output, true);
        $this->assertEquals($status, $decoded['status']);
        $this->assertEquals($message, $decoded['message']);
        $this->assertEquals($data, $decoded['data']);
    }
}