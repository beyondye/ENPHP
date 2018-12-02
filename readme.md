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
--------

#### 入口文件配置

> 入口文件一般是网站的根目录index文件，具体有几个重要的常量配置。

 设置运行环境变量，三个值分别为测试环境，产品环境，开发环境。
```php
//
// test,production,development
define('ENVIRONMENT', 'development');
```

```php
//您开发的应用程序目录常量APP_DIR设置
define('APP_DIR', realpath('app_dir') . DIRECTORY_SEPARATOR);
```

```php
//框架系统文件目录常量，可以任意存放到其他文件夹
define('SYS_DIR', realpath('system_dir') . DIRECTORY_SEPARATOR);
```
```php
//设置controller模块常量，模块必须是APP_DIR目录下module文件夹的子目录
define('MODULE', 'www');
```

```php
//设置模板目录常量，模板必须是APP_DIR目录下template文件夹的子目录
define('TEMPLATE', 'www');

```

##### 常量设置

#### 数据库配置

#### 自定义配置数据字典

#### 全局变量数组

#### 数据库操作

#### Model数据模型

##### Model数据验证

#### Controller控制器

#### View视图

#### Helper帮助函数



#### Input输入


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


#### Output输出

#### Session会话

#### Cookie管理

#### Lang多语言配置

#### Redis缓存

#### Security安全

#### Upload上传文件

#### Html标签生成

#### Grid表格生成

#### Image图片修饰

#### Smtp发送邮件

#### Captcha验证码生成

#### 应用程序目录布局说明


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
 
