<?php

declare(strict_types=1);

namespace system\database;

class Util
{
    /**
     * 构建查询条件数组
     * @param float|int|string|array ...$wheres 查询条件
     * @return array 查询条件数组
     * @example
     * 示例：
     * where(1); // [['id','=',1]]  默认字段名为id
     * where('1');// [['id','=','1']] 默认字段名为id
     * 
     * where('id',1);// [['id','=',1]]
     * where('id','1');// [['id','=','1']]
     * where('id',[1,2,3]);// [['id','in',[1,2,3]]]
     * 
     * where('id','=',1);// [['id','=',1]]
     * where('id','in',[1,2,3]);// [['id','in',[1,2,3]]]
     * where('id','between',[1,10]);// [['id','between',[1,10]]]
     * 
     * where('id','not in',[1,2,3],'and');// [['id','not','in',[1,2,3]]]
     * where('id','not between',[1,10],'or');// [['id','not','between',[1,10]]]
     *
     * where(['id','=',1],['name','=','张三']);// [['id','=',1],['name','=','张三']]
     * where(['id','in',[1,2,3],'or'],['name','=','张三']);// [['id','in',[1,2,3]],['name','=','张三']]
     * where(['id','>',1,'and'],['name','like','%张三%']);// [['id','>',1],['name','like','%张三%']]
     * 
     */
    public static function where(float|int|string|array ...$wheres): array
    {
        if (empty($wheres)) {
            return [];
        }

        $result = [];

        if (is_string($wheres[0]) || is_numeric($wheres[0])) {

            $count = count($wheres);
            if ($count == 1) {
                $result[] = ['id', '=', $wheres[0]];
            }
            elseif ($count == 2 && is_scalar($wheres[1])) {
                $result[] = [$wheres[0], '=', $wheres[1]];
            }
            elseif ($count == 2 && is_array($wheres[1])) {
                $result[] = [$wheres[0], 'in', $wheres[1]];
            }
            elseif ($count == 3 ) {
                $result[] = [$wheres[0], $wheres[1], $wheres[2]];
            }
            else{
                $result[] = [$wheres[0], $wheres[1], $wheres[2], $wheres[3]];
            }
        }
        elseif (is_array($wheres[0])) {
            foreach ($wheres as $where) {
                if (is_string($where[0]) || is_numeric($where[0])) {
                    $subresult = self::where(...$where);
                    if (!empty($subresult)) {
                        $result[] = $subresult[0];
                    }
                }
            }
        }

        return $result;
    }

}