<?

// Чистка ключей

/*
function removeServiceKeysFromArray($keys,$array) {
	


} */

// Insertion

$ForbiddenKeys = array("section","action","submit","dosometh","do","subject","SWIFT_client","SWIFT_remusername","SWIFT_rempassword","SWIFT_staffsettings","PHPSESSID");

function insert_data ($details, $table) {

	global $keys, $values, $ForbiddenChars, $AllowedChars;

	// removeServiceKeysFromArray($ForbiddenKeys, $_POST);

	foreach($_POST as $key=>$val)

		{
			if ($key !== "section" && $key !== "action" && $key !== "submit" && $key !== "Submit" && $key !== "dosometh" && $key !== "id" && $key !== "do" && $key !== "subject" && $key !== "SWIFT_client" && $key !== "SWIFT_remusername" && $key !== "SWIFT_rempassword" && $key !== "SWIFT_staffsettings" && $key !== "PHPSESSID") {
				$keys .="`$key`,"; $values .= "'".str_replace($ForbiddenChars, $AllowedChars, $val)."',";
			}
		}

	$strlenkey = strlen($keys);
	$keys = substr($keys, 0, $strlenkey-1);

	$strlenval = strlen($values);
	$values = substr($values, 0, $strlenval-1);

	$sql = "INSERT INTO `".PREFIX."$table` ($keys) VALUES ($values)";

	// echo $sql;

	// Don't change here
	if (mysql_query($sql)) return 1; else return 0;
	// Don't change here */
	
}

// Edit data

function edit_data ($details, $table) {

	global $sqlset, $ForbiddenChars, $AllowedChars;

	foreach($details as $key=>$val)

			{
				if ($key !== "section" && $key !== "action" && $key !== "submit" && $key !== "Submit" && $key !== "id" && $key !== "sid") $sqlset .="`$key` = '".
				str_replace($ForbiddenChars, $AllowedChars, $val)."',";
			}

	$strlenset = strlen($sqlset);
	$sqlset = substr($sqlset, 0, $strlenset-1);

		$sql	=	"
					
					
					UPDATE `".PREFIX."$table` set 

						$sqlset
					
					WHERE `id` ='$details[id]'
				
					";

	echo $sql;

	// Don't change here
	if (mysql_query($sql)) return 1; else return 0;
	// Don't change here

}

// 14.12.2007
// Перемещение данных из одного parent в другой
function move_data ($objectkey, $table, $ids, $multiple="0") {

	$sql	= array(
		"UPDATE `".PREFIX."$table` SET `parent_id` = '$_POST[parent_id]' WHERE `$objectkey` = '$ids'",
		"UPDATE `".PREFIX."$table` SET `parent_id` = '$_POST[parent_id]' WHERE `$objectkey` IN ($ids)"
	);

	// echo $sql[$multiple];

	// Don't change here
	if (mysql_query($sql[$multiple])) return 1; else return 0;
	// Don't change here

}


// 21.10.2007
// Добавлена возможность множественного удаления
// С удалением вложенных объектов.
function delete_data ($columnname, $table, $ids, $multiple="0") {

	// $ids = ListChildrenIds($ids);

	$sql = "DELETE FROM `".PREFIX."$table` WHERE `$columnname` IN ($ids)";

	// echo $sql;

	// Don't change here
	if (mysql_query($sql)) return 1; else return 0;
	// Don't change here

}

?>