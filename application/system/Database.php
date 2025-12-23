<?php
declare(strict_types=1);

namespace system;

use system\database\DatabaseAbstract;

class Database
{

    /**
     * 返回数据库实列
     *
     * @param string $service
     *
     * @return object
     */
    public static function instance(string $service = 'default'): DatabaseAbstract
    {
        static $ins = [];
        if (isset($ins[$service])) {
            return $ins[$service];
        }

        $config = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
        if (!isset($config[$service])) {
            exit(" '{$service}' Config Not Exist,Please Check Database Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];
        if ($arguments['driver'] == 'mysqli') {
            $ins[$service] = new database\mysqli\Database($arguments);
        } else {
            exit(" '{$service}' Driver Not Support.");
        }

        return $ins[$service];
    }


}