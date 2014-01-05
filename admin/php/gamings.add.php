<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") ){
	exit();
}
$sent = false;
$errors = array();


if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" ){
	$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));
	$tag = addslashes(trim(htmlspecialchars($_POST['tag'])));
	$utworzony = addslashes(trim(htmlspecialchars($_POST['utworzony'])));
	$www = addslashes(trim(htmlspecialchars($_POST['www'])));
	$opis_pl = addslashes(trim(htmlspecialchars($_POST['opis_pl'])));
	$opis_en = addslashes(trim(htmlspecialchars($_POST['opis_en'])));
	$status = addslashes(trim(htmlspecialchars($_POST['status'])));
	$image = &$_FILES['images'];
	if( empty($nazwa) || empty($tag) )
		$errors[] = "Nie zostały wypełnione wszystkie pola";
	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $tag))
		$errors[] = "Tag może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";
	$temp_date = explode("-", $utworzony);	
	if(!empty($utworzony))
		if( !checkdate( (int)$temp_date[1], (int)$temp_date[2], (int)$temp_date[0] ) )
			$errors[] = "Nie wpisano poprawnego formatu daty";
	if(!empty($www))
		if(!filter_var($www, FILTER_VALIDATE_URL))
			$errors[] = "Wpisano niepoprawny adres strony www";
	if(!empty($image['tmp_name'])){
		$access = array('image/gif', 'image/png', 'image/jpeg');
		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);
		if(!in_array($image['type'], $access))
			$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";
		/*
		if($width > 135 || $height > 135)
			$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 135x135 [px]";
		*/
	}
	
	$sel_tag = $db->query('SELECT `id` FROM `gamings` WHERE `fullname` = "'.$nazwa.'"');
	if($sel_tag->rowCount() == 1)
		$errors[] = "Klan o takiej nazwie już istnieje";
	if( empty($errors) ) {
		if(!empty($image['tmp_name'])){
			$uniqId = uniqid();
			if($image['type'] == 'image/gif'){
				$ext = '.gif';
			}
			else if($image['type'] == 'image/png'){
				$ext = '.png';
			}
			else{
				$ext = '.jpg';
			}
			$filename = $uniqId.$ext;
			//move_uploaded_file($image['tmp_name'], '../files/images/logos/' . $filename);
			require 'SimpleImage.php';
			$img = new abeautifulsite\SimpleImage($image['tmp_name']);
			$img->resize(135, 135)->save('../files/images/logos/' . $filename);
			$source = 'files/images/logos/'.$filename;
		}
		else
		{
			$source = 'files/images/logos/noclan.png';
		}
		$sql = $db->prepare('INSERT INTO `gamings` VALUES(NULL, :fullname, :tag, NOW(), :utworzony, :status, :image, :url, :opispl, :opisen)');
		$sql->bindValue(':fullname', $nazwa, PDO::PARAM_STR);
		$sql->bindValue(':tag', $tag, PDO::PARAM_STR);
		$sql->bindValue(':utworzony', $utworzony, PDO::PARAM_STR);
		$sql->bindValue(':status', $status, PDO::PARAM_STR);
		$sql->bindValue(':image', $source, PDO::PARAM_STR);
		$sql->bindValue(':url', $www, PDO::PARAM_STR);
		$sql->bindValue(':opispl', $opis_pl, PDO::PARAM_STR);
		$sql->bindValue(':opisen', $opis_en, PDO::PARAM_STR);
		$sql->execute();
		$sent = true;
	}
}
else
{
	$nazwa = NULL;
	$tag = NULL;
	$utworzony = NULL;
	$www = "http://";
	$opis_pl = NULL;
	$opis_en = NULL;
	$status = "true";
}

if($sent == true){
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/gamings/add">Dodaj nowy klan</a></li>
		<li><a href="/admin/gamings/edit">Zarządzaj klanami</a></li>	
	</ul>');	
}
else{
	if( !empty($errors ) ) {
		echo('<h4 class="alert_error">Podczas operacji wystąpiły błędy!</h4>');
		echo('<ul>');
		foreach($errors as $error){
			echo('<li>'.$error.'</li>');
		}
		echo('</ul>');
	}
	echo('<form action="'.$_ACTION.'" method="post" class="post_message" enctype="multipart/form-data">
	<fieldset>
		<label for="input_1">Pełna nazwa <span class="star">*</span></label>
		<input type="text" id="input_1" name="nazwa" value="'.$nazwa.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_2">TAG <span class="star">*</span></label>
		<input type="text" id="input_2" name="tag" value="'.$tag.'" required>
	</fieldset>

	<fieldset>
		<label for="input_3">Opis [PL] <span class="star"></span></label>
		<textarea rows="3" id="input_3" name="opis_pl">'.$opis_pl.'</textarea>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Opis [EN] <span class="star"></span></label>
		<textarea rows="3" id="input_4" name="opis_en">'.$opis_en.'</textarea>
	</fieldset>

	<fieldset>
		<label for="input_5">Status <span class="star">*</span></label>
		<select id="input_5" name="status" required>
			<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
			<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
		</select>
	</fieldset>

	<fieldset>
		<label for="input_6">Utworzony<span class="star"></span></label>
		<input type="text" id="input_6" name="utworzony" value="'.$utworzony.'">
	</fieldset>

	<fieldset>
		<label for="input_7">Strona WWW <span class="star"></span></label>
		<input type="text" id="input_7" name="www" value="'.$www.'">
	</fieldset>

	<fieldset>
		<label for="input_8">Logo klanu <span class="star"></span></label>
		<input type="file" id="input_8" name="images" accept="image/jpeg,image/gif,image/png">
	</fieldset>

	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>

	');

}



?>