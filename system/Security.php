<?php

namespace system;

/**
 *  Security
 *
 * @author Ding<beyondye@gmail.com>
 */
class Security
{

    /**
     * 转化标签为实体
     * 
     * @param string  $str
     * @return string
     */
    public function entity($str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML401, CHARSET);
    }

    /**
     * 删除php和html标签
     * 
     * @param string $str
     * @return string
     */
    public function tag($str)
    {
        return strip_tags($str);
    }

    /**
     * 多个空格转换成一个
     * 
     * @param string $str
     * @return string
     */
    public function blank($str)
    {
        return preg_replace('/[\s]+/is', ' ', $str);
    }

    /**
     * 生成表单token，同一个上下文只能有一个token
     * 
     * @global object array $instances
     * @return string
     */
    public function token()
    {
        $token = md5(uniqid());

        global $instances;
        $instances['system']['System']->session->set(TOKEN_SESSION_NAME, $token);

        return $token;
    }
    
    

}
