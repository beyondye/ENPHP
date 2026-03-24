<?php

declare(strict_types=1);

namespace system;

use system\database\DatabaseAbstract;
use system\model\ModelException;
use system\model\Select;
use system\model\Safe;

class Model
{

    //表名
    protected string $table;

    //主键
    protected string $primary;

    //主键是否自增
    protected bool $autoincrement = false;


    /**
     * @var array 表字段，带验证规则
     * @example
     * $fields=[
     *     'id' => 'integer',
     *     'category_id' => ['integer','unsigned'=>true,'max'=>255,'min'=>0],
     *     'name' => 'varchar',
     *     'price' => 'decimal',
     *     'discount' => ['decimal','length'=>10,'precision'=>2],
     *     'description' => ['varchar','length'=>255],
     *     'status' => ['enum','options'=>['active','inactive']],
     *     'content' => 'text',
     *     'created_at' => 'datetime',
     *     'updated_at' => 'datetime',
     *     'time' => 'datetime'
     * ];
     */
    protected array $schema = [];

    /**
     * @var array 可填充字段，带默认值
     * @example
     * $fillable=[
     *    'category_id'=>0,
     *    'name'=>'',
     *    'price'=>0.0,
     *    'discount'=>0.0,
     *    'description'=>'',
     *    'status'=>'active',
     *    'content'=>''
     * ];
     */
    protected array $fillable = [];


    //查询对象
    protected array $objects = [
        'select' => null,
    ];

    //数据库对象
    protected DatabaseAbstract $db;

    //构造函数
    public function __construct(DatabaseAbstract $db)
    {
        $this->db = $db;
    }


    //创建前事件
    protected function creating(): void {}

    //删除前事件
    protected function updating(): void {}

    //删除前事件
    protected function deleting(): void {}


    //查询方法
    public function select(array $fields = []): object
    {

        if ($this->objects['select']) {
            $select = $this->objects['select'];
        } else {
            $select = new Select();
            $select->table = $this->table;
            $select->schema = $this->schema;
            $select->primary = $this->primary;
            $this->objects['select'] = $select;
        }

        $select->fields = $fields;
        $select->db = $this->db;
        $select->wheres = [];
        $select->orders = [];
        $select->groups = [];

        return $select;
    }


    /**
     * 删除方法
     * @param float|string|int ...$wheres 删除条件
     * @return bool 是否删除成功
     * @example
     * delete(1); // primary key = 1
     * delete('1');// primary key = 1 
     * 
     * delete('id',1);// id = 1 
     * delete('id','1');// id = 1 
     * delete('id',[1,2,3]);// id in (1,2,3)
     * 
     * delete('id','=',1);// id = 1
     * delete('id','in',[1,2,3]);// id in (1,2,3)
     * delete('id','between',[1,10]);// id between (1,10)
     * 
     * delete('id','not in',[1,2,3],'and');// id not in (1,2,3) and
     * delete('id','not between',[1,10],'or');// id not between (1,10) or
     *
     * delete(['id','=',1],['name','=','张三']);// id = 1 and name = '张三'
     * delete(['id','in',[1,2,3],'or'],['name','=','张三']);// id in (1,2,3) or name = '张三'
     * delete(['id','>',1,'and'],['name','like','%张三%']);// id > 1 and name like '%张三%'
     * 
     */
    public function delete(float|string|int ...$wheres): bool
    {
        if (empty($wheres)) {
            throw new ModelException('删除条件不能为空');
        }

        $wheres = $this->where($wheres);
        if (empty($wheres)) {
            throw new ModelException('删除条件不能为空');
        }

        $this->deleting();

        return $this->db->delete($this->table, $wheres);
    }


    /**
     * 更新方法
     * @param array $data 更新数据
     * @param array|int|string ...$wheres 更新条件
     * @return bool 是否更新成功
     * @example
     * 示例：
     *
     * update(['name'=>'张三'],1); // primary key = 1
     * update(['name'=>'张三'],'1'); // primary key = 1
     * 
     * update(['name'=>'张三'],'id',1); // id = 1
     * update(['name'=>'张三'],'id','1'); // id = 1
     * update(['name'=>'张三'],'id',[1,2,3]); // id in (1,2,3)
     * 
     * update(['name'=>'张三'],'id','=',1); // id = 1
     * update(['name'=>'张三'],'id','in',[1,2,3]); // id in (1,2,3)
     * update(['name'=>'张三'],'id','between',[1,10]); // id between (1,10)
     * 
     * update(['name'=>'张三'],'id','not in',[1,2,3],'and'); // id not in (1,2,3) and
     * update(['name'=>'张三'],'id','not between',[1,10],'or'); // id not between (1,10) or
     * 
     * update(['name'=>'张三'],['id','=',1],['name','=','李四']);// id = 1 and name = '李四'
     * update(['name'=>'张三'],['id','in',[1,2,3],'or'],['name','=','李四']);// id in (1,2,3) or name = '李四'
     * 
     */
    public function update(array $data, float|array|int|string ...$wheres): bool
    {
        if (empty($wheres)) {
            throw new ModelException('更新条件不能为空');
        }

        if (empty($data)) {
            throw new ModelException('更新数据不能为空');
        }

        $wheres = $this->where($wheres);
        if (empty($wheres)) {
            throw new ModelException('更新条件不能为空');
        }

        Safe::fillable($data, $this->fillable); //检查填充字段
        Safe::data($data, $this->schema); //检查数据字段

        $this->updating();

        return $this->db->update($this->table, $data, $wheres);
    }

    public function insert(array $data = []): bool
    {

        if (empty($data)) {
            throw new ModelException('插入数据不能为空');
        }

        if (!isset($this->fillable) || empty($this->fillable)) {
            throw new ModelException('Fillable字段未定义');
        }

        if (!isset($this->fields) || empty($this->fields)) {
            throw new ModelException('Fields字段未定义');
        }

        // 处理数据格式
        $dataset = [];
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            // 多条数据
            $dataset = $data;
        } else {
            // 单条数据
            $dataset[] = $data;
        }

        $merged = [];
        foreach ($dataset as $key => $rs) {
            Safe::fillable($rs, $this->fillable); //检查填充字段
            Safe::data($rs, $this->schema); //检查数据字段
            $merged[$key] = array_merge($this->fillable, $rs);//合并填充字段和数据字段
        }

        $this->creating();

        return $this->db->insert($this->table, $merged);
    }

    /**
     * 获取最后插入的主键id
     * @return int
     */
    public function lastid(): int
    {
        return $this->db->lastid();
    }


    /**
     * 构建查询条件数组
     * @param float|int|string|array ...$wheres 查询条件
     * @return array 查询条件数组
     * @example
     * 示例：
     * where(1); // [['primary','=',1]]  默认字段名为$this->primary
     * where('1');// [['primary','=','1']] 默认字段名为$this->primary   
     * where('id',1);// [['id','=',1]]
     * where('id','1');// [['id','=','1']]
     * where('id',[1,2,3]);// [['id','in',[1,2,3]]] 
     * where('id','between',[1,10]);// [['id','between',[1,10]]] 
     * where('id','not in',[1,2,3],'and');// [['id','not','in',[1,2,3]]]
     * where('id','not between',[1,10],'or');// [['id','not','between',[1,10]]]
     * where(['id','=',1],['name','=','张三']);// [['id','=',1],['name','=','张三']]
     * where(['id','in',[1,2,3],'or'], ['name','=','李四']);// [['id','in',[1,2,3],'or'], ['name','=','李四']]
     * 
     **/
    protected function where(float|int|string|array ...$wheres): array
    {
        if (is_numeric($wheres[0]) || is_string($wheres[0])) {
            if (count($wheres) == 1) {
                $wheres = [$this->primary, '=', $wheres[0]];
            }
        }

        return Safe::where($wheres, $this->schema);
    }

    /**
     * 统计方法
     * @param array|int|string ...$wheres 查询条件
     * @return int 统计结果
     */
    public function count(float|int|string|array ...$wheres): int
    {
        $wheres = $this->where($wheres);
        $params = ['where' => $wheres, 'fields' => " COUNT({$this->primary}) AS ct "];

        return $this->db->select($this->table, $params)->row()->ct;
    }

    // 查询单条数据
    public function find(float|int|string|array ...$wheres): array
    {
        $wheres = $this->where($wheres);
        $params = ['where' => $wheres, 'fields' => array_keys($this->schema)];

        return $this->db->select($this->table, $params)->row();
    }

    // 查询所有数据
    public function findAll(): array
    {
        $params = ['fields' => array_keys($this->schema)];

        return $this->db->select($this->table, $params)->result();
    }


    // 查询第一条数据
    public function first(): array
    {
        $params = ['fields' => array_keys($this->schema), 'orderby' => [$this->primary => 'ASC']];

        return $this->db->select($this->table, $params)->row();
    }

    // 查询最后一条数据
    public function last(): array
    {
        $params = ['fields' => array_keys($this->schema), 'orderby' => [$this->primary => 'DESC']];

        return $this->db->select($this->table, $params)->row();
    }

    // 查询多条数据
    public function rows(int ...$offset): array
    {
        $params = ['fields' => array_keys($this->schema), 'offset' => $offset];

        return $this->db->select($this->table, $params)->result();
    }

}
