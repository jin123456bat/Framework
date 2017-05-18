<?php
namespace framework\core;

class upload extends component
{

	function __construct()
	{
	}

	/**
	 * 获取文件上传的配置信息
	 * 配置可以直接写在upload配置文件中  
	 * 配置文件以 key => array(所有相关的配置)的形式 
	 * @param unknown $config 配置文件中的key
	 * 假如没有指定key 用有default=true的配置
	 * 假如没有default=true的配置使用第一个
	 * 假如整个就是一个配置 则使用整个配置
	 * @return mixed
	 */
	public function getConifg($config = null)
	{
		$configs = parent::getConfig('upload');
		//upload没有配置过直接返回空数组
		if (empty($configs))
		{
			return array();
		}
		
		//判断是否整个就是一个大配置
		if ((isset($configs['path']) && is_scalar($configs['path'])) || isset($configs['ext']) || (isset($configs['size']) && is_numeric($configs['size'])))
		{
			return $configs;
		}
		
		//判断是否有指定的配置
		if (! empty($config))
		{
			//假如存在指定配置 直接使用指定配置
			if (isset($configs[$config]))
			{
				if (isset($configs[$config]['ext']) && is_scalar($configs[$config]['ext']))
				{
					$configs[$config]['ext'] = explode(',', $configs[$config]['ext']);
				}
				if (isset($configs[$config]['type']) && is_scalar($configs[$config]['type']))
				{
					$configs[$config]['type'] = explode(',', $configs[$config]['type']);
				}
				return $configs[$config];
			}
		}
		
		//获取默认的配置
		foreach ($configs as $index => $c)
		{
			if (isset($c['default']) && $c['default'] === true)
			{
				if (isset($c['ext']) && is_scalar($c['ext']))
				{
					$c['ext'] = explode(',', $c['ext']);
				}
				if (isset($c['type']) && is_scalar($c['type']))
				{
					$c['type'] = explode(',', $c['type']);
				}
				return $c;
			}
		}
		
		//使用第一个配置
		if(is_array(current($configs)))
		{
			$c = current($configs);
			if (isset($c['ext']) && is_scalar($c['ext']))
			{
				$c['ext'] = explode(',', $c['ext']);
			}
			if (isset($c['type']) && is_scalar($c['type']))
			{
				$c['type'] = explode(',', $c['type']);
			}
			return $c;
		}
		
		return array();
	}

	/**
	 * 获取上传的文件路径
	 *
	 * @param unknown $name        	
	 * @param unknown $config 配置在upload中的名称
	 * @return 上传失败返回错误代码否则返回文件路径
	 */
	function receive($name, $config = null)
	{
		$config = self::getConifg($config);
		if (isset($config['path']))
		{
			if (! is_dir($config['path']))
			{
				mkdir($config['path'], 0777, true);
			}
		}
		if (isset($_FILES[$name]))
		{
			if (is_scalar($_FILES[$name]['error']))
			{
				if ($_FILES[$name]['error'] == UPLOAD_ERR_OK)
				{
					$ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
					
					if (isset($config['size']) && ! empty($config['size']))
					{
						if ($_FILES[$name]['size'] > $config['size'])
						{
							return UPLOAD_ERR_INI_SIZE;
						}
					}
					
					if (isset($config['type']) && ! empty($config['type']))
					{
						if (! in_array($_FILES[$name]['type'], $config['type']))
						{
							return UPLOAD_ERR_EXTENSION;
						}
					}
					
					if (isset($config['ext']) && ! empty($config['ext']))
					{
						if (! in_array($ext, $config['ext']))
						{
							return UPLOAD_ERR_EXTENSION;
						}
					}
					
					if (isset($config['path']))
					{
						$dist = rtrim($config['path'], '/') . '/' . md5_file($_FILES[$name]['tmp_name']) . '.' . $ext;
						move_uploaded_file($_FILES[$name]['tmp_name'], $dist);
						return $dist;
					}
					return $_FILES[$name]['tmp_name'];
				}
				else
				{
					return $_FILES[$name]['error'];
				}
			}
			else if (is_array($_FILES[$name]['error']))
			{
				$files = array();
				foreach ($_FILES[$name]['error'] as $index => $error)
				{
					if ($error == UPLOAD_ERR_OK)
					{
						$ext = pathinfo($_FILES[$name]['name'][$index], PATHINFO_EXTENSION);
						
						if (isset($config['size']) && ! empty($config['size']))
						{
							if ($_FILES[$name]['size'][$index] > $config['size'])
							{
								if (isset($files[$_FILES[$name]['name'][$index]]))
								{
									$files[$_FILES[$name]['name'][$index] . '_' . $index] = UPLOAD_ERR_INI_SIZE;
								}
								else
								{
									$files[$_FILES[$name]['name'][$index]] = UPLOAD_ERR_INI_SIZE;
								}
								continue;
							}
						}
						
						if (isset($config['type']) && ! empty($config['type']))
						{
							if (! in_array($_FILES[$name]['type'][$index], $config['type']))
							{
								if (isset($files[$_FILES[$name]['name'][$index]]))
								{
									$files[$_FILES[$name]['name'][$index] . '_' . $index] = UPLOAD_ERR_EXTENSION;
								}
								else
								{
									$files[$_FILES[$name]['name'][$index]] = UPLOAD_ERR_EXTENSION;
								}
								continue;
							}
						}
						
						if (isset($config['ext']) && ! empty($config['ext']))
						{
							if (! in_array($ext, $config['ext']))
							{
								if (isset($files[$_FILES[$name]['name'][$index]]))
								{
									$files[$_FILES[$name]['name'][$index] . '_' . $index] = UPLOAD_ERR_EXTENSION;
								}
								else
								{
									$files[$_FILES[$name]['name'][$index]] = UPLOAD_ERR_EXTENSION;
								}
								continue;
							}
						}
						
						if (isset($config['path']))
						{
							$dist = rtrim($config['path'], '/') . '/' . md5_file($_FILES[$name]['tmp_name'][$index]) . '.' . $ext;
							if (move_uploaded_file($_FILES[$name]['tmp_name'][$index], $dist))
							{
								if (isset($files[$_FILES[$name]['name'][$index]]))
								{
									$files[$_FILES[$name]['name'][$index] . '_' . $index] = $dist;
								}
								else
								{
									$files[$_FILES[$name]['name'][$index]] = $dist;
								}
							}
							else
							{
								if (isset($files[$_FILES[$name]['name'][$index]]))
								{
									$files[$_FILES[$name]['name'][$index] . '_' . $index] = $_FILES[$name]['tmp_name'][$index];
								}
								else
								{
									$files[$_FILES[$name]['name'][$index]] = $_FILES[$name]['tmp_name'][$index];
								}
							}
						}
						else
						{
							if (isset($files[$_FILES[$name]['name'][$index]]))
							{
								$files[$_FILES[$name]['name'][$index] . '_' . $index] = $_FILES[$name]['tmp_name'][$index];
							}
							else
							{
								$files[$_FILES[$name]['name'][$index]] = $_FILES[$name]['tmp_name'][$index];
							}
						}
					}
					else
					{
						if (isset($files[$_FILES[$name]['name'][$index]]))
						{
							$files[$_FILES[$name]['name'][$index] . '_' . $index] = $error;
						}
						else
						{
							$files[$_FILES[$name]['name'][$index]] = $error;
						}
					}
				}
				return $files;
			}
		}
	}
}
