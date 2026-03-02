<?php

namespace test;

use system\Validator as ValidatorClass;

class Validator extends \PHPUnit\Framework\TestCase
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
            'xxx3' => ['label' => 'xxx3', 'rules' => ['id']],
            'html' => ['label' => 'HTML', 'rules' => ['filter' => ['html', 'trim', 'blank', 'tag']]]
        ]);
        $result = $validator->execute([
            'name' => 'abc',
            'age' => 19,
            'email' => 'test@example.com',
            'func' => 'test',
            'regex' => 'abc',
            'default' => 'default_value',
            'xxx' => 'xxx_value',
            'xxx3' => '',
            'html' => ' <b>test</b> '
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
            'default' => ['label' => '默认', 'rules' => ['required']],
            'xxx' => ['label' => 'xxx', 'rules' => function ($value) {
                return $value === 'xxx_value';
            }],
            'xxx2' => ['label' => 'xxx2', 'rules' => ';z+$/'],
            'html' => ['label' => 'HTML', 'rules' => ['filter' => ['trim', 'blank']]]
        ]);
        $result = $validator->execute([
            'name' => 'abcd',
            'age' => 17,
            'email' => 'test@example',
            'func' => 'TEST',
            'regex' => 'Abc',
            'default' => 'default_value',
            'xxx' => 'xxx_value',
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
        $this->assertIsCallable($validator->rules['func']['rules']);
        $this->assertEquals('正则表达式不匹配', $validator->errors['regex']);
        $this->assertEquals('默认不能为空', $validator->errors['default']);
        $this->assertEquals('xxx验证不通过', $validator->errors['xxx']);
        $this->assertEquals('正则表达式不匹配', $validator->errors['xxx2']);

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
    }
    public function testLt()
    {
        $this->assertTrue(ValidatorClass::lt('3', 4));
        $this->assertFalse(ValidatorClass::lt('4', 4)); 
    }
    public function testGte()
    {
        $this->assertTrue(ValidatorClass::gte('4', 3));
        $this->assertTrue(ValidatorClass::gte('3', 3)); 
    }
    public function testLte()
    {
        $this->assertTrue(ValidatorClass::lte('3', 4));
        $this->assertTrue(ValidatorClass::lte('4', 4)); 
    }

    public function testEq()
    {
        $this->assertTrue(ValidatorClass::eq('4', 4));
        $this->assertFalse(ValidatorClass::eq('4', 3));
    }
    public function testNeq()
    {
        $this->assertTrue(ValidatorClass::neq('4', 3));
        $this->assertFalse(ValidatorClass::neq('4', 4)); 
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


    public function testArray()
    {
        $this->assertTrue(ValidatorClass::array([1, 2, 3]));
        $this->assertFalse(ValidatorClass::array('not an array'));
    }

    public function testFloat()
    {
        $this->assertTrue(ValidatorClass::float('123.456'));
        $this->assertFalse(ValidatorClass::float('123.456.789'));
    }

    public function testNum()
    {
        $this->assertTrue(ValidatorClass::num('123'));
        $this->assertFalse(ValidatorClass::num('123.456'));
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
}
