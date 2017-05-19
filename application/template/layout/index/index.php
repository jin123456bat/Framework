<html>
<body>
<!-- tag的支持 -->
{%import file='index/header.php'%}<style type="text/css">
h1 {color:red}
p {color:blue}
</style><!-- 直接访问数组中的内容 -->
{%$array.name.firstname%}

<!-- 静态函数 -->
{%\framework\vendor\csrf::token()%}

<!-- 复杂的表达式和函数 -->
{%(6*strlen($name1.'123'.'456'))+6%}
{%strtoupper(strtolower($name3))%}
{%((4+5)/(1+2))*(3*1)%}


<!-- section的嵌套 -->
{%section from=$name value=persion%}

	大家好，我是{%$persion%}
	
		{%section from=$fruit value=what%}
		
		{%$persion%}现在正在吃{%$what%}
		
		{%/section%}
	
	{%$persion%}已经吃完了

{%/section%}

<!-- section的支持 -->
{%section from=$name%}
{%$value%}
{%/section%}

<!-- 不存在的变量直接输出空 -->
{%$asdfasdfadf%}

<!-- if的支持  语言构造器的支持 -->
{%if !empty($name)%}
name不是空的
{%/if%}

<!-- 结构语句支持 -->
{%if empty($abc)%}
$abc是空的
{%else%}
$abc不是空的
{%/if%}

<!-- elseif的支持 -->
{%if $name1==1%}
$name1是1
{%elseif $name1==2%}
$name1是2
{%else%}
$name1是其他值
{%/if%}

</body>
</html>