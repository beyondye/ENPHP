<?php

namespace system;

class System
{

    /**
     * Database
     *
     * @param string $service
     *
     * @return object
     */
    protected function db(string $service)
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
    protected function model(string $name)
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
    protected function redis(string $service)
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
    protected function lang(string $lang)
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
    protected function cache(string $service)
    {
        return Cache::instance($service);

    }

    /**
     * Service
     *
     * @param $name
     *
     * @return string
     *
     */
    protected function service(string $name)
    {

        return load('service\\' . str_replace('/', '\\', $name));

    }

}
