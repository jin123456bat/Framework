<?php
namespace framework\vendor;
use framework\core\response;
use framework\core\session;

class captcha extends response
{
	/**
	 * 验证码的配置
	 * @var unknown
	 */
	private $_config;
	
	/**
	 * 背景颜色
	 * @var unknown
	 */
	private $_backColor = 0xFFFFFF;
	
	/**
	 * 字体颜色
	 * @var unknown
	 */
	private $_foreColor = 0x2040A0;
	
	/**
	 * 背景是否透明
	 * @var string
	 */
	private $_transparent = false;
	
	/**
	 * 验证码字体文件
	 * @var unknown
	 */
	private $_fontFile = SYSTEM_ROOT.'/assets/fonts/SpicyRice.ttf';
	
	private $_offset = -2;
	
	private $_padding = 2;
	
	/**
	 * 验证码存储在session中的名称
	 * @var string
	 */
	private static $_session_name = '__captcha';
	
	/**
	 * 验证码有效期
	 * @var unknown
	 */
	private $_expire = 60*10;
	
	function __construct()
	{
		parent::__construct();
		$this->_config = self::getConfig('captcha');
	}
	
	function initlize()
	{
		parent::initlize();
		$this->setHeader('Content-Type','image/png');
	}
	
	/**
	 * 获取验证码内容
	 */
	private function getString()
	{
		//默认 验证码长度为4个
		$length = 4;
		if (isset($this->_config['length']))
		{
			$length = $this->_config['length'];
		}
		
		//验证码中不生成这些字符
		$skip_content = array(
			'1',
			'i',
			'l',
			'0',
			'o',
			'O',
			'I',
			'L'
		);
		$content = array();
		
		$types = $this->_config['type'];
		$types = explode('|',$types);
		if (in_array('number', $types))
		{
			for($i = ord('0');$i <= ord('9');$i++)
			{
				if (in_array(chr($i), $skip_content))
				{
					continue;
				}
				$content[] = chr($i);
			}
		}
		if (in_array('word', $types))
		{
			for($i = ord('a');$i <= ord('z');$i++)
			{
				if (in_array(chr($i), $skip_content))
				{
					continue;
				}
				$content[] = chr($i);
			}
		}
		
		if (in_array('word', $types))
		{
			for($i = ord('A');$i <= ord('Z');$i++)
			{
				if (in_array(chr($i), $skip_content))
				{
					continue;
				}
				$content[] = chr($i);
			}
		}
		
		if ($length > count($content))
		{
			$length = 4;
		}
		
		$string = '';
		$keys = array_rand($content,$length);
		foreach ($keys as $k)
		{
			$string .= $content[$k];
		}
		
		return $string;
	}
	
	/**
	 * 图像验证码
	 */
	function getBody()
	{
		$string = $this->getString();
		
		$width = isset($this->_config['width'])?$this->_config['width']:400;
		$height = isset($this->_config['height'])?$this->_config['height']:200;;
		
		$image = imagecreatetruecolor($width,$height);
		
		$backColor = imagecolorallocate(
			$image,
			(int) ($this->_backColor % 0x1000000 / 0x10000),
			(int) ($this->_backColor % 0x10000 / 0x100),
			$this->_backColor % 0x100
			);
		imagefilledrectangle($image, 0, 0, $width, $height, $backColor);
		imagecolordeallocate($image, $backColor);
		if ($this->_transparent) {
			imagecolortransparent($image, $backColor);
		}
		
		$foreColor = imagecolorallocate(
			$image,
			(int) ($this->_foreColor % 0x1000000 / 0x10000),
			(int) ($this->_foreColor % 0x10000 / 0x100),
			$this->_foreColor % 0x100
			);
		
		$length = strlen($string);
		$box = imagettfbbox(30, 0, $this->_fontFile, $string);
		$w = $box[4] - $box[0] + $this->_offset * ($length - 1);
		$h = $box[1] - $box[5];
		$scale = min(($width - $this->_padding * 2) / $w, ($height - $this->_padding * 2) / $h);
		$x = 10;
		$y = round($height * 27 / 40);
		for ($i = 0; $i < $length; ++$i) {
			$fontSize = (int) (rand(26, 32) * $scale * 0.8);
			$angle = rand(-10, 10);
			$letter = $string[$i];
			$box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $this->_fontFile, $letter);
			$x = $box[2] + $this->_offset;
		}
		
		//画干扰点
		for($i=0;$i<10;$i++){
			//设置随机颜色
			$randColor=imagecolorallocate($image,rand(0,255),rand(0,255),rand(0,255));
			//画点
			imagesetpixel($image,rand(1,$width-2),rand(1,$height-2),$randColor);
		}
		//画干扰线
		for($i=0;$i<5;$i++){
			//设置随机颜色
			$randColor=imagecolorallocate($image,rand(0,200),rand(0,200),rand(0,200));
			//画线
			imageline($image,rand(1,$width-2),rand(1,$height-2),rand(1,$height-2),rand(1,$width-2),$randColor);
		}
		
		imagecolordeallocate($image, $foreColor);
		
		$this->storeCode($string);
		
		ob_start();
		imagepng($image);
		imagedestroy($image);
		return ob_get_clean();
	}
	
	private function storeCode($code)
	{
		//0是内容  1是有效期  2是生成时间
		$cap = array($code,$this->_expire,time());
		
		$captcha = session::get(self::$_session_name);
		
		//假如之前没有使用过验证码
		if (empty($captcha))
		{
			$captcha = array();
		}
		
		
		//删除过期的验证码信息
		foreach ($captcha as $index => $code_info)
		{
			if ($code_info[1] + $code_info[2] < time())
			{
				unset($captcha[$index]);
			}
		}
		
		$captcha[] = $cap;
		
		session::set(self::$_session_name, $captcha);
		return true;
	}
	
	/**
	 * 验证验证码是否正确
	 * @param unknown $code
	 * @return boolean
	 */
	public static function validate($code)
	{
		$captcha = session::get(self::$_session_name);
		
		foreach ($captcha as $index => $cap)
		{
			//删除超时的验证码
			if (time() > $cap[1] + $cap[2])
			{
				unset($captcha[$index]);
				continue;
			}
			
			if (strtolower($cap[0]) === strtolower($code))
			{
				unset($captcha[$index]);
				return true;
			}
		}
		session::set(self::$_session_name, $captcha);
		return false;
	}
}
?>