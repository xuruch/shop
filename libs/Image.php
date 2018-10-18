<?php
namespace libs;

class Image{

    //保存图片资源
    private $imgObj;

    public function image(){
        header("Content-type:image/jpeg");
        // 定义路径
        $bigPath = "images/big.jpg";
        $thumbPath = "images/big-thumb.jpg";
    
        // 定义缩略图宽高
        $thumbWidth = 180;
        $thumbHeight = 120;
        // 获取大图片宽高
        list($imgWidth,$imgHeight) = getimagesize($bigPath);
        // 创建画布资源
        $bigImg = imagecreatefromjpeg($bigPath);
        // 缩放倍数
        $Per = 1;
        if($imgWidth > $thumbWidth){
            $Per = round($thumbWidth / $imgWidth,2);
        }
        if($imgHeight > $thumbHeight){
            $Per = round($thumbHeight / $imgHeight,2);
        }
        // 计算缩略图实际宽高
        $thumbWidth = $imgWidth * $Per;
        $thumbHeight = $imgHeight * $Per;
        // 穿件缩略图宽高
        $thumbImg = imagecreatetruecolor($thumbWidth,$thumbHeight);
    
        imagecopyresized($thumbImg, $bigImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
        // 显示保存
        imagejpeg($thumbImg);
        // imagejpeg($thumbImg,$thumbPath);
    }

    /*
    *  参数： 要获取的图片的路径
    */
    public function getImageInfo($imgPath){

        $arr = array();
        //1、长度
        $info = getimagesize($imgPath);
        $arr['width'] = $info[0];
        //2、宽度
        $arr['height'] = $info[1];
        //3、后缀              $info['mime'] = "image/gif"
        // 获取 / 位置   
        $loc = strrpos( $info['mime'],"/");
        //
        $arr['ext'] = substr($info['mime'],$loc+1);

        return $arr;
    }

    public function thumbImage($imgPath,$thumbWidth,$thumbHeight,$is_zoom){
    
        // 获取大图片宽高
        $imageInfoArr = $this->getImageInfo($imgPath);
        // 创建画布资源
        $method = "imagecreatefrom{$imageInfoArr['ext']}";
        $bigImg = $method($imgPath);

        if($is_zoom){
            // 缩放倍数
            $Per = 1;
            if($imageInfoArr['width'] > $thumbWidth){
                $Per = round($thumbWidth / $imageInfoArr['width'],2);
            }
            if($imageInfoArr['height'] > $thumbHeight){
                $Per = round($thumbHeight / $imageInfoArr['height'],2);
            }
            // 计算缩略图实际宽高
            $thumbWidth = $imageInfoArr['width'] * $Per;
            $thumbHeight = $imageInfoArr['height'] * $Per;
        }
        // 穿件缩略图宽高
        $thumbImg = imagecreatetruecolor($thumbWidth,$thumbHeight);
    
        imagecopyresized($thumbImg, $bigImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imageInfoArr['width'], $imageInfoArr['height']);

        // 把图片资源保存到类的属性中
        $this->imgObj = $thumbImg;
        $this->ext = $imageInfoArr['ext'];
    }

    public function getThumbImage($img,$maxWidth,$maxHeight,$is_zoom=true){
        // 在网页中显示缩略图
        header("Content-type:image/jpeg");
        $this->thumbImage($img,$maxWidth,$minHeight,$is_zoom);
        // ob_clean();
        // 显示缩略图
        imagejpeg($this->imgObj);
    }

    public function saveThumbImage($img,$maxWidth,$maxHeight,$is_zoom=true,$dirPath,$fileName){

        $this->thumbImage($img,$maxWidth,$maxHeight,$is_zoom);
        
        //如果图片的后缀是.jpeg 的，我改为 .jpg
        if($this->ext =='jpeg'){
            $fileName = $fileName.".jpg";
        }else {
            //拼接文件名
            $fileName = $fileName.".".$this->ext;
        }
        $method = "image{$this->ext}";
        //保存图片
        if($method($this->imgObj,$dirPath.$fileName)){
            //如果保存成功，返回文件的名称
            return $fileName;
        }else {
            return false;
        }
    }

}
