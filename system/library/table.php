<?php

namespace System\Library;

use \System\Library\Html;

/**
 * 表格
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Table
{

    /**
     * 设置需要显示的字段
     * @var array
     */
    public $fields;

    /**
     * 设置需要过滤筛选字段表单项
     * @var array
     */
    public $filter;

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
    public $datasource;

    /**
     * 设置当前页码
     * @var int
     */
    public $pager;
    
    /**
     *字段的默认处理
     * @var array 
     */
    private $field_default= [
        'convert' => null,
        'primary' => false,
        'hide'=>false,
    ];

    /**
     * 模板渲染返回html
     * 
     * @return string
     */
    public function render()
    {
        
    }

}
