<?php
namespace framework\core\database\mysql;

use framework\core\database\driver\mysql;
use framework\core\model;
use framework\core\component;

/**
 * model用来管理表的数据
 * table用来管理表的结构
 * 
 * @author fx
 */
class table extends component
{

	/**
	 *
	 * @var mysql
	 */
	private $_db;

	/**
	 * 表名
	 * 
	 * @var string
	 */
	private $_name;

	/**
	 * 表是否存在
	 * 
	 * @var string
	 */
	private $_exist = false;

	/**
	 * 存储表结构
	 * 
	 * @var array
	 */
	private $_desc = array();

	/**
	 * 存储索引结构
	 * 
	 * @var array
	 */
	private $_index_list = array();
	
	/**
	 * 存储主键索引结构
	 * @var array
	 */
	private $_primary_list = array();
	
	/**
	 * 表的注释
	 * @var string
	 */
	private $_comment;
	
	/**
	 * 存储引擎
	 * @var string
	 */
	private $_engine = 'MyISAM';
	
	/**
	 * 字符集
	 * @var string
	 */
	private $_collation = 'utf8_general_ci';
	
	/**
	 * 主键自增下标
	 * @var number
	 */
	private $_auto_increment;
	
	/**
	 * row_format
	 * @var string
	 */
	private $_row_format;
	
	/**
	 * 索引对象
	 * @var array
	 */
	private $_index_object = array();

	function __construct($table_name, $config = NULL)
	{
		$this->_name = $table_name;
		
		if (empty($config))
		{
			$config = self::getConfig();
		}
		else if (is_scalar($config))
		{
			$config = $this->getConfig($config);
		}
		
		$type = $config['type'];
		$type = '\\framework\\core\\database\\driver\\' . $type;
		
		$this->_db = $type::getInstance($config);
		
		$status = $this->_db->query('show table status where name = ?',array(
			$this->getName(),
		));
		$this->_exist = !empty($status);
		if (isset($status[0]) && !empty($status))
		{
			$this->_comment = $status[0]['Comment'];
			$this->_engine = $status[0]['Engine'];
			$this->_collation = $status[0]['Collation'];
			$this->_auto_increment = $status[0]['Auto_increment'];
			$this->_row_format = $status[0]['Row_format'];
		}
		
		if ($this->exist())
		{
			$descs = $this->_db->query('show full columns from '.$this->getName());
			foreach ($descs as $desc)
			{
				preg_match('/[a-zA-Z]+/', $desc['Type'], $type);
				preg_match('/\((?<length>.+)\)/', $desc['Type'], $lengthData);
				
				$type = strtolower($type[0]);
				
				$length = 0;
				if (isset($lengthData['length']))
				{
					$length = $lengthData['length'];
				}
				
				$auto_increment = false;
				$prototype = '';
				switch (strtolower(trim($desc['Extra'])))
				{
					case 'auto_increment':
						$auto_increment = true;
						break;
					case 'on update current_timestamp':
						$prototype = 'on update CURRENT_TIMESTAMP';
						break;
				}
				
				if (stripos($desc['Type'], 'unsigned zerofill')!==false)
				{
					$prototype = 'unsigned zerofill';
				}
				else if (stripos($desc['Type'], 'unsigned')!==false)
				{
					$prototype = 'unsigned';
				}
				
				
				$this->_desc[$desc['Field']] = array(
					'type' => $type,
					'length' => $length,
					'null' => $desc['Null'] !== 'NO',
					'default' => $desc['Default'],
					'auto_increment' => $auto_increment,
					'collation' => $desc['Collation'],
					'prototype' => $prototype,
					'comment' => $desc['Comment'],
				);
			}
			
			// 索引结构
			$keys = $this->_db->query('show keys from ' . $this->getName());
			foreach ($keys as $key)
			{
				$keyname = strtolower($key['Key_name']);
				if (! isset($this->_index_list[$keyname]))
				{
					$this->_index_list[$keyname] = array(
						'index_type' => $key['Index_type'], // 索引类型 btree hash  判断这个是否为空判断索引是否存在
						'unique' => $key['Non_unique'] == 0, // 是否唯一索引
						'comment' => $key['Comment'], // 注释
						'fields' => array( // 字段
							$key['Column_name']
						)
					);
				}
				else
				{
					$this->_index_list[$keyname]['fields'][] = $key['Column_name'];
				}
			}
		}
	}
	
	function initlize()
	{
		return parent::initlize();
	}
	
	
	/**
	 * 获取数据库配置
	 * @param unknown $name
	 */
	public static function getConfig($name = null)
	{
		//$config = parent::getConfig('db'); 
		return model::getConfig($name);
	}

	/**
	 * 获取表名
	 * 
	 * @return \framework\core\database\mysql\unknown
	 */
	function getName()
	{
		return $this->_name;
	}

	/**
	 * 锁定字段
	 * 
	 * @param unknown $field_name        
	 * @return \framework\core\database\mysql\field
	 */
	function field($field_name)
	{
		//字段默认属性
		$field_info = isset($this->_desc[$field_name])?$this->_desc[$field_name]:array(
			'type' => 'int',
			'length' => 11,
			'null' => false,
			'default' => NULL,
			'auto_increment' => false,
			'collation' => '',
			'prototype' => '',
			'comment' => '',
		);
		$field = new field($field_info,$field_name, $this->getName(), $this->_db,isset($this->_desc[$field_name]));
		if (!$this->exist())
		{
			//当表不存在的时候 创建表
			$comment = !empty($this->_comment)?' COMMENT = "'.$this->_comment.'"':'';
			$engine = ' ENGINE = '.$this->_engine;
			$charset = '';
			if (!empty($this->_collation))
			{
				$charset = current(explode('_', $this->_collation));
				$charset = ' CHARSET='.$charset.' COLLATE '.$this->_collation;
			}
			
			$sql = 'CREATE TABLE `'.$this->_name.'` ( '.field::getFieldSqlString($field_name,$field_info).' )'.$engine.$charset.$comment;
			
			$this->_db->execute($sql);
			
			$this->_exist = true;
		}
		return $field;
	}
	
	/**
	 * 设置或更改字符集
	 * @param string $charset
	 * @return \framework\core\database\mysql\table
	 */
	function charset($charset = 'utf-8')
	{
		switch (strtolower(trim($charset)))
		{
			case 'utf-8':
			case 'utf8':
				$charset = 'utf8_general_ci';
				break;
			case 'utf8mb4':
				$charset = 'utf8mb4_general_ci';
				break;
			case 'gbk':
				$charset = 'gbk_chinese_ci';
				break;
			case 'gb2321':
				$charset = 'gb2312_chinese_ci';
				break;
		}
		$this->_collation = $charset;
		$charset = current(explode('_', $this->_collation));
		$sql = 'ALTER TABLE `'.$this->_name.'` DEFAULT CHARSET='.$charset.' COLLATE '.$this->_collation;
		$this->_db->execute($sql);
		return $this;
	}

	/**
	 * 获取索引
	 * @param unknown $index_name
	 * @return \framework\core\database\mysql\index|NULL
	 */
	function index($index_name)
	{
		if (!isset($this->_index_object[$index_name]))
		{
			$index_info = isset($this->_index_list[$index_name])?$this->_index_list[$index_name]:array(
				'index_type' => '', // 索引类型
				'unique' => false, // 是否唯一索引
				'comment' => '', // 注释
				'fields' => array( // 字段
				)
			);
			$this->_index_object[$index_name] = new index($index_info,$index_name, $this->getName(), $this->_db,isset($this->_index_list[$index_name]));
		}
		return $this->_index_object[$index_name];
	}
	
	/**
	 * 获取主键索引
	 * @return \framework\core\database\mysql\index|\framework\core\database\mysql\NULL
	 */
	function primary()
	{
		return $this->index('primary');
	}

	/**
	 * 删除表
	 * @return table
	 */
	function drop()
	{
		if ($this->_exist)
		{
			$sql = 'DROP TABLE `' . $this->getName() . '`';
			$this->_db->execute($sql);
			//删除之后清空配置
			$this->_exist = false;
			$this->_index_list = array();
			$this->_desc = array();
			$this->_comment = '';
			$this->_engine = 'MyISAM';
			$this->_collation = 'utf8_general_ci';
			$this->_auto_increment = 1;
			$this->_row_format = 'row_format';
		}
		return $this;
	}

	/**
	 * 给表添加注释
	 * 
	 * @param string $string        
	 * @return \framework\core\database\mysql\table
	 */
	function comment($string)
	{
		$this->_comment = $string;
		if ($this->exist())
		{
			$sql = 'ALTER TABLE ' . $this->getName() . ' COMMENT="' . $string . '"';
			$this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 设置存储引擎
	 * @param unknown $engine
	 * @return \framework\core\database\mysql\table
	 */
	function engine($engine)
	{
		$this->_engine = $engine;
		if ($this->exist())
		{
			$sql = 'ALTER TABLE `'.$this->_name.'` ENGINE = '.$this->_engine;
			$this->_db->execute($sql);
		}
		return $this;
	}

	/**
	 * 索引列表
	 */
	function getIndex()
	{
		return $this->_index_list;
	}

	/**
	 * 表结构
	 * @return array
	 */
	function getDesc()
	{
		return $this->_desc;
	}
	
	/**
	 * 判断表是否存在
	 * @return boolean
	 */
	function exist()
	{
		return $this->_exist;
	}
	
	/**
	 * 改名
	 * @param string $new_name
	 * @return \framework\core\database\mysql\table
	 */
	function rename($new_name)
	{
		if ($this->exist())
		{
			$sql = 'RENAME TABLE `'.$this->_name.'` TO `'.$new_name.'`';
			$this->_db->execute($sql);
		}
		$this->_name = $new_name;
		return $this;
	}
	
	/**
	 * 优化表
	 * @return \framework\core\database\mysql\table
	 */
	function optimize()
	{
		if ($this->exist())
		{
			$sql = 'OPTIMIZE TABLE `'.$this->_name.'`';
			$this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 刷新表
	 * @return \framework\core\database\mysql\table
	 */
	function flush()
	{
		if ($this->exist())
		{
			$sql = 'OPTIMIZE TABLE `'.$this->_name.'`';
			$this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 从逻辑上验证数据是否发生改变
	 * 加读锁
	 * 读取权限
	 * @return \framework\core\database\mysql\table
	 */
	function checksum()
	{
		if ($this->exist())
		{
			$sql = 'CHECKSUM TABLE `'.$this->_name.'`';
			$result = $this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 检查表
	 * @return \framework\core\database\mysql\table
	 */
	function check()
	{
		if ($this->exist())
		{
			$sql = 'check TABLE `'.$this->_name.'`';
			$this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 分析表
	 * @return \framework\core\database\mysql\table
	 */
	function analyze()
	{
		if ($this->exist())
		{
			$sql = 'ANALYZE TABLE `'.$this->_name.'`';
			$this->_db->execute($sql);
		}
		return $this;
	}
	
	/**
	 * 备份
	 * 只备份数据而不备份结构，
	 * 会锁表
	 * @param string $file 文件完整路径  最好做一个校验防止注入
	 * @return boolean
	 */
	function export($file)
	{
		if ($this->exist())
		{
			$sql = 'LOCK TABLES `'.$this->_name.'` WRITE;SELECT * INTO OUTFILE '.$file.' FROM '.$this->_name.';UNLOCK TABLES;';
			$this->_db->execute($sql);
		}
		return true;
	}
	
	/**
	 * 导入备份文件
	 * @param string $file
	 */
	function import($file)
	{
		if ($this->exist())
		{
			$sql = 'LOAD DATA LOW_PRIORITY INFILE '.$file.' REPLACE INTO TABLE '.$this->_name;
			$this->_db->execute($sql);
		}
		return true;
	}
}

