<?php

namespace system;

/**
 * Framework Core Class
 *
 * @author Ding<beyondye@gmail.com>
 */
class System
{
    /**
     * 加载类并返回类实例
     *
     * @global array $instances 全局实例数组
     *
     * @param string $class 类名称
     * @param string|array $arguments 类构造函数参数
     *
     * @return array 类实例数组
     */
    public function load($class, $arguments = '')
    {
        global $instances;

        if (isset($instances[$class])) {
            return $instances[$class];
        }


        if (!class_exists($class)) {
            exit(' Not Found ' . $class);
        }

        //实例化并返回
        $instances[$class] = new $class($arguments);

        return $instances[$class];
    }

    /**
     * 覆盖__get
     *
     * @param string $name 类名
     *
     * @return object
     */
    public function __get($name)
    {
        switch ($name) {
            case 'input';
            case 'config';
            case 'output';
            case 'session';
            case 'cookie';
            case 'lang';
            case 'helper';
            case 'security';
                return $this->load('system\\' . ucfirst($name));
            case 'vars';
                global $vars;
                return $vars;
            case 'db';
                return Database::instance('default');
            case 'redis';
                return Redis::instance('default');
            case 'auth';
                return Auth::instance();
            case 'cache';
                return Cache::instance('default');
        }

    }

    /**
     * 通过方法返回数据库连接实例
     *
     * @param string $service
     *
     * @return object
     */
    protected function db($service)
    {
        return Database::instance($service);
    }

    /**
     * 调用model
     *
     * @param string $name
     *
     * @return object
     */
    protected function model($name)
    {
        return $this->load('model\\' . str_replace('/', '\\', $name));
    }

    /**
     * 调用redis
     *
     * @param string $service
     *
     * @return object
     */
    protected function redis($service)
    {
        return Redis::instance($service);
    }

    /**
     * 加载语言包
     *
     * @param string $lang
     *
     * @return object
     */
    protected function lang($lang)
    {
        return $this->load('system\\Lang', $lang);
    }

    /**
     * 加载缓存
     *
     * @param string $service
     *
     * @return object
     */
    protected function cache($service)
    {
        return Cache::instance($service);

    }

}
