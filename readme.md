# framework3.0 document

## 前言

好的编程习惯将会减少很多弯路，虽然在框架中部分地方声明并且实现了gbk或者gb2312编码，但是依然我们希望使用utf8编码来完成整个工作，考虑到utf8的通用性和以后的移植甚至对接的时候将减少很多工作量，甚至内部的phpdoc和默认的编码全部都是utf8。

文档中涉及到很多命名空间和目录，我们会在命名空间中使用`\`符号来分割，而在路径中使用`/`符号来分割

## 一、Installation

### 1、环境以及配置说明

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

## 三、Response

## 四、Router

## 五、Control