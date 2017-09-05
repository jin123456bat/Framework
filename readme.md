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
| captcha     | 验证码模块        |
| csrf        | csrf_token配置 |

> 模块配置暂定，随着版本叠加，可能会有增加或更改

upload、db、cookie模块的配置中会存在配置名称（也就是key），代表可以同时存在多个配置，可以在控制器中自由调整使用哪一个配置，假如没有指定，默认有限寻找带default=true字段的配置，假如没有配置default=true，默认使用第一个配置，

假如在配置中不存在多个配置，则把整体作为配置来使用

1、cache模块配置

```php
<?php
return array(
	'type' => 'file', // 缓存类型  mysql memcache
	'expires' => 0, // 默认缓存时间 永久有效
	
	//当type为memcache的时候，以下配置memcache的相关信息
	'memcache' => array(
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
 */
return array(
	'test' => array(
		'default' => true,
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
      	'init_command' => '',//当mysql连接之后执行的sql
      	'model' => array(),//允许指定model使用指定的配置
	),
  	//最小配置
  	'test1' => array(
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
    ),
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
  
  	//直接将某个类做为controller，需要遵循以下原则
	//1、类必须继承于/framework/core/response类
	//2、响应内容由类中的getBody方法定义
	//3、类的构造函数必须支持无参数类型，
	//4、调用方法是c=类名，类名为不带命名空间的类名 如下为index.php?c=captcha
	//pathinfo模式调用方法为index.php/captcha
	//因此，假如有多个相同的类名，以第一个为准，第二个不会调用
	//5、不允许声明同样的control，否则会被control覆盖
	//6、key是别名，假如被control覆盖，可以通过别名来调用
	//7、别名的优先级要高一些
	/*
	 * 2个都是alias，根据第4规则，应该调用第一个类，但是事实上应该调用第二个类
	 *  array(
		'/framework/vendor/alias',
		'alias' => '/framework/vendor/content',
	) */
	'class' => array(
		'alias'=>'\framework\vendor\captcha',
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

​	对于任意一个request，应用程序必须要返回一个response，在控制器的方法中我们可以通过输出一个字符串来给客户端response，同样我们也可以通过return一个字符串的方式来输出一个字符串

​	更或者我们可以返回一个`\framework\core\response`对象来输出一些内容

​	以下3种方式是等价的

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

​	我们可以通过继承`\framework\core\response`类来实现一些特殊的响应，以json为例

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

`	\framework\core\response\json` 输出json数据

`	\framework\core\response\xml` 数据xml数据

`	\framework\core\response\url` 302重定向跳转

`	\framework\core\response\file` 文件下载，支持断点续传



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

​	四、绑定扩展

​	framework允许将一个第三方的类库作为响应来使用，这样framework可以做到直接更改配置，而无需去修改任何代码即可实现第三方类库的引入

​	在路由配置中新增如下配置：

```
//直接将某个类做为controller，需要遵循以下原则
//1、类必须继承于/framework/core/response类
//2、响应内容由类中的getBody方法定义
//3、类的构造函数必须支持无参数类型，
//4、调用方法是c=类名，类名为不带命名空间的类名 如下为index.php?c=captcha
//pathinfo模式调用方法为index.php/captcha
//因此，假如有多个相同的类名，以第一个为准，第二个不会调用
//5、不允许声明同样的control，否则会被control覆盖
//6、key是别名，假如被control覆盖，可以通过别名来调用
//7、别名的优先级要高一些
//2个都是alias，根据第4规则，应该调用第一个类，但是事实上应该调用第二个类
//array(
//	'/framework/vendor/alias',
//	'alias' => '/framework/vendor/content',
//)
'class' => array(
	'alias'=>'\framework\vendor\captcha',
),
```

## 五、Control

框架可以在web或者cli模式运行，具体执行的代码取决于运行的模式

在框架中提供了3种不同的控制器，

`framework\core\cliControl`  cli模式控制器

`framework\core\webControl` web模式控制器

`framework\core\socketControl` socket模式控制器

这三种控制器都继承了`framework\core\control`类



​	cli模式控制器只用于cli模式，  在windows或者linux下执行 `php index.php -c cli -a index` 将执行cli控制器中的index方法，cli控制器必须继承`framework\core\cliControl`

> cli模式下部分类或者方法调用无效，比如cookie，session等



​	web控制器只用于web模式，只能通过http(s)的协议的方式访问，在地址栏中输入对应的url地址，将执行对应控制器中的对应的方法



​	socket控制器 只用于websocket模式，在websocket的模式下，由客户端发送一个json对象来确认控制器和方法,其中c和a分别代表控制器和方法，其他参数可以由开发者自行扩展

```json
{"c":"test","a":"message","data":"参数1","data2":"参数2"}
```

​	开发者可以在终端或者命令行中执行`php index.php[ -websocket socket_name]`的方式来启动一个websocket监听进程，详细参考websocket章节



> 错误的调用了错误的模式控制器将会返回404 not found

> 假如开发者希望一个控制器在不同的模式下工作，可以通过继承`framework\core\control`来实现

错误的调用了错误的模式控制器的时候，开发者可以通过在控制器中自定义__runningMode方法来实现一个更加友好的提示信息或者错误

	/**
	 * 当其它的模式调用了这个控制器中的方法的时候，调用这个函数来提供一个友好的输出提示
	 * 
	 * @param unknown $mode        
	 */
	public function __runningMode($mode)
	{
		return 'can\'t running in ' . $mode . ' mode';
	}
## 六、websocket

​	假设开发者希望开发一个基于websocket的应用的时候，可以通过框架启动一个websocket进程

`php index.php[ -websocket socket_name]`

> socket_name是websocket的名称，且必须唯一。
>
> 默认的websocket监听端口号是2000

​	开发者可以在应用程序目录的extend目录中创建一个继承`framework\core\webSocket`的类，类名就是socket_name

​	假设我们现在创建一个chat的一个websocket的类，它的类名应该为`application\extend\chat`

```php
class chat extends webSocket
{
	function initlize()
	{
		console::log('chat is running');
		parent::initlize();
	}
	
	/**
	 * 端口号
	 * @return number
	 */
	function __port()
	{
		return 2001;
	}
}
```

​	在这个类中我们声明了一个__port的方法，并且这个方法返回了2001，代表我们使用2001端口来启动监听，

之后我们可以执行 `php index.php -websocket chat`来启动这个websocket监听程序

```shell
$ php index.php -websocket chat
chat is running
Server Chat Startted on 2001!
```

​	OK！我们的监听程序开起来正常启动了，接下来就是编写我们的逻辑代码了，

​	我们在control目录中创建一个chat的控制器，当然它必须继承`framework\core\socketControl`，代码如下

```php
namespace application\control;
use framework\core\socketControl;

class chat extends socketControl
{
	function message()
	{
		return strrev($this->getParam('data'));
	}
}
```

​	我们的逻辑很简单，将收到的data的数据按照字符串反转后发送回客户端

​	最后就要编写我们的客户端脚本，这里我直接引用网络上的一段代码，我自己做了稍微的修改

```html
<html>
<head>
    <meta charset="UTF-8">
    <title>Web sockets test</title>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var ws;
        function ToggleConnectionClicked() {          
                try {
                    ws = new WebSocket("ws://127.0.0.1:2001");//连接服务器        
                    ws.onopen = function(event){alert("已经与服务器建立了连接\r\n当前连接状态："+this.readyState);};
                    ws.onmessage = function(event){alert("接收到服务器发送的数据：\r\n"+event.data);};
                    ws.onclose = function(event){alert("已经与服务器断开连接\r\n当前连接状态："+this.readyState);};
                    ws.onerror = function(event){alert("WebSocket异常！");};
                } catch (ex) {
                    alert(ex.message);      
                }
        };
 
        function SendData() {
            try{
                var content = document.getElementById("content").value;
                var content = {//这里要注意了，必须要有c和a，作用是用来执行哪个控制器的哪个方法
                	'c':'chat',
                	'a':'message',
                	'data':content,
                }
                if(content){
                	//客户端必须要将数据转化为json串才可以发送到服务器
                    ws.send(JSON.stringify(content));
                }
            }catch(ex){
                alert(ex.message);
            }
        };
 
        function seestate(){
            alert(ws.readyState);
        }
       
    </script>
</head>
<body>
   <button id='ToggleConnection' type="button" onclick='ToggleConnectionClicked();'>连接服务器</button><br /><br />
   <textarea id="content" ></textarea>
    <button id='ToggleConnection' type="button" onclick='SendData();'>发送我的名字：beston</button><br /><br />
    <button id='ToggleConnection' type="button" onclick='seestate();'>查看状态</button><br /><br />
</body>
</html>
```

​	好，接下来在浏览器中执行前端脚本，点击链接服务器，弹出

```
已经与服务器建立了连接
当前连接状态：1
```

​	服务器连接成功，接下来再输入框中输入我们要传输的数据，假设为abcd，好，正常提示dcba！了，这就是一个完整的websocket通信流程。（因为服务器端的逻辑是strrev，所以不支持中文，测试的时候注意，不要以为电脑有问题就砸电脑）



## 七、model

框架中任何需要操作数据库数据的地方都可以使用model。开发者可以在项目的任何类中使用model，使用方式很简单

```php
$this->model($tableName);
```

$tableName是数据库表名

model方法返回的是一个`framework\core\model`对象，假如开发者在应用程序的model目录中创建了自定义的model，那么返回的将是自定义对象，

> 当实例化model的时候，model对应的数据表必须存在，否则将会出错

例如开发者创建了这样的一个类

```php
namespace application\model;

use framework\core\model;

class accounts extends model
{
}
```

那么开发者就可以在任意类中使用

```php
$this->model('accounts')
```

的方式来获得`application\model\accounts`类的实例，

假如开发者没有创建`application\model\accounts`类，那么model方法将返回`framework\core\model`类



通常在一个项目中可能连接了多个数据库，同样，我们也允许不同的model连接不能的数据库，首先我们应该按照config章节中的说明在db.php中合理的配置不同的连接方式的配置。



框架允许在自定义model中声明一个__config方法，这个方法可以有2种返回值，一种是存储了详细配置的数据，或者是数据库连接的配置名称

假设我们在db.php的配置文件中已经声明了如下配置

```php
return array(
	'test' => array(
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
	)
);
```

那么在自定义model中按照如下方式使用是等价的

```php
//使用手动指定的配置
function __config()
{
	return array(
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
	);
}
//等价于
//自动在配置文件中查找名为test的配置
function __config()
{
  return 'test';
}
```



默认情况下，框架使用表名来作为model的名称，有的时候，数据库表名可能和model名称不一致，或者希望可以自定义表名，开发者可以通过添加__tableName的方法来声明实际表名

```php
namespace application\model;

use framework\core\model;

class accounts extends model
{
	function __tableName()
    {
      return 'accounts_in_test';
    }
}
```

如上代码所示，虽然model名为model，但实际上操作的却是accounts_in_test表



简单的查询，所有的查询操作必须以select/find/scalar/count/max/sum/avg等结尾

```php
$this->model('accounts')->select();//查询accounts表中的所有数据
$this->model('accounts')->where('id=?',array(1))->select();//查询id为1的数据
$this->model('accounts')->limit(0,10)->select();//相当于 limit 0,10
$this->model('accounts')->limit(10)->select();//相当于 limit 10
$this->model('accounts')->order('id')->select();//相当与order by id asc
$this->model('accounts')->order('id','desc')->select();//相当于order by id desc
$this->model('accounts')->in('id',array(1,2,3));//相当于 and in (1,2,3)
$this->model('accounts')->where('id like ?',array(1))->where('name >= ?',array('123'))->find();//多个where
$this->model('accounts')->group('name,id')  //等价于
$this->model('accounts')->group(array('name','id'))
//指定字段查询
$this->model('accounts')->select('id,name');//等价于
$this->model('accounts')->select(['id','name']);

$this->model('accounts')->select();//select返回多行数据
$this->model('accounts')->find();//find返回第一行数据  参数等同于select
$this->model('accounts')->scalar();//第一行的第一个数据

//select的as操作
$this->model('accounts')->select(array('number'=>'id'));
//select id as number from accounts;

//select相关的扩展方法
$this->model('accounts')->likein('name',array('张三','李四'))->select();
//select * from accounts where name like "张三" or name like "李四"

//where其他的用法，
$this->model('accounts')->where('id=:id and name=:name',array('id'=>1,'name'=>'张三'))->select();
```

多表查询功能

```php
//如何添加多表以及as使用
$this->model('accounts')->from('admin')->select();
//select * from accounts,admin;
$this->model('accounts')->from('admin','a')->select();
//select * from accounts,admin as a
$this->model('accounts')->setFrom('admin','a')->select();
//select * from admin as a;


//join相关函数
//join/leftJoin/rightJoin/innerJoin/fullJoin
//使用方法一样，所以就拿一个join来举例
$this->model('accounts')->join('admin','admin.id=accounts.aid')->select();
//select * from accounts join admin on admin.id=accounts.aid
$this->model('accounts')->join('admin',array(
  'admin.id=accounts.aid',
  'admin.name=accounts.aname'
));
//select * from accounts join admin on admin.id=accounts.aid and admin.name=accounts.aname
$this->model('accounts')->join('admin',array(
  'admin.id=accounts.aid',
  'admin.name=accounts.aname'
),'or');
//select * from accounts join admin on admin.id=accounts.aid or admin.name=accounts.aname
```

更加复杂的sql，union实例

框架提供了一个sql拼装的一个类`framework\core\database\sql`

```php
$a = new framework\core\database\sql();
$b = new framework\core\database\sql();
$c = new framework\core\database\sql();
$a->setFrom('a')->select();
$b->setFrom('b')->select();
$c->setFrom('c')->select();
$a->union(false,$b,$c);
//等价于生成了一个sql
//select * from a union select * from b union select * from c

//接下来就是从配置中查找一个配置
$config = $this->getConfig('db');
//使用制定配置实例化一个mysql连接
$m = mysql::getInstance($config['test']);
//使用这个mysql连接来执行上面的sql
$result = $m->query($a);
```


## 八、Table



特别的，框架中集成了数据表结构变更的类，`\framework\core\database\mysql\table`，这个类目前只支持mysql，对于其他的数据库，目前尚未支持

	$table = this->table('cache');//实例化cache表
	//假如第二个参数为空将会自动在db的配置中查找带default的或者在db配置中指定了model的，指定model的方式优先
	$table = this->table('cache','test');//使用test的db配置来实例化cache表
	if(!$table->exist())//判断数据库表是否存在
	{
		//创建表结构
	  	$table->field('unique_key')->varchar(32)->comment('唯一键');
	  	$table->field('createtime')->datetime()->comment('创建时间');
	  	$table->field('expires')->int()->comment('有效期，0不限制');
	  	$table->field('value')->longtext()->comment('存储的值，seralize后');
	  	//为数据库添加索引
	  	$table->primary()->add('unique_key');
	  	$table->index('createtime')->add(array('createtime','expires'));
	}
table类比较特殊，table类中的所有方法都会更改表的结构，而不会更改表中的数据，由结构变更而导致数据变更的不在此列，

所有的结构变更都支持链式操作，特别注意，所有结构变更有可能会失败，而不会在程序中体现出来。



## 九、Entity

所有的实体都必须在entity目录下，并且继承`framework\core\entity`类，

设想这样一个场景，开发者需要往数据库插入一条数据，而这条数据有100或者更多的列，而这数据在很多地方都需要修改，这是很麻烦的，更或者每一列数据都需要经过大量判断，因为一些数据并不都符合我们插入到数据库的期望。

以用户数据为例,

```php
$test = new test(array(
  'username' => 'jin123',
  'password' => '111',
  'age' => 18,
  'money' => '-1',
  'telephone' => 15868481019,
  'ip' => '255.255.255.4/24',
  'email' => '326550324@qq.com',
  'time' => '2017-05-06 12:12:12',
  'sex' => '男',
));
//验证
if($test->validate())
{
  	//添加到数据库
    $test->save();
}
```

1、entity实例化的时候允许一个数组参数，这个数据参数以key=>value的形式传递，key对应数据库中的键名，value对应要插入的数据，（喜欢偷懒的程序员可以直接使用request::post()方式直接传递一个数据，开发者不必要担心提交的数据，下面会讲到其他的方式来过滤数据、验证数据，以确保数据安全）

2、validate验证器

开发者可以在自定义的entity中自定义__rules方法来验证数据，rules方法返回一个数组，key为验证器，值为验证规则，下面列出了所有支持的验证器和验证方式，



```php
function __rules()
{
  return array(
    //一个验证器，验证多个字段，省略写法
    'required' => array(
      'fields' => 'username,password',
      'message' => '用户名或密码必须填写'
    ),
    // 最标准的写法应该是这样子的，完整写法
    //验证字符串长度的例子，
    '>=' => array(
      'fields' => array(
        'username' => array(
          'render' => 'mb_strlen', // 对参数先经过这个函数 然后在做判断
          'message' => '用户名长度不能低于6位',
          'data' => 6,
          'on' => array(
            'insert'
          ) // 在什么样的情景下才可用，默认是所有情景，可以数组，多个情景 也可以是逗号分开的字符串
        ),
        'age' => array(
          'message' => '年龄必须大于等于18岁',
          'data' => 18
        )
      )
    ),
    //不安全验证器  required相反
    'unsafe' => array(
      'fields' => 'sql', // 提交的参数中不能包含sql这个参数
      'message' => '提交不能存在sql参数'
    ),
    //验证码
    'captcha' => array(
      'fields' => 'code',
      'message' => '验证码错误',
    ),
    //整型验证
    'int' => array(
      'fields' => 'age',
      'message' => '年龄必须是整数'
    ),
    //数字验证器
    'decimal' => array(
      'fields' => array(
        'money' => '金额必须是数字'
      )
    ),
    //数据唯一验证器
    'unique' => array(
      'telephone' => array(
        'message' => '手机号码必须唯一'
      )
    ),
    //手机号码验证器
    'telephone' => array(
      'telephone' => '请填写一个正确的手机号码'
    ),
    //IP地址验证器
    'ip' => array(
      'ip' => 'IP错误'
    ),
    //邮箱验证器
    'email' => array(
      'email' => 'email错误'
    ),
    //枚举类型验证器
    'enum' => array(
      'fields' => array(
        'sex'
      ),
      'data' => array(
        '男',
        '女'
      )
    ),
    //日期验证器
    'datetime' => array(
      'fields' => 'time',
      'data' => 'Y-m-d H:i:s', // 这个是可选的 默认为Y-m-d H:i:s
      'message' => array(
        '时间错误'
      )
    ),
    'function' => array( // 自定义函数验证器
      'fields' => 'relations',
      'render' => function ($val) { // $val => $this->_data['relations']
        return '123';
      },
      'message' => '自定义{field}函数校验失败,它的值为{value}',
      'callback' => function ($val) {
        return true; // 返回true或者false
      }
    )
  );
}
```

除此之外验证器还支持

 `>=` 等同于 `ge`

`<=`等同于`le`

`!=`等同于`ne`

`<`等同于`lt`

`>`等同于`gt`

`=`等同于`eq`

所有的验证器在`framework\core\validator`类中，开发者也可以通过injection这个类来增加其他的验证器



验证器的标准写法应该如下

```php
'验证器名称' => array(
      'fields' => array(
        '字段名1' => array(
          'render' => '函数名（可选）',
          'message' => '错误消息（必须，没有的话虽然可以使用但是会报错）',
          'data' => 验证器的额外参数（可选）,
          'on' => 情景（可选）。数组或者,号分开的字符串
        ),
        '多个字段名' => array(
          'message' => 错误消息,
        )
      )
    ),
```

其他的写法都是简写形式



validate方法返回boolen值，当为true的时候说明验证通过，false的话验证失败，当验证失败的时候可以通过`getError`方法来获取错误信息

```
if(!$test->validate())
{
  	//获取错误信息
    $errors = $test->getError();
}
```



回到之前的话题，当数据验证通过之后，数据需要被保存起来，比如password，通常password服务器端接受到的是明文，而数据库中需要保存密文，也就是说期望在保存之前经行一次加密，这个时候可以在自定义的entity中重写__preInsert函数来实现

```php
/**
  * 执行插入之前执行的函数
  * 这个函数可以返回false,
  * 当返回false的时候则中断添加操作，并且save函数假如是insert的时候返回false
  * 返回0或者NULL不会认为是false
  */
function __preInsert()
{
  $this->_data['password'] = md5($this->_data['password']);
}
```

类似的，还有`__afterRemove`,`__preRemove`,`__preUpdate`,`__afterUpdate`,`__afterInsert`函数

3、save函数

save函数是一个集成函数，它会通过是否存在主键来自动判断是保存还是删除，默认情况下id字段是主键，假如数据库中实际上用了其他的主键，可以通过在entity中自定义`__primaryKey`函数来声明其他主键名称。

```
/**
 * 默认的情况下使用ID字段作为主键
 * 
 * @return string
 */
function __primaryKey()
{
	return 'id';
}
```



调用save，remove，findByField等方法的时候，数据需要从数据库中获取，因此，entity中实际上有一个内置的model，这个model是通过类名来找到的，因此通常情况下，model，entity和数据库中的表名是一致的，比如数据库的表叫user，开发者又定义了myuser的model，而开发者定义的entity叫accounts的话，这个时候需要在entity中自定义`__model`方法，并且返回实际上的model的名称（返回model的名称而不是数据库的名称，这里返回myuser，而又需要在myuser的model中定义`__tableName`方法并返回数据库名）

4、多表添改

entity不仅仅可以添加实体类型的数据，还可以添加关系型的数据，比如商品，商品分类，分类。

商品是一个数据表，分类也是一个数据表，商品分类是关系表，当添加商品的时候如何给商品分类表同时也添加一个条数据呢？

假设商品表结构

| 名字   | 类型          |      |
| ---- | ----------- | ---- |
| id   | int         | 主键   |
| name | varchar(32) |      |

假设关系表结构

| 名字   | 类型   |      |
| ---- | ---- | ---- |
| pid  | int  | 商品id |
| cid  | int  | 分类id |

商品和分类多对多的关系

插入的代码为

```
$product = new product(array(
	'name' => '商品名称',
	'category' => array(
		10,20
	)
));

$product->save();
```

在entity中定义`__relation`函数，并且返回如下的实例

```
	function __relation($field, $primaryKey, $data)
	{
		if ($field == 'category')
		{
			$insert = array();
			foreach ($data as $d)
			{
				$insert[] = array(
					'pid'=> $primaryKey,
					'cid' => $d,
				);
			}
			
			return array(
				'product_category' => array(
					'insert' => $insert,
					'delete' => array(
						'pid' => $primaryKey,
					),
				),
			);
		}
	}
```

代码执行完毕后，可以看到商品表以及关系表中的数据，商品表新增一条数据，关系表新增2条数据，



> save函数内部实现是基于事务的。
>
> save的更新操作实际上是先删除在添加，因此关系表最好不要存在自增主键，



十、模板引擎

framework框架内置了一种模板引擎，你可以通过返回一个`framework\core\view`对象，这个对象有2个参数，第一个模板名称，第二位布局文件夹名称。模板必须统一放在应用程序目录的template文件夹下面，template文件夹下面一级是布局文件夹名称，布局之下才是模板名称

例如：

```php
template -  					#template文件夹
	layout -					#布局文件名称
		index-					#自定义的目录
			index.html			#模板文件名称
		product-				#自定义目录
			index.php			#模板文件名称
```

要在页面中显示模板可以这样

```php
return new view('test/page.html');
//假如布局文件名为layout的话也可以这样
return new view('test/page.html','layout');
```



布局文件的名称可以在view的配置文件中修改

