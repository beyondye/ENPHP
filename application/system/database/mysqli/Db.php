<?php

namespace system\database\mysqli;

use mysqli;

class Db
{

    /**
     * 保存数据库连接句柄
     * @var object
     */
    private object $db;

    /**
     * 数据库配置信息
     * @var array
     */
    public array $config = [];

    public function __get($name)
    {
        //返回最新插入id
        if ($name == 'insert_id') {
            return $this->db->insert_id;
        }

        //返回影响行数
        if ($name == 'affected_rows') {
            return $this->db->affected_rows;
        }
    }

    /**
     * 构造函数
     * @param array $config 连接参数数组
     */
    public function __construct($config = [])
    {
        //$config = array('host' => '', 'username' => '', 'password' => '', 'database' => '', 'port' => '', 'charset' => '');
        $this->config = $config;

        profiler('benchmark', 'database', $config['host']);
        $this->db = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
        profiler('benchmark', 'database');

        if ($this->db->connect_errno) {
            exit('Database Connection Error :' . $this->db->connect_error);
        }

        $this->db->set_charset($config['charset']);
    }

    /**
     * 改变本次链接操作的数据库
     * @param string $db
     * @return bool
     */
    public function selectDb(string $db): bool
    {
        return $this->db->select_db($db);
    }

    /**
     * 有数据sql查询
     * @param string $sql
     * @return Result
     */
    public function query(string $sql)
    {
        profiler('benchmark', 'queries', $sql);
        $result = $this->db->query($sql);
        profiler('benchmark', 'queries');

        if ($this->db->errno) {
            exit('Database Error : [' . $sql . '] ' . $this->db->error . ' [Code:' . $this->db->errno . ']');
        }

        return new Result($result);
    }


    /**
     * 无数据sql查询
     * @param string $sql
     * @return bool
     */
    public function execute(string $sql)
    {

        profiler('benchmark', 'executes', $sql);
        $result = $this->db->query($sql);
        profiler('benchmark', 'executes');

        if ($this->db->errno) {
            exit('Database Error : [' . $sql . '] ' . $this->db->error . ' [Code:' . $this->db->errno . ']');
        }

        return $result;
    }

    /**
     * 转义字符串
     * @param string|array $string
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

        return $this->db->real_escape_string($string);
    }

    /**
     * 添加数据到数据库
     * @param string $table 表名
     * @param array $data 数组键值对应
     * @return bool
     */
    public function insert(string $table, array $data)
    {

        if (empty($data[0])) {
            $data = $this->escape($data);
            $keys = implode(',', array_keys($data));
            $values = implode("','", array_values($data));
            $sql = "INSERT INTO $table($keys) VALUES('$values')";
        } else {

            $keys = implode(',', array_keys($this->escape($data[0])));
            $values = [];
            foreach ($data as $rs) {
                $values[] = implode("','", array_values($this->escape($rs)));
            }

            $sql = "INSERT INTO $table($keys) VALUES('" . implode("'),('", $values) . "')";
        }

        return $this->execute($sql);
    }

    /**
     * 更新数据
     * @param string $table
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update(string $table, array $data, array $where = [])
    {
        $data = $this->escape($data);

        $set = [];
        foreach ($data as $key => $value) {
            $set[] = $key . "='$value'";
        }

        $sql = "UPDATE $table SET " . implode(',', $set) . $this->sqlWhere($where);

        return $this->execute($sql);
    }

    /**
     * replace
     * @param string $table
     * @param array $data
     * @return bool
     */
    public function replace(string $table, array $data)
    {
        $data = $this->escape($data);
        $keys = implode(',', array_keys($data));
        $values = implode("','", array_values($data));

        $sql = "REPLACE INTO $table($keys) VALUES('$values')";

        return $this->execute($sql);
    }

    /**
     * 删除数据
     * @param string $table
     * @param array $where
     * @return boolean
     */
    public function delete(string $table, array $where = [])
    {
        $sql = 'DELETE FROM ' . $table . $this->sqlwhere($where);
        return $this->execute($sql);
    }

    /**
     * 查询数据
     * @param string $table
     * @param array $condition @subparam int|array $limit
     * @return Result
     */
    public function select(string $table, array $condition = ['where' => [], 'groupby' => [], 'having' => [], 'fields' => [], 'orderby' => [], 'limit' => []])
    {
        $default = ['where' => [], 'fields' => [], 'groupby' => [], 'having' => [], 'orderby' => [], 'limit' => []];
        $condition = array_merge($default, $condition);

        $sql = 'SELECT '
            . $this->sqlField($condition['fields'])
            . ' FROM '
            . $table
            . $this->sqlwhere($condition['where'])
            . $this->sqlGroupBy($condition['groupby'])
            . $this->sqlHaving($condition['having'])
            . $this->sqlOrderBy($condition['orderby'])
            . $this->sqlLimit($condition['limit']);

        return $this->query($sql);
    }

    /**
     * 关闭连接
     * @return void
     */
    public function close()
    {
        $this->db->close();
    }

    /**
     * 构造 sql where 字符串
     * @param array $where
     * @return string
     */
    private function sqlWhere(array $where = [])
    {
        if (is_string($where) && trim($where) != '') {
            return ' WHERE ' . $where;
        }

        $sql = '';
        if (is_array($where) && count($where) > 0) {
            $sql .= ' WHERE ';
            $i = 0;
            foreach ($where as $rs) {

                if (!is_array($rs) || count($rs) != 3 || $rs[2] == '') {
                    continue;
                }

                if (!in_array(strtolower($rs[1]), ['=', '>', '<', '>=', '<=', '<>', '!=', 'in', 'like', 'between'])) {
                    continue;
                }

                $rs[2] = $this->escape($rs[2]);

                $op = strtolower($rs[1]);
                if ($op == 'in') {
                    $in = str_replace(',', "','", $rs[2]);
                    $sub = "$rs[0] IN ('$in')";
                } elseif ($op == 'like') {
                    $sub = "$rs[0] LIKE '$rs[2]'";
                } elseif ($op == 'between') {

                    $bet = explode(',', $rs[2]);

                    if (count($bet) != 2) {
                        continue;
                    }
                    $sub = "$rs[0] BETWEEN '$bet[0]' AND '$bet[1]'";

                } else {
                    $sub = $rs[0] . $rs[1] . "'$rs[2]'";
                }

                $sql .= ($i == 0 ? $sub : ' AND ' . $sub);
                $i++;
            }
        }

        return $sql;
    }


    /**
     * 构造sql select字段
     * @param array|string $fields
     * @return string
     */
    private function sqlField($fields)
    {
        if (is_string($fields) && trim($fields) != '') {
            return $fields;
        }

        $sql = '*';
        if (is_array($fields) && count($fields) > 0) {
            $sql = '';
            $i = 0;
            foreach ($fields as $value) {
                $sql .= ($i == 0 ? $value : ',' . $value);
                $i++;
            }
        }

        return $sql;
    }

    /**
     * 构造sql order by
     * @param array|string $fields
     * @return string
     */
    private function sqlOrderBy($fields)
    {
        if (is_string($fields) && trim($fields) != '') {
            return ' ORDER BY ' . $fields;
        }

        $sql = '';
        if (is_array($fields) && count($fields) > 0) {
            $sql .= ' ORDER BY ';
            $i = 0;
            foreach ($fields as $key => $value) {
                $sql .= ($i == 0 ? $key . ' ' . $value : ',' . $key . ' ' . $value);
                $i++;
            }
        }

        return $sql;
    }

    /**
     * 构造sql limit
     * @param int|array $offset
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
            return " LIMIT $offset[0],$offset[1] ";
        }

        return '';
    }

    /**
     * 构造sql group by数据分组
     * @param array|string $fields
     * @return string
     */
    private function sqlGroupBy($fields)
    {
        if (is_string($fields) && trim($fields) != '') {
            return ' GROUP BY ' . $fields;
        }

        $sql = '';
        if (is_array($fields) && count($fields) > 0) {
            $sql .= ' GROUP BY ';
            $i = 0;
            foreach ($fields as $value) {
                $sql .= ($i == 0 ? $value : ',' . $value);
                $i++;
            }
        }

        return $sql;
    }

    /**
     * sql having
     * @param int|array $having
     * @return string
     */
    private function sqlHaving($having)
    {
        if (is_string($having) && trim($having) != '') {
            return ' HAVING ' . $this->escape($having);
        }

        $sql = '';
        if (is_array($having) && count($having) > 0) {
            $having = $this->escape($having);
            $sql .= ' HAVING ';
            $i = 0;
            foreach ($having as $key => $value) {
                $compare = $this->sqlCompare($key, $value);
                $sql .= ($i == 0 ? $compare : ' AND ' . $compare);
                $i++;
            }
        }

        return $sql;
    }

    //destruct
    function __destruct()
    {
        $this->close();
    }

}
