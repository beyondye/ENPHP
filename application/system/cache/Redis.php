<?php

namespace system\cache;

class Redis extends AbstractCache
{

    /**
     * redis实列
     *
     * @var null|object
     */
    private $redis = null;

    /**
     * 缓存标签
     *
     * @var array
     */
    private $tags = [];

    /**
     *
     * 构造函数
     *
     * @param array $config
     *
     * @return void
     */
    function __construct($config)
    {
        $this->redis = \system\Redis::instance($config['service']);
    }

    /**
     * 添加或覆盖一个key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire default 0 second lifetime forever
     *
     * @return bool
     */
    public function set(string $key, $value, int $expire = 0)
    {
        $this->setTags($key);

        if ($expire) {
            return $this->redis->set($key, $value, $expire);
        }

        return $this->redis->set($key, $value);
    }

    /**
     * 加法递增
     *
     * @param string $key
     * @param int $value
     *
     * @return mixed
     */
    public function increment(string $key, int $value = 1)
    {
        return $this->redis->incr($key, $value);
    }

    /**
     * 减法递增
     *
     * @param string $key
     * @param int $value
     *
     * @return mixed
     */
    public function decrement(string $key, int $value = 1)
    {
        return $this->redis->decr($key, $value);
    }

    /**
     * 删除一个key
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 清楚所有缓存
     *
     * @return bool
     */
    public function flush()
    {
        if ($this->flushTags()) {
            return true;
        }

        return $this->redis->flushDb();
    }

    /**
     * 获取缓存数据
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if (!$this->getTags($key)) {
            return false;
        }

        return $this->redis->get($key);
    }


    /**
     * 设置标签缓存
     *
     * @param string $key
     *
     * @return void
     */
    private function setTags(string $key)
    {
        if ($this->tags) {
            $tags = $this->tags;
            $this->tags = [];
            foreach ($tags as $tag) {
                $tag = $tag . '__private';
                $this->redis->sAdd($tag, $key);
            }
        }

    }

    /**
     * 获取标签缓存
     *
     * @param string $key
     *
     * @return bool
     */
    private function getTags(string $key)
    {
        if ($this->tags) {
            $tags = $this->tags;
            $this->tags = [];
            foreach ($tags as $tag) {
                $tag = $tag . '__private';
                if ($this->redis->sIsMember($tag, $key)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * 清除标签缓存
     *
     * @return bool
     */
    private function flushTags()
    {
        if ($this->tags) {

            $tags = $this->tags;
            $this->tags = [];

            foreach ($tags as $tag) {
                $tag = $tag . '__private';
                $data = $this->redis->sMembers($tag);
                if (is_array($data)) {
                    foreach ($data as $key) {
                        $this->redis->del($key);
                    }
                }
                $this->redis->del($tag);
            }

            return true;
        }

        return false;
    }

    /**
     * 获取设置标签缓存
     *
     * @param array $tags
     *
     * @return object
     */
    public function tags(array $tags = [])
    {
        if (is_array($tags)) {
            $this->tags = $tags;
        } else {
            $this->tags[] = $tags;
        }

        return $this;
    }

}
