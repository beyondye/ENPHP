<?php

namespace system\library;

use system\library\Html;

/**
 * 表格
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Grid
{

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
    public $tools = [];

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
     * 插入到字段前面最后面
     */
    const FIELD_POS_BEFORE = 'before';

    /**
     * 插入到最前面
     */
    const FIELD_POS_AFTER = 'after';

    /**
     * 默认工具按钮组
     */
    const TOOL_DEFAULT_GROUP = 'default';

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
     * 
     * @param string $pos 字段的前后 val='before' or 'after'
     * @param string $to  具体字段名称，如果空为数组的头和尾
     *  
     * @return $this
     */
    public function setField($fields = [], $to = '', $pos = Grid::FIELD_POS_AFTER)
    {

        //默认字段值
        $default = [
            'convert' => null,
            'primary' => false,
            'hide' => false,
            'literal' => '',
            'width' => ''
        ];

        $that_fields = $this->fields;

        $insert = [];
        foreach ($fields as $key => $value) {

            //如果是一维数组
            if (is_int($key)) {

                if (isset($that_fields[$value])) {

                    if ($to) {
                        $insert[$value] = $that_fields[$value];
                        unset($that_fields[$value]);
                    }
                    continue;
                }

                $insert[$value] = $default;
                $insert[$value]['literal'] = $value;
                continue;
            }

            //如果是二维数组
            if (isset($that_fields[$key])) {

                if ($to) {
                    $insert[$key] = array_merge($that_fields[$key], $value);
                    unset($that_fields[$key]);
                    continue;
                }

                $that_fields[$key] = array_merge($that_fields[$key], $value);
                continue;
            }

            $insert[$key] = array_merge($default, $value);
            if ($insert[$key]['literal'] == '') {
                $insert[$key]['literal'] = $key;
            }
        }

        //分拣数组头部元素
        $head = [];
        foreach ($that_fields as $k => $v) {
            if ($k == $to) {
                break;
            }

            $head[$k] = $v;
        }

        //分拣尾部元素
        $tail = [];
        foreach ($that_fields as $k => $v) {

            if ($k == $to) {
                $tail = [];
                continue;
            }

            $tail[$k] = $v;
        }

        //判断是否默认
        if ($to && $that_fields) {
            if ($pos === self::FIELD_POS_AFTER) {
                $head[$to] = $that_fields[$to];
            }

            if ($pos === self::FIELD_POS_BEFORE) {
                $tail = array_merge([$to => $that_fields[$to]], $tail);
            }
        }

        //组合返回新顺序的字段数组
        $this->fields = $head + $insert + $tail;

        return $this;
    }

    /**
     * 设置过滤表单项
     * 
     * @param array $filters  html tags
     * @return $this
     */
    public function setFilter($filters = [])
    {

        foreach ($filters as $val) {
            $this->filters[] = $val;
        }

        return $this;
    }

    /**
     * 返回filter html字符串
     * 
     * @param string $gorup
     * 
     * @return string
     */
    public function filters()
    {
        return Html::tags($this->filters);
    }

    /**
     * 设置表格数据工具
     * 
     * @param array $tools htmls
     * @param string $group 
     * @return $this
     */
    public function setTool($tools = [], $gorup = self::TOOL_DEFAULT_GROUP)
    {

        foreach ($tools as $val) {
            $this->tools[$gorup][] = $val;
        }

        return $this;
    }

    /**
     * 返回tools html字符串
     * 
     * @param string $gorup
     * 
     * @return string
     */
    public function tools($gorup = self::TOOL_DEFAULT_GROUP)
    {
        return Html::tags($this->tools[$gorup]);
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

            if ($value['width']) {
                $ths = $ths . '<th width="' . $value['width'] . '">' . $value['literal'] . '</th>';
                continue;
            }

            $ths = $ths . '<th>' . $value['literal'] . '</th>';
        }

        return '<thead><tr>' . $ths . '</tr></thead>';
    }

    /**
     * 生成表格
     * 
     * @return string
     */
    public function table()
    {
        $thead = $this->thead();
        $tfoot = '<tfoot></tfoot>';
        $tbody = '<tbody>';
        foreach ($this->datasource as $row) {
            $tbody = $tbody . $this->tr($row);
        }
        $tbody .= '</tbody>';
        return '<table>' . $thead . $tbody . $tfoot . '</table>';
    }

    /**
     * 生成表格html
     * 
     * @return string
     */
    public function render(callable $make)
    {
        return $make($this);
    }

}
