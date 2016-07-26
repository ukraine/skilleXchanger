<?

switch($action) {

// Вывод страницы по умолчанию
default:

	$title = "Список контактов";

	// Инициализация перменных
	$where = $qeuryforpaginator = $paging = "";

		// Получение дополнительных параметров
		parse_str($_SERVER['QUERY_STRING'],$query);
		foreach($query as $key=>$val)

				{
					if ($key != "section" && 
						$key != "page" && 
						$key != "sortby" && 
						$key != "ascdesc" && 
						$key != "orderby" && 
						$key != "parent_id" && 
						$key != "limit" &&
						$key != "PHPSESSID") {
						
						// Дополнительная строка для запроса в БД
						$where =" $key = '$val'";

						// echo $where;

					}

					if ($key !== "section" & $key !== "page") {
												
						// Для пагинатора
						$qeuryforpaginator .= "$key=$val&";

					}

				}

				if (!empty($where)) $where = "WHERE $where";

	// echo $where;

	$url = $_SERVER['REDIRECT_URL'] . "?" . $qeuryforpaginator;

	// Формирование сути нашего запроса
	$sql = "FROM `persons` $where ORDER BY `$orderby` $ascdesc";

	// echo $sql;

	// Считаем общее кол-во объектов
	$res = @mysql_fetch_array(mysql_query("select count(*) as count $sql"));

	// Если хоть что-то есть, выводим список
	if($res) {
		
		// Всего записей
		$count = $res['count'];

		// Считаем общее кол-во страниц
		$totalpages = floor($count/$limit);

		// узнаем на какой странице находимся
		if(empty($_GET['page'])) $page = 0; else $page = intval($_GET['page']);

		// Выставление лимита кол-ва объектов на странице (так же исп-зуется для нумерации объ. на страницах)
		$startlimit = $limit*$page;

		// Первый объект на странице
		$startobject = $startlimit + 1;

		// Последний объект на странице
		$endobject = $startobject + $limit - 1;

		// Если объектов меньше, чем разрешено на странице
		if ($count <= $limit) $endobject = $count;
		
		// Исп. для последней страницы
		if ($endobject > $count) $endobject = $count ;

		// Формирование конечного запроса
		$sql = $sql . " LIMIT $startlimit, $limit";

		// echo $sql;

		// Собственно делаем выборку
		$res = mysql_query("SELECT * $sql");

	}

	break;

// Страница добавления объекта
// Добавлен путь к странице
case "add":

		$title = "Добавление контакта в систему";
		$action = "addedit";

	break;

// Выполнение добавления объекта
case "do_add":

	$action = "addedit";
	$tolocation = "/";

	$location = $siteurl.$section.$tolocation;

		if (!empty($_POST['nickname']) && empty($_POST['nickname1233'])) die("Ooooopsie");
		elseif (!empty($_POST['nickname1233'])) {

			$_POST['nickname'] = $_POST['nickname1233'];
			unset($_POST['nickname1233']);
		
		// Проверяем получаемые данные на корректность и если все хорошо, вводим данные
		if (IsRequiredFieldsFilled($SectionsRequiredFields["persons"]) ) {

			if (insert_data ($_POST, "persons")) {
				header("Location: $location");
			}	else {
				$title = "Ошибка добавления";
				$error_msg = "Не могу добавить, где-то в процессе добавления информации системой были допушены ошибки. Свяжитесь с автором.".mysql_errno() . ": " . mysql_error();
			}

		}

		}

	break;

// Выполнение внесения изменений
case "do_edit":

		$action = "edit";
		$tolocation = "/";

		/*

		// 1.b Если мы на странице изменения пароля, то шифруем его и заносим в таблицу
		if (!empty($_POST['password']) && $_POST['password']!=$Settings['password']) {
			$_POST['password'] = md5($_POST['password']);
		} else 	unset($_POST['password']);

		// "Save" or "Continue Edit"
		if (!empty($_POST['submit'])) $location = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		*/

		$_POST['id'] = $_SESSION['id'];

		// Сохраняем данные и возвращаемся в начальную раздела
		if (edit_data ($_POST, "persons")) {
			header("Location: $location");
		}	else {
			$title = $translation['93'];
			$error_msg = "Не могу добавить, где-то в процессе добавления информации системой были допушены ошибки. Свяжитесь с автором.".mysql_errno() . ": " . mysql_error();
		}

	break;

// Страница редактирования
case "edit":

		$action = "addedit";
		$title = "Редактирование";

		if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!== "yes") header("Location: {$siteurl}?action=add");
		$f = ProcessSQL("SELECT * FROM `persons` WHERE `id` = '$_SESSION[id]'");

	break;

// Изменение значение. Режим AJAX
case "stats":

	$title = "Забавная статистика";

	break;

// Изменение значение. Режим AJAX
case "changevalue":

		$table = $_GET['section'];
		$request = parse_url($_SERVER['REQUEST_URI']);
		parse_str($request['query'], $details);

	if (ChangeValue()) header("Location: {$siteurl}../changed.html");

	break;

// Выполнение входа
case "do_login":

			$email = clearHTML($_POST['email']);
			$password = clearHTML($_POST['password']);
			
		$UserDataAet = mysql_query("SELECT `id`,`email`,`nickname`,`password` FROM `".PREFIX."persons` WHERE `email` = '$email' AND `password` ='$password'");
		$UserDetails = mysql_fetch_array($UserDataAet);

			if (mysql_num_rows($UserDataAet)>0) {

				$_SESSION['loggedin'] = "yes";
				$_SESSION['id'] = $UserDetails['id'];
				$_SESSION['nickname'] = $UserDetails['nickname'];
				
				header("Location: $siteurl");
			}

			else $loggedintext = "Попробуйте ввести правильные<br> данные для входа";


	break;


// Выполнение Выхода
case "logout":
		
		session_destroy();
		header("Location: " . SITEURL);
		
	break;

// Удаление объекта

case "delete":

	$tolocation = "/";

	$location = $siteurl.$section.$tolocation;

		if (delete_data("id", "persons", $_GET['id'])) {
			header("Location: $location");
		}	else {
			$title = "Error";
			$error_msg = $translation['17'];
		}

	break;

// Просмотр элемента
case "view":

	$action = "view";
	$title = "Просмотр информации о контакте";

	// Получаем массив с данными об объекте
	$f = RunQueryReturnDataArray("persons", "WHERE `id` = '".intval($_GET['id'])."'");

	// Если нет информации по объекту с таким ИД
	if (!$f) { $error_msg = "Прошу прощения, пользователя с таким идентификатором не существует. Возможно ошибка в ссылке?"; break; }

	break;

// Форма отправки почты клиенту
case "emailto":

	$title = "Такого пользователя не существует";
	$action = "emailto";

	// Получаем массив с данными об объекте
	$f = RunQueryReturnDataArray("persons", "WHERE `id` = '".intval($_GET['id'])."'","name,nickname");

	if ($f) {

	$title = "Пишем письмо к <span style='color: red; font-size: 140%;'>$f[nickname]</span>";
	$action = "emailto";

	}

	break;

case "do_emailto":

	$action = "emailto";

	// Получаем массив с данными об объекте
	$f = RunQueryReturnDataArray("persons", "WHERE `id` = '".intval($_POST['id'])."'","name,nickname,email");

	$title = "Пишем письмо к <span style='color: red; font-size: 140%;'>$f[nickname]</span>";

	// Если нет информации по объекту с таким ИД
	if (!$f) { $error_msg = "Прошу прощения, пользователя с таким идентификатором не существует. Возможно ошибка в ссылке?"; break; }

	elseif (IsRequiredFieldsFilled($SectionsRequiredFields["emaform"]) ) {

		$_POST['rcpt'] = "$f[name] <$f[email]>";
		$error_msg = sendmail();
		$action="none";

	}

	break;

// Просмотр элемента
case "do_search":

	$action = "default";


	$nickname = strtolower(clearHTML($_POST['q']));
	$title = "Поиск человека";

	$where = "WHERE `nickname` = '$nickname'";

	// Смотрим сколько контактов с таким именем
	$count = GetTotalData("persons", $where);

	$error_msg = "Человека с ником <B>$nickname</B> нет в базе данных :(
	<br> Если вы искали себя и не нашли, то может быть добавимся? ;) Жмите на кнопке &quot;Добавить&quot; вверху";

	// echo "For <B>$nickname</B> we found <B>$count</B> results";

	if ($count) {

		$action = "view";
		$title = "Просмотр информации о контакте";

		unset($error_msg);

		// Если больше двух
		if ($count > 1)

			// выводим список
			header("Location: {$siteurl}?nickname=$nickname");
		
		// Если только один
		elseif ($count == "1")

			// Если 1 - прямо таки выводим его профиль
			$f = RunQueryReturnDataArray("persons", $where);

	}

	break;

// Множественное удаление объектов
case "massactiontoobjects":

	$error_msg = $translation['156'];
	$title = $translation['93'];

		// Если были переданы ID объектов
		if (@$_POST['id']) {

			// Инициализируем переменную для перенаправления
			$tolocation = "/";
			$ids = "";

			// Если мы находимся в разделе страницы, то должны вернуть пользовотеля туда, откуда он пришел
			if ($section == "pages" && @$_GET['parent_id']) $tolocation .= "?parent_id=$_GET[parent_id]";

			// Формирурем адрес "туда"
			$location = $siteurl.$section.$tolocation;

			// Разбиваем массив с ID объектов
			foreach($_POST['id'] as $key=>$value) {
				
				// Делаем невозможным перенос объекта в самого себя
				if ($value !== $_POST['parent_id']) $ids .= "$value,";

			}

			// Формируем строку с объектами (требуется для создания запроса множественных строк в БД)
			$ids = substr_replace($ids ,"",-1);
			
			/*** Начинаем выполнять массовые действия ***/

			// Если нужно переместить, выполняем перемещение
			if (@$_POST['massaction']) {

					if (move_data("id", $section, $ids,"1")) header("Location: $location");
					else $error_msg = $translation['17'];			
			
			
			}

			// В противном случае это удаление
			else {
				
					// echo $ids;

					if (delete_data("id", $section, $ids,"1")) header("Location: $location");
					else $error_msg = $translation['17'];


			}

		}

	break;

// Выполнение запроса к базе данных
case "do_runquery":

		$action = "default";
		$error_msg = $translation['137'];

		$title = $translation['167'];

		// Don't change here
		if (mysql_query(str_replace("\\", "", $_POST['sql']))) return 1; else { $error_msg = $translation['138'] . mysql_errno() . ": " .mysql_error();  return 0; }
		// Don't change here */


	break;

}

?>