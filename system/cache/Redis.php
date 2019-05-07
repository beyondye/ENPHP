<?php

namespace system\cache;

/**
 * redis缓存
 *
 * @author Ding <beyondye@gmail.com>
 *
 */
class Redis extends AbstractCache
{

    /**
     * 默认配置信息
     *
     * @var array
     */
    public $config = ['service' => 'default'];


    /**
     * @var null|object
     */
    private $redis = null;

    /**
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
        global $sys;
        $this->config = array_merge($this->config, $config);
        $this->redis = $sys->redis($this->config['service']);
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
    public function set($key, $value, $expire = 0)
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
     * @param $key
     * @param int $value
     *
     * @return mixed
     */
    public function increment($key, $value = 1)
    {
        return $this->redis->incr($key, $value);
    }

    /**
     * 减法递增
     *
     * @param $key
     * @param int $value
     *
     * @return mixed
     */
    public function decrement($key, $value = 1)
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
        return $this->redis->delete($key);
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
     * 获取数据
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->getTags($key)) {
            return false;
        }

        return $this->redis->get($key);
    }


    /**
     * set tags
     *
     * @param string $key
     *
     * @return void
     */
    private function setTags($key)
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
     * get tags cache
     *
     * @param string $key
     *
     * @return bool
     */
    private function getTags($key)
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
     * flush tags
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
                        $this->redis->delete($key);
                    }
                }

                $this->redis->delete($tag);

            }
            return true;
        }

        return false;
    }

    /**
     * set tags
     *
     * @param array $tags
     * @return $this|AbstractCache
     */
    public function tags($tags = [])
    {
        $this->tags = $tags;

        return $this;
    }

}
