<html>
<body>
{%import file='index/header.php'%}


{%$array.name.firstname%}

{%(6*strlen($name1.'123'.'456'))+6%}
{%strtoupper(strtolower($name3))%}


{%section from=$name value=persion%}

{%section from=$fruit value=what%}
{%$persion%}吃{%$what%}
{%/section%}

{%/section%}

{%section from=$name%}
{%$value%}
{%/section%}

</body>
</html>