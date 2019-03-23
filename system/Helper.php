<?php

namespace system;

/**
 * 助手函数
 */
class Helper
{

    public function __get($name)
    {
        global $instances;
        $sys = $instances['system']['System'];
        return $sys->load(ucfirst($name), 'helper');
    }

    /**
     * 分页函数
     *
     * @param int $size 每页数据条数
     * @param int $total 数据总数
     * @param int $page 当前页码
     * @param string $url 链接url模板 如：backstage.php?page=<%page%>&tail=0
     * @param int $visible 可见页码
     *
     * @return string
     */
    public function pager($size, $total, $page, $url, $visible = 5)
    {
        $previous = $page - 1;
        $next = $page + 1;
        $html = '';
        $start = 1;

        $info = '<span class="info">共 ' . $total . ' 条记录</span>';
        $url = urldecode($url);

        if ($total > $size) {
            $count = ceil($total / $size);
        } else {
            return '<div class="pager">' . $info . '</div>';
        }


        if ($count <= $visible) {
            $end = $count;
        } else {

            if ($page <= ceil($visible / 2)) {
                $end = $visible;
            } else if ($page > ($count - floor($visible / 2))) {
                $start = $count - $visible + 1;
                $end = $count;
            } else {
                $start = $page - floor($visible / 2);
                $end = $page + floor($visible / 2);
            }
        }

        //previous
        if ($previous > 0) {
            $html .= str_replace('<%page%>', $previous, '<a href="' . $url . '" class="prev">上一页</a>');
        }

        //first page
        if ($page > ceil($visible / 2) && $count > $visible) {
            $html .= str_replace('<%page%>', 1, '<a class="number" href="' . $url . '">1</a><span class="ellipsis">...</span>');
        }

        //loop page number
        for (; $start <= $end; $start++) {
            if ($start == 1) {
                $html .= str_replace('<%page%>', $start, '<a class="number" href="' . $url . '">' . $start . '</a>');
            } else {
                $html .= str_replace('<%page%>', $start, '<a class="number ' . ($page == $start ? 'current' : '') . '" href="' . $url . '">' . $start . '</a>');
            }
        }

        //last page
        if ($page < ($count - floor($visible / 2)) && $count > $visible) {
            $html .= str_replace('<%page%>', $count, '<span class="ellipsis">...</span><a  class="number" href="' . $url . '">' . $count . '</a>');
        }

        // next page
        if ($next < $count) {
            $html .= str_replace('<%page%>', $next, '<a href="' . $url . '" class="next">下一页</a>');
        }

        //info and return
        return '<div class="pager">' . $html . $info . '</div>';
    }

    /**
     * 生成url链接
     *
     * @param array $param
     * @param string $path
     * @param string $anchor
     *
     * @return string
     */
    public function url($param = [], $path = ENTRY, $anchor = '')
    {
        global $vars;

        $anchor = $anchor == '' ? '' : '#' . $anchor;

        $default = [CONTROLLER_KEY_NAME => $vars['controller'], ACTION_KEY_NAME => $vars['action']];

        $keys = $param = array_merge($default, $param);
        $key = $param[CONTROLLER_KEY_NAME] . '/' . $param[ACTION_KEY_NAME];

        unset($keys[CONTROLLER_KEY_NAME]);
        unset($keys[ACTION_KEY_NAME]);

        if ($keys) {
            $key = $key . '/' . join('/', array_keys($keys));
        }

        if (isset(URL[MODULE]) && array_key_exists($key, URL[MODULE])) {

            $temp = URL[MODULE][$key];
            $url = '';
            foreach ($param as $k => $v) {
                if ($url) {
                    $url = str_replace('{' . $k . '}', $v, $url);
                } else {
                    $url = str_replace('{' . $k . '}', $v, $temp);
                }
            }
            return $url . $anchor;
        }

        $query = http_build_query($param) . $anchor;
        return $query ? $path . '?' . $query : $path;
    }

}
