<?
session_start();

// ini_set('display_errors', on);
// error_reporting(E_ALL);

/*----------- БД ------------------------------- */

$connection = mysql_connect("localhost", 
                            "v1tr0ff_skillex", 
                            "QED54urIiA");

/*----------- БД иницилизация -------------------*/

mysql_query("SET NAMES utf8");
mysql_select_db("v1tr0ff_skillex", $connection);



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

/*----------- авторизация через социальные сети -------*/

// вконтакте
$vk_client_id = ''; // ID приложения
$vk_client_secret = ''; // Защищённый ключ
$vk_redirect_uri = 'http://skillex.nemovlyatko.com/'; // Адрес сайта

$fb_client_id = ''; // Client ID
$fb_client_secret = ''; // Client secret
$fb_redirect_uri = 'http://skillex.nemovlyatko.com/'; // Redirect URIs

// LinkedIn
$li_client_id = ''; // Client ID
$li_client_secret = ''; // Client secret
$li_redirect_uri = 'http://skillex.nemovlyatko.com'; // Redirect URIs

// авторизация линкедин
$state_li = generateRandString(32);

$authUrlLinkedIn = 'https://www.linkedin.com/uas/oauth2/authorization?'.(http_build_query(array(
    'state' => $state_li,
    'scope' => '', 
    'response_type'   => 'code',
    'approval_prompt' => 'auto',
    'client_id'   => $li_client_id,
    'redirect_uri' => $li_redirect_uri
), null, '&'));

if (isset($_GET['code']) && 
    isset($_GET['state']) && 
    !empty($_GET['state']) && 
    isset($_SESSION['oauth2state']) &&     
    $_GET['state'] === $_SESSION['oauth2state']) {
        
    $postdata = http_build_query(
        array(
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'redirect_uri'  => $li_redirect_uri, 
            'client_id'     => $li_client_id,
            'client_secret' => $li_client_secret
        ), null, '&'
    );
    
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'Host'  => 'www.linkedin.com',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    
    $context  = stream_context_create($opts);
    
    $result = file_get_contents('https://www.linkedin.com/uas/oauth2/accessToken', false, $context);
    
    $result = json_decode($result);
    
    
    if (is_object($result) && isset($result->access_token)) {
        $token = $result->access_token;
                  
        try {
            $params=array();
	    	$fields = array('id', 'email-address', 'first-name', 'last-name', 'headline',
                            'location', 'industry', 'picture-url', 'public-profile-url');
	    	$request = join(',',$fields);
		    $params['url'] = "https://api.linkedin.com/v1/people/~:({$request})";
	    	$params['method']='get';
		    $params['args']['format']='json';
        
            $params['headers'] = array(
                'Authorization' => 'Bearer '.$token
            );
        
            $method=isset($params['method'])?$params['method']:'get';
            $headers = isset($params['headers'])?$params['headers']:array();
            $args = isset($params['args'])?$params['args']:'';
            $url = $params['url'];

            if($method=='get'){
                $url.='?'.preparePostFields($args); 
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
            if(is_array($headers) && !empty($headers)){
                $headers_arr=array();
                foreach($headers as $k=>$v){
                    $headers_arr[]=$k.': '.$v;
                }    
                curl_setopt($ch,CURLOPT_HTTPHEADER,$headers_arr);
            }
            $result = curl_exec($ch);
            curl_close($ch);
            
            $user = json_decode($result,true); 
            
            if ($user && is_array($user) && !empty($user)) {
                $socialemail=isset($user['emailAddress']) ? $user['emailAddress'] : null;
                $socialID=isset($user['id']) ? $user['id'] : null;
                $socialname=isset($user['firstName']) ? $user['firstName'] : null;
                $socialname2=isset($user['lastName']) ? $user['lastName'] : null;
                $social_url_li=isset($user['publicProfileUrl']) ? $user['publicProfileUrl'] : null;
            
                // проверка есть ли пользователь в базе
                $result_soctop = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email = '$socialemail'");
                if (@mysql_num_rows($result_soctop) == 0) 
                { // нет пользователя
                    srand((double)microtime()*1000000);
                    $code_soc=md5(uniqid(rand()));
                    $code_soc=substr($code_soc,1,12);
                    $result_top1=mysql_query("insert into `".PREFIX."persons` (email,password,name,nickname,social_url_li) values ('$socialemail','$code_soc','$socialname $socialname2','$socialname','$social_url_li')");
                
                    $resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
                    while ($myrow=mysql_fetch_array($resultaut1)) {
                        $_SESSION['loggedin'] = "yes";
                        $_SESSION['id']=$myrow["id"];
                        $_SESSION['nickname']=$myrow["nickname"];
                        $loggedin = "yes";
                        $id=$myrow["id"];
                        $nickname=$myrow["nickname"];
                    }
            
                } // нет пользователя
            
                if (@mysql_num_rows($result_soctop) != 0) 
                { // есть пользователь
                    $resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
                    while ($myrow=mysql_fetch_array($resultaut1)) {
                        $_SESSION['loggedin'] = "yes";
                        $_SESSION['id']=$myrow["id"];
                        $_SESSION['nickname']=$myrow["nickname"];
                        $loggedin = "yes";
                        $id=$myrow["id"];
                        $nickname=$myrow["nickname"];
                    }
            
                } // есть пользователь
            
                header("Location: $li_redirect_uri");
        
            }
        } catch (Exception $e) {
            header("Location: $li_redirect_uri");
        }   
        
    }    
}

$_SESSION['oauth2state'] = $state_li;

// авторизация вконтакте
if (isset($_GET['code']) and $_GET['state'] == 'vk') {

    $vk_url = 'http://oauth.vk.com/authorize';

    $params = array(
        'client_id'     => $vk_client_id,
        'redirect_uri'  => $vk_redirect_uri,
        'response_type' => 'code'
    );

    $result = false;
    $params = array(
        'client_id' => $vk_client_id,
        'client_secret' => $vk_client_secret,
        'code' => $_GET['code'],
        'redirect_uri' => $vk_redirect_uri
    );

    $token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);

    if (isset($token['access_token'])) {
        $params = array(
            'uids'         => $token['user_id'],
            'email'         => $token['email'],
            'fields'       => 'uid,first_name,last_name,email,screen_name,sex,bdate,photo_big',
            'access_token' => $token['access_token']
        );

        $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
        if (isset($userInfo['response'][0]['uid'])) {
            $userInfo = $userInfo['response'][0];
            $result = true;
        }
    }

    if ($result) {

$socialemail=$token['email'];
$socialID=$userInfo['uid'];
$socialname=$userInfo['first_name'];
$socialname2=$userInfo['last_name'];
//$socialname=iconv('utf-8','windows-1251',$socialname);
//$socialname2=iconv('utf-8','windows-1251',$socialname2);
//if ($socialemail == '') {$socialemail=$socialID;}

//echo "Социальный ID пользователя: " . $userInfo['uid'] . '<br />';
//echo "Email: " . $socialemail . '<br />';
//echo "Имя: $socialname";

// проверка есть ли пользователь в базе
$result_soctop = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email = '$socialemail'");
if (@mysql_num_rows($result_soctop) == 0) 
{ // нет пользователя
srand((double)microtime()*1000000);
$code_soc=md5(uniqid(rand()));
$code_soc=substr($code_soc,1,12);
$result_top1=mysql_query("insert into `".PREFIX."persons` (email,password,name,nickname) values ('$socialemail','$code_soc','$socialname $socialname2','$socialname')");

$resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
while ($myrow=mysql_fetch_array($resultaut1)) {
$_SESSION['loggedin'] = "yes";
$_SESSION['id']=$myrow["id"];
$_SESSION['nickname']=$myrow["nickname"];
$loggedin = "yes";
$id=$myrow["id"];
$nickname=$myrow["nickname"];
}

} // нет пользователя

if (@mysql_num_rows($result_soctop) != 0) 
{ // есть пользователь
$resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
while ($myrow=mysql_fetch_array($resultaut1)) {
$_SESSION['loggedin'] = "yes";
$_SESSION['id']=$myrow["id"];
$_SESSION['nickname']=$myrow["nickname"];
$loggedin = "yes";
$id=$myrow["id"];
$nickname=$myrow["nickname"];
}

} // есть пользователь

header("Location: $vk_redirect_uri");

    }
}
// авторизация вконтакте

// авторизация facebook
if (isset($_GET['code']) and eregi('fb',$_GET['state'])) {

    $result = false;

    $params = array(
        'client_id'     => $fb_client_id,
        'redirect_uri'  => $fb_redirect_uri,
        'client_secret' => $fb_client_secret,
        'code'          => $_GET['code']
    );

    $fb_url = 'https://graph.facebook.com/oauth/access_token';

    $tokenInfo = null;
    parse_str(file_get_contents($fb_url . '?' . http_build_query($params)), $tokenInfo);

    if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
        $params = array('access_token' => $tokenInfo['access_token']);

        $userInfo = json_decode(file_get_contents('https://graph.facebook.com/me' . '?fields=email,name&' . urldecode(http_build_query($params))), true);

        if (isset($userInfo['id'])) {
            $userInfo = $userInfo;
            $result = true;
        }
    }

    if ($result) {

//echo "Социальный ID пользователя: " . $userInfo['id'] . '<br />';

$socialemail=$userInfo['email'];
$socialID=$userInfo['id'];
$socialname=$userInfo['name'];
//$socialname=iconv('utf-8','windows-1251',$socialname);
if ($socialemail == '') {$socialemail=$socialID;}

//echo "ID: $socialID<br>Имя: $socialname $socialname2<br>Email: $socialemail<br>$socialbirth";

// проверка есть ли пользователь в базе
$result_soctop = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email = '$socialemail'");
if (@mysql_num_rows($result_soctop) == 0) 
{ // нет пользователя
srand((double)microtime()*1000000);
$code_soc=md5(uniqid(rand()));
$code_soc=@substr($code_soc,1,12);
$result_top1=mysql_query("insert into `".PREFIX."persons` (email,password,name,nickname) values ('$socialemail','$code_soc','$socialname $socialname2','$socialname')");

$resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
while ($myrow=mysql_fetch_array($resultaut1)) {
$_SESSION['loggedin'] = "yes";
$_SESSION['id']=$myrow["id"];
$_SESSION['nickname']=$myrow["nickname"];
}
} // нет пользователя

if (@mysql_num_rows($result_soctop) != 0) 
{ // есть пользователь
$resultaut1 = @mysql_query("SELECT * FROM `".PREFIX."persons` WHERE email='$socialemail'");
while ($myrow=mysql_fetch_array($resultaut1)) {
$_SESSION['loggedin'] = "yes";
$_SESSION['id']=$myrow["id"];
$_SESSION['nickname']=$myrow["nickname"];
}
} // есть пользователь

header("Location: $fb_redirect_uri");

    }

}
// авторизация facebook


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
	<title>Skillex - биржа скиллов и навыков с открытыми данными</title>
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
	"Биржа" скиллов и навыков для резидентов очки и заочки ФРИИ, а также бизнес инкубатора Вышки
	<? 
	
	if (!isLoggedIn()) { ?>

	<div id="loginbox" style='width: 378px'>



		<form method="POST">
			
			<div style="color: red;"><b><? echo @$loggedintext; ?></b></div>
			
			<TABLE width="100%">

			<!--   <TR> 
				<TD align='right'></TD>
				<TD> 
				  <input class='auto' type='text' name='email' placeholder="Ваш емейл"> 				  <input class='auto' type='password' name='password' placeholder="Ваш пароль">

				  <input type='hidden' name="action" value='do_login'>
				  <input class='auto' type='submit' value='Войти' name="submit">
				</TD>
			  </TR> -->

<tr><td></td>
<td align=right style='vertical-align: center'>

<?


echo $link = '<a href="' . $authUrlLinkedIn . '" class="login_li">Войти через<br> LinkedIn</a>';

// авторизация вконтакте

    $vk_url = 'http://oauth.vk.com/authorize';

    $params = array(
        'client_id'     => $vk_client_id,
        'redirect_uri'  => $vk_redirect_uri,
        'response_type' => 'code',
        'state' => 'vk'
    );

    echo $link = '<a href="' . $vk_url . '?' . urldecode(http_build_query($params)) . '&scope=email" class="login_vk">Войти через вКонташу</a><!--<img src=img/socauth_vk.png border=0>-->';

// авторизация вконтакте

// авторизация facebook

$fb_url = 'https://www.facebook.com/dialog/oauth';

$params = array(
    'client_id'     => $fb_client_id,
    'redirect_uri'  => $fb_redirect_uri,
    'state'         => 'fb',
    'response_type' => 'code',
    'scope'         => 'email'
);

echo $link = '<a href="' . $fb_url . '?' . urldecode(http_build_query($params)) . '" class="login_facebook">Войти через Facebook</a><!--<img src=img/socauth_vk.png border=0>-->';
// авторизация facebook
?>

</td></tr>

			</TABLE>
		</form>

	</div>

		<? } else { ?>

		<div style='position: absolute; text-align: right; top: 9px; right: 3px;'><!-- Приветствую, <B><? echo $_SESSION['nickname']; ?></B>! &nbsp; -->
		&middot; <a href="./?action=view&id=<? echo $_SESSION['id']; ?>">Мой профиль</a>
		&middot; <a href="./?action=edit&id=<? echo $_SESSION['id']; ?>">Редактировать</a>
		&middot; <a href="./?action=logout">Выйти</a></div>

		<? } ?>

</div>

<div class="header">

<div style='padding: 10px'><a href='/'><img src='/img/logo-skillex.png'></a></div>

<!-- <div style='font-size: 120%; padding-top: 120px; padding-left: 15px;'>Skillex позволяет основателям найти друг друга по недостающим компетенциям, а также не потерять контакты после прохождения программ в очке и заочке ФРИИ и Бизнес инкубатора вышки</div>-->
		
	<!-- <div class="subheader">

		<a href="./<? if (!empty($limit)) echo "?limit=$limit"; ?>"			<? SubHeaderTabsHighlight('default'); ?>>Список</a>

<?	if (!isLoggedIn()) { ?>

		<a href="./?action=add"												<? SubHeaderTabsHighlight('addedit'); ?>>Добавить</a>

		<? } ?>

	<a href="./?action=stats"											<? SubHeaderTabsHighlight('stats'); ?> style="margin-left: 45px;">Статистика</a>
	</div>-->

</div>

<!-- <h2><?=$title; ?></h2> -->

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

	&nbsp; Skillex - биржа скиллов и навыков с открытыми данными (v.2.0 beta) &nbsp; 2008, 2016 (c) <a href="./?action=view&id=1" style="text-decoration: underline;">Ярцев Юрий</a> - <a href="javascript:void(0)" onclick="openURL('1')" style="text-decoration: underline;">Написать письмо автору</a>

</div>

</body>
</html>
