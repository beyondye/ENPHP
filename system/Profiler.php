<?php

namespace system;

/**
 * Profiler
 *
 * @author Ding<beyondye@gmail.com>
 */
class Profiler
{

    private $marks = [];

    /**
     * single method
     *
     * @return null|Profiler
     */
    public static function instance()
    {
        static $ins = null;

        if ($ins) {
            return $ins;
        }

        $ins = new self();

        return $ins;
    }

    /**
     * make time point
     *
     * @return float
     */
    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }


    /**
     * benchmark test
     *
     * @param string $mark
     * @param string $desc
     */
    public function benchmark($mark, $desc = '')
    {

        static $marks = [];

        if (!isset($marks[$mark])) {
            $marks[$mark]['start'] = self::microtime();
            $marks[$mark]['desc'] = $desc;
        } else {

            $end = self::microtime();
            $time = round(($end - $marks[$mark]['start']) * 1000, 4);

            $this->marks[$mark][] = " {$marks[$mark]['desc']} \n ms: {$time} \n ";

            unset($marks[$mark]);

        }

    }

    /**
     * get memory usage point
     *
     * @param string $mark
     * @param string $desc
     */
    public function memory($mark, $desc = '')
    {
        $unit = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB');
        $size = memory_get_usage();
        $mem = round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];

        $this->marks[$mark][] = " Usage: {$mem} \n";
    }


    /**
     * write data to log file
     */
    public function __destruct()
    {

        $this->memory('Last Memory Usage');

        $content = "\n " . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . ' [' . date('Y-m-d H:i:s', time()) . "] ---------------\n";

        foreach ($this->marks as $key => $val) {

            $content = $content . "\n # " . ucfirst($key) . " # \n";
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $content = $content . "\n" . $v;
                }
            }

        }

        file_put_contents(PROFILER_LOG_FILE, "{$content}\n\n", FILE_APPEND | LOCK_EX);
    }

}