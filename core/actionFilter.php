<?php
namespace framework\core;

use framework\core\control;
use framework\core\response;
use framework\core\component;

class actionFilter extends component
{

	/**
	 *
	 * @var control
	 */
	private $_control;

	/**
	 *
	 * @var string
	 */
	private $_action;

	/**
	 *
	 * @var response
	 */
	private $_message = null;

	function load(control $control, $action)
	{
		$this->_control = $control;
		$this->_action = $action;
	}

	/**
	 * action是否允许访问
	 */
	function allow()
	{
		// 不存在
		if (! method_exists($this->_control, $this->_action))
		{
			$this->_message = new response('not found', 404);
			return false;
		}
		if (! is_callable(array(
			$this->_control,
			$this->_action
		)))
		{
			$this->_message = new response('forbidden', 403);
			return false;
		}
		
		if (method_exists($this->_control, '__access'))
		{
			$accesses = call_user_func(array(
				$this->_control,
				'__access'
			));
			if (is_array($accesses) && ! empty($accesses))
			{
				foreach ($accesses as $access)
				{
					if ((isset($access['express']) && $access['express']) || ! isset($access['express']))
					{
						if (is_array($access['actions']))
						{
							if (in_array($this->_action, $access['actions']))
							{
								if (trim(strtolower($access[0])) == 'deny')
								{
									$this->_message = isset($access['message']) ? $access['message'] : new response('forbidden', 403);
									return false;
								}
								else if (trim(strtolower($access[0])) == 'allow')
								{
									return true;
								}
							}
						}
						else if (is_string($access['actions']))
						{
							if ($access['actions'] == $this->_action || $access['actions'] == '*')
							{
								if (trim(strtolower($access[0])) == 'deny')
								{
									$this->_message = isset($access['message']) ? $access['message'] : new response('forbidden', 403);
									return false;
								}
								else if (trim(strtolower($access[0])) == 'allow')
								{
									return true;
								}
							}
						}
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * action是否开启csrf验证
	 */
	function csrf()
	{
		if (env::php_sapi_name() == 'cli')
		{
			return false;
		}
		if (method_exists($this->_control, '__csrf'))
		{
			$csrfs = call_user_func(array(
				$this->_control,
				'__csrf'
			));
			
			if (isset($csrfs['message']))
			{
				$this->_message = $csrfs['message'];
			}
			if (isset($csrfs['action']))
			{
				$actions = $csrfs['action'];
				if ((is_array($actions) && in_array($this->_action, $actions)) || current($actions) == '*')
				{
					return true;
				}
				else if((is_string($actions) && $actions== $this->_action) || $actions== '*')
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * 当程序在cli模式下，一个action只允许一个实例
	 * 这个函数是判断action是否在运行中
	 * @return boolean 返回true在运行中 false不在运行中
	 */
	function singleThread()
	{
		if (env::php_sapi_name() == 'cli')
		{
			if (method_exists($this->_control, '__single'))
			{
				$singles = call_user_func(array(
					$this->_control,
					'__single'
				));
				if ((is_array($singles) && in_array($this->_action, $singles)) || current($singles) == '*')
				{
					return true;
				}
				else if((is_string($singles) && $singles== $this->_action) || $singles== '*')
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 假如禁止访问的话获取禁止访问的消息
	 */
	function getMessage()
	{
		return $this->_message;
	}
}
