<?php

namespace System\Database;

/**
 * 简单Mysql数据操作类
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Database
{

    /**
     * 保存数据库连接句柄
     * 
     * @var object
     */
    private $db = null;

    /**
     * 数据库配置信息
     * 
     * @var array
     */
    public $config = [];

    /**
     * 构造函数
     * 
     * @param array $config 连接参数数组
     */
    public function __construct($config = [])
    {
        //$config = array('host' => '', 'username' => '', 'password' => '', 'database' => '', 'port' => '', 'charset' => '');
        $this->config = $config;

        $this->db = new \mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

        if ($this->db->connect_errno) {
            exit('Database Connection Error :' . $this->db->connect_errno);
        }

        $this->db->set_charset($config['charset']);
    }

    /**
     * sql查询
     * 
     * @param string $sql
     * 
     * @return \System\Database\Result
     */
    public function query($sql)
    {
        $result = $this->db->query($sql);

        // var_dump($sql);
        if ($this->db->errno) {
            exit('Database Error : [' . $sql . '] ' . $this->db->error . ' [Code:' . $this->db->errno . ']');
        }

        if (is_object($result)) {
            return new \System\Database\Result($result);
        } else {
            return $result;
        }
    }

    /**
     * 返回最后插入id
     * 
     * @return int 
     */
    public function insertId()
    {
        return $this->db->insert_id;
    }

    /**
     * 转义字符串
     * 
     * @param string|array $string
     * 
     * @return string|array
     */
    public function escape($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->escape($value);
            }
            return $string;
        }

        $string = $this->db->real_escape_string($string);

        return $string;
    }

    /**
     * 添加数据到数据库
     * 
     * @param string $table 表名
     * @param array $data 数组键值对应
     * 
     * @return boolean
     */
    public function insert($table, $data)
    {
        $data = $this->escape($data);

        $keys = implode('`,`', array_keys($data));
        $values = implode("','", array_values($data));

        $sql = "INSERT INTO {$table}(`{$keys}`) VALUES('{$values}')";

        return $this->query($sql);
    }

    /**
     * 更新数据
     * 
     * @param string $table
     * @param array $data
     * @param array|string $where
     * 
     * @return boolean
     */
    public function update($table, $data, $where = [])
    {
        if (is_array($data)) {

            $data = $this->escape($data);
            foreach ($data as $key => $value) {
                $set[] = " `{$key}`='{$value}' ";
            }

            $sql = "UPDATE {$table} SET " . implode(',', $set) . $this->sqlWhere($where);
        } else {
            $sql = "UPDATE {$table} {$data} " . $this->sqlWhere($where);
        }

        return $this->query($sql);
    }

    /**
     *  replace
     * 
     * @param string $table
     * @param array $data
     * 
     * @return boolean
     */
    public function replace($table, $data)
    {
        $data = $this->escape($data);

        $keys = implode('`,`', array_keys($data));
        $values = implode("','", array_values($data));

        $sql = "REPLACE INTO {$table}(`{$keys}`) VALUES('{$values}')";
        return $this->query($sql);
    }

    /**
     * 删除数据
     * 
     * @param string $table
     * @param array|string $where
     * 
     * @return boolean
     */
    public function delete($table, $where = [])
    {
        $sql = "DELETE FROM " . $table . $this->sqlwhere($where);
        return $this->query($sql);
    }

    /**
     * 查询数据
     * 
     * @param string $table
     * @param array|string $where
     * @param array|string $fields
     * @param array|string $orderby
     * @param int|array $limit
     * 
     * @return array
     */
    public function select($table, $where = [], $fields = [], $orderby = [], $limit = [])
    {
        $sql = "SELECT {$this->sqlField($fields)} FROM {$table} {$this->sqlwhere($where)} {$this->sqlOrderBy($orderby)} {$this->sqlLimit($limit)} ";
        return $this->query($sql);
    }

    /**
     * 返回前一条执行影响的记录数
     * 
     * @return int;
     */
    public function affectedRows()
    {
        return $this->db->affected_rows;
    }

    /**
     * 关闭连接
     * 
     * @return void
     */
    public function close()
    {
        $this->db->close();
    }

    /**
     * 构造 sql where 字符串
     * 
     * @param array $where
     * 
     * @return string
     */
    private function sqlWhere($where = [])
    {

        if (is_string($where) && trim($where) != '') {
            return " WHERE " . $where;
        }

        $sql = '';
        if (is_array($where) && count($where) > 0) {
            $where = $this->escape($where);
            $sql .= " WHERE ";
            $i = 0;
            foreach ($where as $key => $value) {
                $compare = $this->sqlCompare($key, $value);
                $sql .= ($i == 0 ? $compare : " AND {$compare} ");
                $i++;
            }
        }

        return $sql;
    }

    /**
     * sql比较运算符号解析
     * 
     * @param string $key
     * 
     * @return string 
     */
    private function sqlCompare($key, $value)
    {
        $key = trim($key);

        if (strpos($key, '>=')) {
            $key = str_replace('>=', '', $key);
            return " `{$key}` >= '{$value}' ";
        }

        if (strpos($key, '<=')) {
            $key = str_replace('<=', '', $key);
            return " `{$key}` <= '{$value}' ";
        }

        if (strpos($key, '!=')) {
            $key = str_replace('!=', '', $key);
            return " `{$key}` != '{$value}' ";
        }

        if (strpos($key, '<>')) {
            $key = str_replace('<>', '', $key);
            return " `{$key}` <> '{$value}' ";
        }

        if (strpos($key, '>')) {
            $key = str_replace('>', '', $key);
            return " `{$key}` > '{$value}' ";
        }

        if (strpos($key, '<')) {
            $key = str_replace('<', '', $key);
            return " `{$key}` < '{$value}' ";
        }

        if (strpos($key, ' like')) {
            $key = str_replace(' like', '', $key);
            return " `{$key}` like '{$value}' ";
        }

        if (strpos($key, ' in')) {
            $key = str_replace(' in', '', $key);
            return " `{$key}` in ({$value}) ";
        }

        return " `{$key}` = '{$value}' ";
    }

    /**
     * 构造sql select字段
     * 
     * @param array $fields
     * 
     * @return string
     */
    private function sqlField($fields)
    {
        if (is_string($fields) && trim($fields) != '') {
            return $fields;
        }

        $sql = ' * ';
        if (is_array($fields) && count($fields) > 0) {
            $sql = " ";
            $i = 0;
            foreach ($fields as $value) {
                $sql .= ($i == 0 ? " `$value` " : " , `$value` ");
                $i++;
            }
        }

        return $sql;
    }

    /**
     * 构造sql order by
     * 
     * @param array|string $fields
     * 
     * @return string
     */
    private function sqlOrderBy($fields)
    {
        if (is_string($fields) && trim($fields) != '') {
            return " ORDER BY " . $fields;
        }

        $sql = '';
        if (is_array($fields) && count($fields) > 0) {
            $sql .= " ORDER BY ";
            $i = 0;
            foreach ($fields as $key => $value) {
                $sql .= ($i == 0 ? " `$key` $value " : " , `$key` $value ");
                $i++;
            }
        }

        return $sql;
    }

    /**
     * 构造sql limit
     * 
     * @param init|array $offset
     * 
     * @return string
     */
    private function sqlLimit($offset)
    {
        if (!$offset) {
            return '';
        }

        if (is_int($offset)) {
            return " LIMIT $offset ";
        }

        if (is_array($offset)) {
            return " LIMIT {$offset[0]},{$offset[1]} ";
        }

        return '';
    }

}

/**
 * 数据查绚结果
 */
class Result
{

    /**
     * 数据集合大小
     * 
     * @var int
     */
    public $num_rows = 0;

    /**
     * 查询结果对象实例
     * 
     * @var object
     */
    public $result = null;

    /**
     * 构造函数
     * 
     * @param object $result 查询结果对象实例
     */
    public function __construct($result)
    {
        $this->result = $result;
        $this->num_rows = $result->num_rows;
    }

    /**
     * 返回数据集
     * 
     * @param string $type 返回结果类型
     * 
     * @return array 数据集合
     */
    public function result($type = 'object')
    {
        $rows = array();

        if ($type == 'array') {
            while ($row = $this->result->fetch_array(MYSQLI_ASSOC)) {   //MYSQLI_ASSOC
                $rows[] = $row;
            }
        } else {
            while ($row = $this->result->fetch_object()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * 返回数据某一条数据
     * 
     * @param int $n 集合数组标
     * @param string $type 返回类型
     * 
     * @return array or object
     */
    function row($n = 0, $type = 'object')
    {
        $result = $this->result($type);
        if (isset($result[$n])) {
            return $result[$n];
        }

        return null;
    }

}
