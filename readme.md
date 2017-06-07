# framework3.0 document

## 一、Installation

## 二、Request

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