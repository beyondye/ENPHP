<?php

namespace system\cache;


abstract class AbstractCache
{

    /**
     * 添加或覆盖一个key
     *
     * @param string $key
     * @param string|int $value
     * @param int $expire default 0 second lifetime forever
     *
     * @return bool
     */
    abstract protected function set(string $key, $value, int $expire = 0);

    /**
     * 加法递增
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    abstract protected function increment(string $key, int $value = 1);


    /**
     * 减法递增
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    abstract protected function decrement(string $key, int $value = 1);


    /**
     * 删除一个key
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function delete(string $key);

    /**
     * 清楚所有缓存
     *
     * @return bool
     */
    abstract protected function flush();

    /**
     * 获取数据
     *
     * @param string $key
     *
     * @return mixed
     */
    abstract protected function get(string $key);

    /**
     * 设置缓存标签
     *
     * @param array $keys
     * @return $this
     */
    abstract protected function tags(array $keys = []);

}