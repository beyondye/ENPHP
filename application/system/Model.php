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
     * $schema=[
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

    //查询条件
    protected array $conditions = [];

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

    //选择字段
    public function select(array $fields): object
    {
        $this->conditions['fields'] = $fields;
        return $this;
    }

    public function where(float|int|string|array ...$wheres): object
    {
        $this->conditions['wheres'] = $this->_bindWhere(...$wheres);
        return $this;
    }

    public function groupBy(array $groups): object
    {
        $this->conditions['groups'] = $groups;
        return $this;
    }

    public function having(array $havings): object
    {
        $this->conditions['havings'] = $havings;
        return $this;
    }

    public function orderBy(array $orders): object
    {
        $this->conditions['orders'] = $orders;
        return $this;
    }


    /**
     * 删除方法
     * @param float|string|int ...$wheres 删除条件
     * @return int 影响的行数
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
    public function delete(float|string|int|array ...$wheres): int
    {
        if (empty($wheres)) {
            throw new ModelException('Delete Condition Cannot Be Empty.');
        }

        $wheres = $this->_bindWhere($wheres);
        if (empty($wheres)) {
            throw new ModelException('Delete Condition Cannot Be Empty.');
        }

        $this->deleting();

        return $this->db->delete($this->table, ...$wheres);
    }

    /**
     * 更新方法
     * @param array $data 更新数据
     * @param array|int|string ...$wheres 更新条件
     * @return int 影响的行数
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
    public function update(array $data, float|array|int|string ...$wheres): int
    {
        if (empty($wheres)) {
            throw new ModelException('Update Condition Cannot Be Empty.');
        }

        if (empty($data)) {
            throw new ModelException('Update Data Cannot Be Empty.');
        }

        if (!isset($this->fillable) || empty($this->fillable)) {
            throw new ModelException('Fillable Array Not Defined.');
        }

        if (!isset($this->schema) || empty($this->schema)) {
            throw new ModelException('Schema Array Not Defined.');
        }

        $wheres = $this->_bindWhere($wheres);
        if (empty($wheres)) {
            throw new ModelException('Update Condition Cannot Be Empty.');
        }

        Safe::fillable($data, $this->fillable);
        Safe::data($data, $this->schema);

        $this->updating();

        return $this->db->update($this->table, $data, ...$wheres);
    }

    public function insert(array ...$data): int|string
    {
        if (empty($data[0]) || !is_array($data[0])) {
            throw new ModelException('Insert Data Cannot Be Empty Or Not Array.');
        }

        if (!isset($this->fillable) || empty($this->fillable)) {
            throw new ModelException('Fillable Array Not Defined.');
        }

        if (!isset($this->schema) || empty($this->schema)) {
            throw new ModelException('Schema Array Not Defined.');
        }

        $merged = [];
        foreach ($data as $key => $rs) {
            Safe::fillable($rs, $this->fillable);
            Safe::data($rs, $this->schema);
            $merged[$key] = array_merge($this->fillable, $rs); //合并填充字段和数据字段
        }

        $this->creating();

        return $this->db->insert($this->table, ...$merged);
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
    protected function _bindWhere(float|int|string|array ...$wheres): array
    {
        if (is_numeric($wheres[0]) || is_string($wheres[0])) {
            if (count($wheres) == 1) {
                $wheres = [$this->primary, '=', $wheres[0]];
            }
        }

        return Safe::where($wheres, $this->schema);
    }

    public function first(): object|null
    {
        $condition = [
            'field' => $this->conditions['fields'] ?? [],
            'where' => $this->conditions['wheres'] ?? [],
            'groupby' => $this->conditions['groups'] ?? [],
            'having' => $this->conditions['havings'] ?? [],
            'orderby' => $this->conditions['orders'] ?? [],
            'limit' => 1
        ];

        $this->conditions = [];
        return $this->db->select($this->table, ...$condition)->first();
    }

    public function all(int $limit = 1000): array
    {
        $condition = [
            'field' => $this->conditions['fields'] ?? [],
            'where' => $this->conditions['wheres'] ?? [],
            'groupby' => $this->conditions['groups'] ?? [],
            'having' => $this->conditions['havings'] ?? [],
            'orderby' => $this->conditions['orders'] ?? [],
            'limit' => $limit
        ];

        $this->conditions = [];
        return $this->db->select($this->table, ...$condition)->all();
    }

    public function count(): int
    {
        $condition = [
            'field' => ['count(*) as total'],
            'where' => $this->conditions['wheres'] ?? [],
            'groupby' => $this->conditions['groups'] ?? [],
            'having' => $this->conditions['havings'] ?? [],
            'orderby' => $this->conditions['orders'] ?? []
        ];

        $this->conditions = [];
        return $this->db->select($this->table, ...$condition)->first('array')['total'] ?? 0;
    }

    public function find(int|string $id): object|null
    {
        return $this->where($id)->first();
    }

    public function exists(int|string $id): bool
    {
        return $this->where($id)->first() !== null;
    }

    public function rows(int $limit = 1000,int $offset = 0): array
    {
        $condition = [
            'field' => $this->conditions['fields'] ?? [],
            'where' => $this->conditions['wheres'] ?? [],
            'groupby' => $this->conditions['groups'] ?? [],
            'having' => $this->conditions['havings'] ?? [],
            'orderby' => $this->conditions['orders'] ?? [],
            'limit' => [$limit,$offset]
        ];

        $this->conditions = [];
        return $this->db->select($this->table, ...$condition)->all();
    }

    public function transaction(callable $callback): mixed
    {
        $result = null;
        try {
            $this->db->transaction();
            $result = $callback();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw new ModelException("Transaction Error: {$e->getMessage()}");
        }
        return $result;
    }
}
