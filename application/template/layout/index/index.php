<html>
<body>
{%import file='index/header.php'%}

{%(6*strlen($name1.'123'.'456'))+6%}
{%strtoupper(strtolower($name3))%}

{%section from=[1,2,3] key=key value=value%}
今天是个好天气啊1{%$value%}
{%/section%}


{%section from=[1,2,3] key=key value=value%}
今天是个好天气啊2{%$value%}
{%/section%}

</body>
</html>