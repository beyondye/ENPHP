<?php

namespace test\unit;

use system\Validator as ValidatorClass;

class ValidatorTest extends \PHPUnit\Framework\TestCase 
{
    public function testExecute()
    {
        $validator = ValidatorClass::make([
            'name' => ['label' => '姓名', 'rules' => ['required', 'len' => 3], 'errors' => '姓名长度必须为3个字符'],
            'age' => ['label' => '年龄', 'rules' => ['gt' => 18], 'errors' => '年龄必须大于18岁'],
            'email' => ['label' => '邮箱', 'rules' => ['required', 'email'], 'errors' => '邮箱格式不正确'],
            'func' => ['label' => '函数', 'rules' => function ($value) {
                return ctype_lower($value);
            }, 'errors' => '{label}必须是小写字母'],
            'regex' => ['label' => '正则', 'rules' => '/^[a-z]+$/', 'errors' => '正则表达式不匹配'],
            'default' => ['label' => '默认', 'rules' => ['required']],
            'xxx' => ['label' => 'xxx', 'rules' => function ($value) {
                return $value === 'xxx_value';
            }],
            'xxx3' => ['label' => 'xxx3', 'rules' => ['required', 'id']],
            'html' => ['label' => 'HTML', 'rules' => ['required', 'filter' => ['html', 'trim', 'blank', 'tag']]],
            'not_rule2' => ['label' => '不规则'],
            'no_method' => ['label' => '无方法', 'rules' => 90],
            'empty_value' => ['label' => '空', 'rules' => ['gt' => 0]],
       
        ]);
        $result = $validator->execute([
            'name' => 'abc',
            'age' => 19,
            'email' => 'test@example.com',
            'func' => 'test',
            'regex' => 'abc',
            'default' => 'default_value',
            'xxx' => 'xxx_value',
            'xxx3' => '42022419900101001X',
            'html' => ' <b>test</b> ',
            'not_rule' => 'should be ignored',
            'empty_value' => '',
            'no_method' => 'value'
        ]);

        $this->assertTrue($result);
        $this->assertTrue($validator->pass);
        $this->assertEquals('test', $validator->data['html']);
        $this->assertEmpty($validator->errors);
        

        $validator = ValidatorClass::make([
            'name' => ['label' => '姓名', 'rules' => ['required', 'len' => 3], 'errors' => '姓名长度必须为3个字符'],
            'age' => ['label' => '年龄', 'rules' => ['gt' => 18], 'errors' => lang('system.validator.gt', ['label' => '年龄', 'limit' => '18岁'])],
            'email' => ['label' => '邮箱', 'rules' => ['required', 'email'], 'errors' => '邮箱格式不正确'],
            'func' => ['label' => '函数', 'rules' => function ($value) {
                return ctype_lower($value);
            }, 'errors' => '{label}必须是小写字母'],
            'regex' => ['label' => '正则', 'rules' => '/^[a-z]+$/', 'errors' => '正则表达式不匹配'],
            'default' => ['label' => '默认1', 'rules' => ['required']],
            'xxx' => ['label' => 'xxx', 'rules' => function ($value) {
                return $value === 'xxx_value';
            }],
            'xxx2' => ['label' => 'xxx2', 'rules' => '/^[a-z]+$/', 'errors' => '正则表达式不匹配'],
            'html' => ['label' => 'HTML', 'rules' => ['filter' => ['trim', 'blank']]]
        ]);
        $result = $validator->execute([
            'name' => 'abcd',
            'age' => 17,
            'email' => 'test@example',
            'func' => 'TEST',
            'regex' => 'Abc',
            'default' => '',
            'xxx' => '',
            'xxx2' => 'z111',
            'html' => ' <b>test</b> '
        ]);
        $this->assertFalse($result);
        $this->assertFalse($validator->pass);
        $this->assertNotEquals('test', $validator->data['html']);
        


        $this->assertEquals('姓名长度必须为3个字符', $validator->errors['name']);
        $this->assertEquals('年龄必须大于18岁', $validator->errors['age']);
        $this->assertEquals('邮箱格式不正确', $validator->errors['email']);
        $this->assertEquals('函数必须是小写字母', $validator->errors['func']);
        $this->assertIsCallable($validator->rules['func']);
        $this->assertEquals('正则表达式不匹配', $validator->errors['regex']);
        $this->assertEquals('默认1不能为空', $validator->errors['default']);
        $this->assertEquals('xxx验证不通过', $validator->errors['xxx']);
        $this->assertEquals('正则表达式不匹配', $validator->errors['xxx2']);

    }

    /**
     * 测试构造函数
     */
    public function testConstruct()
    {
        $validator = new ValidatorClass([
            'name' => ['label' => '姓名', 'rules' => ['required']]
        ]);
        
        $this->assertInstanceOf(ValidatorClass::class, $validator);
        $this->assertNotEmpty($validator->rules);
    }
    
    /**
     * 测试静态 make 方法
     */
    public function testMake()
    {
        $validator = ValidatorClass::make([
            'name' => ['label' => '姓名', 'rules' => ['required']]
        ]);
        
        $this->assertInstanceOf(ValidatorClass::class, $validator);
    }
    
    /**
     * 测试 setRules 方法
     */
    public function testSetRules()
    {
        $validator = new ValidatorClass();
        
        $rules = [
            'name' => ['label' => '姓名', 'rules' => ['required', 'len' => 3]]
        ];
        
        $result = $validator->setRules($rules);
        
        $this->assertSame($validator, $result);
        $this->assertNotEmpty($validator->rules);
    }
    
    /**
     * 测试空规则设置
     */
    public function testSetEmptyRules()
    {
        $validator = new ValidatorClass([
            'name' => ['label' => '姓名', 'rules' => ['required']]
        ]);
        
        $originalRulesCount = count($validator->rules);
        
        $result = $validator->setRules([]);
        
        $this->assertSame($validator, $result);
        $this->assertCount($originalRulesCount, $validator->rules);
    }
    
    /**
     * 测试各种边界条件
     */
    public function testEdgeCases()
    {
        // 测试数字 0 应该被认为是 required 的
        $validator = ValidatorClass::make([
            'number' => ['label' => '数字', 'rules' => ['required']]
        ]);
        
        $result = $validator->execute(['number' => 0]);
        $this->assertTrue($result);
        
        // 测试空字符串应该被判断为非 required
        $validator2 = ValidatorClass::make([
            'text' => ['label' => '文本', 'rules' => ['required']]
        ]);
        
        $result2 = $validator2->execute(['text' => '']);
        $this->assertFalse($result2);
        
        // 测试 null 值
        $validator3 = ValidatorClass::make([
            'field' => ['label' => '字段', 'rules' => ['required']]
        ]);
        
        $result3 = $validator3->execute(['field' => null]);
        $this->assertFalse($result3);
     }
    
    /**
     * 测试验证规则解析
     */
    public function testRuleParsing()
    {
        $validator = ValidatorClass::make([
            'field1' => ['label' => '字段1', 'rules' => ['required', 'minLen' => 5]],
            'field2' => ['label' => '字段2', 'rules' => '/^[a-z]+$/'],
            'field3' => ['label' => '字段3', 'rules' => function ($value) { return strlen($value) > 0; }]
        ]);
        
        $this->assertArrayHasKey('field1', $validator->rules);
        $this->assertArrayHasKey('field2', $validator->rules);
        $this->assertArrayHasKey('field3', $validator->rules);
        
        $this->assertArrayHasKey('required', $validator->rules['field1']);
        $this->assertArrayHasKey('minLen', $validator->rules['field1']);
        $this->assertArrayHasKey('regex', $validator->rules['field2']);
        $this->assertIsCallable($validator->rules['field3']);
    }
    
    /**
     * 测试错误消息设置
     */
    public function testErrorMessages()
    {
        $validator = ValidatorClass::make([
            'name' => [
                'label' => '用户名', 
                'rules' => ['required', 'minLen' => 6],
                'errors' => [
                    'required' => '用户名不能为空',
                    'minLen' => '用户名长度不能少于6个字符',
                    'default' => '用户名验证失败'
                ]
            ]
        ]);
        
        // 测试必填项错误
        $result = $validator->execute(['name' => '']);
        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $validator->errors);
        $this->assertEquals('用户名不能为空', $validator->errors['name']);
        
        // 测试长度错误
        $validator2 = ValidatorClass::make([
            'name' => [
                'label' => '用户名', 
                'rules' => ['required', 'minLen' => 6],
                'errors' => [
                    'required' => '用户名不能为空',
                    'minLen' => '用户名长度不能少于6个字符',
                    'default' => '用户名验证失败'
                ]
            ]
        ]);
        
        $result2 = $validator2->execute(['name' => 'abc']);
        $this->assertFalse($result2);
        $this->assertArrayHasKey('name', $validator2->errors);
        $this->assertEquals('用户名长度不能少于6个字符', $validator2->errors['name']);
    }
    
    /**
     * 测试过滤器功能
     */
    public function testFilters()
    {
        $validator = ValidatorClass::make([
            'text' => [
                'label' => '文本', 
                'rules' => ['filter' => ['trim', 'blank']]
            ]
        ]);
        
        $result = $validator->execute(['text' => '  hello    world  ']);
        
        $this->assertTrue($result);
        $this->assertEquals('hello world', $validator->data['text']);
    }
    
    /**
     * 测试不同数据类型验证
     */
    public function testDifferentDataTypes()
    {
        // 测试布尔值
        $this->assertTrue(ValidatorClass::required(true));
        $this->assertTrue(ValidatorClass::required(false));
        $this->assertFalse(ValidatorClass::required(null));
        
        // 测试浮点数
        $this->assertTrue(ValidatorClass::float(3.14));
        $this->assertTrue(ValidatorClass::float(0.0));
        $this->assertFalse(ValidatorClass::float('not_a_float'));
        
        // 测试整数
        $this->assertTrue(ValidatorClass::num(123));
        $this->assertTrue(ValidatorClass::num('123'));
        $this->assertTrue(ValidatorClass::num(123.45));
        $this->assertTrue(ValidatorClass::num('123.45'));
    }
    
    /**
     * 测试执行方法的边界情况
     */
    /**
     * 全面测试正则表达式验证功能
     */
    public function testRegexComprehensive()
    {
        // 测试基本字母匹配
        $this->assertTrue(ValidatorClass::regex('hello', '/^[a-z]+$/'));
        $this->assertFalse(ValidatorClass::regex('Hello', '/^[a-z]+$/'));
        $this->assertFalse(ValidatorClass::regex('hello123', '/^[a-z]+$/'));
        
        // 测试大小写字母匹配
        $this->assertTrue(ValidatorClass::regex('Hello', '/^[A-Za-z]+$/'));
        $this->assertFalse(ValidatorClass::regex('Hello123', '/^[A-Za-z]+$/'));
        
        // 测试数字匹配
        $this->assertTrue(ValidatorClass::regex('123456', '/^\\d+$/'));
        $this->assertFalse(ValidatorClass::regex('123abc', '/^\\d+$/'));
        
        // 测试字母数字混合
        $this->assertTrue(ValidatorClass::regex('abc123', '/^[a-zA-Z0-9]+$/'));
        $this->assertFalse(ValidatorClass::regex('abc123!', '/^[a-zA-Z0-9]+$/'));
        
        // 测试特殊字符
        $this->assertTrue(ValidatorClass::regex('test@email.com', '/^.+@.+.+$/'));
        $this->assertTrue(ValidatorClass::regex('test_email-test', '/^[a-zA-Z0-9_-]+$/'));
        
        // 测试URL匹配
        $pattern = '/^https?:\\/\\/[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/';
        $this->assertTrue(ValidatorClass::regex('https://www.example.com', $pattern));
        $this->assertTrue(ValidatorClass::regex('http://example.org', $pattern));
        $this->assertFalse(ValidatorClass::regex('ftp://example.com', $pattern));
        
        // 测试IP地址匹配
        $ipPattern = '/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$/';
        $this->assertTrue(ValidatorClass::regex('192.168.1.1', $ipPattern));
        $this->assertTrue(ValidatorClass::regex('127.0.0.1', $ipPattern));
        
        // 测试中文匹配
        $this->assertTrue(ValidatorClass::regex('你好世界', '/^[\\x{4e00}-\\x{9fa5}]+$/u'));
        $this->assertFalse(ValidatorClass::regex('你好123', '/^[\\x{4e00}-\\x{9fa5}]+$/u'));
        
        // 测试空模式
        $this->assertFalse(ValidatorClass::regex('test', ''));
        
        // 测试无效正则表达式
        $this->assertFalse(ValidatorClass::regex('test', '/[invalid/'));
        $this->assertFalse(ValidatorClass::regex('test', '/(/'));
        
        // 测试长度限制
        $this->assertTrue(ValidatorClass::regex('abc', '/^.{3}$/'));
        $this->assertFalse(ValidatorClass::regex('abcd', '/^.{3}$/'));
        $this->assertFalse(ValidatorClass::regex('ab', '/^.{3}$/'));
        
        // 测试电话号码
        $phonePattern = '/^1[3-9]\\d{9}$/';
        $this->assertTrue(ValidatorClass::regex('13812345678', $phonePattern));
        $this->assertFalse(ValidatorClass::regex('12812345678', $phonePattern));
        $this->assertFalse(ValidatorClass::regex('1381234567', $phonePattern));
        
        // 测试身份证号
        $idPattern = '/^(^[1-9]\\d{5}(18|19|([23]\\d))\\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\\d{3}[0-9Xx]$)|(^[1-9]\\d{5}\\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\\d{3}$)/';
        $this->assertTrue(ValidatorClass::regex('42022419900101001X', $idPattern));
        $this->assertFalse(ValidatorClass::regex('12345678901234567', $idPattern));
        
        // 测试日期格式
        $datePattern = '/^\\d{4}-\\d{2}-\\d{2}$/';
        $this->assertTrue(ValidatorClass::regex('2023-12-25', $datePattern));
        $this->assertFalse(ValidatorClass::regex('25-12-2023', $datePattern));
        
        // 测试时间格式
        $timePattern = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';
        $this->assertTrue(ValidatorClass::regex('14:30', $timePattern));
        $this->assertTrue(ValidatorClass::regex('9:05', $timePattern));
        $this->assertFalse(ValidatorClass::regex('25:00', $timePattern));
    }
    
    /**
     * 测试在实际验证场景中使用正则表达式
     */
    public function testRegexInValidation()
    {
        // 测试用户自定义正则表达式验证
        $validator = ValidatorClass::make([
            'username' => [
                'label' => '用户名',
                'rules' => '/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/',  // 以字母开头，包含字母数字下划线，长度3-20
                'errors' => '用户名格式不正确'
            ],
            'zipcode' => [
                'label' => '邮编',
                'rules' => '/^\\d{6}$/',  // 6位数字
                'errors' => '邮编格式不正确'
            ]
        ]);
        
        // 测试有效用户名和邮编
        $result = $validator->execute([
            'username' => 'user_123',
            'zipcode' => '123456'
        ]);
        $this->assertTrue($result);
        
        // 测试无效用户名
        $validator2 = ValidatorClass::make([
            'username' => [
                'label' => '用户名',
                'rules' => '/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/',
                'errors' => '用户名格式不正确'
            ]
        ]);
        
        $result2 = $validator2->execute(['username' => '_invalid']);
        $this->assertFalse($result2);
        $this->assertArrayHasKey('username', $validator2->errors);
        $this->assertEquals('用户名格式不正确', $validator2->errors['username']);
        
        // 测试无效邮编
        $validator3 = ValidatorClass::make([
            'zipcode' => [
                'label' => '邮编',
                'rules' => '/^\\d{6}$/',
                'errors' => '邮编格式不正确'
            ]
        ]);
        
        $result3 = $validator3->execute(['zipcode' => '12345']);
        $this->assertFalse($result3);
        $this->assertArrayHasKey('zipcode', $validator3->errors);
        $this->assertEquals('邮编格式不正确', $validator3->errors['zipcode']);
    }
    
    public function testExecuteEdgeCases()
    {
        // 空数据和空规则
        $validator = ValidatorClass::make([]);
        $result = $validator->execute([]);
        $this->assertTrue($result);
        
        // 空数据有规则
        $validator2 = ValidatorClass::make([
            'field' => ['label' => '字段', 'rules' => ['required']],
            'extra_field' => ['label' => '额外字段', 'rules' => ['required']],
            'bb' => ['rules' => ['required']],

        ]);
        $result2 = $validator2->execute([]);
        $this->assertFalse($result2);
        $this->assertArrayHasKey('field', $validator2->errors);
        $this->assertArrayHasKey('extra_field', $validator2->errors);
        $this->assertEquals('字段不存在', $validator2->errors['field']);
        $this->assertEquals('额外字段不存在', $validator2->errors['extra_field']);
        $this->assertEquals('bb不存在', $validator2->errors['bb']);
        
        // 数据多于规则
        $validator3 = ValidatorClass::make([
            'field' => ['label' => '字段', 'rules' => ['requirxxxed', 'gygy' => 3]],
            'extra_field' => ['label' => '额外字段', 'rules' => ['rered']]

        ]);
        $result3 = $validator3->execute([
            'field' => 'value',
            'extra_field' => 'extra_value'
        ]);
        $this->assertFalse($result3);
        $this->assertArrayHasKey('field', $validator3->errors);
        $this->assertEquals('字段使用不存在的验证方法requirxxxed,gygy', $validator3->errors['field']);
        $this->assertEquals('额外字段使用不存在的验证方法rered', $validator3->errors['extra_field']);

    }
    
    /**
     * 测试多个验证规则组合
     */
    public function testMultipleRules()
    {
        $validator = ValidatorClass::make([
            'password' => [
                'label' => '密码',
                'rules' => ['required', 'minLen' => 8, 'maxLen' => 20, 'alphaNum']
            ]
        ]);
        
        // 测试成功情况
        $result = $validator->execute(['password' => 'MyPass123']);
        $this->assertTrue($result);
        
        // 测试失败情况 - 长度不够
        $validator2 = ValidatorClass::make([
            'password' => [
                'label' => '密码',
                'rules' => ['required', 'minLen' => 8, 'maxLen' => 20, 'alphaNum']
            ]
        ]);
        $result2 = $validator2->execute(['password' => 'short']);
        $this->assertFalse($result2);
        
        // 测试失败情况 - 包含非法字符
        $validator3 = ValidatorClass::make([
            'password' => [
                'label' => '密码',
                'rules' => ['required', 'minLen' => 8, 'maxLen' => 20, 'alphaNum']
            ]
        ]);
        $result3 = $validator3->execute(['password' => 'MyPassword!@#']);
        $this->assertFalse($result3);
    }
    
    public  function testMaxLen()
    {
        $this->assertTrue(ValidatorClass::maxLen('abc', 3));
        $this->assertFalse(ValidatorClass::maxLen('abcd', 3));
    }
    public  function testMinLen()
    {
        $this->assertTrue(ValidatorClass::minLen('abc', 3));
        $this->assertFalse(ValidatorClass::minLen('ab', 3));
    }

    public  function testLen()
    {
        $this->assertTrue(ValidatorClass::len('abc', 3));
        $this->assertFalse(ValidatorClass::len('abcd', 3)); 
    }
    public function testGt()
    {
        $this->assertTrue(ValidatorClass::gt('4', 3));
        $this->assertFalse(ValidatorClass::gt('3', 3)); 
        $this->assertFalse(ValidatorClass::gt('3', 4));
        $this->assertFalse(ValidatorClass::gt('3dsd', '4'));
    }
    public function testLt()
    {
        $this->assertTrue(ValidatorClass::lt('3', 4));
        $this->assertFalse(ValidatorClass::lt('4', 4)); 
        $this->assertFalse(ValidatorClass::lt('4', 3));
        $this->assertFalse(ValidatorClass::lt('4dsd', '3'));
    }
    public function testGte()
    {
        $this->assertTrue(ValidatorClass::gte('4', 3));
        $this->assertTrue(ValidatorClass::gte('3', 3)); 
        $this->assertFalse(ValidatorClass::gte('3', 4));
        $this->assertFalse(ValidatorClass::gte('3dsd', '4'));
    }
    public function testLte()
    {
        $this->assertTrue(ValidatorClass::lte('3', 4));
        $this->assertTrue(ValidatorClass::lte('4', 4)); 
        $this->assertFalse(ValidatorClass::lte('4', 3));
        $this->assertFalse(ValidatorClass::lte('4dsd', '3'));
    }

    public function testEq()
    {
        $this->assertTrue(ValidatorClass::eq('4', 4));
        $this->assertFalse(ValidatorClass::eq('4', 3));
        $this->assertFalse(ValidatorClass::eq('4dsd', '4'));

    
    }
    public function testNeq()
    {
        $this->assertTrue(ValidatorClass::neq('4', 3));
        $this->assertFalse(ValidatorClass::neq('4', 4)); 
        $this->assertTrue(ValidatorClass::neq('4dsd', '4'));

    }

    public function testRegex()
    {
        $this->assertTrue(ValidatorClass::regex('abc', '/^[a-z]+$/'));
        $this->assertFalse(ValidatorClass::regex('abc1', '/^[a-z]+$/'));                    
        $this->assertFalse(ValidatorClass::regex('123', '/^[a-z]+$/'));
        $this->assertTrue(ValidatorClass::regex('中文', '/^[\p{Han}]+$/u'));
        $this->assertFalse(ValidatorClass::regex('中文1', '/^[\p{Han}]+$/u'));
        $this->assertFalse(ValidatorClass::regex('abc', '/^[\p{Han}]+$/u'));
        $this->assertFalse(ValidatorClass::regex('123', '/^9]'));
    }

    public function testId()
    {
        $this->assertTrue(ValidatorClass::id('42022419900101001X'));
        $this->assertFalse(ValidatorClass::id('12345678901234567'));
    }



    public function testMobile()
    {
        $this->assertTrue(ValidatorClass::mobile('13800138000'));
        $this->assertFalse(ValidatorClass::mobile('12345678901'));
    }
    public function testIp4()
    {
        $this->assertTrue(ValidatorClass::ip4('192.168.1.1'));
        $this->assertFalse(ValidatorClass::ip4('192.168.1.256'));
    }
    public function testIp6()
    {
        $this->assertTrue(ValidatorClass::ip6('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse(ValidatorClass::ip6('2001:0db8:85a3:0000:0000:8a2e:0370:7334:'));
    }

    public function testUrl()
    {
        $this->assertTrue(ValidatorClass::url('http://www.example.com'));
        $this->assertFalse(ValidatorClass::url('www.example.com'));
        $this->assertTrue(ValidatorClass::url('https://www.example.com/sdsd/fd.php?a=1&b=2'));
        $this->assertTrue(ValidatorClass::url('ftp://www.example.com/dsd/sdsd'));
        $this->assertFalse(ValidatorClass::url('www.example.com/'));
    }


    public function testFloat()
    {
        $this->assertTrue(ValidatorClass::float('123.456'));
        $this->assertFalse(ValidatorClass::float('123.456.789'));
    }

    public function testNum()
    {
        $this->assertTrue(ValidatorClass::num('123'));
        $this->assertTrue(ValidatorClass::num('123.456'));
    }

    public function testString()
    {
        $this->assertTrue(ValidatorClass::string('abc'));
        $this->assertTrue(ValidatorClass::string('123'));
        $this->assertTrue(ValidatorClass::string('中文'));
        $this->assertTrue(ValidatorClass::string('中文1'));
        $this->assertFalse(ValidatorClass::string(['not a string']));
    }

    public function testChinese()
    {
        $this->assertTrue(ValidatorClass::chinese('中文'));
        $this->assertFalse(ValidatorClass::chinese('中文1'));
        $this->assertFalse(ValidatorClass::chinese('abc'));
        $this->assertFalse(ValidatorClass::chinese('123'));
    }

    public function testEmail()
    {
        $this->assertTrue(ValidatorClass::email('test@example.com'));
        $this->assertFalse(ValidatorClass::email('test@example'));
        $this->assertTrue(ValidatorClass::email('test@example.com1'));
        $this->assertFalse(ValidatorClass::email('test@example..com'));
        $this->assertFalse(ValidatorClass::email('test@example.com.'));
        $this->assertTrue(ValidatorClass::email('test@example.com.cn'));
    }

    public function testAlpha()
    {
        $this->assertTrue(ValidatorClass::alpha('abc'));
        $this->assertFalse(ValidatorClass::alpha('abc1'));
        $this->assertFalse(ValidatorClass::alpha('123'));
    }
    public function testAlphaNum()
    {
        $this->assertTrue(ValidatorClass::alphaNum('abc123'));
        $this->assertFalse(ValidatorClass::alphaNum('abc123!'));
        $this->assertFalse(ValidatorClass::alphaNum('!@#'));
    }
    public function testAlphaNumChinese()
    {
        $this->assertTrue(ValidatorClass::alphaNumChinese('中文123'));
        $this->assertFalse(ValidatorClass::alphaNumChinese('中文123!'));
        $this->assertFalse(ValidatorClass::alphaNumChinese('!@#'));
        $this->assertTrue(ValidatorClass::alphaNumChinese('abc'));
        $this->assertTrue(ValidatorClass::alphaNumChinese('中文1adv23'));
    }

    public function testAlphaNumDash()
    {
        $this->assertTrue(ValidatorClass::alphaNumDash('abc-123'));
        $this->assertFalse(ValidatorClass::alphaNumDash('abc-123!'));
        $this->assertFalse(ValidatorClass::alphaNumDash('!@#'));
    
        $this->assertTrue(ValidatorClass::alphaNumDash('abc_123'));
        $this->assertFalse(ValidatorClass::alphaNumDash('abc_123!'));
        $this->assertFalse(ValidatorClass::alphaNumDash('!@#'));
    }

    public function testRequired()
    {
        $this->assertTrue(ValidatorClass::required('abc'));
        $this->assertFalse(ValidatorClass::required(''));
    }
    public function testSame()
    {
        $this->assertTrue(ValidatorClass::same('123', '123'));
        $this->assertFalse(ValidatorClass::same('123', '456'));
    }

    public function testIn()
    {
        $this->assertTrue(ValidatorClass::in('a', ['a', 'b', 'c']));
        $this->assertFalse(ValidatorClass::in('d', ['a', 'b', 'c']));
    }

    public function testNin()
    {
        $this->assertTrue(ValidatorClass::nin('d', ['a', 'b', 'c']));
        $this->assertFalse(ValidatorClass::nin('a', ['a', 'b', 'c']));
    }

    public function testFilter()
    {
        $this->assertEquals('abc', ValidatorClass::filter(' abc ', ['trim']));
        $this->assertEquals('a b c', ValidatorClass::filter('a   b   c', ['blank']));
        $this->assertEquals('abc', ValidatorClass::filter('<b>abc</b>', ['tag']));
        $this->assertEquals('&lt;b&gt;abc&lt;/b&gt;', ValidatorClass::filter('<b>abc</b>', ['html']));
    }

    public function testInt()
    {
        // 测试整数类型
        $this->assertTrue(ValidatorClass::int(0));
        $this->assertTrue(ValidatorClass::int(123));
        $this->assertTrue(ValidatorClass::int(-123));
        $this->assertTrue(ValidatorClass::int(PHP_INT_MAX));
        $this->assertTrue(ValidatorClass::int(PHP_INT_MIN));
        
        // 测试数字字符串
        $this->assertTrue(ValidatorClass::int('0'));
        $this->assertTrue(ValidatorClass::int('123'));
        $this->assertTrue(ValidatorClass::int('1234567890'));
        
        // 测试浮点数
        $this->assertTrue(ValidatorClass::int(123.0)); // 整数形式的浮点数
        $this->assertFalse(ValidatorClass::int(123.5)); // 非整数形式的浮点数
        
        // 测试非数字值
        $this->assertFalse(ValidatorClass::int('abc'));
        $this->assertFalse(ValidatorClass::int('123abc'));
        $this->assertFalse(ValidatorClass::int('abc123'));
        $this->assertFalse(ValidatorClass::int(''));
        $this->assertFalse(ValidatorClass::int(null));
        $this->assertFalse(ValidatorClass::int(true));
        $this->assertFalse(ValidatorClass::int(false));
        $this->assertFalse(ValidatorClass::int([]));
        $this->assertFalse(ValidatorClass::int(new \stdClass()));
        
        // 测试特殊情况
        $this->assertFalse(ValidatorClass::int(' 123 ')); // 带空格的字符串
        $this->assertFalse(ValidatorClass::int('+123')); // 带正号的字符串
        $this->assertFalse(ValidatorClass::int('-123')); // 带负号的字符串
      
    }

}