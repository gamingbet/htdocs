<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") ){
	exit();
}

$sent = false;
$errors = array();



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" ){
	$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));
	$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));
	$head_pl = addslashes(trim(htmlspecialchars($_POST['head_pl'])));
	$head_en = addslashes(trim(htmlspecialchars($_POST['head_en'])));
	$foot_pl = addslashes(trim(htmlspecialchars($_POST['foot_pl'])));
	$foot_en = addslashes(trim(htmlspecialchars($_POST['foot_en'])));
	$date = addslashes(trim(htmlspecialchars($_POST['date'])));
	$position = addslashes(trim(htmlspecialchars($_POST['position'])));
	$html = addslashes(trim($_POST['html']));
	$content_pl = addslashes(trim(htmlspecialchars($_POST['content_pl'])));
	$content_en = addslashes(trim(htmlspecialchars($_POST['content_en'])));
	$status = addslashes(trim(htmlspecialchars($_POST['status'])));
	$image = &$_FILES['images'];
	if(!empty($image['tmp_name'])){
		$access = array('image/gif', 'image/png', 'image/jpeg');
		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);
		if(!in_array($image['type'], $access))
			$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";
	}
	$duplikat = $db->query('SELECT `id`, TIMESTAMPDIFF(MINUTE, `add`, NOW()) AS `diff` FROM `news` WHERE `title-pl` = "'.$tytul_pl.'" AND `title-en` = "'.$tytul_en.'" ORDER BY `id` DESC');
	if($duplikat->rowCount() > 0){
		$duplikat = $duplikat->fetch();
		if($duplikat['diff'] < 3)
			$errors[] = "News o takim tytule został dodany w ciągu ostatnich 3 minut";
	}
	if(empty($errors)){
		if(!empty($image['tmp_name'])){
				if($image['type'] == 'image/gif'){
					$ext = '.gif';
				}
				else if($image['type'] == 'image/png'){
					$ext = '.png';
				}
				else{
					$ext = '.jpg';
				}
				$filename = $tytul_pl.$ext;
				require 'SimpleImage.php';
				$img = new abeautifulsite\SimpleImage($image['tmp_name']);
				$img->resize(800, 600)->save('../files/images/news/' . $filename);
				$source = 'files/images/news/'.$filename;
		}
		$db->exec('INSERT INTO `news` VALUES(NULL, "'.$tytul_pl.'", "'.$tytul_en.'", "'.$source.'", "'.$head_pl.'", "'.$head_en.'", "'.$foot_pl.'", "'.$foot_en.'", 
		"'.$html.'", "'.$content_pl.'", "'.$content_en.'", NOW(), "'.$_GLOBALS[ 'login' ][ 'userId' ].'", "'.$status.'", "'.$date.'", "'.$position.'")');

	$sent = true;
	}
}
else{
	$tytul_pl = NULL;
	$tytul_en = NULL;
	$head_pl = NULL;
	$head_en = NULL;
	$foot_pl = NULL;
	$foot_en = NULL;
	$date = 'true';
	$position = 'up';
	$html = NULL;
	$content_pl = NULL;
	$content_en = NULL;
	$status = 'true';
}

if($sent == true){
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/news/add">Dodaj nowy news</a></li>
		<li><a href="/admin/news/edit">Zarządzaj newsami</a></li>
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
	<p>W polu <tt>Kod HTML</tt> można wprowadzić kod HTML. Część ta przeznaczona jest na film wideo. Dla obu wersji językowych jest taka sama. Nie trzeba wypełniać wszystkich pól.
	Obowiązkowe są pola oznaczone jedną gwiazdką <span class="star">*</span>, natomiast pozostałe gwiazdki oznaczają powiązane ze sobą pola, tzn. jeżeli wypełnisz pole <i>Kod HTML</i>
	to musisz również wypełnić pola oznaczone nagłówkami górnymi. Analogicznie z dolną cześcią newsa. W dolnej cześci newsa można stosować tagi <b>BBCode</b>.</p>
	
	<fieldset>
		<label for="input_1">Tytuł [PL] <span class="star">*</span></label>
		<input type="text" id="input_1" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_2">Tytuł [EN] <span class="star">*</span></label>
		<input type="text" id="input_2" name="tytul_en" value="'.stripslashes($tytul_en).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Nagłówek górny [PL] <span class="star">**</span></label>
		<input type="text" id="input_3" name="head_pl" value="'.stripslashes($head_pl).'" >
	</fieldset>
	
	<fieldset>
		<label for="input_4">Nagłówek górny [EN] <span class="star">**</span></label>
		<input type="text" id="input_4" name="head_en" value="'.stripslashes($head_en).'" >
	</fieldset>

	

	<fieldset>
		<label for="input_9">Kod HTML (góra) <span class="star">**</span></label>
		<textarea rows="7" id="input_10" name="html" >'.stripslashes($html).'</textarea>
		<input type="file" id="input_9" name="images" accept="image/jpeg,image/gif,image/png">
	</fieldset>

	

	<fieldset>
		<label for="input_5">Nagłówek dolny [PL] <span class="star">***</span></label>
		<input type="text" id="input_5" name="foot_pl" value="'.stripslashes($foot_pl).'" >
	</fieldset>

	<fieldset>
		<label for="input_6">Nagłówek dolny [EN] <span class="star">***</span></label>
		<input type="text" id="input_6" name="foot_en" value="'.stripslashes($foot_en).'" >
	</fieldset>
		
	<fieldset>
		<label for="input_10">Treść dolna newsa [PL] <span class="star">***</span></label>
		<textarea rows="7" id="input_10" name="content_pl" >'.stripslashes($content_pl).'</textarea>
	</fieldset>

		
	<fieldset>
		<label for="input_11">Treść dolna newsa [EN] <span class="star">***</span></label>
		<textarea rows="7" id="input_11" name="content_en" >'.stripslashes($content_en).'</textarea>
	</fieldset>
	
	<fieldset>
		<label for="input_7">Czy pokazać datę? <span class="star">*</span></label>
		<select id="input_7" name="date" required>
			<option value="true"');if($date == "true"){echo(' selected');}echo('>Tak</option>
			<option value="false"');if($date == "false"){echo(' selected');}echo('>Nie</option>
		</select>
	</fieldset>

	<fieldset>
		<label for="input_8">Miejsce wyświetlenia daty</label>
		<select id="input_8" name="position" >
			<option value="up"');if($position == "up"){echo(' selected');}echo('>Góra</option>
			<option value="down"');if($position == "down"){echo(' selected');}echo('>Dół</option>
		</select>
	</fieldset>

	<fieldset>
		<label for="input_12">Status <span class="star">*</span></label>
		<select id="input_12" name="status" required>
			<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
			<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
		</select>
	</fieldset>

	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>');

}



?>