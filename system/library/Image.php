<?php

namespace System\Library;

/**
 * 图片缩放加水印操作类
 * @example
 * 
 * $image= new \System\Library\Image();
  $image->width=300;
  $image->source='G:\fw.png';
  $image->save='G:\fw300.png';
  $image->fontsize=20;
  $image->font=APP_DIR.'font/1.ttf';
  $image->text='www.comdal.codm';
  $image->watermark=true;
  //$image->markimg='G:\water.png';
  $image->resize();
  echo $image->message;
 * 
 * @author Ding<beyondye@gmail.com>
 */
class Image
{

    /**
     * 缩放宽度
     * @var int
     */
    public $width = 0;

    /**
     * 缩放高度
     * 
     * @var int
     */
    public $height;

    /**
     * 源文件地址
     * @var string 
     */
    public $source;

    /**
     * 保存到目标文件地址
     * @var string 
     */
    public $save;

    /**
     * 图片质量
     * @var int
     */
    public $quality = 90;

    /**
     * 是否加水印
     * @var bool 
     */
    public $watermark = false;

    /**
     * 字体地址
     * @var string 
     */
    public $font;

    /**
     * 字体文本大小
     * @var string 
     */
    public $fontsize = 12;

    /**
     * 水印文字
     * @var string 
     */
    public $text;

    /**
     * 水印图片
     * @var string 
     */
    public $markimg;

    /**
     * 运行状态码
     * @var int 
     */
    public $code;

    /**
     * 运行返回信息
     * @var string
     */
    public $message;

    /**
     * 当前文件扩展名
     * @var string 
     */
    private $ext;

    /**
     * 允许的图片扩展名
     */
    const ALLOW_EXTENSION = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * 源文件扩展错误
     */
    const SOURCE_EXT_ERR = 1;

    /**
     * 源文件不存在
     */
    const SOURCE_NO_EXSIT = 2;

    /**
     * 源文件加载失败
     */
    const SOURCE_LOAD_FAIL = 3;

    /**
     * 加水印失败
     */
    const WATER_MARK_FAIL = 4;

    /**
     * 水印文件不存在
     */
    const WATER_FILE_NO_EXSIT = 5;

    /**
     * 字体文件不存在
     */
    const FONT_FILE_NO_EXSIT = 6;

    /**
     * 水印文字不能为空
     */
    const WATER_TEXT_NO_NULL = 7;

    /**
     * 保存数据失败
     */
    const SAVE_FAIL = 8;

    /**
     * 缩放成功
     */
    const RESIZE_SUCCESS = 0;

    /**
     * GD库未安装
     */
    const GD_FUCTION_NO_EXSIT = 10;

    /**
     * 保存文件不能为空
     */
    const SAVE_FILE_NO_NULL = 11;

    /**
     * 状态 信息数组
     */
    const MSG = [
        self::SOURCE_EXT_ERR => '源文件扩展错误',
        self::SOURCE_NO_EXSIT => '源文件不存在',
        self::SOURCE_LOAD_FAIL => '源文件加载失败',
        self::WATER_MARK_FAIL => '加水印失败',
        self::WATER_FILE_NO_EXSIT => '水印文件不存在',
        self::FONT_FILE_NO_EXSIT => '字体文件不存在',
        self::WATER_TEXT_NO_NULL => '水印文字不能为空',
        self::SAVE_FAIL => '保存数据到文件失败',
        self::RESIZE_SUCCESS => '缩放成功',
        self::GD_FUCTION_NO_EXSIT => '没有安装GD库',
        self::SAVE_FILE_NO_NULL => '保存文件不能为空'
    ];

    /**
     * 载入源文件图片
     * 
     * @return resource
     */
    private function create()
    {
        if (!$this->ext()) {
            $this->message = self::MSG[self::SOURCE_EXT_ERR];
            $this->code = self::SOURCE_EXT_ERR;
            return false;
        }

        $loadfunc = 'imagecreatefrom' . $this->ext;

        if (!function_exists($loadfunc)) {
            $this->message = self::MSG[self::GD_FUCTION_NO_EXSIT];
            $this->code = self::GD_FUCTION_NO_EXSIT;
            return false;
        }

        if (!file_exists($this->source)) {

            $this->message = self::MSG[self::SOURCE_NO_EXSIT];
            $this->code = self::SOURCE_NO_EXSIT;
            return false;
        }

        $source = $loadfunc($this->source);

        if ($source) {
            return $source;
        }

        $this->message = self::MSG[self::SOURCE_LOAD_FAIL];
        $this->code = self::SOURCE_LOAD_FAIL;
        return false;
    }

    /**
     * 获取源文件扩展名
     * @return bool
     */
    private function ext()
    {
        $ext_arr = explode('.', $this->source);
        $ext = strtolower(end($ext_arr));

        if (in_array($ext, self::ALLOW_EXTENSION)) {
            if ($ext == 'jpg') {
                $ext = 'jpeg';
            }

            $this->ext = $ext;
            return true;
        }

        return false;
    }

    /**
     * 保存数据到文件
     * 
     * @param type $image
     * @return boolean
     */
    private function save($image)
    {
        $ext = $this->ext;
        $savefunc = 'image' . $ext;

        if ($ext == 'gif') {
            return $savefunc($image, $this->save);
        } elseif ($ext == 'png') {
            return $savefunc($image, $this->save, $this->quality / 10);
        } else {
            return $savefunc($image, $this->save, $this->quality);
        }

        return false;
    }

    /**
     * 图片缩放操作
     * 
     * @return boolean
     */
    public function resize()
    {
        $source = $this->create();

        if (!$source) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if (intval($this->width) > 0) {
            $this->height = $height / ($width / $this->width);
            $image = imagecreatetruecolor($this->width, $this->height);
            imagecopyresampled($image, $source, 0, 0, 0, 0, $this->width, $this->height, $width, $height);
        } else {
            $image = $source;
        }

        if ($this->watermark == true) {
            if (!$this->watermark($image)) {
                imagedestroy($source);
                @imagedestroy($image);
                return false;
            }
        }

        if (!trim($this->save)) {
            $this->message = self::MSG[self::SAVE_FILE_NO_NULL];
            $this->code = self::SAVE_FILE_NO_NULL;
            return false;
        }

        if (!$this->save($image)) {
            imagedestroy($source);
            @imagedestroy($image);

            $this->message = self::MSG[self::SAVE_FAIL];
            $this->code = self::SAVE_FAIL;
            return false;
        }

        imagedestroy($source);
        @imagedestroy($image);

        $this->message = self::MSG[self::RESIZE_SUCCESS];
        $this->code = self::RESIZE_SUCCESS;
        return true;
    }

    /**
     * 给缩放图片加水印
     * 
     * @param resource $image
     * @return boolean
     */
    private function watermark($image)
    {
        $swidth = imagesx($image);
        $sheight = imagesy($image);

        if ($this->markimg) {

            if (!file_exists($this->markimg)) {
                $this->message = self::MSG[self::WATER_FILE_NO_EXSIT];
                $this->code = self::WATER_FILE_NO_EXSIT;
                return false;
            }

            $markimg = imagecreatefrompng($this->markimg);
            $mwidth = imagesx($markimg);
            $mheight = imagesy($markimg);

            imagecopymerge($image, $markimg, $swidth - $mwidth, $sheight - $mheight, 0, 0, $mwidth, $mheight, 30);

            return imagedestroy($markimg);
        }


        if (!file_exists($this->font)) {
            $this->message = self::MSG[self::FONT_FILE_NO_EXSIT];
            $this->code = self::FONT_FILE_NO_EXSIT;
            return false;
        }

        if (!trim($this->text)) {
            $this->message = self::MSG[self::WATER_TEXT_NO_NULL];
            $this->code = self::WATER_TEXT_NO_NULL;
            return false;
        }

        $white = imagecolorallocate($image, 225, 225, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        $box = imageftbbox($this->fontsize, 0, $this->font, $this->text);
        $stringwidth = $box[0] + ($swidth - $box[4] );

        imagettftext($image, $this->fontsize, 0, $stringwidth - 5, $sheight - 5, $black, $this->font, $this->text);
        imagettftext($image, $this->fontsize, 0, $stringwidth - 4, $sheight - 4, $white, $this->font, $this->text);

        return true;
    }

}
