<?php

namespace Helper;

class Paging
{

    /**
     * 分页函数
     * 
     * @param int $page_size 页面数据条目数量
     * @param int $total 总条目
     * @param int $current_page 当前页
     * @param string $url 链接地址
     * @param string $tail 链接地址结尾
     * 
     * @return string
     */
    function paging($page_size, $total, $current_page, $url, $tail = '')
    {
        //计算总页数
        if ($total > $page_size) {
            $page_count = ceil($total / $page_size);
        } else {
            $page_count = 1;
        }

        //上一页数字
        $previous = $current_page - 1;

        //下一页数字
        $next = $current_page + 1;

        //返回内容
        $html = '';

        //可见数量
        $visible = 5;

        //开始位置
        $start = 1;

        //结束位置
        $end = 0;

        //如果页面数量>1构造分页内容
        if ($page_count > 1) {

            if ($page_count <= $visible) {
                $end = $page_count;
            } else {

                if ($current_page <= ceil($visible / 2)) {
                    $end = $visible;
                } else if ($current_page > ($page_count - floor($visible / 2))) {
                    $start = $page_count - $visible + 1;
                    $end = $page_count;
                } else {
                    $start = $current_page - floor($visible / 2);
                    $end = $current_page + floor($visible / 2);
                }
            }

            //上一页链接
            if ($previous > 0) {
                $html.='<a href="' . $url . $previous . $tail . '" class="paging-prev">上一页</a>';
            }

            //第一页
            if ($current_page > ceil($visible / 2) && $page_count > $visible) {
                $html.='<a class="p" href="' . $url . '1' . $tail . '">1</a><span class="paging-ellipsis">...</span>';
            }

            //页码循环
            for (; $start <= $end; $start++) {
                if ($start == 1) {
                    $html.='<a   class="p" href="' . $url . $start . $tail . '">' . $start . '</a>';
                } else {
                    $html.='<a class="p  ' . ($current_page == $start ? 'paging-current' : '') . '" href="' . $url . $start . $tail . '">' . $start . '</a>';
                }
            }

            //最后页
            if ($current_page < ($page_count - floor($visible / 2)) && $page_count > $visible) {
                $html.='<span class="paging-ellipsis">...</span><a  class="p" href="' . $url . $page_count . $tail . '">' . $page_count . '</a>';
            }

            //下一页
            if ($next < $page_count) {
                $html.='<a href="' . $url . $next . $tail . '" class="paging-next">下一页</a>';
            }

            //页码位置
            $html.='<span class="paging-info"><span class="paging-bold">' . $current_page . '/' . $page_count . '</span>页</span>';

            //页面跳转
            if ($page_count > $visible) {

                $html.='<span class="paging-which"><input id="page-jump" value="' . ($next > $page_count ? $page_count : $next) . '" type="text"></span><a class="paging-info paging-goto" href="javascript:location.href=\'' . $url . '\'+document.getElementById(\'page-jump\').value+\'' . $tail . '\'">跳转</a>';
            }
        }

        return $html . ' <span>共' . $total . '条记录</span>';
    }

}
