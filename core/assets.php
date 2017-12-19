<?php
namespace framework\core;
use framework\vendor\file;

class assets extends component
{
	/**
	 * 加载css文件
	 */
	static public function css($filename)
	{
		$config = self::getConfig('assets');
		$path = $config['css']['path'];
		$host = isset($config['css']['host']) && !empty($config['css']['host'])?$config['css']['host']:$config['host'];
		foreach ($path as $p)
		{
			$file = rtrim($p,'/').'/'.$filename;
			if (file_exists($file))
			{
				$time = filemtime($file);
				if (empty($host))
				{
					$host = '.';
				}
				$path = rtrim($host,'/').str_replace('\\', '/', str_replace(realpath(APP_ROOT), '', realpath($file))).'?'.$time;
				return $path;
			}
		}
	}
	
	/**
	 * 加载js文件
	 */
	static public function js($filename)
	{
		$config = self::getConfig('assets');
		$path = $config['css']['path'];
		$host = isset($config['css']['host']) && !empty($config['css']['host'])?$config['css']['host']:$config['host'];
		foreach ($path as $p)
		{
			$file = rtrim($p,'/').'/'.$filename;
			if (file_exists($file))
			{
				$time = filemtime($file);
				if (empty($host))
				{
					$host = '.';
				}
				$path = rtrim($host,'/').str_replace('\\', '/', str_replace(realpath(APP_ROOT), '', realpath($file))).'?'.$time;
				return $path;
			}
		}
	}
	
	/**
	 * 加载图像
	 */
	static public function image($filename)
	{
		$config = self::getConfig('assets');
		$path = $config['css']['path'];
		$host = isset($config['css']['host']) && !empty($config['css']['host'])?$config['css']['host']:$config['host'];
		foreach ($path as $p)
		{
			$file = rtrim($p,'/').'/'.$filename;
			if (file_exists($file))
			{
				$time = filemtime($file);
				if (empty($host))
				{
					$host = '.';
				}
				$path = rtrim($host,'/').str_replace('\\', '/', str_replace(realpath(APP_ROOT), '', realpath($file))).'?'.$time;
				return $path;
			}
		}
	}
}