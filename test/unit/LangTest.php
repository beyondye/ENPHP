<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;

class LangTest extends TestCase
{
    /**
     * 测试基本语言文本获取
     */
    public function testBasicLangRetrieval()
    {
        // 测试系统默认语言文件中的值
        $result = lang('system.validator.required');
        $this->assertEquals('{label}不能为空', $result);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
 /**
     * 测试设置语言后返回对应语言
     */
    public function testSetLanguageAndReturn()
    {
        // 保存默认语言
        $defaultLang = \system\Lang::get();
        
        try {
            // 设置为中文
            $resultZh = lang('system.validator.required', [], 'zh');
            $this->assertIsString($resultZh);
            
            // 设置为英文
            $resultEn = lang('system.validator.required', [], 'en');
            $this->assertIsString($resultEn);
            
            // 两种语言的结果应该不同
            $this->assertNotEquals($resultZh, $resultEn);
            
            // 再次设置为中文，应该返回中文
            $resultZh2 = lang('system.validator.required', [], 'zh');
            $this->assertEquals($resultZh, $resultZh2);
        } finally {
            // 恢复默认语言
            \system\Lang::set($defaultLang);
        }
    }
    /**
     * 测试不存在的语言
     */
    public function testNonExistentLanguage()
    {
       // 测试不存在的语言代码
        $nonExistentLang = 'xx'; // 假设 xx 是一个不存在的语言代码
        
        // 1. 测试不存在的语言 + 存在的键
        $result = lang('system.validator.required', [], $nonExistentLang);
        $this->assertIsString($result);
        
        // 2. 测试不存在的语言 + 不存在的键
        $nonExistentKey = 'system.non_existent_key_' . time();
        $result2 = lang($nonExistentKey, [], $nonExistentLang);
        $this->assertEquals($nonExistentKey, $result2);
        
        // 3. 测试不存在的语言 + 占位符替换
        $result3 = lang('system.validator.required', ['label' => '用户名'], $nonExistentLang);
        $this->assertIsString($result3);
        $this->assertEquals('system.validator.required', $result3);
        
      
        // 5. 测试连续调用不存在的语言
        $result5 = lang('system.validator.required', [], $nonExistentLang);
        $result6 = lang('system.validator.required', [], $nonExistentLang);
        $this->assertEquals($result5, $result6);
        
        // 6. 测试多个不存在的语言代码
        $nonExistentLangs = ['yy', 'zz', 'aa'];
        foreach ($nonExistentLangs as $lang) {
            $result = lang('system.validator.required', [], $lang);
            $this->assertEquals('system.validator.required', $result);
        }
    }
    
    /**
     * 测试占位符替换
     */
    public function testPlaceholderReplacement()
    {
        // 测试单个占位符替换
        $result = lang('system.validator.required', ['label' => '用户名']);
        $this->assertIsString($result);
        $this->assertStringContainsString('用户名', $result);
        
        // 测试多个占位符替换
        $result = lang('system.validator.minLen', ['label' => '密码', 'limit' => '6']);
        $this->assertIsString($result);
        $this->assertStringContainsString('密码', $result);
        $this->assertStringContainsString('6', $result);
    }
    
    /**
     * 测试不存在的键
     */
    public function testNonExistentKey()
    {
        $nonExistentKey = 'system.non_existent_key_' . time();
        $result = lang($nonExistentKey);
        $this->assertEquals($nonExistentKey, $result);
    }
    
    /**
     * 测试数组类型的语言值
     */
    public function testArrayLangValue()
    {
        // 假设 system.php 中有数组类型的配置
        $result = lang('system');
        $this->assertIsArray($result);
    }
    
    /**
     * 测试不同语言环境
     */
    public function testDifferentLanguage()
    {
        // 测试中文语言
        $resultZh = lang('system.validator.required', [], 'zh');
        $this->assertIsString($resultZh);
        
        // 测试英文语言
        $resultEn = lang('system.validator.required', [], 'en');
        $this->assertIsString($resultEn);

        // 两种语言的结果应该不同
        $this->assertNotEquals($resultZh, $resultEn);
    }
    
    /**
     * 测试缓存功能
     */
    public function testCaching()
    {
        // 第一次调用，应该加载语言文件
        $result1 = lang('system.validator.required');
        
        // 第二次调用，应该使用缓存
        $result2 = lang('system.validator.required');
        
        // 两次结果应该相同
        $this->assertEquals($result1, $result2);
    }
    
    /**
     * 测试空替换数组
     */
    public function testEmptyReplaceArray()
    {
        $result = lang('system.validator.required', []);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
    
    /**
     * 测试语言文件不存在的情况
     */
    public function testNonExistentLanguageFile()
    {
        $nonExistentFileKey = 'non_existent_file.key_' . time();
        $result = lang($nonExistentFileKey);
        $this->assertEquals($nonExistentFileKey, $result);
    }
}