<?php

namespace Library\Email;

/**
 * SMTP邮件发送, 支持发送纯文本邮件和HTML格式的邮件
 * 
 * @author Ye Ding<beyondye@gmail.com>
 * 
 *例子：
 * $mail = new Smtp(['server'=>'', 'username' => "", 'password' => "",'port' => 25]); 设置smtp服务器
 * $mail->from("XXXXX"); 设置发件人
 * $mail->to("XXXXX"); 设置收件人，多个收件人，调用多次
 * $mail->cc("XXXX"); 设置抄送，多个抄送，调用多次
 * $mail->bcc("XXXXX"); 设置秘密抄送，多个秘密抄送，调用多次
 * $mail->subject("test"); 设置邮件主题
 * $mail->body("<b>test</b>");设置邮件内容
 * $mail->send(); 发送
 */
class Smtp
{

    /**
     * @var string 邮件传输代理用户名
     */
    private $username;

    /**
     * @var string 邮件传输代理密码
     */
    private $password;

    /**
     * @var string 邮件传输代理服务器地址
     */
    protected $server;

    /**
     * @var int 邮件传输代理服务器端口
     */
    protected $port = 25;

    /**
     * @var string 发件人
     */
    protected $from;

    /**
     * @var string 收件人
     */
    protected $to;

    /**
     * @var string 抄送
     */
    protected $cc;

    /**
     * @var string 秘密抄送
     */
    protected $bcc;

    /**
     * @var string 主题
     */
    protected $subject;

    /**
     * @var string 邮件正文
     */
    protected $body;

    /**
     * @var string 附件
     */
    protected $attachment;

    /**
     * @var string 邮件文本类型
     */
    protected $mimetype = 'text/html';

    /**
     * @var reource socket资源
     */
    protected $socket;

    /**
     * @var string 错误信息
     */
    protected $errorMessage;

    /**
     * 设置邮件传输代理
     * 
     * @param string $config['server'] 代理服务器的ip或者域名
     * @param string $config['username'] 认证账号
     * @param string $config['password'] 认证密码
     * @param int $config['port'] 代理服务器的端口，smtp默认25号端口
     */
    public function __construct($config = [])
    {
        //$config=['server'=>'', 'username' => "", 'password' => "",'port' => 25]
        $config = array_merge(['port' => 25], $config);

        $this->server = $config['server'];
        $this->port = $config['port'];

        if (!empty($config['username'])) {
            $this->username = base64_encode($config['username']);
        }

        if (!empty($config['password'])) {
            $this->password = base64_encode($config['password']);
        }
    }

    /**
     * 设置发件人
     * @param string $from 发件人地址
     * @return object
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * 设置收件人，多个收件人，连续调用多次.
     * 
     * @param string $to 收件人地址
     * @return object
     */
    public function to($to)
    {
        if (isset($this->to)) {

            if (is_string($this->to)) {

                $this->to = array($this->to);
                $this->to[] = $to;
            } elseif (is_array($this->to)) {

                $this->to[] = $to;
            }
        } else {

            $this->to = $to;
        }

        return $this;
    }

    /**
     * 设置抄送，多个抄送，连续调用多次.
     * 
     * @param string $cc 抄送地址
     * @return object
     */
    public function cc($cc)
    {
        if (isset($this->cc)) {
            if (is_string($this->cc)) {
                $this->cc = array($this->cc);
                $this->cc[] = $cc;
            } elseif (is_array($this->cc)) {
                $this->cc[] = $cc;
            }
        } else {
            $this->cc = $cc;
        }

        return $this;
    }

    /**
     * 设置秘密抄送，多个秘密抄送，连续调用多次
     * 
     * @param string $bcc 秘密抄送地址
     * @return object
     */
    public function bcc($bcc)
    {
        if (isset($this->bcc)) {
            if (is_string($this->bcc)) {
                $this->bcc = array($this->bcc);
                $this->bcc[] = $bcc;
            } elseif (is_array($this->bcc)) {
                $this->bcc[] = $bcc;
            }
        } else {
            $this->bcc = $bcc;
        }

        return $this;
    }

    /**
     * 设置邮件附件
     * 
     * @param string $attachment 附件，文件地址
     * @return object
     */
    public function attachment($attachment = '')
    {
        if (!empty($attachment)) {
            $this->attachment = $attachment;
        }
        return $this;
    }

    /**
     * 设置邮件主题
     * 
     * @param string $subject 邮件主题
     * @return object
     */
    public function subject($subject = '')
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * 设置邮件mime信息
     * 
     * @param string $mime 是否是纯文本邮件，默认否
     * @return object
     */
    public function mime($mime = 'text/html')
    {
        $this->mimetype = $mime;
        return $this;
    }

    /**
     * 设置邮件主体内容
     * 
     * @param string $body 邮件主体内容
     * @return object
     */
    public function body($body = '')
    {
        $this->body = $body;
        return $this;
    }

    /**
     * 发送邮件
     * 
     * @return boolean
     */
    public function send()
    {
        $command = $this->getCommand();
        $this->socket();

        //print_r($command);exit;
        foreach ($command as $value) {
            if ($this->sendCommand($value[0], $value[1])) {
                continue;
            } else {
                return false;
            }
        }

        $this->close(); //其实这里也没必要关闭，smtp命令：QUIT发出之后，服务器就关闭了连接，本地的socket资源会自动释放
        //echo 'Mail OK!';
        return true;
    }

    /**
     * 返回错误信息
     * 
     * @return string
     */
    public function error()
    {
        if (!isset($this->errorMessage)) {
            $this->errorMessage = "";
        }
        return $this->errorMessage;
    }

    /**
     * 返回mail命令
     * 
     * @return array
     */
    protected function getCommand()
    {
        $command = array(
            array("HELO sendmail\r\n", 250),
            array("AUTH LOGIN\r\n", 334),
            array($this->username . "\r\n", 334),
            array($this->password . "\r\n", 235),
            array("MAIL FROM:<" . $this->from . ">\r\n", 250)
        );

        //邮件头
        $header = "MIME-Version:1.0\r\n";
        if ($this->mimetype == 'text/plain') {
            $header .= "Content-type:text/plain;charset=utf-8\r\n";
        } else {
            $header .= "Content-type:text/html;charset=utf-8\r\n";
        }

        //设置发件人
        $header .= "FROM:test<" . $this->from . ">\r\n";

        //设置收件人
        if (is_array($this->to)) {

            $count = count($this->to);
            for ($i = 0; $i < $count; $i++) {
                $command[] = array("RCPT TO:<" . $this->to[$i] . ">\r\n", 250);
                $header .= "TO:<" . $this->to[$i] . ">\r\n";
            }
        } else {
            $command[] = array("RCPT TO:<" . $this->to . ">\r\n", 250);
            $header .= "TO:<" . $this->to . ">\r\n";
        }

        //设置抄送
        if (isset($this->cc)) {
            if (is_array($this->cc)) {
                $count = count($this->cc);
                for ($i = 0; $i < $count; $i++) {
                    $command[] = array("RCPT TO:<" . $this->cc[$i] . ">\r\n", 250);
                    $header .= "CC:<" . $this->cc[$i] . ">\r\n";
                }
            } else {
                $command[] = array("RCPT TO:<" . $this->cc . ">\r\n", 250);
                $header .= "CC:<" . $this->cc . ">\r\n";
            }
        }

        //设置秘密抄送
        if (isset($this->bcc)) {
            if (is_array($this->bcc)) {
                $count = count($this->bcc);
                for ($i = 0; $i < $count; $i++) {
                    $command[] = array("RCPT TO:<" . $this->bcc[$i] . ">\r\n", 250);
                    $header .= "BCC:<" . $this->bcc[$i] . ">\r\n";
                }
            } else {
                $command[] = array("RCPT TO:<" . $this->bcc . ">\r\n", 250);
                $header .= "BCC:<" . $this->bcc . ">\r\n";
            }
        }

        $header .= "Subject:" . $this->subject . "\r\n\r\n";
        $body = $this->body . "\r\n.\r\n";
        $command[] = array("DATA\r\n", 354);
        $command[] = array($header, "");
        $command[] = array($body, 250);
        $command[] = array("QUIT\r\n", 221);

        return $command;
    }

    /**
     * @param string $command 发送到服务器的smtp命令
     * @param int $code 期望服务器返回的响应吗
     * @param boolean
     */
    protected function sendCommand($command, $code)
    {
        echo 'Send command:' . $command . ',expected code:' . $code . '<br />';
        //发送命令给服务器
        try {
            if (socket_write($this->socket, $command, strlen($command))) {

                //当邮件内容分多次发送时，没有$code，服务器没有返回
                if (empty($code)) {
                    return true;
                }

                //读取服务器返回
                $data = trim(socket_read($this->socket, 1024));
                echo 'response:' . $data . '<br /><br />';

                if ($data) {
                    $pattern = "/^" . $code . "/";
                    if (preg_match($pattern, $data)) {
                        return true;
                    } else {
                        $this->errorMessage = "Error:" . $data . "|**| command:";
                        return false;
                    }
                } else {
                    $this->errorMessage = "Error:" . socket_strerror(socket_last_error());
                    return false;
                }
            } else {
                $this->errorMessage = "Error:" . socket_strerror(socket_last_error());
                return false;
            }
        } catch (Exception $e) {
            $this->errorMessage = "Error:" . $e->getMessage();
        }
    }

    /**
     * 读取附件文件内容，返回base64编码后的文件内容
     * 
     * @return mixed
     */
    protected function readFile()
    {
        if (isset($this->attachment) && file_exists($this->attachment)) {
            $file = file_get_contents($this->attachment);
            return base64_encode($file);
        } else {
            return false;
        }
    }

    /**
     * 建立到服务器的网络连接
     * 
     * @return boolean
     */
    private function socket()
    {
        if (!function_exists("socket_create")) {
            $this->errorMessage = "Extension sockets must be enabled";
            return false;
        }
        //创建socket资源
        $this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));

        if (!$this->socket) {
            $this->errorMessage = socket_strerror(socket_last_error());
            return false;
        }

        socket_set_block($this->socket); //设置阻塞模式
        //连接服务器
        if (!socket_connect($this->socket, $this->server, $this->port)) {
            $this->errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        socket_read($this->socket, 1024);

        return true;
    }

    /**
     * 关闭socket
     * 
     * @return boolean
     */
    private function close()
    {
        if (isset($this->socket) && is_object($this->socket)) {
            $this->socket->close();
            return true;
        }
        $this->errorMessage = "No resource can to be close";
        return false;
    }

}
