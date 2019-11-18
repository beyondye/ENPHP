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
     * @param string $name
     * @return array|mixed|null|object|auth\Cookie|auth\Jwt|auth\Session
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
                return load('system\\' . ucfirst($name));
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

        return null;

    }

    /**
     * Database
     *
     * @param string $service
     *
     * @return object
     */
    public function db($service)
    {
        return Database::instance($service);
    }

    /**
     * Model
     *
     * @param string $name
     *
     * @return object
     */
    public function model($name)
    {
        return load('model\\' . str_replace('/', '\\', $name));
    }

    /**
     * Redis
     *
     * @param string $service
     *
     * @return object
     */
    public function redis($service)
    {
        return Redis::instance($service);
    }

    /**
     * Language
     *
     * @param string $lang
     *
     * @return object
     */
    public function lang($lang)
    {
        return load('system\\Lang', $lang);
    }

    /**
     * Cache
     *
     * @param string $service
     *
     * @return object
     */
    public function cache($service)
    {
        return Cache::instance($service);

    }

    /**
     * Service
     * @param $name
     * @return string
     *
     */
    public function service($name)
    {

        return load('service\\' . str_replace('/', '\\', $name));

    }

}
