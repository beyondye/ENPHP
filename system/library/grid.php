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
     * 设置需要显示的字段
     * @var array
     */
    public $field;

    /**
     * 设置需要显示的字段字面量标题
     * @var array
     */
    public $title;

    /**
     * 设置需要隐藏的字段
     * @var array
     */
    public $hide;

    /**
     * 设置需要转换显示的字段
     * @var array
     */
    public $convert;

    /**
     * 设置需要过滤筛选字段表单项
     * @var array
     */
    public $filter;

    /**
     * 设置操作表格需要的工具按钮
     * @var string
     */
    public $tool;

    /**
     * 设置表格尾部额外字段
     * @var array
     */
    public $after ;

    /**
     * 设置表格首部额外字段
     * @var array
     */
    public $before;

    /**
     * 设置表格单项或多项操作
     * @var string
     */
    public $operation;

    /**
     * 设置表格数据源
     * @var array
     */
    public $datasource;

    /**
     * 设置当前页码
     * @var int
     */
    public $page_num = 0;

    /**
     * 设置当前分页地址 url?page=
     * @var string
     */
    public $page_url = '';

    /**
     * 设置每个页面条数
     * @var int
     */
    public $page_size = 0;

    /**
     * 设置数据总条数
     * @var int
     */
    public $total = 0;

    /**
     * 表格模板路径
     * @var string
     */
    public $template = '';

    /**
     * 模板渲染返回html
     * 
     * @return string
     */
    public function render()
    {
        $this->field = array_filter($this->field, function($var) {
            return !in_array($var, $this->hide);
        });


        ob_start();
        include $this->template;
        $_buffer = ob_get_contents();
        ob_end_clean();

        return $_buffer;
    }

}
