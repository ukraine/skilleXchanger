<?

error_reporting(0);

/*----------- БД ------------------------------- */

$connection = mysql_connect("localhost", 
                            "yyifua_note", 
                            "3!c0-r;hgC8S");



/*----------- БД иницилизация -------------------*/

mysql_query("SET NAMES utf8");
mysql_select_db("yyifua_note", $connection);



/*----------- Поключение служебных файлов ------ */

// Функции
include "lib.php";

// Базовые функции обработки запросов MySQL
include "mysql.func.php";


/*----------- Установка базовых переменных -------*/

$title = array("default"=>"Список пользователей","addedit"=>"Добавить информацию о себе");
$forums = array("clubnote"=>"http://www.clubnote.ru/forum/index.php","nissannoteinfo"=>"http://www.nissan-note.info/forum/index.php");
$mdistrict = array("ЗАО","ВАО","Зеленоград","САО","СВАО","СЗАО","ЮВАО","ЦАО","ЮЗАО","ЮАО");
$itemlimitation = array("5", "10","20", "30", "50", "100");
$ButtonNames = array("add" => "Добавить", "edit" => "Редактировать");



/*----------- ИНИЦИАЛИЗАЦИЯ --------------------- */

// Определяем, какую страницу будет сейчас открывать
if (!empty($_REQUEST['action'])) $action = clearHTML($_REQUEST['action']);

// Получаем данные о последовательности сортировки
if (!empty($_GET['ascdesc'])) $ascdesc = clearHTML($_GET['ascdesc']);

// Получаем данные по какому полю сортировать
if (!empty($_GET['sortby'])) $orderby = clearHTML($_GET['sortby']); 

// Получаем данные по какому полю сортировать
if (!empty($_GET['limit'])) $limit = intval(clearHTML($_GET['limit'])); 

// Получаем адрес сайта и параметры запросов _GET
$siteurl = "http://".$_SERVER['SERVER_NAME'].str_replace("index.php","",$_SERVER['PHP_SELF'])."?".$_SERVER['QUERY_STRING'];

define("SITEURL","http://".$_SERVER['SERVER_NAME'].str_replace("index.php","",$_SERVER['PHP_SELF']));

// Знак для сортировки
if (!empty($_SERVER['QUERY_STRING'])) $sign = "&";

/*----------- Обработка -------------------------- */

// Старт сессии
session_start();

// Обработка входящих параметров и генерация страницы
include "default.php";

/*----------- Дебаггинг ------------------------

echo session_cache_expire();
echo session_cache_limiter();
print_r($_SESSION);
print_r($_POST);
print_r($_GET);

*/

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Страница бизнес-контактов владельцев Nissan Note (v.1.0 beta)</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="./_mad.css" type="text/css">
	<script language="javascript" type="text/javascript" src="./img/mm.js"></script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-59846622-10', 'auto');
  ga('send', 'pageview');

</script>

</head>
<body bgcolor="#FFFFFF" text="#000000" class="<? echo $action; ?>">

<div class="demover">

	<div id="loading">Выполняю...</div>
	&nbsp; Бизнес-контактамы владельцев Nissan Note. Меняемся своими контактами здесь! <!-- <b style='color: #CC0000; background-color: white; padding: 2px 5px;' >Кстати, автор системы принимает заказы на создание сайтов, систем управления сайтом и пр. 8-499-501-654-1. Юрий</b> -->

	<? 
	
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!== "yes") { ?>

	<div id="loginbox">



		<form method="POST">
			
			<div style="color: red;"><b><? echo @$loggedintext; ?></b></div>
			
			<TABLE width="178">
			  <TR> 
				<TD width="58">Ваш емейл </TD>
				<TD> 
				  <input class='auto' type='text' name='email'>
				</TD>
			  </TR>
			  <TR> 
				<TD>Пароль</TD>
				<TD> 
				  <input class='auto' type='password' name='password'>
				</TD>
			  </TR>
			  <TR>
				<TD>&nbsp;</TD>
				<TD>
				  <input type='hidden' name="action" value='do_login'>
				  <input class='auto' type='submit' value='Войти' name="submit">
				</TD>
			  </TR>
			</TABLE>
		</form>

	</div>

		<? } else { ?>

		<div style='position: absolute; text-align: right: top: 3px; right: 3px;'>Приветствую, <B><? echo $_SESSION['nickname']; ?></B>! &nbsp; 
		&middot; <a href="./?action=view&id=<? echo $_SESSION['id']; ?>">Мой профиль</a>
		&middot; <a href="./?action=edit&id=<? echo $_SESSION['id']; ?>">Редактировать</a>
		&middot; <a href="./?action=logout">Выйти</a></div>

		<? } ?>

</div>

<div class="header">
		
	<div class="subheader">

		<a href="./<? if (!empty($limit)) echo "?limit=$limit"; ?>"			<? SubHeaderTabsHighlight('default'); ?>>Список</a>
		<a href="./?action=add"												<? SubHeaderTabsHighlight('addedit'); ?>>Добавить</a>
		<a href="./?action=stats"											<? SubHeaderTabsHighlight('stats'); ?> style="margin-left: 45px;">Статистика</a>
	</div>

</div>

<h2><?=$title; ?></h2>

<!-- Content -->
<div class="content">

 	<table width="1210px">
		<tr>

			<td valign="top">
			<?

				errorMsg();
				@include $action.".html";

			?>

			</td>
		</tr>
	</table>

</div>
<!-- Content -->

<div class="demover" style="border-top: 1px solid #ccc;">

	&nbsp; Система управления бизнес-контактами владельцев Nissan Note (v.1.0 beta) &nbsp; 2008 (c)<a href="./?action=view&id=313" style="text-decoration: underline;">Яцив Юрий</a> - <a href="javascript:void(0)" onclick="openURL('313')" style="text-decoration: underline;">Написать письмо автору</a> - &nbsp; Аська службы поддержки 699199


</div>

</body>
</html>