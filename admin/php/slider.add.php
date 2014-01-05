<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();



// wysłano formularz

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

	$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));

	$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));

	$opis_pl = addslashes(trim(htmlspecialchars($_POST['opis_pl'])));

	$opis_en = addslashes(trim(htmlspecialchars($_POST['opis_en'])));

	$status = addslashes(trim(htmlspecialchars($_POST['status'])));

	$url = addslashes(trim(htmlspecialchars($_POST['url'])));

	$image = &$_FILES['images'];

	

	$access = array('image/gif', 'image/png', 'image/jpeg');

	list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

	

	if( empty($tytul_pl) || empty($tytul_en) || empty($opis_pl) || empty($opis_en) || empty($status) || empty($url) || empty($image['tmp_name']) )

		$errors[] = "Nie zostały wypełnione wszystkie pola";

		

	if(!in_array($image['type'], $access))

		$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

	/*

	if($width > 455 || $height > 198)

		$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 445x198 [px]";

	*/

	if( empty($errors) ) 

	{

		$uniqId = uniqid();

		

		if($image['type'] == 'image/gif')

		{

			$ext = '.gif';

		}

		else if($image['type'] == 'image/png')

		{

			$ext = '.png';

		}

		else

		{

			$ext = '.jpg';

		}

		

		$filename = $uniqId.$ext;

		//move_uploaded_file($image['tmp_name'], '../files/images/slider/' . $filename);

		require 'SimpleImage.php';

		$img = new abeautifulsite\SimpleImage($image['tmp_name']);
		
		$img->resize(438, 247)->save('../files/images/slider/' . $filename);
		

		$policz = $db->query('SELECT `lp` FROM `slider` ORDER BY `lp` DESC LIMIT 1');

		$policz = $policz->fetch();		

		

		$sql = $db->prepare('INSERT INTO `slider` VALUES(NULL, :lp, :namepl, :nameen, :image, :descriptionpl, :descriptionen, :url, :active)');

		$sql->bindValue(':lp', (int) $policz['lp']+1, PDO::PARAM_INT);

		$sql->bindValue(':namepl', $tytul_pl, PDO::PARAM_STR);

		$sql->bindValue(':nameen', $tytul_en, PDO::PARAM_STR);

		$sql->bindValue(':image', $filename, PDO::PARAM_STR);

		$sql->bindValue(':descriptionpl', $opis_pl, PDO::PARAM_STR);

		$sql->bindValue(':descriptionen', $opis_en, PDO::PARAM_STR);

		$sql->bindValue(':url', $url, PDO::PARAM_STR);

		$sql->bindValue(':active', $status, PDO::PARAM_STR);

		$sql->execute();

		

		$sent = true;

	}

		

}

else

{

	$tytul_pl = NULL;

	$tytul_en = NULL;

	$opis_pl = NULL;

	$opis_en = NULL;

	$status = NULL;

	$url = NULL;

}



// komunikat o dodaniu lub wyświetlenie formularza (i błędów)

if($sent == true)

{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/slider/add">Dodaj nową stronę do slidera</a></li>

		<li><a href="/admin/slider/edit">Zarządzaj stronami slidera</a></li>

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



<fieldset>

	<label for="input_1">Tytuł [PL] <span class="star">*</span></label>

	<input type="text" id="input_1" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>

</fieldset>



<fieldset>

	<label for="input_2">Opis [PL] <span class="star">*</span></label>

	<textarea rows="3" id="input_2" name="opis_pl" required>'.stripslashes($opis_pl).'</textarea>

</fieldset>



<fieldset>

	<label for="input_3">Tytuł [EN] <span class="star">*</span></label>

	<input type="text" id="input_3" name="tytul_en" value="'.stripslashes($tytul_en).'" required>

</fieldset>



<fieldset>

	<label for="input_4">Opis [EN] <span class="star">*</span></label>

	<textarea rows="3" id="input_4" name="opis_en" required>'.stripslashes($opis_en).'</textarea>

</fieldset>



<fieldset>

	<label for="input_5">Status <span class="star">*</span></label>

	<select id="input_5" name="status" required>

		<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>

		<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>

	</select>

</fieldset>



<fieldset>

	<label for="input_6">URL <span class="star">*</span></label>

	<input type="text" id="input_6" name="url" value="'.stripslashes($url).'" required>

</fieldset>



<fieldset>

	<label for="input_7">Obrazek <span class="star">*</span></label>

	<input type="file" id="input_7" name="images" accept="image/jpeg,image/gif,image/png" required>

</fieldset>



<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">



</form>



');



}



?>