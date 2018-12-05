<?php

namespace system\library;

/**
 * 上传文件
 *
 * @author Ding<beyondye@gmail.com>
 */
class Upload
{
    /**
     * 保存物理文件夹目录
     *
     * @var string
     */
    public $dir;

    /**
     * 保存的文件名
     * @var string
     */
    public $filename;

    /**
     * 来自表单的数据字段数组
     * @var array
     */
    public $data;

    /**
     * 允许的文件扩展名
     * @var array
     */
    public $extension;

    /**
     * 执行状态码
     * @var int;
     */
    public $code;

    /**
     * 返回当前执行结果信息
     * @var string
     */
    public $message;

    /**
     * 创建目录权限码
     * @var int
     */
    public $mode = 0777;

    /**
     * Data未设置错误码
     */
    const DATA_ARRAY_NULL = 12;

    /**
     * Extension未设置错误码
     */
    const EXT_ARRAY_NULL = 13;

    /**
     * 不合法扩展名错误码
     */
    const EXT_NAME_ILLEGAL = 14;

    /**
     * 数据不是来自表单提交错误码
     */
    const DATA_NO_POST = 15;

    /**
     * 创建目录错误码
     */
    const MKDIR_ERR = 16;

    /**
     * Dir未设置错误码
     */
    const DIR_NULL = 17;

    /**
     * 未知错误代码
     */
    const UNKNOW_ERR = 18;

    /**
     * 提示信息数组
     */
    const ERROR_MSG = [
        UPLOAD_ERR_OK => '上传成功',
        UPLOAD_ERR_INI_SIZE => '文件大小超过php.ini中限制的值',
        UPLOAD_ERR_FORM_SIZE => '文件大小超过表单中MAX_FILE_SIZE指定的值',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE => '没有上传文件',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        UPLOAD_ERR_EXTENSION => '上传扩展错误',
        self::DATA_ARRAY_NULL => 'Data未设置',
        self::EXT_ARRAY_NULL => 'Extension未设置',
        self::EXT_NAME_ILLEGAL => '扩展名不合法',
        self::DATA_NO_POST => '文件数据不合法',
        self::MKDIR_ERR => '创建文件目录错误',
        self::DIR_NULL => 'Dir未设置',
        self::UNKNOW_ERR => '未知错误'
    ];

    /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct($config = ['dir' => '', 'filename' => '', 'data' => [], 'extension' => []])
    {
        $this->dir = $config['dir'];
        $this->filename = $config['filename'];
        $this->data = $config['data'];
        $this->extension = $config['extension'];
    }

    /**
     * 获取扩展名
     *
     * @return str
     */
    private function getExt()
    {
        $ext = explode('.', $this->data['name']);
        return strtolower(end($ext));
    }

    /**
     * 判断扩展名是否合法
     *
     * @return boolean
     */
    private function isExt()
    {
        if (!$this->extension || !is_array($this->extension)) {
            $this->code = self::EXT_ARRAY_NULL;
            $this->message = self::ERROR_MSG[self::EXT_ARRAY_NULL];
            return false;
        }

        $ext = $this->getExt();

        if (in_array($ext, $this->extension)) {
            return true;
        }

        $this->code = self::EXT_NAME_ILLEGAL;
        $this->message = self::ERROR_MSG[self::EXT_NAME_ILLEGAL];
        return false;
    }

    /**
     * 执行上传操作
     *
     * @return boolean
     */
    public function execute()
    {
        if (!$this->data) {
            $this->code = self::DATA_ARRAY_NULL;
            $this->message = self::ERROR_MSG[self::DATA_ARRAY_NULL];
            return false;
        }

        if ($this->data['error'] != UPLOAD_ERR_OK) {
            $this->code = $this->data['error'];
            $this->message = self::ERROR_MSG[$this->data['error']];
            return false;
        }

        if (trim($this->dir) == '') {
            $this->code = self::DIR_NULL;
            $this->message = self::ERROR_MSG[self::DIR_NULL];
            return false;
        }

        if (!$this->isExt()) {
            return false;
        }

        $tempFile = $this->data['tmp_name'];
        if (!is_uploaded_file($tempFile)) {
            $this->message = self::ERROR_MSG[self::DATA_NO_POST];
            return false;
        }


        if (!$this->createDir($this->dir)) {
            $this->code = self::MKDIR_ERR;
            $this->message = self::ERROR_MSG[self::MKDIR_ERR];
            return false;
        }

        //生成文件名
        if (trim($this->filename)) {
            $this->filename = $filename = $this->filename . '.' . $this->getExt();
        } else {
            $this->filename = $filename = uniqid() . '.' . $this->getExt();
        }

        $targetFile = $this->dir . $filename;

        //移动文件
        if (move_uploaded_file($tempFile, $targetFile)) {
            $this->code = UPLOAD_ERR_OK;
            $this->message = self::ERROR_MSG[UPLOAD_ERR_OK];
            return true;
        }

        $this->code = self::UNKNOW_ERR;
        $this->message = self::ERROR_MSG[self::UNKNOW_ERR];

        return false;
    }

    /**
     * 递归创建目录
     * @param string $path
     * @return boolean
     */
    private function createDir($path)
    {
        if (!is_dir($path)) {
            if ($this->createDir(dirname($path))) {
                return mkdir($path, $this->mode);
            }
        }
        return true;
    }

}
