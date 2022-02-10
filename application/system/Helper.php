<?php

namespace system;


class Helper
{

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
    public static function pager(int $size, int $total, int $page, string $url, int $visible = 5)
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

}
