<?php

namespace system\cache;

/**
 * file cache class
 * 
 * @author Ding<beyondye@gmail.com>
 */
class File
{
    /**
     *默认配置信息
     * 
     * @var array
     */
    public $config = ['dir' => APP_DIR . '/cache/', 'driver' => 'file'];

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
        $this->config = array_merge($this->config, $config);

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
    public function set($key, $value, $expire = 0)
    {

        $data = ['data' => $value, 'time' => time(), 'expire' => $expire];
        $data = json_encode($data);

        $file = $this->config['dir'] . $key;

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
                return false;
            }

            return $value;
        }

        $item = $item + $value;

        $result = $this->set($key, $item);
        if ($result === false) {
            return false;
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
                return false;
            }

            return $value;
        }

        $item = $item - $value;

        $result = $this->set($key, $item);
        if ($result === false) {
            return false;
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
        $file = $this->config['dir'] . $key;

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * 清楚所有缓存
     * 
     * @return mixed
     */
    public function flush()
    {
        $dir = $this->config['dir'];
        $files = scandir($dir);

        foreach ($files as $file) {

            if (is_dir($dir . $file)) {
                continue;
            }

            unlink($dir . $file);
        }

        return ture;
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
        $file = $this->config['dir'] . $key;
        $data = file_get_contents($file);

        if ($data) {

            $data = json_decode($data);
            if (!$this->expire($data)) {
                $this->delete($key);
                return fasle;
            }

            return $data['data'];
        }

        return flase;
    }

    /**
     * 检查key是否过期
     * 
     * @param $data
     * @return bool
     */
    private function expire($data)
    {
        $now = time();
        $expire = intval($data['expire']);
        $time = intval($data['time']);

        if ($expire === 0) {
            return true;
        }

        if ($expire + $time > $now) {
            return false;
        }

        return true;
    }
}