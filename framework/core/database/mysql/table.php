<?php
namespace framework\core\database\mysql;
use framework\core\database\driver\mysql;
use framework\core\model;

/**
 * @author fx
 *
 */
class table
{
	/**
	 * 表名
	 * @var unknown
	 */
	private $_name;
	
	/**
	 * 存储了表的所有字段属性
	 * @var array
	 */
	private $_fields = array();
	
	/**
	 * 唯一索引
	 * @var array
	 */
	private $_unique = array(
		'name' => array(
			//有名称的
		),
		'noname' => array(
			//没名称的
		),
	);
	
	/**
	 * 主键索引
	 * @var array
	 */
	private $_primary = array();
	
	/**
	 * 普通索引
	 * @var array
	 */
	private $_index = array(
		'name' => array(
			//有名称的
		),
		'noname' => array(
			//没名称的
		)
	);
	
	/**
	 * 数据表的存储引擎
	 * @var string
	 */
	private $_engine = 'innodb';
	
	/**
	 * 数据表的字符集
	 * @var string
	 */
	private $_charset = 'utf8';
	
	/**
	 * 只有当不存在表的时候才创建表
	 * @var string
	 */
	private $_not_exist = true;
	
	const ENGINE_INNODB = 'innodb';
	
	const ENGINE_MYISAM = 'myisam';
	
	private $_db = array();
	
	private $_config = '';
	
	function __construct($name,$config = '')
	{
		$this->_name = $name;
		$this->initlize_db($name,$config);
	}
	
	private function initlize_db($name,$config)
	{
		$this->_config = $config;
		if (empty($config))
		{
			$dbs = model::getConfig('db');
			foreach ($dbs as $configname => $config)
			{
				$instance = mysql::getInstance($config);
				if ($instance->isExist($name))
				{
					$this->_db[$configname] = $instance;
				}
			}
		}
		else
		{
			$db = model::getConfig('db');
			if (isset($dbs[$config]) && !empty($dbs[$config]))
			{
				$instance = mysql::getInstance($db);
				if ($instance->isExist($name))
				{
					$this->_db[$config] = $instance;
				}
			}
		}
	}
	
	/**
	 * 获取表名
	 */
	function getName()
	{
		return $this->_name;
	}
	
	/**
	 * 更改表名
	 */
	function setName($name)
	{
		$this->_name = $name;
		$this->initlize_db($this->_name, $this->_config);
	}
	
	/**
	 * 只有当不存在的时候才创建表
	 */
	function notExist($exist = false)
	{
		$this->_not_exist = $exist;
	}
	
	/**
	 * 添加一个varchar类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function varchar($name,$length)
	{
		$temp = new field($name,'varchar',$length);
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` varchar('.$length.')';
			$db->query($sql);
		}
		return $temp;
	}
		
	/**
	 * 添加一个char类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function char($name,$length)
	{
		$temp = new field($name, 'char',$length);
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` char('.$length.')';
			$db->query($sql);
		}
		return $temp;
	}
	
	/**
	 * 添加一个int类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function int($name,$length = 11)
	{
		$temp = new field($name, 'int',$length);
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` int('.$length.')';
			$db->query($sql);
		}
		return $temp;
	}
	
	/**
	 * 添加一个timestamp类型的字段
	 * @param unknown $name 字段名称
	 * @return \framework\core\database\mysql\field
	 */
	function timestamp($name)
	{
		$temp = new field($name, 'timestamp');
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` timestamp';
			$db->query($sql);
		}
		return $temp;
	}
	
	/**
	 * 添加一个datetime类型的字段
	 * @param unknown $name 字段名称
	 * @return \framework\core\database\mysql\field
	 */
	function datetime($name)
	{
		$temp = new field($name, 'datetime');
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` datetime';
			$db->query($sql);
		}
		return $temp;
	}
	
	/**
	 * 添加一个text类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function text($name,$length)
	{
		$temp = new field($name, 'text',$length);
		$this->_fields[] = $temp;
		
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD `ddd` VARCHAR(123) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2' COMMENT '1213123' AFTER `email`;
			$sql = 'alter table `'.$this->_name.'` add `'.$name.'` text';
			$db->query($sql);
		}
		return $temp;
	}
	
	/**
	 * 添加主键索引 
	 * 与字段内的主键索引冲突，优先使用字段内的主键索引
	 * @param string|array $field
	 */
	function primary($field)
	{
		if (is_scalar($field))
		{
			$field = array($field);
		}
		$this->_primary = array_merge($this->_primary,$field);
		foreach ($this->_db as $db)
		{
			//ALTER TABLE `authorize` ADD PRIMARY KEY(` id `)
			$db->query('alter table `'.$this->_name.'` add primary key(`'.implode('`,`', $field).'`)');
		}
		return $this;
	}
	
	/**
	 * 添加索引
	 * @param string|array $field
	 * @param string $name 索引名 可选，默认第一个字段名 必须是字符串
	 */
	function index($field,$name = '')
	{
		if (is_scalar($field))
		{
			$field = array($field);
		}
		if (!empty($name))
		{
			if (!isset($this->_index['name'][$name]) || empty($this->_index['name'][$name]))
			{
				$this->_index['name'][$name] = $field;
			}
			else
			{
				$this->_index['name'][$name] = array_merge($this->_index['name'][$name],$field);
			}
		}
		else 
		{
			$this->_index['noname'] = array_merge($this->_index['noname'],$field);
		}
		return $this;
	}
	
	/**
	 * 添加唯一索引
	 * @param string|array $field 字段名
	 * @param string $name 可选 索引名
	 */
	function unique($field,$name = '')
	{
		if (is_scalar($field))
		{
			$field = array($field);
		}
		
		if (!empty($name))
		{
			if (!isset($this->_unique['name'][$name]) || empty($this->_unique['name'][$name]))
			{
				$this->_unique['name'][$name] = $field;
			}
			else
			{
				$this->_unique['name'][$name] = array_merge($this->_unique['name'][$name],$field);
			}
		}
		else 
		{
			$this->_unique['noname'] = array_merge($this->_unique['noname'],$field);
		}
		return $this;
	}
	
	/**
	 * 设置数据库引擎
	 * @param string $engine
	 */
	function engine($engine)
	{
		$this->_engine = $engine;
		
		foreach ($this->_db as $db)
		{
			$sql = 'ALTER TABLE `'.$this->_name.'` ENGINE = '.$engine;
			$db->query($sql);
		}
		return $this;
	}
	
	/**
	 * 设置表的字符集
	 * @param unknown $charset
	 */
	function charset($charset)
	{
		$this->_charset = $charset;
		
		switch ($charset)
		{
			case 'utf8':
				$collate = 'utf8_general_ci';
				break;
			case 'utf8mb4':
				$collate = 'utf8mb4_general_ci';
				break;
			case 'gbk':
				$collate = 'gbk_chinese_ci';
				break;
			case 'gb2312':
				$collate = 'gb2312_chinese_ci';
				break;
		}
		foreach ($this->_db as $db)
		{
			$sql = 'ALTER TABLE `'.$this->_name.'` DEFAULT CHARSET='.$charset.' COLLATE '.$collate;
			$db->query($sql);
		}
		return $this;
	}
	
	/**
	 * 转化为sql语句 尚未完成
	 */
	function __toSql()
	{
		$fields = array_map(function($field){
			return $field->__toSql();
		}, $this->_fields);
		
		
		$engine = empty($this->_engine)?'':'ENGINE='.$this->_engine;
		
		$charset = empty($this->_charset)?'':'DEFAULT CHARSET='.$this->_charset;
		
		$exist = $this->_not_exist?'IF NOT EXISTS ':'';
		
		$key = '';
		if (!empty($this->_primary))
		{
			$hasPrimary = false;
			foreach ($this->_fields as $f)
			{
				if ($f->isPrimary())
				{
					$hasPrimary = true;
				}
			}
			if (!$hasPrimary)
			{
				$key .= ',';
				$key .= 'PRIMARY KEY ('.implode(',',$this->_primary).')';
			}
		}
		if (!empty($this->_index['name']))
		{
			$key .= ',';
			foreach ($this->_index['name'] as $name => $f)
			{
				$key .= 'INDEX `'.$name.'` ('.implode(',', $f).')';
			}
		}
		if (!empty($this->_index['noname']))
		{
			$key .= ',';
			$key .= 'INDEX ('.implode(',', $this->_index['noname']).')';
		}
		if (!empty($this->_unique['name']))
		{
			$key .= ',';
			foreach ($this->_unique['name'] as $name => $f)
			{
				$key .= 'UNIQUE KEY `'.$name.'` ('.implode(',', $f).')';
			}
		}
		if (!empty($this->_unique['noname']))
		{
			$key .= ',';
			$key .= 'UNIQUE KEY ('.implode(',', $this->_unique['noname']).')';
		}
		
		return 'CREATE TABLE '.$exist.'`'.$this->getName().'` ('.implode(',', $fields).' '.$key.') '.$engine.' '.$charset.';';
	}
}

