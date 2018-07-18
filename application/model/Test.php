<?php

namespace model;

/**
 * @author Ding <beyondye@gmail.com>
 */
class Test extends \inherit\Model
{

    public function __construct()
    {
        parent::__construct();
        $this->table = 'test';
        $this->primary = 'id';

        $this->schema = [
            'id' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => 'ID 不能为空'],
                'literal' => 'ID',
                'default' => null,
                'required' => false
            ],
            'name' => [
                'validate' => ['regex' => '/^\S+$/', 'message' => '名称不能为空'],
                'literal' => '名称',
                'default' => '',
                'required' => true
            ],
            'parent_id' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => '上级不能为空'],
                'literal' => '上级',
                'default' => 0,
                'required' => true
            ],
            'sort' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => '排序不能为空'],
                'literal' => '排序',
                'default' => 0,
                'required' => true
            ],
            'capital' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => 'CAPITAL 不能为空'],
                'literal' => '是否省会',
                'default' => 0,
                'required' => true
            ],
            'open' => [
                'validate' => ['regex' => '/^\d+$/', 'message' => '是否开通不能为空'],
                'literal' => '是否开通',
                'default' => 0,
                'required' => true
        ]];
    }

}
