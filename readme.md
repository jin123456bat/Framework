# framework3.0 document

## 前言

好的编程习惯将会减少很多弯路，虽然在框架中部分地方声明并且实现了gbk或者gb2312编码，但是依然我们希望使用utf8编码来完成整个工作，考虑到utf8的通用性和以后的移植甚至对接的时候将减少很多工作量，甚至内部的phpdoc和默认的编码全部都是utf8。

文档中涉及到很多命名空间和目录，我们会在命名空间中使用`\`符号来分割，而在路径中使用`/`符号来分割

## 一、Installation

### 1、Start

​	框架已经在php5.3及以上包括php7都经过测试，没有任何问题，正常情况下即使开启了display_errors也不会出现任何警告和错误信息，假如有请提交bug

​	一个完成的应用程序应该包含三部分，框架，入口，业务逻辑代码

​	框架只包含一个framework的目录，开发者只需要创建一个存放业务逻辑代码的目录和入口文件即可

​	一个标准的入口文件如下

```php
<?php
// 定义根目录
! defined('ROOT') & define('ROOT', __DIR__);

//调试模式
define('DEBUG', false);

include ROOT . '/xhprof.php';

// 定义框架的目录
! defined('SYSTEM_ROOT') & define("SYSTEM_ROOT", ROOT . '/framework');
// 定义APP的目录
! defined('APP_ROOT') & define("APP_ROOT", ROOT . '/application');
// 定义app的名称 app的代码必须放在app名称对应的文件夹里面
! define("APP_NAME", "application");

// 载入框架
include SYSTEM_ROOT . '/framework.php';

$framework = new framework();
$app = $framework->createApplication(APP_NAME, APP_ROOT);
$app->run();
```

​	而一个业务逻辑代码的目录结构应该是这样子的

```diff
>application
>	config	#配置存放的目录
>	control	#控制器代码存放的目录
>	model(可选)	#数据模型存放的目录
>	template(可选)	#前台模板
>	upload(可选)	#上传文件
>	extend(可选)	#injection注入代码
>	entity(可选)	#实体
```

> 除了这些目录外，开发者可以自由的创建其他目录用于其他的业务

### 2、配置

> 为了方便，以下将业务逻辑部分的代码称为应用程序

​	所有的配置都应该放在应用程序目录的config目录下，我们将不同的模块放在不同的文件下面

| 文件名称        | 对应模块         |
| ----------- | ------------ |
| cache       | 缓存           |
| cookie      | cookie       |
| db          | 数据库          |
| environment | 环境配置，ini_set |
| router      | 路由           |
| session     | session      |
| upload      | 上传           |
| view        | 模板           |
| app         | 应用程序名称前3个字符  |

> 模块配置暂定，随着版本叠加，可能会有增加或更改

upload、db、cookie模块的配置中会存在配置名称（也就是key），代表可以同时存在多个配置，可以在控制器中自由调整使用哪一个配置，假如没有指定，默认有限寻找带default=true字段的配置，假如没有配置default=true，默认使用第一个配置，

假如在配置中不存在多个配置，则把整体作为配置来使用

1、cache模块配置

```php
<?php
return array(
	'type' => 'file', // 缓存类型  mysql memcached
	'expires' => 0, // 默认缓存时间 永久有效
	
	//当type为memcached的时候，以下配置memcached的相关信息
	'memcached' => array(
		array(
			'host' => 'localhost',
			'port' => 11211,
			'weight' => 100,
		)
	),
	
	//当type为mysql的时候，以下配置mysql的相关信息，假如没有使用db中的配置，暂时还无法使用自定义的配置
	'mysql' => array(
		array(
			'host' => 'localhost',
			'port' => 3306,
			'user' => 'root',
			'password' => '',
			'dbname' => 'test',
			'charset' => 'utf8',
			'init_command' => '',
		)
	),
	
	//当type为redis的时候，一下配置redis的相关信息  尚未实现
	'redis' => array(
		array(
			'host' => 'localhost',
			'port' => 6379,
			'timeout' => 1,//超时时间
			'password' => '123456',
			'database' => 0,
		),
	),
	
	//当type为file的时候，以下配置生效
	'file' => array(
		'path' => '/var/www/html/php/application/cache',//文件路径  注意 这必须是一个文件夹的路径
	)
);
```

2、cookie模块配置

```php
<?php
return array(
	
	//可以在程序运行过程中使用指定的配置来使用cookie
	'cookie_name1' => array(
		'default'=>true,//指定这个则为默认的配置，假如不指定则使用第一个配置组
		'expire' => 0,
		'secure' => false,
		'httponly' => true,//禁止ajax通过cooke访问
		'domain' => $_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/',//对整个服务器路径有效
	),
	
	/*
	 * 下标为__csrf的cookie配置为csrf存储用的cookie配置
	 */
	'__csrf' => array(
		'expire' => 0,
		'secure' => false,
		'httponly' => true,//禁止ajax通过cooke访问，假如是ajax请求，请将csrf存储在header中
		'domain' => $_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/',//对整个服务器路径有效
	),
);
```

3、db数据库模块配置

```php
/*
 * 数据库配置文件
 * model 定义了哪些model使用这个配置  假如一个model在多个配置中同时存在，第一个生效
 * default 定了一个默认的配置，在没有声明model使用的配置的时候使用default配置
 * 假如上面都没有 使用第一个配置
 */
return array(
	'cloud_web_v2' => array(
		'model' => array(
			//定义了哪些model使用这个配置
		),
		'type' => 'mysql',
		'server' => '192.168.1.225',
		'dbname' => 'cloud_web_v2',
		'user' => 'cm2_admin',
		'password' => 'fxd^CM2-2016',
		'charset' => 'utf8',
		'port'=>3306,
		'init_command' => $init_command
	),
	
	'test' => array(
		'default' => true,
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
	)
);
```

4、环境配置

> 在此定义的环境配置会覆盖php.ini中的环境配置，但是并不是所有的环境配置在这里配置后都会生效，具体参考
>
> [envionment配置清单]: http://php.net/manual/zh/ini.list.php	"设定范围必须是PHP_INI_USER或者PHP_INI_ALL"

```php
<?php
return array(
	'display_errors' => 'On',
	'error_reporting' => E_ALL,
	'memory_limit' => '128M',
	
	'max_execution_time' => 60,
	'session' => array(
		'cookie_lifetime' => 0,
		'gc_maxlifetime' => 3600,
		'use_cookies' => 1, //session的传递通过cookie实现
		'name' => 'FXDATA',
		'use_trans_sid' => 0, // 禁止url中的session_id
		'use_only_cookies' => 1, // 只使用cookie中的session_id
		'cookie_httponly' => 'On', // 禁止js读取cookie
		                           // 'cookie_secure' => 'On',//https
		'hash_function' => 'sha256',
		
		//只需要配置这2个东西就可以实现session存储在memcached中
		//'save_handler' => 'memcached',
		//'save_path' => 'localhost:11211',
		
		//只需要配置这2个东西就可以实现session存储在redis中
		//session.save_handler => 'redis',
		//session.save_path => "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2&read_timeout=2.5"
	),
	'date' => array(
		'timezone' => 'Asia/Shanghai'
	)
);

```

5、router路由模块

```php
<?php
return array(
	//默认的control和action
	'default' => array(
		'control' => 'index',
		'action' => 'index',
	),
	
	//路由绑定,key中允许正则表达式，假如有多个正则表达式匹配，第一个优先
	//值必须是数组，array('control','action')或者array('c'=>'control','a'=>'action')的形式
	'bind' => array(
		//固定式匹配,query_string必须和/about一摸一样才可以  优先级最高
		'/about' => array(
			'c'=>'index','a'=>'page',
		),
		
		//匹配式绑定  id第一个不能数字开头，可以下划线开头或字母   
		//对应的参数放在get中  可以有多个，
		//目前测试pathinfo形式的url是可以的，其他形式的url不能确定
		//假如id不存在也无法正常匹配
		//可以通过request::get('id')的方式获取到id的值
		//优先级其次
		'/about/{id}' => array(
			'index','page',
		),
	),
);
```

6、session

```php
<?php
return array(
	'handler' => 'SessionHandler',//重写sessionHanlder，默认在extend目录下，也可以指定一个带命名空间的类名
);
```

7、upload

> key为配置名称

```php
<?php
return array(
	'vio' => array(
		'size' => 1000000, // 文件大小限制 字节 不存在则不限制
		'ext' => array(
			'mp4',
		), // 文件后缀限制 不存在则不限制
		'path' => './application/upload'
	),
	'text' => array(
		//'default' => true,
		'size' => 1000, // 文件大小限制 字节 不存在则不限制
		// 文件后缀限制 不存在则不限制
		'ext' => array(
			'text'
		),
		// 文件存储路径 假如不填写则不保存，函数直接返回tmp_name
		'path' => './application/upload',
	)
);
```

8、view

```php
<?php
return array(
	//(这里的模板名称是指出layout后面的部分)
	'compress' => true,//页面是否开启压缩，假如是数组的话 是指在指定的模板才开启压缩  压缩和不压缩同时配置 优先压缩 因为这并不会有特大的性能开销
	'no_compress' => array(),//可以在这里配置不压缩的模板名称 
	
	'layout' => 'layout',
);
```



## 二、Injection

​	依赖注入，程序在整个运行当中需要加载不同类，这些类基本上都已经在`\framework\`的命名空间下，当我们想要去修改框架的运行方式或者行为的时候，我们不需要去修改源代码，框架已经提供了一种更好的方式来实现它。

​	我们可以在应用程序的extend目录中创建一个同名的class，而唯一不同的是命名空间`\应用程序名称\extend\类名`，比如`\application\extend\filter`，系统的一些行为可能会加载过滤器并调用过滤器中的一些方法，当我们实现`\application\extend\filter`类并且继承与`\framework\core\filter`类的时候系统实际上加载的是`\application\extend\filter`类，而我们这时候可以在`\application\extend\filter`类中重载默认的函数或者增加其他的函数供系统调用。

## 三、Request

​	在framework中所有请求都是一个request，包括http请求，websocket请求

​	Request存在于`\framework\core\request`空间下

### 1、获取请求参数

​	你可以通过以下的一些方式获取get参数



```php
name=123
var_dump(request::get('name'));\\123
var_dump(request::get('name',333));\\123  不存在的时候333
var_dump(request::get('name',NULL,'strlen'));\\3   使用过滤器
var_dump(request::get('name',NULL,'strlen|explode:",","?"'));\\array(3) 使用多个过滤器以及如何在过滤器中增加参数
var_dump(request::get('name',NULL,NULL,'a'));\\array(123);  使用强制变量转换
```

​	过滤器分为2种，一种是系统已经定义好的过滤器，存在于`\framework\core\filter`中，一种是php原生函数。

> 变量强制转换的优先级是最低的，也就是说，假如过滤器返回了一个string类型的变量，而变量强制转换设定的为a，那么返回的依然是array

> 通过|分割开不同的过滤器，过滤器从左到右依次过滤

> 在过滤器的后面通过:声明过滤器的参数部分，具体的每一个参数都必须用单引号或双引号来包裹，至于两个参数中使用什么符号来分割，完全取决于你，甚至不使用也可以

> 在过滤器中使用?号代表上一个值在参数中的位置

> 我们可以通过injection的方式来实现自定义过滤器

> 除了get的方法，相同的还有**post**方法，**param**方法

### 2、获取上传文件

​	我们依然提供了直接获取上传文件的方法

```php
request::file('file');\\使用默认配置
request::file('file','video');\\使用视频配置
```

​	第一个参数是参数名称，第二个参数可选，配置名称

​	这个函数允许同时接受多个文件，返回值为一个数组

​	开发者获取到这个方法的返回值的时候必须要判断是否真的是一个文件，因为当客户端上传失败的时候，这个方法返回的不是一个文件，而是一个int类型的数字，开发者可以通过和UPLOAD_ERR_*经行对比来确认具体的错误原因

```php
$file = request::file('file');
if(is_file($file) || $file instanceof \framework\vendor\file || is_array($file))
{
  	echo "确定是文件"
}
else
{
  echo "错误代码为".$file;
}
```

> 注意：假如开发者安装了\framework\vendor\file扩展，这个函数返回的是\framework\vendor\file对象或者是包含这个对象的数组

## 三、Response

对于任意一个request，应用程序必须要返回一个response，在控制器的方法中我们可以通过输出一个字符串来给客户端response，同样我们也可以通过return一个字符串的方式来输出一个字符串

更或者我们可以返回一个`\framework\core\response`对象来输出一些内容

以下3种方式是等价的

```php
function hello()
{
  echo "a";
}

function hello1()
{
  return "a";
}

function hello2()
{
  return new response('a');
}
```

我们可以通过继承`\framework\core\response`类来实现一些特殊的响应，以json为例

```php
<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\filter;

class json extends response
{

	const OK = 1;

	const FAILED = 0;

	/**
	 *
	 * @param unknown $code
	 *        	信息代码
	 * @param unknown $result
	 *        	信息代码的描述
	 * @param unknown $data
	 *        	附加数据
	 * @param number $cache
	 *        	缓存时间，默认不缓存
	 * @param string $encode
	 *        	json中的汉字是否编码
	 */
	function __construct($code, $result = null, $data = null, $cache = 0, $encode = false)
	{
		parent::__construct();
		if (! (is_array($code) || is_object($code)))
		{
			$code = array(
				'code' => $code,
				'result' => $result
			);
			
			if ($data !== null)
			{
				$code['data'] = $data;
			}
		}
		if ($encode)
		{
			$content_string = json_encode($code);
		}
		else
		{
			$content_string = $this->json_encode_ex($code);
		}
		$this->setBody($content_string);
		
		$this->setContentType('application/json');
		
		$this->setHeader('Expires', date('D, d M Y H:i:s ', time() + filter::int($cache)) . 'GMT');
		$this->setHeader('Cache-Control', 'max-age=' . filter::int($cache));
	}

	/**
	 * 对变量进行 JSON 编码
	 *
	 * @param
	 *        	mixed value 待编码的 value ，除了resource 类型之外，可以为任何数据类型，该函数只能接受 UTF-8 编码的数据
	 * @return string 返回 value 值的 JSON 形式
	 */
	function json_encode_ex($value)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			$str = json_encode($value);
			$str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matchs)
			{
				return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
			}, $str);
			return $str;
		}
		else
		{
			return json_encode($value, JSON_UNESCAPED_UNICODE);
		}
	}
}
```

当我们在控制器中使用

```php
function a(){
  return new json(array('1','2'));
}
```

的时候，客户端收到的是

```html
[1,2]
```

框架中已经实现了一些response对象

`\framework\core\response\json` 输出json数据

`\framework\core\response\xml` 数据xml数据

`\framework\core\response\url` 302重定向跳转

`\framework\core\response\file` 文件下载，支持断点续传

## 四、Router

框架中默认使用c和a两个参数来确定一个控制器中的方法，c代表控制器的名称，a代表方法的名称

​	访问控制器中的方法的三种方法

​	一、参数访问

​	`index.php?c=index&a=page`访问index控制器中的page方法

​	二、路由绑定

​	开发者可以在router.php配置文件中指定某一个url绑定某一个control和action

```php
'bind' => array(
  //固定式匹配,query_string必须和/about一摸一样才可以  优先级最高
  '/about' => array(
  	'c'=>'index','a'=>'page',
  ),

  //匹配式绑定  id第一个不能数字开头，可以下划线开头或字母   
  //对应的参数放在get中  可以有多个，
  //目前测试pathinfo形式的url是可以的，其他形式的url不能确定
  //假如id不存在也无法正常匹配
  //优先级其次
  '/about/{id}' => array(
  	'about','page',
  ),
),
```

​	如上所示，当用户访问`index.php/about`的时候，实际上访问的是index控制器中的page方法

​	当用户访问`index.php/about/123`的时候的时候，实际上访问的是about控制器中的page方法，同时可以获取到一个get参数，$_GET['id'] = 123

​	上面2种方式都是路由绑定，值可以是c=XXX,a=XXX的数组，也可以是XXX,XXX的数组，第一个是控制器名，第二个是方法名

​	三、pathinfo模式的支持

​	使用这个模式的时候注意，php接受到的url中index.php不能省略，假如希望url中省略掉index.php请使用htaccess方案或者rewrite方案

​	访问`index.php/index/page`代表控制器名为index，方法名为page

## 五、Control

在框架中提供了3种不同的控制器，

`framework\core\cliControl`  cli模式控制器

`framework\core\webControl` web模式控制器

`framework\core\socketControl` socket模式控制器

这三种控制器都继承了`framework\core\control`类



cli模式控制器只用于cli模式，  在windows或者linux下执行 `php index.php -c cli -a index` 将执行cli控制器中的index方法，cli控制器必须继承`framework\core\cliControl`

> cli模式下部分类或者方法调用无效，比如cookie，session等



web控制器只用于web模式，在地址栏中输入对应的url地址，将执行对应控制器中的对应的方法