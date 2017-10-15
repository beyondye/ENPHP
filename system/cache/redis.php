<?php

namespace System\Cache;

/**
 * redis操作
 *
 * @author Ye Ding <beyondye@gmail.com>
 * 
 */
class Redis extends \Redis
{

    /**
     * redis配置信息
     * 
     * @var array
     */
    public $config = ['host' => '', 'port' => 6379, 'password' => '', 'database' => 0, 'timeout' => 30, 'serialization' => true];

    /**
     * 
     * redis 构造函数
     * 
     * @param array $config
     * 
     * @return \Redis
     */
    function __construct($config)
    {
        parent::__construct();

        $this->config = array_merge($this->config, $config);

        if (php_sapi_name() == 'cli') {
            if ($this->connect($config['host'], $config['port'], $config['timeout']) == false) {
                echo "Redis '{$config['host']}' Connected Failed. \n";
                exit($this->getLastError());
            }
        } else {
            if ($this->pconnect($config['host'], $config['port'], $config['timeout']) == false) {
                echo "Redis '{$config['host']}' Connected Failed. \n";
                exit($this->getLastError());
            }
        }

        if ($config['password']) {
            if ($this->auth($config['password']) == false) {
                echo "Redis '{$config['host']}' Password Is Incorrect. \n";
                exit($this->getLastError());
            }
        }

        //选择库
        $this->select($config['database']);

        //开启自动序列化
        if ($config['serialization']) {
            $this->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        }
    }

}
