<?php
namespace framework\core;

class upload extends component
{

	function __construct()
	{
	}

	/**
	 * 获取文件上传的配置信息
	 *
	 * @param unknown $config        	
	 * @return mixed
	 */
	public function getConifg($config = null)
	{
		$configs = parent::getConfig('upload');
		if (! empty($config))
		{
			if (isset($configs[$config]))
			{
				if (isset($configs['ext']) && is_scalar($configs[$config]['ext']))
				{
					$configs[$config]['ext'] = explode(',', $configs[$config]['ext']);
				}
				if (isset($configs['type']) && is_scalar($configs[$config]['type']))
				{
					$configs[$config]['type'] = explode(',', $configs[$config]['type']);
				}
				return $configs[$config];
			}
		}
		
		$whole = '';
		foreach ($configs as $index => $c)
		{
			if ($whole === '' && $index === 0)
			{
				$whole = true;
			}
			if (isset($c['default']) && $c['default'] === true)
			{
				if (isset($configs['ext']) && is_scalar($c['ext']))
				{
					$c['ext'] = explode(',', $c['ext']);
				}
				if (isset($configs['type']) && is_scalar($c['type']))
				{
					$c['type'] = explode(',', $c['type']);
				}
				return $c;
			}
		}
		
		if ($whole === true)
		{
			if (isset($configs['ext']) && is_scalar($configs[0]['ext']))
			{
				$configs[0]['ext'] = explode(',', $configs[0]['ext']);
			}
			if (isset($configs['type']) && is_scalar($configs[0]['type']))
			{
				$configs[0]['type'] = explode(',', $configs[0]['type']);
			}
			return $configs[0];
		}
		else
		{
			if (isset($configs['ext']) && is_scalar($configs['ext']))
			{
				$configs['ext'] = explode(',', $configs['ext']);
			}
			if (isset($configs['type']) && is_scalar($configs['type']))
			{
				$configs['type'] = explode(',', $configs['type']);
			}
			return $configs;
		}
	}

	/**
	 * 获取上传的文件路径
	 *
	 * @param unknown $name        	
	 * @param unknown $config        	
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
