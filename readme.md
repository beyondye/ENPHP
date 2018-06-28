框架入门介绍

运行环境 nginx,php5.6 ,redis, mysqli, mb_string, gd

<br>
PHP代码编写规范遵循php psr
换行符使用LF  
缩进4个空格
请参考此页面介绍  https://github.com/PizzaLiu/PHP-FIG


框架简单操作使用

目录结构（以www应用为列）
|——system    系统框架程序目录
|
|——application  应用程序目录
|
|————helper   自定义工具帮助库
|————library  自定义公共类库
|————language  语言包目录
|————font 字体目录
|————document 开发文档目录
|
|
|————module   程序模块
|——————www  模块名
|———————main.php 具体controller业务逻辑文件   
|
|
|————model    model文件目录
|——————test.php 具体数据model


|————template  视图模板文件
|——————www  模块名
|————————main.php 具体模板文件


|————config    配置文件目录
|——————development 开发环境配置
|————————database.php 数据库配置文件
|————————constance.php 常量配置文件
|————————redis.php redis配置文件
|——————test 开发环境配置
|————————database.php 
|————————constance.php 
|————————redis.php 
|——————production 产品环境配置
|————————database.php 
|————————constance.php 
|————————redis.php 
|
|
|————inherit    model和controller重写继承目录
|————————controller.php 
|————————model.php 


|——public    应用程序入口目录
|————static    静态文件资源
|————www   此目录绑域名用
|——————index.php    入口文件

---------------------------------------------

类库调用方法实例（仅限controller和model中调用）

核心类调用
$this->input->get('querystring')
$this->input->post('querystring')
$this->cookie->get()
$this->cookie->set()
$this->session->get()
$this->session->set()

model的调用
$this->model('model_name') 返回对象实例
$this->model('dir/model_name') 返回对象实例
$this->model('dir/model_name')->one(id) 返回数据

自定义helper的调用
$this->helper->form->select()

视图模板的调用
$this->output->view('view_name', $data); //输出网页
$this->output->json()//输出json

数据库操作
$this->db  自动调用默认数据库
$this->db->query(sql)
$this->db('db_name') 调用制定数据库
$this->db('db_name')->query()

入口地址結構
index.php?c=controller_name&a=action_name
c為控制器名稱 默認控制器main
a為方法名稱 默認方法index

redis调用方法
全部继承原有方法，调用如：
$this->redis->hset()  默认redis实例服务器
$this->redis('write')->hset() Write redis实例服务器
$this->redis->get()
$this->redis->set()


语言包调用
$this->lang->mod_name['key'];
$this->lang('en_us')->mod_name['key'];

加载配置数据
$this->config->data['action_method']

