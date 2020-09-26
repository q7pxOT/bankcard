<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 图像操作类库
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 */
class Image {

    /**
     * 生成UUID 单机使用
     * @access public
     * @return string
     */
     static public function uuid() {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
               .substr($charid, 0, 8).$hyphen
               .substr($charid, 8, 4).$hyphen
               .substr($charid,12, 4).$hyphen
               .substr($charid,16, 4).$hyphen
               .substr($charid,20,12)
               .chr(125);// "}"
        return $uuid;
    }

    /**
     * 生成Guid主键
     * @return Boolean
     */
    static public function keyGen() {
        return str_replace('-','',substr(String::uuid(),1,-1));
    }

    /**
     * 取得图像信息
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
    static function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 为图片添加水印
     * @static public
     * @param string $source 原文件名
     * @param string $water  水印图片
     * @param string $$savename  添加水印后的图片名
     * @param string $alpha  水印的透明度
     * @return void
     */
    static public function water($source, $water, $savename=null, $alpha=80,$offsetX=0,$offsetY=0,$text='') {
        //检查文件是否存在
        if (!file_exists($source) || !file_exists($water))
            return false;

        //图片信息
        $sInfo = self::getImageInfo($source);
        $wInfo = self::getImageInfo($water);

        //如果图片小于水印图片，不生成图片
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height'])
            return false;

        //建立图像
        $sCreateFun = "imagecreatefrom" . $sInfo['type'];
        $sImage = $sCreateFun($source);
        $wCreateFun = "imagecreatefrom" . $wInfo['type'];
        $wImage = $wCreateFun($water);

        //设定图像的混色模式
        imagealphablending($wImage, true);

        //图像位置,默认为右下角右对齐
        $posY = $sInfo["height"] - $wInfo["height"]-$offsetX;
        $posX = $sInfo["width"] - $wInfo["width"]-$offsetY;

        //生成混合图像imagecopymerge
        self::imagecopymerge_alpha($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $alpha);
		
		if($text){
			$font = '/www/img.htbb.tv/public/font/baby.ttf';
			$white = ImageColorAllocate($sImage, 255,255,255);
			$fontSize=16;
			$text_cnt=utf8_strlen($text);
			if($text_cnt>=12){
				$fontSize=14;
			}
			$fontBox = imagettfbbox($fontSize, 0, $font, $text);//文字水平居中实质
			$width=imagesx($sImage);
			$x=($sInfo['width']-$fontBox[2])/2-2;
			$y=$sInfo['height']-$wInfo['height']-$fontSize-10;
			imagettftext($sImage , $fontSize , 0 , $x ,$y , $white , $font ,$text);
		}
		
        //输出图像
        $ImageFun = 'Image' . $sInfo['type'];
        //如果没有给出保存文件名，默认为原图像名
        if (!$savename) {
            $savename = $source;
            @unlink($source);
        }
        //保存图像
		if($imageFun=='imagejpeg'){
			$ImageFun($sImage, $savename,100);
		}else{
			$ImageFun($sImage, $savename);
		}
        imagedestroy($sImage);
    }

	static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);
              
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        //$opacity = 100 - $opacity;
              
        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }
	
    function showImg($imgFile, $text='', $x='10', $y='10', $alpha='50') {
        //获取图像文件信息
        //2007/6/26 增加图片水印输出，$text为图片的完整路径即可
        $info = Image::getImageInfo($imgFile);
        if ($info !== false) {
            $createFun = str_replace('/', 'createfrom', $info['mime']);
            $im = $createFun($imgFile);
            if ($im) {
                $ImageFun = str_replace('/', '', $info['mime']);
                //水印开始
                if (!empty($text)) {
                    $tc = imagecolorallocate($im, 0, 0, 0);
                    if (is_file($text) && file_exists($text)) {//判断$text是否是图片路径
                        // 取得水印信息
                        $textInfo = Image::getImageInfo($text);
                        $createFun2 = str_replace('/', 'createfrom', $textInfo['mime']);
                        $waterMark = $createFun2($text);
                        //$waterMark=imagecolorallocatealpha($text,255,255,0,50);
                        $imgW = $info["width"];
                        $imgH = $info["width"] * $textInfo["height"] / $textInfo["width"];
                        //$y	=	($info["height"]-$textInfo["height"])/2;
                        //设置水印的显示位置和透明度支持各种图片格式
                        imagecopymerge($im, $waterMark, $x, $y, 0, 0, $textInfo['width'], $textInfo['height'], $alpha);
                    } else {
                        imagestring($im, 80, $x, $y, $text, $tc);
                    }
                    //ImageDestroy($tc);
                }
                //水印结束
                if ($info['type'] == 'png' || $info['type'] == 'gif') {
                    imagealphablending($im, FALSE); //取消默认的混色模式
                    imagesavealpha($im, TRUE); //设定保存完整的 alpha 通道信息
                }
                Header("Content-type: " . $info['mime']);
                $ImageFun($im);
                @ImageDestroy($im);
                return;
            }

            //保存图像
            $ImageFun($sImage, $savename);
            imagedestroy($sImage);
            //获取或者创建图像文件失败则生成空白PNG图片
            $im = imagecreatetruecolor(80, 30);
            $bgc = imagecolorallocate($im, 255, 255, 255);
            $tc = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
            imagestring($im, 4, 5, 5, "no pic", $tc);
            Image::output($im);
            return;
        }
    }

    /**
     * 生成缩略图
     * @static
     * @access public
     * @param string $image  原图
     * @param string $type 图像格式
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    static function thumb($image, $thumbname, $type='', $maxWidth=200, $maxHeight=50, $interlace=true,$fixed=false) {
        // 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int) ($srcWidth * $scale);
                $height = (int) ($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if(!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);
              //png和gif的透明处理 by luofei614
            if('png'==$type){
                imagealphablending($thumbImg, false);//取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
            }elseif('gif'==$type){
                $trnprt_indx = imagecolortransparent($srcImg);
                 if ($trnprt_indx >= 0) {
                        //its transparent
                       $trnprt_color = imagecolorsforindex($srcImg , $trnprt_indx);
                       $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                       imagefill($thumbImg, 0, 0, $trnprt_indx);
                       imagecolortransparent($thumbImg, $trnprt_indx);
              }
            }
			
			if($fixed){
				
				//创建个   固定的缩略图盒子
				$thumbImg = imagecreatetruecolor($maxWidth,$maxHeight);  
				$padColor = imagecolorallocate($thumbImg,255,255,255);//盒子 背景色    
				imagefilledrectangle($thumbImg,0,0,$maxWidth,$maxHeight,$padColor);  //盒子填充box
				
                //$imgWidth == $srcWidth   $srcHeight  
                //$width = $maxWidth    $height = $maxHeight  
                if($srcWidth >= $srcHeight) {  
                    $thumbWidth  = $maxWidth;  
                    $thumbHeight = ($maxWidth / $srcWidth) * $srcHeight;  
                }  else {  
                    $thumbWidth  = ($maxHeight / $srcHeight) * $srcWidth;  
                    $thumbHeight = $maxHeight;  
                }
                if(function_exists('ImageCopyResampled')){ 
					//生成固定比例的缩略图  
                    imagecopyresampled($thumbImg, $srcImg, ($maxWidth-$thumbWidth)/2, ($maxHeight-$thumbHeight)/2, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
                }else{  
                    imagecopyresized($thumbImg, $srcImg, ($maxWidth-$thumbWidth)/2, ($maxHeight-$thumbHeight)/2, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);  
                }
			}else{
				
				// 复制图片
				if (function_exists("ImageCopyResampled"))
					imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
				else
					imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
				
			}

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
			if($imageFun=='imagejpeg'){
				 $imageFun($thumbImg, $thumbname,100);
			}else{
				 $imageFun($thumbImg, $thumbname);
			}
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }

    static function output($im, $type='png', $filename='') {
        header("Content-type: image/" . $type);
        $ImageFun = 'image' . $type;
        if (empty($filename)) {
            $ImageFun($im);
        } else {
            $ImageFun($im, $filename);
        }
        imagedestroy($im);
    }

}