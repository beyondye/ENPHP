<?php

namespace System\Library;

/**
 * 表格
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Grid
{

    /**
     * 唯一主键字段
     * 
     * @var string
     */
    public $primary = '';

    /**
     * 表格html
     * 
     * @var string
     */
    public $table = '';

    /**
     * 设置需要显示的字段
     * @var array
     */
    public $fields = [];

    /**
     * 设置需要过滤筛选字段表单项
     * @var array
     */
    public $filters = [];

    /**
     * 设置操作表格需要的工具按钮
     * @var string
     */
    public $tools;

    /**
     * 设置表格单项或多项操作
     * @var string
     */
    public $operations;

    /**
     * 设置表格数据源
     * @var array
     */
    public $datasource = [];

    /**
     * 设置当前页码
     * @var int
     */
    public $pager;

    /**
     * 设置补全字段数据
     * 
     * @param array $fields
     * @example 
     * $fields=['id'=>['primary'=>true,'literal'=>'ID'],'name','time']
     * 
     * 
     * $default = [
     *      'convert' => null,
     *      'primary' => false,
     *      'hide' => false,
     *      'literal'=>''
     * ];
     * 
     * @return $this
     */
    public function setField(array $fields = [])
    {

        $default = [
            'convert' => null,
            'primary' => false,
            'hide' => false,
            'literal' => ''
        ];

        foreach ($fields as $key => $value) {

            if (is_int($key)) {

                if (isset($this->fields[$value])) {
                    continue;
                }

                $this->fields[$value] = $default;
                $this->fields[$value]['literal'] = $value;
                continue;
            }

            if (isset($this->fields[$key])) {
                $this->fields[$key] = array_merge($this->fields[$key], $value);
            } else {
                $this->fields[$key] = array_merge($default, $value);
            }

            if ($this->fields[$key]['literal'] == '') {
                $this->fields[$key]['literal'] = $key;
            }

            if ($this->fields[$key]['primary'] === true) {
                $this->primary = $key;
            }
        }

        return $this;
    }

    /**
     * 设置过滤表单项
     * 
     * @param array $container htmls
     * 
     * @return $this
     */
    public function setFilter(array $filters = [])
    {

        foreach ($filters as $val) {
            $this->filters[] = $val;
        }

        return $this;
    }

    /**
     * 生成表格行
     * 
     * @param object $row
     * 
     * @return string
     */
    private function tr($row)
    {
        $tds = '';
        foreach ($this->fields as $key => $value) {

            if ($value['hide'] === true) {
                continue;
            }

            $convert = $value['convert'];

            if (isset($row->$key)) {
                $str = $convert === null ? $row->$key : $convert($row);
                $tds = $tds . '<td>' . $str . '</td>';
                continue;
            }

            $str = $convert === null ? '' : $convert($row);
            $tds = $tds . '<td>' . $str . '</td>';
        }

        return '<tr>' . $tds . '</tr>';
    }

    /**
     * 生成表格头部
     * 
     * @return string
     */
    private function thead()
    {
        $ths = '';
        foreach ($this->fields as $key => $value) {

            if ($value['hide'] === true) {
                continue;
            }

            $ths = $ths . '<th>' . $value['literal'] . '</th>';
        }

        return '<thead><tr>' . $ths . '</tr></thead>';
    }

    /**
     * 生成表格html
     * 
     * @return string
     */
    public function render(callable $make)
    {
        $thead = $this->thead();
        $tfoot = '<tfoot></tfoot>';
        $tbody = '<tbody>';
        foreach ($this->datasource as $row) {
            $tbody = $tbody . $this->tr($row);
        }
        $tbody . '</tbody>';
        $this->table = '<table>' . $thead . $tbody . $tfoot . '</table>';

        return $make($this);
    }

}
