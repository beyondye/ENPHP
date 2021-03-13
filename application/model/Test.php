<?php

namespace model;


class Test extends \system\Model
{

    public function __construct()
    {

        $this->table = 'test';
        $this->primary = 'id';

        $this->schema = [
            'id' => [
                'rules' => 'num',
                'label' => 'ID',
                'default' => null,
                'required' => false
            ],
            'name' => [
                'rules' => 'required|string|filter:tag,blank',
                'label' => '名称',
                'default' => '',
                'required' => true
            ],
            'parent_id' => [
                'rules' => 'required|num',
                'label' => '上级',
                'default' => 0,
                'required' => true
            ],
            'sort' => [
                'rules' => 'required|num',
                'label' => '排序',
                'default' => 0,
                'required' => true
            ],
            'capital' => [
                'rules' =>'requires|num',
                'label' => '是否省会',
                'default' => 0,
                'required' => true
            ],
            'open' => [
                'validate' => 'required|num',
                'label' => '是否开通',
                'default' => 0,
                'required' => true
        ]];
    }

}
