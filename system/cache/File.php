<?php

namespace system\cache;

/**
 * file cache class
 *
 * @author Ding<beyondye@gmail.com>
 */
class File extends AbstractCache
{

    //start time
    const START_TIME = 1557149377;

    /**
     * 默认配置信息
     *
     * @var array
     */
    protected $config = ['dir' => APP_DIR . 'cache/', 'mode' => 0777];


    /**
     * @var array
     */
    protected $tags = [];

    /**
     *
     * 构造函数
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * route file path
     *
     * @param string $key
     * @param string $base
     *
     * @return string
     */
    protected function route($key)
    {
        $hash = substr(md5($key), 0, 2);
        return $this->config['dir'] . $hash . '/' . $key;
    }


    /**
     * 添加或覆盖一个key
     *
     * @param $key
     * @param $value
     * @param $expire default 0 second lifetime forever
     *
     * @return bool
     */
    public function set($key, $value = '', $expire = 0)
    {

        $this->setTags($key);

        $file = $this->route($key);

        if (!file_exists($file)) {

            $parts = explode('/', $file);
            array_pop($parts);
            $dir = '';
            foreach ($parts as $part) {
                if (!is_dir($dir .= "/$part")) {
                    mkdir($dir, $this->config['mode']);
                }
            }

        }

        $data = ['data' => $value, 'time' => time() - self::START_TIME, 'expire' => intval($expire)];
        $data = serialize($data);

        return file_put_contents($file, $data, LOCK_EX);

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
        $item = $this->get($key);

        if ($item === false) {

            $result = $this->set($key, $value);
            if ($result === false) {
                return $result;
            }

            return $value;
        }

        $item = $item + $value;

        $result = $this->set($key, $item);
        if ($result === false) {
            return $result;
        }

        return $item;
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
        $item = $this->get($key);

        if ($item === false) {

            $result = $this->set($key, $value);
            if ($result === false) {
                return $result;
            }

            return $value;
        }

        $item = $item - $value;

        $result = $this->set($key, $item);
        if ($result === false) {
            return $result;
        }

        return $item;
    }

    /**
     * 删除一个key，同事会删除缓存文件
     *
     * @param $key
     *
     * @return bool
     */
    public function delete($key)
    {
        $file = $this->route($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     *  清除所有缓存
     *
     * @return boolean
     */
    public function flush()
    {

        if ($this->flushTags()) {
            return true;
        }

        if (!is_dir($this->config['dir'])) {
            return true;
        }

        return $this->delTree($this->config['dir']);

    }

    /**
     * delete dir tree
     *
     * @param string $dir
     *
     * @return bool
     */
    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * 获取数据
     *
     * @param $key
     *
     * @return bool|mixed|string
     */
    public function get($key)
    {

        if (!$this->getTags($key)) {
            return false;
        }

        $file = $this->route($key);

        if (!file_exists($file)) {
            return false;
        }

        $data = file_get_contents($file);

        if ($data) {

            $data = unserialize($data);

            if ($this->expire($data) === true) {
                $this->delete($key);
                return false;
            }

            return $data['data'];
        }

        return false;
    }

    /**
     * 检查key是否过期
     *
     * @param $data
     * @return bool
     */
    protected function expire($data)
    {
        $now = time() - self::START_TIME;
        $expire = $data['expire'];
        $time = $data['time'];

        if ($expire === 0) {
            return false;
        }

        if (($expire + $time) < $now) {
            return true;
        }

        return false;
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
                $data = $this->get($tag);

                if ($data === false) {
                    $data = [];
                    $data[] = $key;
                    $this->set($tag, $data);
                }

                if (is_array($data) && in_array($key, $data) == false) {
                    $data[] = $key;
                    $this->set($tag, $data);
                }

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
                $data = $this->get($tag);

                if ($data && in_array($key, $data)) {
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
                $data = $this->get($tag);

                if (is_array($data)) {

                    foreach ($data as $key) {
                        $this->delete($key);
                    }
                }

                $this->delete($tag);

            }
            return true;
        }

        return false;
    }

    /**
     * get tags cache
     *
     * @param array $tags
     *
     * @return $this|AbstractCache
     */
    public function tags($tags = [])
    {
        $this->tags = $tags;
        return $this;
    }

}
