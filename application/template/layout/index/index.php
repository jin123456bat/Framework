<html>
<body>
{%import file='index/header.php'%}


{%$array.name.firstname%}

{%(6*strlen($name1.'123'.'456'))+6%}
{%strtoupper(strtolower($name3))%}
{%((4+5)/(1+2))*(3*1)%}

{%section from=$name value=persion%}

	大家好，我是{%$persion%}
	
		{%section from=$fruit value=what%}
		
		{%$persion%}现在正在吃{%$what%}
		
		{%/section%}
	
	{%$persion%}已经吃完了

{%/section%}

{%section from=$name%}
{%$value%}
{%/section%}

</body>
</html>