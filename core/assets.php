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
		
		if (isset($config['css']['mapping'][$filename]))
		{
			return $config['css']['mapping'][$filename];
		}
	}
	
	/**
	 * 加载js文件
	 */
	static public function js($filename)
	{
		$config = self::getConfig('assets');
		$path = $config['js']['path'];
		$host = isset($config['js']['host']) && !empty($config['js']['host'])?$config['js']['host']:$config['host'];
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
		
		if (isset($config['js']['mapping'][$filename]))
		{
			return $config['js']['mapping'][$filename];
		}
	}
	
	/**
	 * 加载图像
	 */
	static public function image($filename)
	{
		$config = self::getConfig('assets');
		$path = $config['image']['path'];
		$host = isset($config['image']['host']) && !empty($config['image']['host'])?$config['image']['host']:$config['host'];
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
	 * 直接通过路径来引用资源
	 * 路径是相对项目的根目录计算
	 * @param unknown $path
	 */
	static public function path($path)
	{
		return rtrim(APP_ROOT,'/').'/assets/'.ltrim($path,'/');
	}
}