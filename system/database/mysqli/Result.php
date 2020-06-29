<?php

namespace system\database\mysqli;

/**
 * 数据查绚结果
 *
 * @author Ding<beyondye@gmail.com>
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
    public function result(string $type = 'object')
    {
        $rows = [];

        if ($type == 'array') {
            while ($row = $this->result->fetch_array(MYSQLI_ASSOC)) {   //MYSQLI_ASSOC
                $rows[] = $row;
            }
        } else {
            while ($row = $this->result->fetch_object()) {
                $rows[] = $row;
            }
        }
        $this->result->close();

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
    public function row(int $n = 0, string $type = 'object')
    {
        $result = $this->result($type);
        if (isset($result[$n])) {
            return $result[$n];
        }

        return null;
    }

}
