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
     * @param string $str
     * @return string
     */
    public function entity(string $str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML401, CHARSET);
    }

    /**
     * 删除php和html标签
     *
     * @param string $str
     * @return string
     */
    public function tag(string $str)
    {
        return strip_tags($str);
    }

    /**
     * 多个空格转换成一个
     *
     * @param string $str
     * @return string
     */
    public function blank(string $str)
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
        global $sys;

        $time = time();
        $hash = hash_hmac('sha1', $time, ENCRYPTION_KEY);

        $token = base64_encode($time . '|' . $hash);

        $sys->session->set(TOKEN_SESSION_NAME, $hash);

        return $token;
    }

    /**
     * token input 名称
     *
     * @global object $sys
     * @return string
     */
    public function tokenName()
    {
        global $sys;

        $name = uniqid('_');
        $sys->session->set(TOKEN_INPUT_NAME, $name);

        return $name;
    }

    /**
     * 验证token
     *
     * @global object $sys
     * @return boolean
     */
    function checkToken()
    {
        global $sys;

        $name = $sys->session->get(TOKEN_INPUT_NAME);
        if (!$name) {
            return false;
        }

        $clienttoken = $sys->input->post($name);
        if (!$clienttoken) {
            return false;
        }

        $clienttoken = explode('|', base64_decode($clienttoken));

        if (count($clienttoken) != 2) {
            return false;
        }

        $clienttime = $clienttoken[0];
        $clienthash = $clienttoken[1];

        if ((intval($clienttime) + TOKEN_EXPIRE) < time()) {
            return false;
        }

        $serverhash = $sys->session->get(TOKEN_SESSION_NAME);
        if ($serverhash == hash_hmac('sha1', $clienttime, ENCRYPTION_KEY)) {

            $sys->session->delete(TOKEN_INPUT_NAME);
            $sys->session->delete(TOKEN_SESSION_NAME);
            $sys->session->regenerate();
            return true;
        }

        $sys->session->delete(TOKEN_INPUT_NAME);
        $sys->session->delete(TOKEN_SESSION_NAME);
        $sys->session->regenerate();

        return false;
    }

}
