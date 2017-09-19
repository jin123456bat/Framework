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
		if (method_exists($this->_control, '__csrf'))
		{
			$csrfs = call_user_func(array(
				$this->_control,
				'__csrf'
			));
			if ((is_array($csrfs) && in_array($this->_action, $csrfs)) || current($csrfs) == '*')
			{
				return true;
			}
			else if((is_string($csrfs) && $csrfs == $this->_action) || $csrfs == '*')
			{
				return true;
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
