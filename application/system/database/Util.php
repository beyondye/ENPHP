<?php

declare(strict_types=1);

namespace system\database;

class Util
{
    /**
     * 构建查询条件数组
     * @param mixed ...$wheres 查询条件
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
     * where('id','not in',[1,2,3],'and');// [['id','not in',[1,2,3]]]
     * where('id','not between',[1,10],'or');// [['id','not between',[1,10]]]
     *
     * where(['id','=',1],['name','=','张三']);// [['id','=',1,'and'],['name','=','张三']]
     * where(['id','in',[1,2,3],'or'],['name','=','张三']);// [['id','in',[1,2,3],'or'],['name','=','张三']]
     * where(['id','>',1,'and'],['name','like','%张三%']);// [['id','>',1,'and'],['name','like','%张三%']]
     * 
     * where(null);// ['id','=',null]
     * where(['id'=>'211','age'=>[18,20]]);// [['id','=','211','and'],['age','in',[18,20]]]
     * 
     */
    public static function where(mixed ...$wheres): array
    {
        if (empty($wheres)) {
            return [];
        }

        if (is_string($wheres[0]) && trim($wheres[0]) === '') {
            return [];
        }

        $result = [];

        $build = function ($wheres, $build, &$result): void {

            if (empty($wheres)) {
                return;
            }

            if (array_key_exists(0, $wheres) && (is_scalar($wheres[0]) || is_null($wheres[0]))) {

                $count = count($wheres);

                if ($count == 1) {
                    $result[] = ['id', '=', $wheres[0]];
                    return;
                }


                if ($count == 2 && is_string($wheres[0]) && array_key_exists(1, $wheres) && (is_scalar($wheres[1]) || is_null($wheres[1]))) {
                    $result[] = [$wheres[0], '=', $wheres[1]];
                    return;
                }

                if ($count == 2 && array_key_exists(1, $wheres) && is_array($wheres[1]) && is_string($wheres[0])) {
                    $result[] = [$wheres[0], 'in', $wheres[1]];
                    return;
                }

                if ($count == 3 && array_key_exists(1, $wheres) && array_key_exists(2, $wheres) && is_string($wheres[0]) && is_string($wheres[1])) {
                    $result[] = [$wheres[0], $wheres[1], $wheres[2]];
                    return;
                }

                if ($count == 4 && array_key_exists(1, $wheres) && array_key_exists(2, $wheres) && array_key_exists(3, $wheres) && is_string($wheres[0]) && is_string($wheres[1]) && is_string($wheres[3])) {
                    $result[] = [$wheres[0], $wheres[1], $wheres[2], $wheres[3]];
                    return;
                }

                throw new DatabaseException('Not Support Where Condition Format,Please Check The Format.' . json_encode($wheres));
            }

            if (array_key_exists(0, $wheres) && is_array($wheres[0])) {
                foreach ($wheres as $where) {

                    if (!is_array($where)) {
                        throw new DatabaseException('If First Parameter Is Array,Other Parameters Must Be Array.' . json_encode($wheres));
                    }
                    $build($where, $build, $result);
                }
                return;
            }

            if (is_array($wheres)) {
                foreach ($wheres as $key => $value) {
                    if (is_string($key)) {
                        if (is_scalar($value) || is_null($value)) {
                            $result[] = [$key, '=', $value];
                            continue;
                        }

                        if (is_array($value)) {
                            $result[] = [$key, 'in', $value];
                            continue;
                        }

                        throw new DatabaseException('Not Support Where Condition Format,Please Check The Format.' . json_encode($wheres));
                    }
                }
                return;
            }

            throw new DatabaseException('Not Support Non-Array Parameter.' . json_encode($wheres));
        };

        $build($wheres, $build, $result);

        $count = count($result);
        $i = 0;
        foreach ($result as $key => $value) {
            $i++;
            if ($count == $i && count($value) == 4) {
                array_pop($result[$key]);
            } elseif ($count > $i && count($value) == 3) {
                array_push($result[$key], 'and');
            }
        }

        return $result;
    }
}
