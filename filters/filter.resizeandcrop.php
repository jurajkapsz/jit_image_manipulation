<?php

Class FilterResizeAndCrop extends JIT\ImageFilter {

    public $mode = 2;

    public static function about() {
        return array(
            'name' => 'JIT Filter: Resize and Crop'
        );
    }

    const TOP_LEFT = 1;
    const TOP_MIDDLE = 2;
    const TOP_RIGHT = 3;
    const MIDDLE_LEFT = 4;
    const CENTER = 5;
    const MIDDLE_RIGHT = 6;
    const BOTTOM_LEFT = 7;
    const BOTTOM_MIDDLE = 8;
    const BOTTOM_RIGHT = 9;

    public static function parseParameters($parameter_string)
    {
        $param = array();

        if(preg_match_all('/^(2|3)\/([0-9]+)\/([0-9]+)\/([1-9])\/([a-fA-F0-9]{3,6}\/)?(?:(0|1)\/)?(.+)$/i', $parameter_string, $matches, PREG_SET_ORDER)){
            $param['mode'] = (int)$matches[0][1];
            $param['settings']['width'] = (int)$matches[0][2];
            $param['settings']['height'] = (int)$matches[0][3];
            $param['settings']['position'] = (int)$matches[0][4];
            $param['settings']['background'] = trim($matches[0][5],'/');
            $param['settings']['external'] = (bool)$matches[0][6];
            $param['image_path'] = $matches[0][7];
        }

        return !empty($param) ? $param : false;
    }

    public static function run(\Image $res, $settings) { //$width, $height, $anchor=self::TOP_LEFT, $background_fill = null){
        $src_w = $res->Meta()->width;
        $src_h = $res->Meta()->height;

        if($settings['settings']['height'] == 0) {
            $ratio = ($src_h / $src_w);
            $dst_h = round($settings['meta']['width'] * $ratio);
        }

        else if($settings['settings']['width'] == 0) {
            $ratio = ($src_w / $src_h);
            $dst_w = round($settings['meta']['height'] * $ratio);
        }

        $src_r = ($src_w / $src_h);
        $dst_r = ($settings['meta']['width'] / $settings['meta']['height']);

        if($src_r < $dst_r) {
            $width = $settings['meta']['width'];
            $height = null;
        }
        else {
            $width = null;
            $height = $settings['meta']['height'];
        }

        $resource = $res->Resource();

        $dst_w = Image::width($resource);
        $dst_h = Image::height($resource);

        if(!empty($width) && !empty($height)) {
            $dst_w = $width;
            $dst_h = $height;
        }
        else if(empty($height)) {
            $ratio = ($dst_h / $dst_w);
            $dst_w = $width;
            $dst_h = round($dst_w * $ratio);
        }
        else if(empty($width)) {
            $ratio = ($dst_w / $dst_h);
            $dst_h = $height;
            $dst_w = round($dst_h * $ratio);
        }

        $tmp = imagecreatetruecolor($dst_w, $dst_h);

        self::__fill($resource, $tmp, $background_fill);

        list($src_x, $src_y, $dst_x, $dst_y) = self::__calculateDestSrcXY($dst_w, $dst_h, Image::width($resource), Image::height($resource), Image::width($resource), Image::height($resource), $anchor);

        imagecopyresampled($tmp, $resource, $src_x, $src_y, $dst_x, $dst_y, Image::width($resource), Image::height($resource), Image::width($resource), Image::height($resource));

        if(is_resource($resource)) {
            imagedestroy($resource);
        }

        $res->setResource($tmp);

        return $res;
    }

    protected static function __calculateDestSrcXY($width, $height, $src_w, $src_h, $dst_w, $dst_h, $position=self::TOP_LEFT){

        $dst_x = $dst_y = 0;
        $src_x = $src_y = 0;

        if($width < $src_w){
            $mx = array(
                0,
                ceil(($src_w * 0.5) - ($width * 0.5)),
                $src_x = $src_w - $width
            );
        }

        else{
            $mx = array(
                0,
                ceil(($width * 0.5) - ($src_w * 0.5)),
                $src_x = $width - $src_w
            );
        }

        if($height < $src_h){
            $my = array(
                0,
                ceil(($src_h * 0.5) - ($height * 0.5)),
                $src_y = $src_h - $height
            );
        }

        else{

            $my = array(
                0,
                ceil(($height * 0.5) - ($src_h * 0.5)),
                $src_y = $height - $src_h
            );
        }

        switch($position){

            case 1:
                break;

            case 2:
                $src_x = 1;
                break;

            case 3:
                $src_x = 2;
                break;

            case 4:
                $src_y = 1;
                break;

            case 5:
                $src_x = 1;
                $src_y = 1;
                break;

            case 6:
                $src_x = 2;
                $src_y = 1;
                break;

            case 7:
                $src_y = 2;
                break;

            case 8:
                $src_x = 1;
                $src_y = 2;
                break;

            case 9:
                $src_x = 2;
                $src_y = 2;
                break;

        }

        $a = ($width >= $dst_w ? $mx[$src_x] : 0);
        $b = ($height >= $dst_h ? $my[$src_y] : 0);
        $c = ($width < $dst_w ? $mx[$src_x] : 0);
        $d = ($height < $dst_h ? $my[$src_y] : 0);

        return array($a, $b, $c, $d);
    }
}