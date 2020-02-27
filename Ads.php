<?php

#
# Ads 广告位
# http://www.koalilab.top
#
# (c) ScientistPun
#

class Ads
{
    # ~

    const version = '1.0.0';

    # ~

    private static $instance = null;
    private $options = ['type'=>0, 'content'=>'', 'width'=>100, 'height'=>100];
    private $str = '';
    private $error = '';

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, is_array($options) ? $options : []);
    }

    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 展示
     */
    public function show() {
        if (!$this->parse()) return $this->error;
        return $this->str;
    }

    /**
     * 格式化输出
     */
    protected function parse() {
        $content = $this->options['content'];
        $w = $this->options['width'];
        $h = $this->options['height'];
        $updatetime = $this->options['updatetime'];
        $str = '';
        switch ($this->options['type']) {
            case 0:
                $content = trim($content);
                $contentInfo = explode("\r\n", $content);
                if (count($contentInfo) < 2) {
                    $this->error = '内容错误';
                    return false;
                }
                $str = "<div><a target='_blank' href='{$contentInfo[0]}' style='display:block;'>";
                // 如果有注释内容
                if (count($contentInfo) == 2) {
                    $contentInfo[2] = $contentInfo[0];
                }

                // 如果有宽高
                $str .= "<img src='{$contentInfo[1]}?t={$updatetime}' alt='{$contentInfo[2]}' style='display: block; ";
                $str .= $w ? "width: {$w}px;":"width: 100%;";
                $str .= $h ? "height: {$h}px;":"height: 100%;";
                $str .= "'/></a></div>";
                break;
            case 1:
                $str = $content;
            break;
            case 2:
                $options = Helper::options();
                $pluginUrl = $options->pluginUrl . '/AdsPlugin';
                $idname = 'carousel-'.mt_rand(1000, 9999);
                $str .= "<div class='layui-carousel' id='{$idname}' style='";
                $str .= $w ? "width: {$w}px;" : "width: 100%;";
                $str .= $h ? "height: {$h}px;" : "height: 100%;";
                $str .= "'><div carousel-item=''>";

                $content = json_decode($content, true);
                foreach ($content as $con) {
                    if (!isset($con['alt'])) $con['alt'] = $con['href'];
                    $str .= "<div><a target='_blank' href='{$con['href']}'><img src='{$con['src']}?t={$updatetime}' alt='{$con['alt']}' style='";
                    $str .= $w ? "width: {$w}px;" : "width: 100%;";
                    $str .= $h ? "height: {$h}px;" : "height: 100%;";
                    $str .= "'></a></div>";
                }
                $str .= " </div> </div>";

                $str .= "<script src='{$pluginUrl}/style/layui.all.js'></script>";

                $width = $w ? "{$w}px;" : "100%;";
                $height = $h ? "{$h}px;" : "100%;";
                $str .= "<script>layui.use(['carousel'], function(){
                    var carousel = layui.carousel;
                    //图片轮播
                    carousel.render({
                        elem: '#{$idname}'
                        ,width: '{$width}'
                        ,height: '{$height}'
                        ,interval: 8000
                        ,indicator: 'none'
                        ,arrow: 'hover' //始终显示箭头
                    });
                
                    //事件
                    carousel.on('change(id)', function(res){
                        console.log(res)
                    });
                });</script>";

                break;
            default:
                $this->error = '错误展示方式';
                return false;
        }

        $this->str = $str;
        return true;
    }

    private function getError() {
        return $this->error;
    }


}