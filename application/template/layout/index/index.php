<html>
<body>
<!-- tag的支持 -->
{%import file='index/header.php'%}

<!-- 直接访问数组中的内容 -->
{%$array.name.firstname%}

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

{%if isset($abc)%}
$abc存在
{%else%}
$abc不存在
{%/if%}

</body>
</html>