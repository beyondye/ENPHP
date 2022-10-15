<?php

namespace system;

class Redis extends \Redis
{
    /**
     * redis配置信息
     *
     * @var array
     */
    public $config = ['host' => '127.0.0.1', 'port' => 6379, 'password' => '', 'database' => 0, 'timeout' => 30, 'serialization' => true];

    /**
     *
     * redis 构造函数
     *
     * @param array $config
     *
     */
    public function __construct(array $config)
    {
        parent::__construct();

        $this->config = array_merge($this->config, $config);

        if (!$this->pconnect($config['host'], $config['port'], $config['timeout'])) {
            echo "Redis '{$config['host']}' Connection Failed. \n";
            exit($this->getLastError());
        }

        if ($config['password']) {
            if (!$this->auth($config['password'])) {
                echo "Redis '{$config['host']}' Password Incorrect. \n";
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

    /**
     * 类销毁
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 返回实列对象
     *
     * @param string $service
     *
     * @return object
     */
    public static function instance(string $service = 'default')
    {
        static $ins = [];
        if (isset($ins[$service])) {
            return $ins[$service];
        }

        $config = include APP_DIR . 'config/' . ENVIRONMENT . '/redis' . EXT;
        if (!isset($config[$service])) {
            exit(" '{$service}' Config Not Exist,Please Check Redis Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];
        $ins[$service] = new self($arguments);
        return $ins[$service];
    }

}
