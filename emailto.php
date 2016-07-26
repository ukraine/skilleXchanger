<?

// Функции
include "lib.php";

// Функции
include "default.php";

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Страница бизнес-контактов владельцев Nissan Note (v.1.0 beta)</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="./_mad.css" type="text/css">
	<script language="javascript" type="text/javascript" src="./img/mm.js"></script>
</head>
<body bgcolor="#FFFFFF" text="#000000">

	<h2>Написать пользователю письмо:</h2>


<!-- Content -->
<div class="content">

<p>Обязательные к заполнению поля помечены красной зведочкой <span class='requiredfield'>*</span></p>

<form method="post" action="" style="" class="emaform">
        
			<? 
			GenerateInputTagForEmailSending("name","Ваше имя");
			GenerateInputTagForEmailSending("email","Адрес вашей электропочты");
			GenerateInputTagForEmailSending("subject","Тема сообщения");
			echo "<br>";
			GenerateTextAreaTag4EmalSedning("content","Краткое сообщение пользователю");
			?>
			<B>Внимание:</B> ссылки из текста сообщения будут удалены автоматом

			<div><br><input type="submit"					value="Отправить письмо">
			<input type="hidden"	name="action"	value="do_sendemail">			
			</div>


</div>

<div class="demover" style="border-top: 1px solid #ccc;">

	&nbsp; 2008 (c)<a href="./?action=view&id=313" style="text-decoration: underline;" target="_parent">Яцив Юрий</a> - <a href="mailto:le@ifstudio.org" style="text-decoration: underline;">Написать мне письмо</a>

</div>

</body>
</html>