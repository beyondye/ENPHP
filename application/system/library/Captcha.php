<?php

namespace system\library;

class Captcha
{
    /**
     * 字体路径
     *
     * @var string
     */
    public $fontPath;

    /**
     * 图片数据
     *
     * @var string
     */
    private $image;

    /**
     * 生成几位验证码
     *
     * @var int
     */
    public $charLen = 4;

    /**
     * 验证码字符
     *
     * @var array
     */
    private $arrChr = array();

    /**
     * 图宽片
     *
     * @var int
     */
    public $width = 100;

    /**
     * 图片高
     *
     * @var int
     */
    public $height = 30;

    /**
     * 验证码文本
     *
     * @var string
     */
    private $text = '';

    /**
     * 背景色
     *
     * @var string
     */
    public $bgcolor = "#ffffff";

    /**
     * 生成杂点
     *
     * @var bool
     */
    private $showNoisePix = true;

    /**
     * 生成杂点数量
     *
     * @var int
     */
    private $noiseNumPix = 80;

    /**
     * 生成杂线
     *
     * @var bool
     */
    private $showNoiseLine = true;

    /**
     * 生成杂线数量
     *
     * @var int
     */
    private $noiseNumLine = 2;

    /**
     * 边框，当杂点、线一起作用的时候，边框容易受干扰
     *
     * @var bool
     */
    public $showBorder = true;

    /**
     * 边框颜色
     *
     * @var string
     */
    public $borderColor = "#cccccc";

    /**
     * 构造函数
     *
     * Captcha constructor.
     */
    public function __construct()
    {
        if (defined('APP_DIR')) {
            $this->fontPath = APP_DIR . 'font/';
        }

        $this->arrChr = array_merge(range(1, 9), range('A', 'Z'), range('a', 'z'));
    }

    /**
     * 获取颜色
     *
     * @param $color
     *
     * @return int|string|string[]|null
     */
    private function getColor($color)
    {
        $color = preg_replace("/^#/i", "", $color);
        $r = $color[0] . $color[1];
        $r = hexdec($r);
        $b = $color[2] . $color[3];
        $b = hexdec($b);
        $g = $color[4] . $color[5];
        $g = hexdec($g);
        $color = imagecolorallocate($this->image, $r, $b, $g);

        return $color;
    }

    /**
     * 设置杂点
     *
     * @return void
     */
    private function setNoisePix()
    {
        for ($i = 0; $i < $this->noiseNumPix; $i++) {
            $randColor = imageColorAllocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));
            imageSetPixel($this->image, rand(0, $this->width), rand(0, $this->height), $randColor);
        }
    }

    /**
     * 设置杂线
     *
     * @return void
     */
    private function setNoiseLine()
    {
        for ($i = 0; $i < $this->noiseNumLine; $i++) {
            $randColor = imageColorAllocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));
            imageline($this->image, rand(1, $this->width), rand(1, $this->height), rand(1, $this->width), rand(1, $this->height), $randColor);
        }
    }

    /**
     * 创建验证码图片
     *
     * @return void
     */
    public function create()
    {
        $this->image = imageCreate($this->width, $this->height);
        $this->back = $this->getColor($this->bgcolor);

        imageFilledRectangle($this->image, 0, 0, $this->width, $this->height, $this->back);

        $size = $this->width / $this->charLen - 4;

        if ($size > $this->height) {
            $size = $this->height;
        }

        $left = ($this->width - $this->charLen * ($size + $size / 10)) / $size + 5;
        $code = '';
        for ($i = 0; $i < $this->charLen; $i++) {

            $randKey = rand(0, count($this->arrChr) - 1);
            $randText = $this->arrChr[$randKey];
            $code .= $randText;
            $textColor = imageColorAllocate($this->image, rand(0, 100), rand(0, 100), rand(0, 100));
            $font = $this->fontPath . '/' . rand(1, 5) . ".ttf";
            $randsize = rand($size - $size / 10, $size + $size / 10);
            $location = $left + ($i * $size + $size / 10);

            imagettftext($this->image, $randsize, rand(-18, 18), $location, rand($size - $size / 10, $size + $size / 10) + 2, $textColor, $font, $randText);
        }

        if ($this->showNoisePix == true) {
            $this->setNoisePix();
        }

        if ($this->showNoiseLine == true) {
            $this->setNoiseLine();
        }

        if ($this->showBorder == true) {
            $this->borderColor = $this->getColor($this->borderColor);
            imageRectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $this->borderColor);
        }

        $this->text = strtolower($code);
    }

    /**
     * 输出显示
     *
     * @return void
     */
    public function show()
    {
        header("Content-type: image/png");
        imagepng($this->image);
        imagedestroy($this->image);
    }

    /**
     * 获取验证码文本
     *
     * @return string
     */
    public function getCode()
    {
        return $this->text;
    }

}
