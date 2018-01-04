<?php

namespace Library\Email;

/**
 * sendcloud模板邮件发送
 * 
 * @author Ding<beyondye@gmail.com>
 * 
 * 例子：
 * $mail = new Sendcloud(['template'=>'mail_template_name', 'user' => 'okmemo', 'key' => 'UXy4i0TfEOiz9AXV']); 设置smtp服务器
 * $mail->from("noreply@noreply.okay.do"); 设置发件人 [必须设置]
 * $mail->to("teset@gmail.com"); 设置收件人，多个收件人为数组[必须设置]
 * $mail->replyto('support@okay.do');回复邮件地址[可选]
 * $mail->fromname('OK记用户体验中心');来自签名[可选]
 * $mail->send(["%var%" => ['var'],"%var1%" => ['var1'] ]);设置邮件占位符并发送
 * 
 */
class Sendcloud
{

    /**
     * 模板发送地址
     */
    const TEMPLATE_URL = 'http://sendcloud.sohu.com/webapi/mail.send_template.json';

    /**
     * 发件人邮件地址
     * 
     * @var string
     */
    private $from;

    /**
     * 接受反馈邮件地址
     * 
     * @var string
     */
    private $replyto;

    /**
     * 发件人名称
     * 
     * @var string
     */
    private $fromname;

    /**
     * 邮件标题
     * 
     * @var string
     */
    private $subject;

    /**
     * 账号用户名
     * 
     * @var string
     */
    private $user;

    /**
     * 账号密钥
     * 
     * @var string
     */
    private $key;

    /**
     * 返回错误
     * 
     * @var string
     */
    public $error;

    /**
     * 构造函数
     * 
     * @param array $config=['template'=>'mail_template_name', 'user' => 'okmemo', 'key' => 'UXy4i0TfEOiz9AXV']
     */
    public function __construct($config)
    {
        $config = array_merge(['user' => 'okmemo', 'key' => 'UXy4i0TfEOiz9AXV'], $config);

        if (!isset($config['template'])) {
            $this->error = '没有设置邮件模板名称';
        }

        if (!isset($config['user'])) {
            $this->error = '没有用户名';
        }

        if (!isset($config['key'])) {
            $this->error = '没有key';
        }

        $this->template = $config['template'];

        // API_USER = 'okmemo';
        //KEY = 'UXy4i0TfEOiz9AXV';
        $this->user = $config['user'];
        $this->key = $config['key'];
    }

    /**
     * 设置发送人
     * 
     * @param string $data
     * @return \Library\Email\Sendcloud
     */
    public function from($data)
    {
        if (!$data) {
            $this->error = '没有设置发件人';
        }

        $this->from = $data;
        return $this;
    }

    /**
     * 设置接收人
     * 
     * @param string|array $emails
     * @return \Library\Email\Sendcloud
     */
    public function to($emails)
    {
        if (!$emails) {
            $this->error = '没有设置收件人';
        }

        if (is_string($emails)) {
            $emails = [$emails];
        }

        $this->to = $emails;
        return $this;
    }

    /**
     * 设置邮件主题
     * 
     * @param string $subject
     * @return \Library\Email\Sendcloud
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * 设置发送人名称
     * 
     * @param string $name
     * @return \Library\Email\Sendcloud
     */
    public function fromname($name)
    {
        $this->fromname = $name;
        return $this;
    }

    /**
     * 设置回复邮件
     * 
     * @param string $email
     * @return \Library\Email\Sendcloud
     */
    public function replyto($email)
    {
        $this->replyto = $email;
        return $this;
    }

    /**
     * 设置占位符并发送邮件
     * 
     * @param array $vars
     * @return boolean
     */
    function send($vars)
    {
        if (count($vars) < 1) {
            $this->error = '没有设置模板变量';
            return false;
        }

        $vars = json_encode(["to" => $this->to, "sub" => $vars]);

        $param = array(
            'api_user' => $this->user,
            'api_key' => $this->key,
            'from' => $this->from,
            'replyto' => $this->replyto,
            'fromname' => $this->fromname,
            'to' => $this->to,
            'subject' => $this->subject,
            'substitution_vars' => $vars,
            'template_invoke_name' => $this->template,
            'resp_email_id' => 'true'
        );

        $data = http_build_query($param);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
        ));

        $context = stream_context_create($options);
        $result = file_get_contents(self::TEMPLATE_URL, false, $context);
        $result = json_decode($result);

        if (isset($result->errors)) {
            $this->error = $result->errors;
            return false;
        }

        return $result;
    }

}
