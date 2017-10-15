<?php

namespace System\Core\Logger;

/**
 * 日志类
 *
 * @author Ding <beyondye@gmail.com>
 */
class Logger
{

    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * 写入对象
     * @var object
     */
    private $headler = null;

    function __construct($handler)
    {
        $this->handler = new $handler;
    }

    /**
     * 系统不可用
     *
     * @param string $message
     * @return null
     */
    public function emergency($message);

    /**
     * 立刻采取行动
     * 
     * @param string $message
     * @return null
     */
    public function alert($message);

    /**
     * 紧急情况
     *
     * @param string $message
     * @return null
     */
    public function critical($message);

    /**
     * 运行时出现的错误。
     *
     * @param string $message
     * @return null
     */
    public function error($message);

    /**
     * 出现非错误性的异常。
     *
     * @param string $message
     * @return null
     */
    public function warning($message);

    /**
     * 一般性重要的事件。
     *
     * @param string $message
     * @return null
     */
    public function notice($message);

    /**
     * 重要事件
     *
     * @param string $message
     * @return null
     */
    public function info($message);

    /**
     * debug 详情
     *
     * @param string $message
     * @return null
     */
    public function debug($message);

    /**
     * 任意等级的日志记录
     *
     * @param mixed $level
     * @param string $message
     * @return null
     */
    public function log($level, $message)
    {
        
    }

}

