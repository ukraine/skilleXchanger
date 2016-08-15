<?

// Очистка входящих параметров $_GET
// 20.08.2008
function clearHTML($variable) {
	return htmlspecialchars(strip_tags(trim($variable)));
}

// Проверка залогинености
// 19.10.2008
function isLoggedIn() {

	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!== "yes") return false;
	else return true;

}

// Вывод сообщения об ошибке
function ErrorMsg () {
	global $error_msg; if (!empty($error_msg))	echo "<div class='error_msg'>$error_msg</div>"; 
}

// Автоматически выбирать требуемые поля тега select
// 25.08.2007
function selectv2($field, $number)	{
	global $f; if($f[$field] == $number) return " selected";
}

// Выполняем запрос и получаем массив с требуемыми данными
// Используем только если нужна только одна строка один массив
// 20.08.2008
function ExecuteSqlGetArray($sql) {
	
	// echo $sql; // Дебаггинг запроса
	return mysql_fetch_array(mysql_query($sql));

}

// Отправка почты с заменой заголовков
// 
function sendmail ($str="")	{

	global $ForbiddenChars, $AllowedChars, $f;

	$error_msg = "Ошибка отправления. Попробуйте позже или сдавайтесь";

	// Зачистка от спама
	if (empty($_POST['message'])) {
	
	foreach($_POST as $key=>$val)
		{

		if  ($key!=="dosometh" 
			&& $key!=="Submit" 
			&& $key!=="action" 
			&& $key!== "message" 
			&& $key!=="PHPSESSID"
			&& $key!=="submit"
			&& $key!=="subject"
			&& $key!=="page_path"
			&& $key!=="cat_path"
			&& !empty($key))
		$str.= "$key: ".clearHTML($val)." \n";
		}

	
	

	$text  = "Здравствуйте $f[name]!\nВы получили сообщение от $_POST[yourname] ($_POST[email]):\n---------------\n\n";
	$text .= "Текст сообщения: " . $_POST['content'];
	$text .= "\n\n---------------\nIP адрес отправителя: ".$_SERVER['REMOTE_ADDR'];

	$headers =
		"From: $_POST[yourname] <$_POST[email]>\r\n" .
		"Bcc: yuriy.yatsiv@gmail.com\r\n" .
		"MIME-version: 1.0\n" .
		"Content-type: text/plain; charset=\"UTF-8\"";


	if(mail($_POST['rcpt'], $_POST['subject'], $text, $headers)) $error_msg = "Сообщение успешно отправлено";

	}
	
	return $error_msg;
}

// Проверка правильности заполнения полей
// 17.07.2007
function IsRequiredFieldsFilled($RequiredFielsArray) {

	global $error_msg;

		foreach($RequiredFielsArray as $key=>$value)	{

			if (empty($_POST[$key]) && $key !== "email") $error_msg .= "Поле \"<B>$value</B>\" не заполнено. <br>";
			if ($key == "email" && !preg_match("/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/", $_POST[$key])) $error_msg .= "Поле \"<B>$value</B>\" не заполнено. <br>";

				
		}

			if (empty($error_msg)) return 1;
			else return 0;
}

// Получить значение общего кол-ва объектов в таблице при определенном условии
function GetTotalData ($table, $more="") {

	// echo "Running SQL";

	$res = RunQueryReturnDataArray($table, $more, $column="COUNT(*)");
	return $res['COUNT(*)'];
}


// Выборки из БД. Если есть значения, то возвращаем их, если нет, то возвращаем bool об ошибке
// Используем только если нужна только одна строка один массив
// 12.10.2007
function RunQueryReturnDataArray ($table, $more="", $column="*") {

	$sql = "SELECT $column FROM `$table` $more";
	// echo $sql; // Дебаггинг запроса
	return mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);

}


// Если значение переменной существует в каком-либо виде - отобразить
function ifExistGetValue($valuename) {

	global $f, $action;
	
	if (!empty($_REQUEST[$valuename])) 
		return $_REQUEST[$valuename];

	elseif (!empty($f[$valuename]))
		return $f[$valuename];

	// elseif ($action == "view") echo "&nbsp;";

}


// Выполнение запроса в БД и получение результирующего массива с данными
// Нужна, если где-то по-старинке используется это имя
// 20.08.2008
function ProcessSQL($sql) { return ExecuteSqlGetArray($sql); }

// Генерация тега input
// 25.08.2007
function GenerateInputTag($name, $description, $type="text", $separator=" &nbsp; ",$br="<br>",$requiredFieldsSet="persons",$js="")	{

	echo "\n<input type='$type' name='$name' value='" . ifExistValueReturnIt($name) .  "' id='label$name'> $separator \n<label for='label$name'>".IsTheFieldRequired($name,$requiredFieldsSet)." $description</label>$br\n";

}

function generateGroupsOfInputs($array) {

	foreach($array as $key=>$val) {
	
		GenerateInputTagV3($name, $description, $type="text", $separator=" &nbsp; ",$br="<br>",$requiredFieldsSet="persons",$js="");

	}

}

// Генерация тега input
// 25.08.2007
function GenerateInputTagV3($name, $description, $type="text", $separator=" &nbsp; ",$br="<br>",$requiredFieldsSet="persons",$js="")	{

	echo "\n<input type='$type' name='$name' value='" . ifExistValueReturnIt($name) .  "' id='label$name' placeholder='$description'>$br\n";

}


// Генерация тега input
// 25.08.2007
function GenerateInputTagForEmailSending($name, $description)	{

	GenerateInputTag($name, $description, "text", " &nbsp; ","<br>","emaform");

}

// 25.08.2008
// Поиск элементыа в массиве. Если найден - поле обязательно к заполнению
function IsTheFieldRequired($name,$requiredFieldsSet) {

	global $SectionsRequiredFields;
	if (array_key_exists($name, $SectionsRequiredFields[$requiredFieldsSet])) return "<span class='requiredfield'>*</span>";

}


// Генерация тега select
// 03.08.2007
// Название поля
function GenerateSelectList($WhatWhatTableToSelect, $nameOfIdentificatorAutoToSelect, $nameofvaluetoshow, $description="", $separator=" &nbsp; ")	{

	$res = mysql_query("select * from `$WhatWhatTableToSelect`");

	$select = "<select name='$nameOfIdentificatorAutoToSelect' id='label$nameOfIdentificatorAutoToSelect'>";
	
	while($col = mysql_fetch_array($res))	{
		$select .= "\t\t<option value='".$col['id']."'";
		$select .= selectv2($nameOfIdentificatorAutoToSelect, $col['id']);
		$select .= ">$col[$nameofvaluetoshow]</option>\n";
	}

	echo $select."</select>
	 $separator <label for='label$nameOfIdentificatorAutoToSelect'>$description</label>
	
	";

}


function ifExistValueReturnIt($valuename) {

	global $f;
	
	if (isset($_REQUEST[$valuename])) 
		return $_REQUEST[$valuename]; 
	else return @$f[@$valuename];
}

// Генерация тега textarea
// 03.08.2008
function GenerateTextAreaTag($name,$description,$requiredFieldsSet="persons")	{

	echo "<div class='textareadesc'><label for='label$name'>". IsTheFieldRequired($name,$requiredFieldsSet) . " $description</label></div><div><textarea name='$name' id='label$name'>" . ifExistValueReturnIt($name) . "</textarea></div>";

}

// Генерация тега textarea
// 03.08.2008
function GenerateTextAreaTagV3($name,$description,$requiredFieldsSet="persons")	{

	echo "<div class='textareadesc'><label for='label$name'>". IsTheFieldRequired($name,$requiredFieldsSet) . "</label></div><div><textarea name='$name' id='label$name' placeholder='$description'>" . ifExistValueReturnIt($name) . "</textarea></div>";

}


// Генерация тега textarea
// 03.08.2008
function GenerateTextAreaTag4EmalSedning($name,$description)	{

	GenerateTextAreaTag($name,$description,"emaform");

}


function SubHeaderTabsHighlight($currentAction="default") {

	global $action;

	if ($action  == $currentAction)		echo 'class=current';
}



function utf8_substr($str,$from,$len){
# utf8 substr
# www.yeap.lv
  return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                       '$1',$str);
}


// Обрезаем видимую часть строки, если она больше $limitto символов
function limitVisiblePart($fieldname, $limitto="16", $threedots = "") {

	global $f;

	if (strlen($fieldname) > $limitto) $threedots = "...";
	$fieldname = strip_tags(utf8_substr(stripslashes($fieldname), 0, $limitto)).$threedots;
	return $fieldname;

}

function isUrl($url) {

	if (ifExistGetValue($url)) {

	$https = array("http://","https://");
	$rep = array("","");

	$url = str_replace($https,$rep,ifExistGetValue($url));

	$url = "<a href='//$url' target='_blank'>" .$url . "</a>";

	return $url;

	}

}

/*----------- Пре-инициализация -----------------*/

// Чистка на всякий случай
unset($action,$ascdesc,$orderby,$limit);

// Глобализируем некоторые вещи. Может оно и не надо?
global $action;

// Замена служебных символов в БД MySQL
$ForbiddenChars = array("'", "”", "“", "’", "‘ ", "`", "'");
$AllowedChars = array("&#39;", "&rdquo;", "&ldquo;", "&rsquo;", "&lsquo;", "&#96;", "&#39;");

// Установка значений по умолчанию
$sign = "?";
$action="default";
$ascdesc="desc";
$orderby="id";
$limit='10';
$auth = '0';

// Префикс БД
define("PREFIX","");

// Обязательные к заполнению поля в зависимости от раздела
$SectionsRequiredFields = array(
	"emaform"		=> array("yourname" => "Ваше имя","email" => "Адрес вашей электропочты", "subject" => "Тема сообщения", "content" => "Краткое сообщение пользователю"),
//	"persons"		=> array("nickname" => "Ваш ник на форуме","email" => "Ваш емейл", "activity" => "Краткое описание вашей деятельности"),

);


function generateRandString($length = 8){
   $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
   $numChars = strlen($chars);
   $string = '';
   for ($i = 0; $i < $length; $i++) {
     $string .= substr($chars, rand(1, $numChars) - 1, 1);
   }
   return $string;
}

function preparePostFields($array) { 
    if(is_array($array)){
        $params = array();
        foreach ($array as $key => $value) {
            $params[] = $key . '=' . urlencode($value);
        }
        return implode('&', $params);
    }else{
        return $array;
    }    
}