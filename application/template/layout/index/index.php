<html>
<body>
{%import file='index/header.php'%}

{%(6*strlen($name1))+6%}
{%strtoupper(strtolower($name3))%}

{%section from=[1,2,3]%}
今天是个好天气啊
{%/section%}


{%section from=[1]%}
明天是个坏天气
{%/section%}

</body>
</html>