### ENPHP Framework是一个轻量级，开包即用的PHP框架。

特别适合中小型网站的开发建设，自带数据表验证，多数据库分离支持，常用的库文件。
以简化那些80%重复功能为目标打造出此框架，如果您厌烦那些重量级，请不妨试试ENPHP Framework。

### 版本依赖

> 版本 PHP7+

> mb_string扩展

> GD2扩展

### 文档目录索引

&nbsp;|&nbsp;|&nbsp;|&nbsp;
-----|-----|-----|-----
[入口文件配置](#入口文件配置)       | [常量设置](#常量设置)       | [数据配置](#数据配置)  | [自定义配置数据字典](#自定义配置数据字典)
[全局变量数组](#全局变量数组)      | [数据库操作](#数据库操作)     | [Model数据模型](#Model数据模型)      | [Model数据验证](#Model数据验证)
[Controller控制器](#Controller控制器) | [View视图](#View视图)   | [Helper帮助函数](#Helper帮助函数)    | [Input输入](#Input输入)
[Output输出](#Output输出)       | [Session会话](#Session会话)   | [Cookie管理](#Cookie管理)       |[Lang多语言配置](#Lang多语言配置)
[Redis缓存](#Redis缓存)        | [Security安全](#Security安全)  | [Upload上传文件](#Upload上传文件)  |[Html标签生成](#Html标签生成)
[Grid表格生成](#Grid表格生成)  |[Image图片修饰](#Image图片修饰)  |[Smtp发送邮件](#Smtp发送邮件)   |[Captcha验证码生成](#Captcha验证码生成)
[应用程序目录布局说明](#应用程序目录布局说明) | | | 

----

#### 入口文件配置{#entry}

#### 常量设置

#### 数据库配置

#### 自定义配置数据字典

#### 全局变量数组

#### 数据库操作

#### Model数据模型

##### model数据验证

#### controller控制器

#### View视图

#### Helper帮助函数

#### input输入

#### output输出

#### session会话

#### cookie管理

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
 
基础功能
-------

### 输入类 ：system/Input

##### 获取地址查询字符串
```php
   //如果var_name为null，就返回默认值default_str
   $this->input->get('var_name','default_str');
```
##### 获取表单数据
```php
//字段不存在返回null
$this->input->post('field_name');
```

##### 获取v4 IP地址
```php
$this->input->ip();
```

##### 判断是否ajax请求
```php
//$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
$this->input->isAjax();
```

##### 获取原始请求数据
```php
$this->input->body();
```

##### 获取上一个来源地址url
```php
//如果没有为空
$this->input->referer();
```

##### 获取当前请求方法
```php
$this->input->method();
```


**model的调用**

``` php
$this->model('model_name') 返回对象实例  
$this->model('dir/model_name') 返回对象实例  
$this->model('dir/model_name')->one(id) 返回数据  
```
 
**自定义helper的调用** 

``` php
$this->helper->form->select()   
```

**视图模板的调用**

``` php
$this->output->view('view_name', $data); //输出网页  
$this->output->json()//输出json  
```
 
**数据库操作**

``` php
$this->db  自动调用默认数据库  
$this->db->query(sql)  
$this->db('db_name') 调用制定数据库  
$this->db('db_name')->query()  
 ```
 
***入口地址結構***

``` php
index.php?c=controller_name&a=action_name  
c為控制器名稱 默認控制器main  
a為方法名稱 默認方法index  

```
 
**redis调用方法**

``` php
$this->redis->hset()  默认redis实例服务器  
$this->redis('write')->hset() Write redis实例服务器  
$this->redis->get()  
$this->redis->set()  
 ```
 
**语言包调用**

``` php
$this->lang->mod_name['key'];  
$this->lang('en_us')->mod_name['key'];  
 ```
 
**加载配置数据** 

``` php
$this->config->data['action_method']  
```
