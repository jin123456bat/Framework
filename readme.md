# framework3.0 document

## 前言

好的编程习惯将会减少很多弯路，虽然在框架中部分地方声明并且实现了gbk或者gb2312编码，但是依然我们希望使用utf8编码来完成整个工作，因为utf8是国际通用的，考虑到以后的移植或者对接的时候将减少很多工作量。

文档中涉及到很多命名空间和目录，我们会在命名空间中使用`\`符号来分割，而在命名空间中使用`/`符号来分割

## 一、Installation

## 二、Injection

​	依赖注入，程序在整个运行当中需要加载不同类，这些类基本上都已经在`\framework\`的命名空间下，当我们想要去修改框架的运行方式或者行为的时候，我们不需要去修改源代码，框架已经提供了一种更好的方式来实现它。

​	我们可以在应用程序的extends目录中创建一个同名的class，而唯一不同的是命名空间`/应用程序名称/extends/类名`，比如`/application/extends/filter`，系统的一些行为可能会加载过滤器并调用过滤器中的一些方法，当我们实现`/application/extends/filter`类并且继承与`/framework/core/filter`类的时候系统实际上加载的是`/application/extends/filter`类，而我们这时候可以在/application/extends/filter类中重载默认的函数或者增加其他的函数供系统调用。

## 三、Request

​	在framework中所有请求都是一个request，包括http请求，websocket请求

​	Request存在于`\framework\core\request`空间下
你可以通过以下的一些方式获取get参数



```php
name=123
var_dump(request::get('name'));//123
var_dump(request::get('name',333));//123  不存在的时候333
var_dump(request::get('name',NULL,'strlen'));//3   使用过滤器
var_dump(request::get('name',NULL,'strlen|explode:",","?"'));//array(3) 使用多个过滤器以及如何在过滤器中增加参数
var_dump(request::get('name',NULL,NULL,'a'));//array(123);  使用强制变量转换
```



​	有一点要值得说明，变量强制转换的优先级是最低的，也就是说，假如过滤器返回了一个string类型的变量，而变量强制转换设定的为a，那么返回的依然是array



​	通过|分割开不同的过滤器，过滤器从左到右依次过滤



​	在过滤器的后面通过:声明过滤器的参数部分，具体的每一个参数都必须用单引号或双引号来包裹，至于两个参数中使用什么符号来分割，完全取决于你，甚至不使用也可以



​	在过滤器中使用?号代表上一个值在参数中的位置

## 三、Response

## 四、Router

## 五、Control