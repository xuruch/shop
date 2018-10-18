<?php
namespace libs;

class Uploader{
    private function __construct(){}
    private function __clone(){}
    private static $_obj = null;

    public static function make(){
        if(self::$_obj === null){
            self::$_obj = new self;
        }
        return self::$_obj;
    }
   // 图片保存的一级目录
    private $_root = ROOT . 'public/uploads/';
    private $_ext = ['image/jpeg','image/jpg','image/ejpeg','image/png','image/gif','image/bmp'];
    private $_maxSize = 1024*1024*1.8;

    private $_file;  // 保存用户上传的图片信息
    private $_subDir;

    // 上传图片
    public function upload($name, $subdir){
        // 把用户图片的信息保存到属性上
        $this->_file = $_FILES[$name];
        $this->_subDir = $subdir;
        // var_dump($_FILES);

        if(!$this->_checkType()){
            die('图片类型不正确！');
        }

        if(!$this->_checkSize()){
            die('图片尺寸不正确！');
        }

        // 创建目录
        $dir = $this->_makeDir();
        // 缩略图目录
        $slt = $this->_makeThumbDir();
        // 生成唯一的名字
        $name = $this->_makeName();
        // 缩略图name
        $sltName = strstr($name,'.',-1);
        // die;
        // 移动图片
        move_uploaded_file($this->_file['tmp_name'], $this->_root.$dir.$name);
        // 生成3张缩略图
        $thumbImage = new \libs\Image;
        for($i=1;$i<4;$i++){
            $thumbImage->saveThumbImage('uploads/'.$dir.$name,$i*100,$i*80,true,'uploads/'.$slt,$i.'Thumb__'.$sltName);
        }
        // 返回二级目录开始的路径
        return $dir.$name;
    }

    // 创建缩略图目录
    private function _makeThumbDir(){
        $dir = $this->_subDir . '/' . date('Ymd')."/thumb";
        if(!is_dir($this->_root . $dir))
            mkdir($this->_root . $dir, 0777, TRUE);
        return $dir.'/';
    }

    // 创建目录
    private function _makeDir(){
        $dir = $this->_subDir . '/' . date('Ymd');
        if(!is_dir($this->_root . $dir))
            mkdir($this->_root . $dir, 0777, TRUE);

        return $dir.'/';
    }

    private function _makeName(){
        $name = md5( time() . rand(1,9999) );
        $ext = strrchr($this->_file['name'], '.');
        return $name . $ext;
    }

    private function _checkType(){
        return in_array($this->_file['type'], $this->_ext);
    }

    private function _checkSize(){
        return $this->_file['size'] < $this->_maxSize;
    }
}