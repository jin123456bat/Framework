<?php
namespace framework\vendor;


use framework\core\base;
use framework\core\response;

/**
 * @author jin
 *
 */
class image extends file
{
	/**
	 * @var resource
	 */
	private $_image;
	
	/**
	 * 图片类型
	 * 1 IMAGETYPE_GIF
	 * 2 IMAGETYPE_JPEG
	 * 3 IMAGETYPE_PNG
	 * 4 IMAGETYPE_SWF
	 * 5 IMAGETYPE_PSD
	 * 6 IMAGETYPE_BMP
	 * 7 IMAGETYPE_TIFF_II（Intel 字节顺序）
	 * 8 IMAGETYPE_TIFF_MM（Motorola 字节顺序）
	 * 9 IMAGETYPE_JPC
	 * 10 IMAGETYPE_JP2
	 * 11 IMAGETYPE_JPX
	 * 12 IMAGETYPE_JB2
	 * 13 IMAGETYPE_SWC
	 * 14 IMAGETYPE_IFF
	 * 15 IMAGETYPE_WBMP
	 * 16 IMAGETYPE_XBM
	 * @var int
	 */
	private $_type;
	
	/**
	 * @var integer
	 */
	private $_jpeg_quality = 75;
	
	function __construct($file)
	{
		$pattern = '/data:image\/\w{3,4};base64,(?<base64>.+)/';
		if (preg_match($pattern, $file,$match))
		{
			$file = tempnam(sys_get_temp_dir(),'image_');
			file_put_contents($file, base64_decode($match['base64']));
		}
		
		parent::__construct($file);
		
		$this->_type = exif_imagetype($this->path());
		
		switch ($this->_type)
		{
			case IMAGETYPE_PNG:
				$this->_image = imagecreatefrompng($this->path());
			break;
			case IMAGETYPE_JPEG:
				$this->_image = imagecreatefromjpeg($this->path());
			break;
			case IMAGETYPE_GIF:
				$this->_image = imagecreatefromgif($this->path());
			break;
			case IMAGETYPE_WBMP:
				$this->_image = imagecreatefromwbmp($this->path());
			break;
		}
		
	}
	
	/**
	 * 获取图像类型 git|bmp|png等等
	 * @return string
	 */
	function imageType()
	{
		return image_type_to_extension($this->_type,false);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\vendor\file::mimeType()
	 */
	function mimeType($magic = null)
	{
		return image_type_to_mime_type($this->_type);
	}

	/**
	 * 获取图片的宽度
	 * 像素
	 * @return int
	 */
	function width()
	{
		return imagesx($this->_image);
	}

	/**
	 * 获取图片的高度
	 * 像素
	 */
	function height()
	{
		return imagesy($this->_image);
	}

	/**
	 * 更改图片的高度或者宽度
	 * 支持格式
	 * jpg、png、bmp、gif、webp、tiff
	 * 
	 * @param int $height 高度 默认为0 代表不压缩
	 * 	当0<$height<1的时候为按比例压缩高度
	 * 	当$height大于原图的高度的时候为放大
	 * @param int $width 宽度  默认为0代表不压缩
	 * 	当0<$width<1的时候为按比例压缩宽度
	 * 	当$width大于原图的高度的时候为放大
	 * @param bool $scale 是否保持高宽比
	 */
	function resize($height = 0, $width = 0,$scale = true)
	{
		//计算宽高
		if ($height<=1 && $height>0)
		{
			$height = $this->height() * $height;
		}
		else if ($height <= 0)
		{
			$height = $this->height();
		}
		
		if ($width<=1 && $width>0)
		{
			$width = $this->width() * $width;
		}
		else if ($width <= 0)
		{
			$width = $this->width();
		}
		
		if ($scale)
		{
			if ($width < $height)
			{
				$width = $height / $this->height() * $this->width();
			}
			else
			{
				//高度随着宽度 自适应
				$height = $width / $this->width() * $this->height();
			}
		}
		
		$image = imagecreatetruecolor($width,$height);
		
		if ( ($this->_type == IMAGETYPE_GIF) || ($this->_type== IMAGETYPE_PNG) ) {
			$trnprt_indx = imagecolortransparent($this->_image);
			if ($trnprt_indx >= 0) {
				$trnprt_color  = imagecolorsforindex($this->_image, $trnprt_indx);
				$trnprt_indx  = imagecolorallocate($image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($image, 0, 0, $trnprt_indx);
				imagecolortransparent($image, $trnprt_indx);
			}
			elseif ($this->_type == IMAGETYPE_PNG) {
				imagealphablending($image, false);
				$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
				imagefill($image, 0, 0, $color);
				imagesavealpha($image, true);
			}
		}
		
		imagecopyresampled($image,$this->_image,0,0,0,0,$width,$height,$this->width(),$this->height());
		
		imagedestroy($this->_image);
		$this->_image = $image;
		
		return $this;
		//imagepng($image);
	}
	
	/**
	 * 内切圆
	 * 原点为$height/2,$width/2
	 * @param int $radius 内切圆的半径  不能大于min(width,height)
	 */
	function circle($radius = 100)
	{
		$max_radius = min(array($this->width(),$this->height()));
		if ($radius<1)
		{
			$radius = 100;
		}
		if ($radius > $max_radius/2)
		{
			$radius = $max_radius/2;
		}
		
		$src_image = imagecreatetruecolor($this->width(), $this->height());
		
		imagealphablending($src_image,false);
		$transparent = imagecolorallocatealpha($src_image, 0, 0, 0, 127); 
		
		for($x=0;$x<$this->width();$x++)
			for($y=0;$y<$this->height();$y++){
				$c = imagecolorat($this->_image,$x,$y);
				$_x = $x - $this->width()/2;
				$_y = $y - $this->height()/2;
				
				if((pow($_x,2) + pow($_y,2)) < pow($radius,2)){
					imagesetpixel($src_image,$x,$y,$c);
				}else{
					imagesetpixel($src_image,$x,$y,$transparent);
				}
		}
		imagesavealpha($src_image, true);
		
		//将image居中裁剪
		$length = 2* $radius;
		$dst_image = imagecreatetruecolor($length, $length);
		imagealphablending($dst_image,false);
		$src_x= ($this->width() - $length )/2;
		$src_y= ($this->height() - $length)/2;
		imagecopy($dst_image, $src_image, 0, 0, $src_x, $src_y, $length, $length);
		imagesavealpha($dst_image, true);
		$this->_image = $dst_image;
		
		return $this;
	}
	
	/**
	 * 获取图片的exif信息
	 * @return array|false 当没有exif信息的时候返回false
	 */
	function exif()
	{
		return @exif_read_data($this->path(),NULL,true,false);
	}
	
	/**
	 * 质量转换
	 * 只对jpg/webp图片有效
	 * @see https://help.aliyun.com/document_detail/44705.html?spm=5176.doc55811.6.964.QosgC5
	 * @param int $q [1,100] 绝对质量
	 */
	function quality($q)
	{
		if ($q<1)
		{
			$q = 75;
		}
		if ($q>100)
		{
			$q = 100;
		}
		$this->_jpeg_quality = $q;
		
		return $this;
	}

	/**
	 * 图片增加水印
	 * 
	 * @param string $string
	 *        文字或者图片路径
	 */
	function water($string, $pos_x, $pos_y)
	{
		if ($string instanceof file && $string->isImage())
		{
			//图片水印
			$file = $string->path();
		}
		else if ($string instanceof file)
		{
			//文字水印
			$string = $string->content();
		}
		else if (is_string($string) && is_file($string) && file::isImage($string))
		{
			//图片水印
			$file = $string;
		}
		else if (is_string($string) && is_file($string))
		{
			//文字水印
			$string = file_get_contents($string);
		}
		
		
		if (!empty($file))
		{
			
		}
		else if (!empty($string))
		{
			
		}
	}
	
	/**
	 * 旋转
	 * @see https://help.aliyun.com/document_detail/44690.html?spm=5176.doc44691.6.955.WiHIRn
	 * @param int $r
	 */
	function rotate($r)
	{
		$this->_image = imagerotate($this->_image,$r,2130706432);
		return $this;
	}
	
	/**
	 * 根据数组创建滤波值
	 * @param array $array
	 * @return array|array|unknown
	 */
	public static function createWave(array $array)
	{
		if(count($array)%2 != 1)
		{
			return array();
		}
		
		foreach ($array as $value)
		{
			if (count($value)%2 != 1)
			{
				return array();
			}
			
			if (count($value) != count($array))
			{
				return array();
			}
		}
		
		$temp = array();
		foreach ($array as $key => $value)
		{
			foreach($value as $k => $v)
			{
				$temp[$key - floor(count($value)/2)][$k - floor(count($value)/2)] = $v;
			}
		}
		
		return $temp;
	}
	
	/**
	 * 神奇的滤波函数
	 * 这个有点问题
	 */
	function filter($wave)
	{
		$wave = self::createWave($wave);
		
		$image = imagecreatetruecolor($this->width(), $this->height());
		
		for($x=0;$x<$this->width();$x++)
		{
			for($y=0;$y<$this->height();$y++)
			{
				$color = 0;
				$c = imagecolorat($this->_image,$x,$y);
				$result = true;
				
				foreach ($wave as $key => $value)
				{
					$b = $key + $y;
					
					foreach ($value as $k => $v)
					{
						$a = $k + $x;
						if ($a>=0 && $a<$this->width() && $b>=0 && $b<$this->height())
						{
							$color += imagecolorat($this->_image,$a,$b)* $v;
						}
						else
						{
							$result = false;
						}
					}
				}
				
				if ($result)
				{
					imagesetpixel($image,$x,$y,$color);
				}
				else
				{
					imagesetpixel($image, $x, $y, $c);
				}
			}
		}
		
		$this->_image = $image;
		
		return $this;
	}
	
	/**
	 * 高斯模糊效果
	 * @param int $value 模糊程度
	 */
	function blur($value)
	{
		$value = abs($value);
		while ($value--)
		{
			imagefilter($this->_image, IMG_FILTER_GAUSSIAN_BLUR);
		}
		
		return $this;
	}
	
	/**
	 * 亮度调节
	 * @param int $value
	 * 0 表示原图亮度，小于 0 表示低于原图亮度，大于 0 表示高于原图亮度。
	 */
	function bright($value)
	{
		imagefilter($this->_image, IMG_FILTER_BRIGHTNESS,$value);
		
		return $this;
	}
	
	
	/**
	 * 圆角矩形
	 * 有锯齿
	 * @see https://help.aliyun.com/document_detail/44694.html?spm=5176.doc44696.6.952.O1o5yv
	 * @param int $radius
	 */
	function roundedCorners($radius)
	{
		$radius= abs($radius);
		
		if (empty($radius))
		{
			return $this;
		}
		$src_image = imagecreatetruecolor($this->width(), $this->height());
		
		imagealphablending($src_image,false);
		$transparent = imagecolorallocatealpha($src_image, 0, 0, 0, 127);
		
		for($x=0;$x<$this->width();$x++)
			for($y=0;$y<$this->height();$y++){
				$c = imagecolorat($this->_image,$x,$y);
				
				$_x_left_top = $x - $radius;
				$_y_left_top = $y - $radius;
				
				$_x_right_top = $x - $this->width() + $radius;
				$_y_right_top = $y - $radius;
				
				$_x_left_buttom = $x - $radius;
				$_y_left_buttom = $y - $this->height() + $radius;
				
				$_x_right_buttom = $x - $this->width() + $radius;
				$_y_right_buttom = $y - $this->height() + $radius;
				
				if((pow($_x_left_top,2) + pow($_y_left_top,2)) > pow($radius,2) && $x < $radius && $y < $radius)
				{
					imagesetpixel($src_image,$x,$y,$transparent);
				}
				else if ((pow($_x_right_top,2) + pow($_y_right_top,2)) > pow($radius,2) && $x > ($this->width() - $radius) && $y < $radius)
				{
					imagesetpixel($src_image,$x,$y,$transparent);
				}
				else if ((pow($_x_left_buttom,2) + pow($_y_left_buttom,2)) > pow($radius,2) && $x < $radius && $y > ($this->height() - $radius))
				{
					imagesetpixel($src_image,$x,$y,$transparent);
				}
				else if ((pow($_x_right_buttom,2) + pow($_y_right_buttom,2)) > pow($radius,2) && $x > ($this->width() - $radius) && $y > ($this->height() - $radius))
				{
					imagesetpixel($src_image,$x,$y,$transparent);
				}
				else
				{
					imagesetpixel($src_image,$x,$y,$c);
				}
		}
		imagesavealpha($src_image, true);
		
		$this->_image = $src_image;
		return $this;
	}
	
	/**
	 * 对比度
	 * @param int $value
	 * 对比度调整。0 表示原图对比度，小于 0 表示低于原图对比度，大于 0 表示高于原图对比度。
	 */
	function contrast($value)
	{
		imagefilter($this->_image, IMG_FILTER_CONTRAST,$value);
		return $this;
	}
	
	/**
	 * 锐化
	 * @see https://help.aliyun.com/document_detail/44700.html?spm=5176.doc44699.6.960.LxI1Fs
	 * @param unknown $value 默认为100 [50, 399] 
	 * 取值为锐化参数，参数越大，越清晰
	 */
	function sharpen($value = 100)
	{
		
	}
	
	/**
	 * 格式转换
	 * 支持jpg, png, bmp, webp，gif
	 * @see https://help.aliyun.com/document_detail/44703.html?spm=5176.doc44704.6.962.jPcTHw
	 * @param string $format
	 */
	function format($format)
	{
		$data = array(
			IMAGETYPE_GIF => 'gif',
			IMAGETYPE_JPEG => 'jpeg',
			IMAGETYPE_PNG => 'png',
			IMAGETYPE_SWF => 'swf',
			IMAGETYPE_PSD => 'psd',
			IMAGETYPE_BMP => 'bmp',
			IMAGETYPE_TIFF_II => 'tiff',
			IMAGETYPE_TIFF_MM => 'tiff',
			IMAGETYPE_JPC => 'jpc',
			IMAGETYPE_JP2 => 'jp2',
			IMAGETYPE_JPX => 'jpf',
			IMAGETYPE_JB2 => 'jb2',
			IMAGETYPE_SWC => 'swc',
			IMAGETYPE_IFF => 'aiff',
			IMAGETYPE_WBMP => 'wbmp',
			IMAGETYPE_XBM => 'xbm',
		);
		$result = array_search(strtolower(trim($format)), $data,true);
		if ($result === NULL || $result === false)
		{
			if(in_array($format, array_keys($data)))
			{
				$this->_type = $format;
			}
		}
		else
		{
			$this->_type = $result;
		}
		
		return $this;
	}
	
	/**
	 * 渐进显示
	 * 只对jpg图片有效
	 * jpg图片有2种显示方式
	 * 1、自上而下的扫描式
	 * 2、先模糊后逐渐清晰（在网络环境比较差时明显）
	 * @ses https://help.aliyun.com/document_detail/44704.html?spm=5176.doc44703.6.963.snW3k7
	 * @param boolean $value
	 * 1 表示保存成渐进显示的 jpg格式
	 * 0 表示保存成普通的 jpg 格式
	 */
	function interlace($value = true)
	{
		
	}

	/**
	 * 裁剪
	 * 原点为左下角
	 * @param unknown $width 裁剪的宽度
	 * @param unknown $height 裁剪的高度
	 * @param unknown $pos_x 裁剪的起点横坐标
	 * @param unknown $pos_y 裁剪的起点纵坐标
	 */
	function crop($width, $height, $pos_x, $pos_y)
	{
		
	}
	
	/**
	 * 将图片保存到一个文件当中
	 * @param unknown $file
	 */
	function save($file)
	{
		switch ($this->_type)
		{
			case IMAGETYPE_PNG:
				imagepng($this->_image,$file);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($this->_image,$file,$this->_jpeg_quality);
				break;
		}
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	function base64()
	{
		$temp_file = tempnam(sys_get_temp_dir(),'image_');
		$this->save($temp_file);
		return 'data:'.$this->mimeType().';base64,'.base64_encode(file_get_contents($temp_file));
	}
	
	/**
	 * 转化为一个response
	 */
	function output()
	{
		header('Content-Type:'.$this->mimeType());
		switch ($this->_type)
		{
			case IMAGETYPE_PNG:
				imagepng($this->_image);
			break;
			case IMAGETYPE_JPEG:
				imagejpeg($this->_image,NULL,$this->_jpeg_quality);
			break;
		}
		return $this;
	}
}
