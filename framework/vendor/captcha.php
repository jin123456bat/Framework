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
	
	private $_config_name;
	
	private $_backColor = 0xFFFFFF;
	
	private $_foreColor = 0x2040A0;
	
	private $_transparent = false;
	
	private $_fontFile = ROOT.'/framework/assets/fonts/SpicyRice.ttf';
	
	private $_offset = -2;
	
	private $_padding = 2;
	
	private $_session_name = '__captcha';
	
	private $_expire = 60*10;
	
	function __construct($config = NULL)
	{
		parent::__construct();
		$this->_config_name = $config;
		$this->_config = $this->getConfig('captcha',$config);
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
		for($i = ord('0');$i <= ord('9');$i++)
		{
			if (in_array(chr($i), $skip_content))
			{
				continue;
			}
			$content[] = chr($i);
		}
		
		for($i = ord('a');$i <= ord('z');$i++)
		{
			if (in_array(chr($i), $skip_content))
			{
				continue;
			}
			$content[] = chr($i);
		}
		
		for($i = ord('A');$i <= ord('Z');$i++)
		{
			if (in_array(chr($i), $skip_content))
			{
				continue;
			}
			$content[] = chr($i);
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
		
		$captcha = session::get($this->_session_name);
		
		if (!empty($this->_config_name) && isset($captcha[$this->_config_name]))
		{
			$captcha = $captcha[$this->_config_name];
		}
		else if (!isset($captcha[$this->_config_name]) || empty($captcha[$this->_config_name]))
		{
			$captcha = array();
		}
		
		$captcha[] = $cap;
		
		$new_captcha = array();
		if (!empty($this->_config_name))
		{
			$new_captcha[$this->_config_name] = $captcha;
		}
		else
		{
			$new_captcha = $captcha;
		}
		session::set($this->_session_name, $new_captcha);
		return true;
	}
	
	/**
	 * 验证验证码是否正确
	 * @param unknown $code
	 * @return boolean
	 */
	public function validate($code)
	{
		$captcha = session::get($this->_session_name);
		if (!empty($this->_config_name))
		{
			$captcha = $captcha[$this->_config_name];
		}
		
		foreach ($captcha as $index => $cap)
		{
			if (time() > $cap[1] + $cap[2])
			{
				unset($captcha[$index]);
				continue;
			}
			
			if (strtolower($cap[0]) === strtolower($code))
			{
				return true;
			}
		}
		
		if (!empty($this->_config_name))
		{
			session::set($this->_session_name, array($this->_config_name=>$captcha));
		}
		else
		{
			session::set($this->_session_name, $captcha);
		}
		return false;
	}
}
?>