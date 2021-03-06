<?php
namespace framework\vendor;

class encryption
{

	/**
	 * 获取一个唯一的ID，长度为prefix的长度+32位
	 * 
	 * @param string $prefix        
	 * @return string
	 */
	public static function unique_id($prefix = '')
	{
		mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
		return $prefix . md5(uniqid(mt_rand(), true));
	}
	
	/**
	 * guid
	 * @return string
	 */
	public static function guid()
	{
		mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45);
		$uuid = chr(123) . substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12) . chr(125);
		return $uuid;
	}
	
	/**
	 * 单向加密
	 * @param string $password
	 * @return string
	 */
	public static function password_hash($password)
	{
		$options = array(
			'cost' => 12,
		);
		return password_hash($password, PASSWORD_BCRYPT, $options);
	}
	
	/**
	 * 验证明文和password_hash方法创建出来的hash是否一致
	 * @param string $password 明文
	 * @param string $hash password_hash函数的返回值
	 * @return boolean
	 */
	public static function password_verify($password,$hash)
	{
		return password_verify($password,$hash);
	}
	
	/**
	 * 生成随机字符串
	 * 区分大小写
	 * 0-9a-zA-Z
	 * @param int $length 字符串长度
	 * @param string 一个包含number|supper_word|lower_word的字符串，中间用竖线分割，或者数组
	 * @param char[] $skip_content 生成的字符串中不包含的字符
	 */
	public static function random($length,$types = 'number|supper_word|lower_word',$skip_content = array())
	{
		$content = array();
		
		$types = is_array($types)?$types:explode('|',$types);
		
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
		if (in_array('supper_word', $types))
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
		
		if (in_array('lower_word', $types))
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
		
		$string = '';
		while ($length--)
		{
			$k = mt_rand(0,count($content)-1);
			$string .= $content[$k];
		}
		return $string;
	}
	
	/**
	 * 加密
	 * 可以通过decode方法解密
	 * @param string $string
	 * 要加密的明文
	 * @param string $salt
	 * 盐值
	 */
	static function encode($string,$salt = '')
	{
		
	}
	
	/**
	 * 尚未实现
	 * 解密encode的返回值
	 * 假如encode的时候salt非空，这里的salt参数也非空
	 * 
	 * @param string $string
	 * 要解密的密文
	 * @param string $salt
	 * 加密的时候用到的盐值
	 */
	static function decode($string,$salt)
	{
		
	}
}