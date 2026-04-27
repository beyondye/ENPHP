<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Output;

class OutputCompressTest extends TestCase
{
    /**
     * 测试 compress 方法 - 基本功能
     */
    public function testCompressBasic()
    {
        $input = "
            <div>
                <p>Hello World</p>
                <pre>
                    function test() {
                        return true;
                    }
                </pre>
                <textarea>
                    This is a textarea
                    with multiple lines
                </textarea>
            </div>
        ";
        
        $expected = "<div><p>Hello World</p><pre>
                    function test() {
                        return true;
                    }
                </pre> <textarea>
                    This is a textarea
                    with multiple lines
                </textarea></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 移除 HTML 注释
     */
    public function testCompressRemoveHtmlComments()
    {
        $input = "
            <div>
                <!-- This is an HTML comment -->
                <p>Hello World</p>
            </div>
        ";
        
        $expected = "<div><p>Hello World</p></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 移除 CSS 注释
     */
    public function testCompressRemoveCssComments()
    {
        $input = "
            <style>
                /* This is a CSS comment */
                body { margin: 0; }
            </style>
        ";
        
        $expected = "<style> body { margin: 0; }</style>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 移除 JavaScript 注释
     */
    public function testCompressRemoveJsComments()
    {
        $input = "
            <script>
                /* This is a JS comment */
                console.log('Hello');
            </script>
        ";
        
        $expected = "<script> console.log('Hello');</script>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 移除多余的空白字符
     */
    public function testCompressRemoveWhitespace()
    {
        $input = "<div>   <p>   Hello   </p>   </div>";
        $expected = "<div><p>Hello</p></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 空字符串
     */
    public function testCompressEmptyString()
    {
        $input = "";
        $expected = "";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 只有空白字符
     */
    public function testCompressOnlyWhitespace()
    {
        $input = "   \n\t   \r\n   ";
        $expected = " ";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 没有需要压缩的内容
     */
    public function testCompressNoCompressionNeeded()
    {
        $input = "<div><p>Hello</p></div>";
        $expected = "<div><p>Hello</p></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 嵌套的 pre 和 textarea 标签
     */
    public function testCompressNestedTags()
    {
        $input = "
            <div>
                <pre>
                    <code>
                        function test() {
                            return true;
                        }
                    </code>
                </pre>
                <textarea>
                    <p>This is inside textarea</p>
                </textarea>
            </div>
        ";
        
        $expected = "<div><pre>
                    <code>
                        function test() {
                            return true;
                        }
                    </code>
                </pre> <textarea>
                    <p>This is inside textarea</p>
                </textarea></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 compress 方法 - 混合内容
     */
    public function testCompressMixedContent()
    {
        $input = "
            <div>
                <!-- HTML comment -->
                <p>Hello World</p>
                <pre>
                    // JS comment
                    function test() {
                        return true;
                    }
                </pre>
                <style>
                    /* CSS comment */
                    body { margin: 0; }
                </style>
                <textarea>
                    Textarea content
                    with newlines
                </textarea>
            </div>
        ";
        
        $expected = "<div><p>Hello World</p><pre>
                    // JS comment
                    function test() {
                        return true;
                    }
                </pre><style> body { margin: 0; }</style><textarea>
                    Textarea content
                    with newlines
                </textarea></div>";
        
        $result = Output::compress($input);
        $this->assertEquals($expected, $result);
    }
}