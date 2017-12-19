<?php
namespace framework\core;
/**
 * 语言
 * @author jin
 *
 */
class lang extends component
{
	public static $_language_storage_key = '__framework__language';
	
	/**
	 * 设置语言区域
	 * @param string $lang
	 * @return boolean
	 */
	public static function setLocale($lang = 'zh')
	{
		return session::set(self::$_language_storage_key, $lang);
	}
	
	/**
	 * 获取当前设置的语言区域
	 * @return \framework\core\NULL|mixed
	 */
	private static function getLocale()
	{
		return session::get(self::$_language_storage_key);
	}
	
	/**
	 * 获取翻译
	 * @example
	 * 在语言文件中定义
	 * messages文件，内容为
	 * array(
	 * 	'welcome' => '您好，{name}|您好，{name}们'
	 * )
	 * 模板中使用
	 * lang::get('messages.welcome',['name'=>'开发者'],1)
	 * 页面显示
	 * 您好，开发者们
	 * @param string $key 'messages.welcome'  messages.php文件中的welcome下标
	 * @param array $param  ['name' => 'Dayle'] 参数  翻译中用{name}来代替
	 * @param number $num 第几个语句 默认为0 通常用来解决复数等问题
	 * @return unknown|mixed
	 */
	public static function translate($key,array $param = array(),$num = 0)
	{
		$lang = self::getLocale();
		list($file,$var) = explode('.', $key,2);
		
		$path = APP_ROOT.'/language/'.$lang.'/'.$file.'.php';
		if (file_exists($path))
		{
			$language = include $path;
			if (isset($language[$var]))
			{
				if (!empty($param))
				{
					foreach ($param as $key => $value)
					{
						$language[$var] = str_replace('{'.$key.'}', $value, $language[$var]);
					}
				}
				
				$language = explode('|', $language[$var]);			
				return $language[$num];
			}
		}
		return $var;
	}
}