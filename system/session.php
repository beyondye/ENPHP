<?php

namespace System;

/**
 * 会话类
 * 
 * @author Ye Ding <beyondye@gmail.com>
 */
class Session
{

    public function __construct()
    {
        if (SESSION_USE_DATABASE) {
            $handler = new \System\SessionHandler();
            session_set_save_handler($handler, true);
        }

        session_name(SESSION_COOKIE_NAME);
        session_set_cookie_params(SESSION_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
        session_start();
        // session_regenerate_id();
    }

    /**
     * 设置会话
     * 
     * @param string $name
     * @param string $value
     * 
     * @return boolean
     */
    public function set($name, $value = '')
    {
        if ($value) {
            $_SESSION[$name] = $value;
        } else {
            unset($_SESSION[$name]);
        }

        return true;
    }

    /**
     * 获取会话
     * 
     * @param string $name
     * 
     * @return string|null
     */
    public function get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

}

/**
 * 自定义session存储
 * 
 * @author Ye Ding<beyondye@gmail.com>
 */
class SessionHandler implements \SessionHandlerInterface
{

    /**
     * 数据库句柄
     * 
     * @var object
     */
    private $db;

    /**
     * old session_id
     * 
     * @var string
     */
    private $old_session_id = '';

    public function open($savePath, $sessionName)
    {
        global $instances;

        $db = $instances['system']->db(SESSION_DATABASE_NAME);
        unset($instances['database'][SESSION_DATABASE_NAME]);

        //var_dump($instances['database']);

        if ($db) {
            $this->db = $db;
            return true;
        } else {
            return false;
        }
    }

    public function close()
    {
        $this->db->close();
        return true;
    }

    public function read($id)
    {
        $this->old_session_id = $id;

        $time = time();

        $result = $this->db->select(SESSION_TABLE_NAME, " session_id='{$id}' and expire>{$time} ", ['data'])->row();

        if ($result) {
            return (string)$result->data;
        } else {
            return "";
        }
    }

    public function write($id, $data)
    {
        $time = time() + SESSION_EXPIRE;
        $record = ['session_id' => $id, 'expire' => $time, 'data' => $data, 'updated' => time()];

        $result = $this->db->replace(SESSION_TABLE_NAME, $record);

        if ($result) {

            if ($this->old_session_id) {
                $this->destroy($this->old_session_id);
            }

            return true;
        } else {
            return false;
        }
    }

    public function destroy($id)
    {
        $result = $this->db->delete(SESSION_TABLE_NAME, ['session_id' => $id]);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function gc($maxlifetime)
    {
        $time = time();
        $result = $this->db->delete(SESSION_TABLE_NAME, " `expire`<{$time} ");
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}
