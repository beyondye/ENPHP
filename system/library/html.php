<?php

namespace System\Library;

/**
 * 表单元素
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Html
{

    /**
     * 无闭合标签，需要自行添加
     */
    const NO_END_TAGS = ['br', 'img', 'hr', 'input'];

    /**
     * 特定处理的标签
     */
    const SPECIFIED_TAGS = ['select', 'input'];

    /**
     * 生成html标签
     * 
     * @param array $param
     * @example
     * $param=[
     * 'name'=>'p'
     * 'properties'=>['name'=>''],
     * 'elements'=>[['name'='','elements'=[],'properties'=>[]]
     * ]'
     * 
     * @return string 返回html代码
     */
    public function tag(array $param)
    {
        $param = array_merge(['name' => '', 'elements' => [], 'properties' => [], 'text' => ''], $param);


        if (in_array($param['name'], self::NO_END_TAGS)) {
            return '<' . $param['name'] . $this->_properties($param['properties']) . '/>';
        }

        $start = '';
        $end = '';

        if ($param['name']) {
            $start = '<' . $param['name'] . $this->_properties($param['properties']) . '>';
            $end = '</' . $param['name'] . '>';
        }

        if ($param['elements']) {
            return $start . $this->_elements($param['elements']) . $end;
        }

        if ($param['text']) {
            return $start . $param['text'] . $end;
        }



        return $start . $end;
    }

    /**
     * 一次处理多个
     * 
     * @param array $param
     * 
     * @example
     * $param=[[
     * 'name'='p',
     * 'properties'=>['name'=>''],
     * 'elements'=>[['name'='','elements'=[],'properties'=>[]]
     * ],
     * [
     * 'name'='p',
     * 'properties'=>['name'=>''],
     * 'elements'=>[['name'='','elements'=[],'properties'=>[]]
     * ]]'
     * 
     * @return string 返回html代码
     */
    public function tags(array $param)
    {
        return $this->_elements($param);
    }

    /**
     * 构造表单select
     * 
     * @param array $param 参见example
     * 
     * @example 
     * $param=[
     * 'name'=>'select',
     * 'options' => $this->menu->read('parent_id=0', 'id,name'),
     * 'default' => ['literal'=>'请选择','value'=>''],
     * 'model' => array('value' => 'id', 'literal' => 'name'),
     * 'selected' => $this->input->get('parent_id'),
     * 'properties' => array('name' => 'parent_id')
     * ]
     * 
     * @return string 返回select html代码
     */
    private function select(array $param)
    {
        $param = array_merge(['name' => '', 'properties' => [], 'default' => [], 'model' => [], 'options' => [], 'selected' => ''], $param);

        $options = '';
        if ($param['default']) {
            $options .= "<option value=\"{$param['default']['value']}\">{$param['default']['literal']}</option>";
        }

        if ($param['model']) {

            $value = $param['model']['value'];
            $literal = $param['model']['literal'];

            foreach ($param['options'] as $rs) {
                $rs = (array) $rs;
                if ($rs[$value] === $param['selected']) {
                    $options .= "<option value=\"{$rs[$value]}\" selected>{$rs[$literal]}</option>";
                } else {
                    $options .= "<option value=\"{$rs[$value]}\">{$rs[$literal]}</option>";
                }
            }
        }

        return '<select' . $this->_properties($param['properties']) . '>' . $options . '</select>';
    }

    /**
     * 生成表单input
     * 
     * @param array $param 参见$this->tag()
     * 
     * @return string
     */
    private function input(array $param)
    {
        $param['properties'] = array_merge(['type' => 'text'], $param['properties']);

        return $this->tag($param);
    }

    /**
     * 构造子标签
     * 
     * @param array $elements
     * 
     * @return string
     */
    private function _elements($elements)
    {
        $str = '';
        foreach ($elements as $value) {

            $name = isset($value['name']) ? $value['name'] : '';

            if (in_array($name, self::SPECIFIED_TAGS)) {
                $str .= $this->$name($value);
            } else {
                $str .= $this->tag($value);
            }
        }

        return $str;
    }

    /**
     * 遍历标签属性
     * 
     * @param array $properties
     * @return string
     */
    private function _properties($properties)
    {
        $str = '';
        foreach ($properties as $key => $value) {
            //if ($value) {
            $str .= ' ' . $key . '="' . $value . '"';
            //}
        }

        return $str;
    }

}
