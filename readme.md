### ENPHP Framework是一个轻量级，开包即用的PHP框架。

特别适合中小型网站的开发建设，自带数据表验证，多数据库分离支持，常用的库文件。
以简化那些80%重复功能为目标打造出此框架，如果您厌烦那些重量级框架，请不妨试试ENPHP Framework。

### 版本依赖

> 版本 PHP7+

> mb_string扩展

> GD2扩展

### 文档目录索引

&nbsp;|&nbsp;|&nbsp;|&nbsp;
-----|-----|-----|-----
[入口文件配置](#入口文件配置)       | [常量设置](#常量设置)       | [数据库配置](#数据库配置)  | [自定义配置数据字典](#自定义配置数据字典)
[全局变量数组](#全局变量数组)      | [数据库操作](#数据库操作)     | [Model数据模型](#Model数据模型)      | [Model数据验证](#Model数据验证)
[Controller控制器](#Controller控制器) | [View视图](#View视图)   | [Helper帮助函数](#Helper帮助函数)    | [Input输入](#Input输入)
[Output输出](#Output输出)       | [Session会话](#Session会话)   | [Cookie管理](#Cookie管理)       |[Lang多语言配置](#Lang多语言配置)
[Redis缓存](#Redis缓存)        | [Security安全](#Security安全)  | [Upload上传文件](#Upload上传文件)  |[Html标签生成](#Html标签生成)
[Grid表格生成](#Grid表格生成)  |[Image图片修饰](#Image图片修饰)  |[Smtp发送邮件](#Smtp发送邮件)   |[Captcha验证码生成](#Captcha验证码生成)
[应用程序目录布局说明](#应用程序目录布局说明) | | | 


文档内容
====

### 入口文件配置

> 入口文件一般是网站的根目录index.php文件，有几个重要的常量配置。

 设置运行环境变量，三个值分别为测试环境，产品环境，开发环境。
```php
// test,production,development
define('ENVIRONMENT', 'development');
```

您开发的应用程序目录常量APP_DIR设置。
```php
define('APP_DIR', realpath('app_dir') . DIRECTORY_SEPARATOR);
```

框架系统文件目录常量，可以存放到其他地方，以便共用和升级。
```php
define('SYS_DIR', realpath('system_dir') . DIRECTORY_SEPARATOR);
```

设置controller模块常量，模块必须是APP_DIR目录下module文件夹的子目录。
```php
define('MODULE', 'www');

```
设置模板目录常量，模板必须是APP_DIR目录下template文件夹的子目录。
```php
define('TEMPLATE', 'www');
```

### 常量设置

> 常量文件位置在APP_DIR/config/下面三个子目录test,production,development中的constans.php文件分别按环境设置

地址路由配置,以/index.php?c=main&a=index为例子。

c代表控制器类名字，默认名字控制器为Main。

a代表action方法名称，默认action为index。

你可以自定义设置这些值。

```php
define('DEFAULT_CONTROLLER', 'main');
define('DEFAULT_ACTION', 'index');
define('CONTROLLER_KEY_NAME', 'c');
define('ACTION_KEY_NAME', 'a');
```

输出字符编码设置，以便$this->output->view()和$this->output->json()输出
```php
define('CHARSET', 'utf-8');
```

Cookie相关设置
```php
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false);
define('COOKIE_PATH', '/');
define('COOKIE_HTTPONLY', false);
define('COOKIE_EXPIRE', 0);
```

Session设置
```php

//自定义session cookie名
define('SESSION_COOKIE_NAME', 'SE');

//session保存时间，0为关闭浏览器即失效，秒为单位
define('SESSION_EXPIRE', 0);
```

安全配置
```php
//加密安全混淆值
define('ENCRYPTION_KEY', 'weryi9878sdfddtgtbsdfh');

//表单提交token session 名称
define('TOKEN_SESSION_NAME', '34efddddre');

//表单token字段名
define('TOKEN_INPUT_NAME', 'fh40dfk9dd8dkfje');

//token过期时间，秒为单位
define('TOKEN_EXPIRE', 3600);
```

多语言应用
```php
//默认语言环境
define('LANG', 'zh_cn');
```

URL重写转换输出模版，和路由无关，以配合$this->helper->url()使用
```php
//url 重写
define('URL', ['mod_name'=>['controller_name/action_name'=>'/{controller_key}/{action_key}']]);


//例子 

//注意$this->helper->url()参数和数组key的顺序
define('URL', [
    'www' => [ 
    //www表示模块名称
    
        'main/index' => '/',  
        //echo $this->helper->url(['c'=>'main','a'=>'index'])
        //输出 /
        
        'main/lists/type' => '/list/{type}.html',
         //echo $this->helper->url(['c'=>'main','a'=>'lists','type'=>'2'])
         //输出 /list/2.html
        
        'main/lists/type/page' => '/list/{type}_{page}.html'
         //echo $this->helper->url(['c'=>'main','a'=>'lists','type'=>'2','page'=>'34']) 
         //输出 /list/2_34.html
                 
    ]
]);

```

### 数据库配置

### 自定义配置数据字典

#### 全局变量数组

### 数据库操作

### Model数据模型

### Model数据验证

### Controller控制器

### View视图

### Helper帮助函数



### Input输入


获取地址查询字符串
```php
//如果var_name为null，就返回默认值default_str
$this->input->get('var_name','default_str');
```
获取表单数据
```php
//字段不存在返回null
$this->input->post('field_name');
```

获取v4 IP地址
```php
//如果没有获取成功返回0.0.0.0
$this->input->ip();
```

判断是否ajax请求
```php
//$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
//返回布尔值
$this->input->isAjax();
```

获取原始请求数据
```php
$this->input->body();
```

获取上一个来源地址url
```php
//如果没有为空
$this->input->referer();
```

获取当前请求方法
```php
//返回 POST，GET，OPTION等
$this->input->method();
```


### Output输出

### Session会话

### Cookie管理

### Lang多语言配置

### Redis缓存

### Security安全

### Upload上传文件

### Html标签生成

### Grid表格生成

### Image图片修饰

### Smtp发送邮件

### Captcha验证码生成

### 应用程序目录布局说明


|——system    系统框架程序目录 

|——application  应用程序目录  
|————helper   自定义工具帮助库  
|————library  自定义公共类库  
|————language  语言包目录  
|————font 字体目录  
|————document 开发文档目录  
|————module   程序模块  
|——————www  模块名  
|———————-main.php 具体controller业务逻辑文件  

|————model    model文件目录  
|——————test.php 具体数据model 
  
|————template  视图模板文件  
|——————www  模块名   
|————————main.php 具体模板文件  
  
|————config    配置文件目录  
|——————development 开发环境配置  
|————————database.php 数据库配置文件  
|————————constans.php 常量配置文件  
|————————redis.php redis配置文件  
|——————test 开发环境配置  
|————————database.php   
|————————constans.php   
|————————redis.php   
|——————production 产品环境配置  
|————————database.php   
|————————constans.php   
|————————redis.php   

|————inherit    model和controller重写继承目录  
|————————controller.php   
|————————model.php   
  
|——public    应用程序入口目录  
|————static    静态文件资源  
|————www   此目录绑域名用  
|——————index.php    入口文件  
 
