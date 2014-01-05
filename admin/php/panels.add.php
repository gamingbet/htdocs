<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$uniq_id = addslashes(trim(htmlspecialchars($_POST['uniq_id'])));
	$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
	$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
	$typ = addslashes(trim(htmlspecialchars($_POST['typ'])));
	$content = addslashes(trim($_POST['content']));
	$status = addslashes(trim(htmlspecialchars($_POST['status'])));
	$file = &$_FILES['file'];
	$upload = false;
	
	$check = $db->query('SELECT * FROM `panels` WHERE `name` = "'.$uniq_id.'" LIMIT 1');
	
	if($check->rowCount() == 1)
		$errors[] = "Taki panel jest już używany";
	
	if( empty($uniq_id) || empty($nazwa_pl) || empty($nazwa_en) )
			$errors[] = "Nie zostały wypełnione wymagane pola";
	
	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $uniq_id))
		$errors[] = "Identyfikator może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";
	
	if( !empty($file['tmp_name']) )
	{
		$files = fopen($file['tmp_name'], 'r');
		$bytes = bin2hex(fread($files, 3));
		
		if($bytes != '3c3f70') // '3c3f70' = php
			$errors[] = "Wgrywany plik nie jest plikiem PHP";
		
		fclose($files); 

		if(empty($errors))
		{
			$content = 'file:'.$uniq_id.'.php';
			$typ = '';
			$upload = true;
		}
	}
	else
	{
		if(empty($content))
			$errors[] = "Treść panelu nie może być pusta";
	}
	
	if(empty($errors))
	{
		$policz = $db->query('SELECT `lp` FROM `panels` ORDER BY `lp` DESC LIMIT 1');
		$policz = $policz->fetch();		
		
		$add_lang = $db->exec('INSERT INTO `langs` VALUES(NULL, "panels", "'.$uniq_id.'", "'.$nazwa_pl.'", "'.$nazwa_en.'")');
		$panel = $db->exec('INSERT INTO `panels` VALUES(NULL, '.($policz['lp']+1).', "'.$status.'", "'.$uniq_id.'", "'.$content.'", "'.$typ.'")');
		
		if($upload == true)
		{
			move_uploaded_file($file['tmp_name'], '../panels/' . $uniq_id . '.php');
		}
		
		$sent = true;
		
	}
}
else
{
	$uniq_id = uniqid();
	$nazwa_pl = NULL;
	$nazwa_en = NULL;
	$typ = 'html';
	$content = NULL;
	$status = NULL;
}

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/panels/add">Dodaj nowy panel</a></li>
		<li><a href="/admin/panels/edit">Zarządzaj panelami</a></li>
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

echo('
<form action="'.$_ACTION.'" method="post" class="post_message" enctype="multipart/form-data">

<p>Na tej stronie możesz umieścić nowy panel, który zostanie umieszczony po prawej stronie. Jeżeli posiadasz plik <tt>*.php</tt> z własnym modułem, to możesz 
go wgrać w odpowiednim polu. Pamiętaj o tym, że jeśli prześlesz plik, to zawartość pola tekstowego zostanie pominięta, a typ automatycznie ustawi się na
plik <u>*.php</u>.</p>

<fieldset>
	<label for="input_1">ID Panelu <span class="star">*</span></label>
	<input type="text" id="input_1" name="uniq_id" value="'.$uniq_id.'" required>
</fieldset>

<fieldset>
	<label for="input_2">Nazwa panelu [PL] <span class="star">*</span></label>
	<input type="text" id="input_2" name="nazwa_pl" value="'.$nazwa_pl.'" required>
</fieldset>

<fieldset>
	<label for="input_3">Nazwa panelu [EN] <span class="star">*</span></label>
	<input type="text" id="input_3" name="nazwa_en" value="'.$nazwa_en.'" required>
</fieldset>

<fieldset>
	<label for="input_4">Typ <span class="star">*</span></label>
	<select id="input_4" name="typ" required>
		<option value="html"');if($typ == "html"){echo(' selected');}echo('>Kod HTML</option>
		<option value="bbcode"');if($typ == "bbcode"){echo(' selected');}echo('>BBCode</option>
		<option value=""');if($typ == ""){echo(' selected');}echo('>Plik *.php</option>
	</select>
</fieldset>

<fieldset>
	<label for="input_5">Status <span class="star">*</span></label>
	<select id="input_5" name="status" required>
		<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
		<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
	</select>
</fieldset>

<fieldset>
	<label for="input_6">Treść panelu <span class="star">**</span></label>
	<textarea rows="20" id="input_6" name="content">'.stripslashes($content).'</textarea>
</fieldset>

<fieldset>
	<label for="input_7">Plik *.php <span class="star">**</span></label>
	<input type="file" id="input_7" name="file" accept="application/x-php">
</fieldset>

<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

</form>
');

}

?>