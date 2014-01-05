<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$url = addslashes(trim(htmlspecialchars($_POST['url'])));
	$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));
	$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));
	$access = addslashes(trim(htmlspecialchars($_POST['access'])));
	$type = addslashes(trim(htmlspecialchars($_POST['type'])));
	
	if($type == 'bbcode')
	{
		$content_pl = addslashes(trim(htmlspecialchars($_POST['content_pl'])));
		$content_en = addslashes(trim(htmlspecialchars($_POST['content_en'])));
	}
	else
	{
		$content_pl = addslashes(trim($_POST['content_pl']));
		$content_en = addslashes(trim($_POST['content_en']));
	}
	
	if( empty( $url ) || empty( $tytul_pl ) || empty( $tytul_en ) || empty( $access ) || empty( $type ) || empty( $content_pl ) || empty( $content_en ) )
		$errors[] = "Nie zostały wypełnione wszystkie pola";
	
	if(in_array($url, $_FILES))
		$errors[] = "URL pliku zawiera jedną z niedozwolonych nazw";
	
	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $url))
		$errors[] = "URL zawiera niedozwolone znaki";
	
	$busy_url = $db->query('SELECT `id` FROM `pages` WHERE `short-url` = "'.$url.'"');
	if($busy_url->rowCount() == 1)
		$errors[] = "Strona o takim adresie url jest już zajęta";
	
	if( empty( $errors ) )
	{
		$sent = true;
		$db->exec('INSERT INTO `pages` VALUES(NULL, "'.$tytul_pl.'", "'.$tytul_en.'", "'.$url.'", "'.$access.'", "'.$content_pl.'", "'.$content_en.'", NOW(), "'.$_GLOBALS[ 'login' ][ 'userId' ].'", "'.$type.'")');
	}

}
else
{
	$url = NULL;
	$tytul_pl = NULL;
	$tytul_en = NULL;
	$access = 'all';
	$type = 'bbcode';
	$content_pl = NULL;
	$content_en = NULL;
}

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/pages/add">Dodaj nową stronę</a></li>
		<li><a href="/admin/pages/edit">Zarządzaj stronami</a></li>
	</ul>');
}
else
{
	if( !empty($errors ) ) 
	{
		echo('<h4 class="alert_error">Podczas operacji wystąpiły błędy!</h4>');
		echo('<ul>');
		foreach($errors as $error)
		{
			echo('<li>'.$error.'</li>');
		}
		echo('</ul>');
	}
	echo('<p>Pole URL może zawierać tylko i wyłącznie znaki A-Z (a-z), 0-9, pauza (-) oraz podkreślnik (_). Poniżej wypisane są nazwy, których w tym polu wpisać nie można.</p><p class="files">');
	foreach($_FILES as $file)
	{
		echo('<span class="file">'.(($url==$file)?'<b>'.$file.'</b>':$file).'</span> ');
	}
	echo('</p>
	<form action="'.$_ACTION.'" method="post" class="post_message">
	
	<fieldset>
		<label for="input_1">URL [A-Z, 0-9, -, _] <span class="star">*</span></label>
		<input type="text" id="input_1" name="url" value="'.stripslashes($url).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_2">Tytuł [PL] <span class="star">*</span></label>
		<input type="text" id="input_2" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Tytuł [EN] <span class="star">*</span></label>
		<input type="text" id="input_3" name="tytul_en" value="'.stripslashes($tytul_en).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Dostęp <span class="star">*</span></label>
		<select id="input_4" name="access" required>
			<option value="all"');if($access == "all"){echo(' selected');}echo('>Wszyscy</option>
			<option value="users"');if($access == "users"){echo(' selected');}echo('>Użytkownicy zalogowani</option>
			<option value="admins"');if($access == "admins"){echo(' selected');}echo('>Tylko administratorzy</option>
		</select>
	</fieldset>
	
	<fieldset>
		<label for="input_5">Typ formatowania <span class="star">*</span></label>
		<select id="input_5" name="type" required>
			<option value="bbcode"');if($type == "bbcode"){echo(' selected');}echo('>BBCode</option>
			<option value="html"');if($type == "html"){echo(' selected');}echo('>HTML</option>
		</select>
	</fieldset>
	
	<fieldset>
		<label for="input_6">Treść [PL] <span class="star">*</span></label>
		<textarea rows="25" id="input_6" name="content_pl" required>'.stripslashes($content_pl).'</textarea>
	</fieldset>

	<fieldset>
		<label for="input_7">Treść [EN] <span class="star">*</span></label>
		<textarea rows="25" id="input_7" name="content_en" required>'.stripslashes($content_en).'</textarea>
	</fieldset>
	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>');
}

?>