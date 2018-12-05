### ENPHP Framework是一个轻量级，开包即用的PHP框架。

特别适合中小型网站的开发建设，自带数据表验证，多数据库分离支持，常用的库文件。
以简化那些80%重复功能为目标打造出此框架，如果您厌烦那些重量级框架，请不妨试试ENPHP Framework。

### 版本库依赖

> 版本 PHP7+<br>
> mb_string扩展<br>
> GD2扩展

### 文档目录索引

&nbsp;|&nbsp;|&nbsp;|&nbsp;
-----|-----|-----|-----
[入口文件配置](#入口文件配置)       | [常量设置](#常量设置)       | [数据库配置](#数据库配置)  | [自定义配置数据字典](#自定义配置数据字典)
[全局变量数组](#全局变量数组)      | [数据库基本操作](#数据库基本操作)     | [Model数据模型](#Model数据模型)      | [Model数据验证](#Model数据验证)
[Controller控制器](#Controller控制器) | [View视图](#View视图)   | [Helper帮助函数](#Helper帮助函数)    | [Input输入](#Input输入)
[Output输出](#Output输出)       | [Session会话](#Session会话)   | [Cookie管理](#Cookie管理)       |[Lang多语言配置](#Lang多语言配置)
[Redis缓存](#Redis缓存)        | [Security安全](#Security安全)  | [Upload上传文件](#Upload上传文件)  |[Html标签生成](#Html标签生成)
[Grid表格生成](#Grid表格生成)  |[Image图片修饰](#Image图片修饰)  |[Smtp发送邮件](#Smtp发送邮件)   |[Captcha验证码生成](#Captcha验证码生成)
[应用程序目录布局说明](#应用程序目录布局说明) | | | 


文档内容
====

### 保留属性及函数方法
> 不推荐覆盖，除非你了解全局代码。

#### 保留的属性
    $this->input //输入类实例
    $this->config //配置类实例
    $this->output //输出类实例
    $this->session //会话类实例
    $this->cookie //cookie类实例
    $this->lang //默认多语言类实例
    $this->helper //帮助类实例
    $this->security //安全类实例
    $this->redis //redis类实例
    $this->vars //全局变量数组
    $this->db //默认数据库实例

#### 保留的方法函数
    $this->db() //自定义数据库并返回实例
    $this->lang() //自定义语言类并返回实例
    $this->load() //加载类并返回实例
    $this->model() //加载model并返回实例
    $this->redis() //自定义redis并返回实例

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

> 常量文件位置在APP_DIR/config/下面三个子目录test,production,development中的constans.php文件分别按环境设置。

地址路由配置,以/index.php?c=main&a=index为例子。<br>
c代表控制器类名字，默认控制器为Main。<br>
a代表action方法名称，默认action为index。<br>
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

> 数据库文件位置在APP_DIR/config/下面三个子目录test,production,development中的database.php文件分别按环境设置。<br>
> 暂时只支持mysqli

default为默认数据库，可以直接$this->db访问默认数据库<br>
$this->db('read)访问read数据库
```php
//例子

return [
    //默认数据库
    'default' => 
    [
        'driver' => 'mysqli',
        'host' => 'set.database.to.hosts.file',
        'username' => 'root', 
        'password' => '123456',
        'database' => 'dataname',
        'port' => 3306,
        'charset' => 'utf8'
    ],
    
    //读数据库
    'read'=>
    [
        'driver' => 'mysqli',
        'host' => 'set.database.to.hosts.file',
        'username' => 'root', 
        'password' => '123456',
        'database' => 'dataname',
        'port' => 3306,
        'charset' => 'utf8'
    ]
];
```

### 自定义配置数据字典

> 自定义配置数据字典，主要为了应对某些应用较多的元数据存储访问<br>
> 保存于APP_DIR/config目录下面PHP文件内容为数组

以APP_DIR/config/test.php为范例,配合$this->config使用
```php
//test.php内容
return ['key2'=>'val2','key'=>['a','b','c'];

//var_dump $this->config->test
//输出 ['key2'=>'val2','key'=>['a','b','c']

//echo $this->config->test['key'][0]
//输出 a
```

### 全局变量数组
全局变量数组有两个，$var和$instances。
```php
//全局实例初始化数组，包含所有已实例化的核心类
$instances = [];

//全局变量数组，
//$this->vars 可以直接访问，
//默认已包含$this->vars['controller']当前控制器值
//默认已包含$this->vars['action']当前action值
$vars = [];
```


### 数据库基本操作

> 暂时只支持mysqli<br>
> 配置好数据库以后，我们可以 $this->db 调用默认数据库。<br>
> 或者可以$this->db('read')调用一个已配置为'read'的数据库。

#### $this->db->query($sql) 方法
原始SQL语句执行，如是select返回数据集，delete，insert，update返回布尔值。

```php
//返回一个结果集对象句柄
$result=$this->db->query('select * from table1 where f=2;');

//返回数据的条数，int类型
$result->num_rows;

//返回结果集其中一条数据，默认第一条以对象形式返回字段
$result->row();

//以数组形式返回第3条数据
//$row['f'];
$row=$result->row(2,'array');


//以对象形式返回第4条数据
//$row->f;
$row=$result->row(3,'object')


//返回数据集，默认对象形式
$recordset=$result->result();
foreach($recordset as $rs){
    echo $rs->f;
}

//数组形式返回数据集
$recordset=$result->result('array');
foreach($recordset as $rs){
    echo $rs['f'];
}
```

#### $this->db->select($table,$condition=[]) 方法
查询数据库表返回数据集对象。
> 参数说明
>> $table 数据表名称<br>
>> $condition 查询条件数组，如果为空返回全部
```php
//查询条件
$condition= [
     'where' => ['f1'=>'2','f3>'=>'3','f4!='=>'8'], //where条件,支持运算符>,<,<>,!=,=,in,like,>=,<=
     'fields' => ['f1','f2','f3'],//返回字段
     'orderby' => ['f1'=>'desc','f2'=>'asc'], //排序
     'limit' => [0,20] //返回数据条数 ，也可以是一个int值，如：limit=>10
  ];
 
//返回数据句对象
$recordset=$this->db->select('table1',$condition);
foreach($recordset->result() as $rs){
    echo $rs->f1;
}
```

#### $this->db->insert($table,$data) 方法
插入数据到数据库表，返回布尔值。
>参数说明
>> $table 数据表名称 <br>
>> $data  插入表的数据数组
```php
//需要插入的数据
$data=['f1'=>'1','f2'=>'2'];

$rs=$this->db->insert('table1',$data)；
if($rs){
   //插入成功，返回最后一条插入语句产生的自增ID
   $this->db->insert_id;
}
```

#### $this->db->delete($table,$where=[]) 方法
删除数据集，返回布尔值
> 参数说明
>> $table 数据表名称<br>
>> $where where条件数组，为空删除全部，谨慎使用！
```php
//删除条件
$data=['f1'=>'1','f2'=>'2'];

$rs=$this->db->delete('table1',$data)；
if($rs){
   //删除成功，返回影响数据行数
   $this->db->affected_rows;
}
```

#### $this->db->escape($str) 方法
SQL语句中的特殊字符进行转义，返回转义后字符串。
```php
//参见 http://php.net/manual/zh/mysqli.real-escape-string.php
$this->db->escape('str');
```

#### $this->db->replace($table,$data) 方法
数据集主键如果存在就替换不然插入新数据，返回布尔值。
> 参数说明
>> $table 数据表名称<br>
>> $data 需要操作的数据数组
```php
//需要插入或替换的数据，如果主键primary=1已存在，即替换本条数据，不然插入新数据。
$data=['primary'=>1,'f1'=>'1','f2'=>'2'];

$rs=$this->db->replace('table1',$data)；
```

#### $this->db->update($table,$data,$where=[]) 方法
更新数据，返回布尔值或影响行数。
>参数说明
>> $table 数据表名称<br>
>> $data 需要更新的数据数组<br>
>> $where where条件，为空更新全部
```php
$data=['f1'=>'3','f3'=>'1'];
$where=['id'=>2];

$rs=$this->db->update('table1',$data,$where);

if($rs){
   //更新成功，返回影响数据行数
   $this->db->affected_rows;
 
}
```

#### $this->db->close() 方法
关闭数据库链接，返回布尔值。<br>
正常情况下，框架在执行完到最后自动关闭链接，也可以提前手动关闭。
```php
//关闭默认数据链接
$this->db->close()；

//关闭read数据链接
$this->db('read')->close()；

```


### Model数据模型

> 每个model必须于数据库某个表对应。<br>
> model文件必须放置在APP_DIR/model/目录下，文件名与类名一致，区分大小写。

通过继承\system\Model，我们可以使用框架自带的功能便捷操作数据。<br>
例如我们创建APP_DIR/model/Tablemodel.php。
```php
//Tablemodel.php

//命名空间
namespace model;

//类必须继承一个自定义\inherit\Model类或是系统\system\Model类
class Tablemodel extends \inherit\Model
{

    //构造函数必须有
    public function __construct()
    {
    
        //运行上级构造函数
        parent::__construct();
        
        //必须设置一个数据表
        $this->table = 'test';
        
        //必须设置一个主键
        $this->primary = 'id';

        //设置一个表的结构，以便验证过滤
        $this->schema = [
            'id' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => 'ID 不能为空'],
                'literal' => 'ID',
                'default' => null,
                'required' => false
            ],
            'name' => [
                'validate' => ['regex' => '/^\S+$/', 'message' => '名称不能为空'],
                'literal' => '名称',
                'default' => '',
                'required' => true
            ]];
    }

}

//我们可以这样调用model
$this->model('Tablemodel')->one(1);
```


#### $this->RDB 属性
设置读数据库，默认为default数据库
```php
$this->RDB='read_database';
```

#### $this->WDB 属性
设置写数据库，默认为default数据库
```php
$this->RDB='write_database';
```

#### $this->table 属性
设置model对应数据表
```php
$this->table='table1';
```

#### $this->primary 属性
设置数据表主键字段
```php
$this->primary='id';
```

#### $this->schema 属性
设置数据表结构，以便验证过滤，数组key必须和字段名一致

> validate['regex'] 正则验证字段数据合法性

> validate['message'] 提示信息

> filter 过滤数据，blank|tag|entity 三个值组合使用，
>> blank把连续多个空白字符转换成一个,<br>
>> tag过滤html标签,<br>
>> entity把html标签转换成实体字符。

> literal 字段的字面名字

> default 字段默认值

> required 是否必须填写字段

```php
      $this->schema = [
            'id' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => 'ID 不能为空'],
                'literal' => 'ID',
                'default' => null,
                'required' => false
            ],
            'name' => [
                'validate' => ['regex' => '/^\S+$/', 'message' => '名称不能为空'],
                'filter'=>'blank|tag|entity'
                'literal' => '名称',
                'default' => '',
                'required' => true
            ]
      ];
```

#### $this->all($fields) 方法
获取数据表全部数据集,大表谨慎使用。
```php
$recordset=$this->all(['fname1','fname2']);

//注意直接返回数据集，而不是result对象
foreach($recordset as $rs){
    echo $rs->fname1;
}

```

#### $this->belongs($model, $relation_model, $relation_foreign_name, $where, $condition) 方法
多对多获取表数据,返回对象数据集

> 参数说明
>> $model 需要关联的model名称<br>
>> $relation_model 关系表model名称<br>
>> $relation_foreign_name 关联表主键名在关系表中的字段名<br>

>> $where=['local_relation_filed_name' => 'local_primary_value']
>>> $local_relation_filed_name 本表在关系表字段名<br>
>>> $local_primary_value 本表主键值

>> $condition 参见$this->select()参数

```php

$this->belongs($model, $relation_model, $relation_foreign_name, $where, $condition);

```

#### $this->count($where=[]) 方法
获取数据表数据条数,适合myisam表。
```php
//带条件的计算
$this->count(['field'=>'val']);

//获取表总条数
$this->count();
```

#### $this->delete($where=[]) 方法
删除表数据，成功返回影响数不然返回false。
```php
$rs=$this->delete(['f1'=>'2']);
if($rs){
    //删除成功返回影响数
    echo $rs;
}
```

#### $this->hasMany($model,$where,$condition=[]) 方法
一对多获取副表数据,返回对象数据集。
> 参数说明
>> $model 需要关联的model
     
>> $where=['foreign_name' => 'local_primary_value']
>>> $foreign 外表字段名<br>
>>> $local_primary_value 本表主键值

>> $condition 参见$this->select()参数

```php
$this->hasMany($model, $where, $condition);
```

#### $this->hasOne($model,$primary_value) 方法
一对一获取数据,返回一行对象数据。
> 参数说明
>> $model 关联的model
>> $primary_value 主键唯一值
```php
$this->hasOne($model, $primary_value);
```

#### $this->insert($data=[]) 方法
插入数据，返回布尔值。
```php
$data=['f1'=>'1','f1'=>'2'];

$rs=$this->insert($data);

if($rs){
    //插入成功
}
```

#### $this->lastid() 方法
获取最后插入的自增主键ID。
```php
$data=['f1'=>'1','f1'=>'2'];

$rs=$this->insert($data);

if($rs){
    //获取最后插入自增主键
    echo $this->lastid();
}
```

#### $this->one($id) 方法
通过主键数字ID或唯一字段获取一条记录。
```php
//如果是主键数字id
$this->one(12);

//如果是唯一字段
$this->one(['uniqname'=>'abc']);
```

#### $this->query($sql) 方法
执行通用SQL语句,<br>
如果是select返回基础数据库result对象，<br>
执行update，insert，delete返回布尔值。
```php
$result=$this->query('select * from table1');
```

#### $this->select($condition=[]) 方法
按条件获取表数据对象集,参数为空，返回全部数据。
```php
$condition=[
     'where' => ['f1'=>'2'],
     'fields' => ['f1','f2'],
     'orderby' => ['f1'=>'desc','f2'=>'asc'],
     'limit' => [0,20] //或 'limit'=>20
   ];

$this->select($condition);
```

#### $this->update($data,$where=[]) 方法
更新数据记录，成功返回影响行数，失败返回false
```php
$data=['f1'=>2,'f2'=>3];
$where=['id'=>12];

$rs=$this->update($data,$where);

if($rs){
    //修改成功，返回影响行数
    echo $rs;
}
```

#### $this->where($where,$fields=[]) 方法
按条件返回数据对象集,主要为了简化$this->select()
```php
$where=['f1'=>'2'];
$fields=['f1','f2'];
$this->where($where,$fields);
```

#### $this->safe 属性
验证过滤入库字段数据，返回system/Safe实例。
> 具体参见 文档Model数据验证部分
```php
//可以在控制器这样调用
$this->model('Tablemodel')->safe->clear($data);
```


### Model数据验证
> Model数据验证，为了用户输入数据的合法性，<br>
> 提供了几个实用方法函数以配合Model的$this->schema属性使用。

#### $this->safe->illegalFields 属性
> 接受返回验证不通过非法字段名数组

#### $this->safe->notMemberFields 属性
> 接受返回不在表字段中的非法字段名数组

#### $this->safe->incompleteFields 属性
> 接受返回未完成及必须填写的字段名数组

#### $this->safe->clear($data) 方法
清理不存在于schema里面的字段，<br>
不是成员的字段保存于$this->notMemberFields<br>
返回清理后的数据
```php
//清理之前的数据
$beforedata=[
     //假如nomember不存在于$this->schema中，将被清理掉
    'notmember'=>'val',
    //假如f1字段名存在于$this->schema
    'f1'=>'val'
];

//清理之后
$afterdata=$this->safe->clear($data);

//输出['f1'=>'val']
var_dump($afterdata);

//输出不是成员字段名['notmember']
var_dump($this->notMemberFields);
```

#### $this->safe->complete($data) 方法
验证是否缺少必要字段，<br>
缺少的必要字段保存于$this->incompleteFields<br>
返回布尔值
```php
//假如$this->schema包含字段f1,f2必须填写不能为空

//注意$data没有包含字段f2
$data=['f1'=>'val'];

//验证完整性
$result=$this->safe->complete($data);

if($result){
   //通过完整性验证
}else{
    //没有通过验证

    var_dump($this->incompleteFields);
    //输出['f2']
}
```

#### $this->safe->merge($data) 方法
与schema默认数据覆盖合并，并且清理不存在于schema里面的字段，<br>
不是成员的字段保存于$this->notMemberFields<br>
返回合并及清理的数据
```php
//假如$this->schema中存在f1字段，默认值等于val1

//注意f1现在的值为val2
$data=['f1'=>'val2','notmember'=>'valxx'];

//合并覆盖数据并清理非成员字段
$result=$this->safe->merge($data);

//输出['f1'=>'val2']
var_dump($result);

//输出不是成员字段名['notmember']
var_dump($this->notMemberFields);

```

#### $this->safe->validate($data) 方法
验证数据合法性，非法字段保存于$this->illegalFields<br>
返回布尔值
```php
//假如$this->schema中字段f1必须为int类型

//注意f1是字符串
$data=['f1'=>'val'];

//验证合法性
$result=$this->safe->validate($data);

if($result){
   //通过合法性验证
}else{
    //没有通过验证

    var_dump($this->illegalFields);
    //输出['f1']
}
```

### Controller控制器

#### 创建一个控制器
> 控制器文件必须放置在APP_DIR/module/module_name目录下，文件名必须和类名一致。

> 必须继承system/Controller。

创建一个控制器，以APP_DIR/module/www/Testcontroller.php为例。
```php
//命名空间
namespace module\www;

//Testcontroller继承自定的inherit/Controller或system/Controller
class Testcontroller extends \inherit\Controller
{
    public function index()
    {
        $data['hello_world']='hello wolrd';
        
        //调用一个model
        $this->model('Testmodel')->select();
        
        //获取表单全部字段数据
        $postdata=$this->input->post();
        
        //清理不是schema成员字段
        $afterdata=$this->model('Testmodel')->safe->clear($data);
        
        //插入表单提交的数据到数据库
        $this->model('Testmodel')->insert($afterdata);

        //视图输出
        $this->output->view('main',$data);
    }
}
```

### View视图
> 视图模板文件必须放置APP_DIR/template/module_name/目录下面

> 模板文件都是标准的原生php与htm混合代码，框架没有专门的模板功能
#### 创建视图模板
例如我们创建一个APP_DIR/template/www/test.php，www为module模块名。
```php
<html>
    <head><title><?php echo $title; ?></title></head>
    <body>
        <h1>
            <?php echo $heading; ?>
        </h1>
        <div><?php echo $content; ?></div>
    </body>
</html>
```
我们可以在控制器里调用模板。<br>
比如下面代码：
```php
//模板变量
$data=['title'=>'网页标题','heading'=>'小标题','content'=>'内容'];

//只需要填写文件名，支持子目录
$this->output->view('test'，$data);
```

### Helper帮助函数
> 自定义帮助函数文件必须放置APP_DIR/helper/目录下面

> 必须以类的形式组织功能函数

#### 自定义helper函数方法
例如我们创建APP_DIR/helper/Testhelper.php
```php
//命名空间
namespace helper;

//我可以继承\system\System,以便使用框架内建属性和函数方法,如果不需要可以忽略
class Testhelper extends \system\System
{
    public function returntex($param){
    
      //只有继承\system\System才能调用此方法
      $this->input->get('str');
      
      return $param;
    }
}
//我们可以在控制器，视图，model里面这样调用
//$this->helper->testhelper->returntex('str');
```

#### $this->helper->url($param=[],$path=ENTRY,$anchor='') 方法

> 此方法框架自带

>参数说明
>>param   查询字符串数组 <br>
>>path    入口文件路径，默认值 ENTRY常量值 <br>
>>anchor  锚点

配合常量URL使用，返回被匹配的URL地址字符串
```php
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

不匹配URL常量返回
```php
//假如入口文件为index.php 

$this->helper->url();
//输出 /index.php?c=main&a=index

$this->helper->url(['p1'=>'1','p2'=>2]);
//输出 /index.php?c=main&a=index&p1=1&p2=2

//注意a参数和/mod/list.php以及anchor
$this->helper->url(['a'=>'lists','p1'=>'1','p2'=>2],'/mod/list.php','anchor');
//输出 /mod/list.php?c=main&a=lists&p1=1&p2=2#anchor
```

#### $this->helper->pager($size, $total, $page, $url, $visible = 5) 方法

框架自带分页方法
```php
//每个页面10条数据
$size=10;

//数据总数
$total=100;

//当前页码
$page=$this->input->get('page',1);

//地址模板
$url='/index/list/<%page%>.html';

//显示多少个页码链接
$visible=5;

$pager=$this->helper->pager($size, $total, $page, $url, $visible);

echo $pager;
//输出
//<div class="pager">
//<a class="number" href="/index/list/1.html">1</a>
//<a class="number " href="/index/list/2.html">2</a>
//<a class="number " href="/index/list/3.html">3</a>
//<a class="number " href="/index/list/4.html">4</a>
//<a class="number " href="/index/list/5.html">5</a>
//<span class="ellipsis">...</span>
//<a  class="number" href="/index/list/27">27</a>
//<a href="/index/list/2.html" class="next">下一页</a>
//<span class="info">共 402 条记录</span>
//</div>
```

### Input输入

#### $this->input->get() 方法
获取地址查询字符串值，不填参数返回全部数据数组
```php
//如果var_name为null，就返回默认值default_str
$this->input->get('var_name','default_str');

//返回全部数据
$this->input->get();
```
#### $this->input->post() 方法
获取表单数据，没有参数返回全部表单字段数组
```php
//字段不存在返回null
$this->input->post('field_name');

//返回全部
$this->input->post();
```
#### $this->input->ip() 方法
获取v4 IP地址
```php
//如果没有获取成功返回0.0.0.0
$this->input->ip();
```
#### $this->input->isAjax() 方法
判断是否ajax请求，前端必须带HTTP_X_REQUESTED_WITH请求头部<br>
返回布尔值
```php
//$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
$this->input->isAjax();
```
#### $this->input->body() 方法
获取原始请求数据,一般用于API接口
```php
$this->input->body();
```
#### $this->input->referer() 方法
获取上一个来源地址url，以便重定向
```php
//如果没有为空
$prev_url=$this->input->referer();

//重定向
$this->output->redirect($prev_url);
```
#### $this->input->method() 方法
获取当前请求方法
```php
//返回 POST，GET，OPTION等
$this->input->method();
```

### Output输出

#### $this->output->compress($string) 方法
删除html多余空白字符<br>
返回处理之后的字符串
```php
$string='<b style=""    >  str </b><div>   ste  </div>';

$result=$this->output->compress($string);

echo $result;
//输出 <b style="">str</b><div>ste</div>
```

#### $this->output->error($name = 'general', $data =[]) 方法

> 错误页面模板必须放置在APP_DIR/error/目录下面

> 参数说明
>> $name模板文件名，默认模板 genrnal<br>
>> $data变量数据数组，默认数组 $data=['heading' => 'Error Message', 'message' => 'An error occurred.']

错误页面设置,自动echo内容
```php
//通用错误页面,
$this->output->error();

//自定义错误页面，假如APP_DIR/error/404.php已存在
$this->output->error('404',['title'=>'Not Found']);
```
#### $this->output->json($status, $message, $data=[], $return=false) 方法
输出json格式数据

> 参数说明
>> $status 设置一个状态码<br>
>> $message 设置一个状态消息字符串<br>
>> $data 需要输出的数组数据，默认为空数组<br>
>> $return 设置布尔值，是否返回内容自定义echo输出，默认自动echo内容<br>

```php
$this->output->json('1002','操作成功',['data'=>'val','data2'=>'val2']);
//输出 {'status':'1002','message':'操作成功','data':{'data1':'val','data2':'val2'}}

//有返回值的自定义输出
$result=$this->output->json('1002','操作成功',['data'=>'val','data2'=>'val2'],true);
echo $result;
```
#### $this->output->redirect($uri, $http_response_code=302) 方法
请求重定向

> 参数说明
>> $uri 重定向地址<br>
>> $http_response_code  http头响应码，默认值为302

```php
//转到index.php，默认响应码302
$this->output->redirect('/index.php');

//重定向到404页面
$this->output->redirect('/notfound.php',404);
```

#### $this->output->status($http_status_code) 方法
设置响应头
```php
$this->output->status('404');
//如同 header('HTTP/1.1 404 Not Found',true)
```
#### $this->output->view($view, $data = [], $return = false, $compress = false) 方法
输出视图,自动echo输出。

> 参数说明
>> $view 视图模板文件名称<br>
>> $data 视图变量数据数组<br>
>> $return 是否返回内容自动输出，默认值false<br>
>> $compress 是否压缩HTML，默认值false

```php
//假如已存在APP_DIR/template/www/test.php

//模板数据
$data['var1'=>'val','var2'=>'val'];

//框架自动echo输出，
$this->output->view('test',$data);

//返回自定义echo输出，并压缩html
$result=$this->output->view('test',$data,true,true);
echo $result
```

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
 
