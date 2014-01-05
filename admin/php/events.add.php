<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

	$change["T"] = " ";

	$change["Z"] = NULL;

	

	$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));

	$url = addslashes(trim(htmlspecialchars($_POST['url'])));

	$gra = addslashes(trim(htmlspecialchars($_POST['game'])));

	$opis_pl = addslashes(trim(htmlspecialchars($_POST['opis_pl'])));

	$opis_en = addslashes(trim(htmlspecialchars($_POST['opis_en'])));

	$begin = addslashes(trim(htmlspecialchars($_POST['begin'])));

	$end = addslashes(trim(htmlspecialchars($_POST['end'])));

	$image = &$_FILES['images'];

	$access = array('image/gif', 'image/png', 'image/jpeg');

	

	//$begin_true = str_replace(array_keys($change), array_values($change), $begin);

	//$end_true = str_replace(array_keys($change), array_values($change), $end);

	$begin_true = $begin;

	$end_true = $end;

	if(empty($nazwa) || empty($gra) || empty($opis_pl) || empty($opis_en))

		$errors [] = "Niewszystkie wymagane pola zostały wypełnione";

	

	if(!empty($image['tmp_name']))

	{

		$upload_image = true;

		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

		

		if(!in_array($image['type'], $access))

			$errors[] = "Plik z logiem wydarzenia powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

			
		/*
		if($width > 640 || $height > 300)

			$errors[] = "Maksymalne rozmiary pliku graficznego nie może przekraczać 640x300 [px]";
		*/

	}

	else

		$upload_image = false;

	

	$begin_time = strtotime($begin_true);

	$end_time = strtotime($end_true);

	

	if($begin_time > $end_time)

		$errors[] = "Wydarzenie nie może zaczynać się później niż się skończyło";

	

	if($begin_time < time() )

		$errors[] = "Wydarzenie nie może odbyć się w przeszłości";

		

	if($end_time < time() )

		$errors[] = "Wydarzenie nie może kończyć się w przeszłości";

	

	if( ($end_time - $begin_time) < 60*60*24 )

		$errors[] = "Wydarzenie musi trwać przynajmniej 24 godziny";

	

	$isset_game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$gra.'"');

	if($isset_game->rowCount() == 0)

		$errors[] = "Wybrana gra nie jest zarejestrowana w bazie";

		

	$sel = $db->query('SELECT * FROM `events` WHERE `name` = "'.$nazwa.'" AND `dataBegin` = "'.$begin_true.'"');

	if($sel->rowCount() != 0)

		$errors[] = "Takie wydarzenie już istnieje";

		

	if(empty($errors))

	{

		if($upload_image == true)

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

			//move_uploaded_file($image['tmp_name'], '../files/images/events/' . $filename);

			require 'SimpleImage.php';

			$img = new abeautifulsite\SimpleImage($image['tmp_name']);
			
			$img->resize(640,300)->save('../files/images/events/' . $filename);

		}

		else

		{

			$filename = '';

		}

		

		$db->exec('INSERT INTO `events` VALUES(NULL, "'.$gra.'", "'.$nazwa.'", "", 0, "'.$begin_true.'", "'.$end_true.'", "'.$opis_pl.'", "'.$opis_en.'", "'.$url.'", "'.$filename.'") ');

		$sent = true;

		

	}

}

else

{

	$nazwa = NULL;

	$url = "http://";

	$gra = "0";

	$opis_pl = NULL;

	$opis_en = NULL;

	$begin = NULL;

	$end = NULL;

}



if($sent == true)

{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/events/add">Dodaj nowe wydarzenie</a></li>

		<li><a href="/admin/events/edit">Zarządzaj wydarzeniami</a></li>

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

	echo('<form action="'.$_ACTION.'" method="post" class="post_message" enctype="multipart/form-data">

	

	<fieldset>

		<label for="input_1">Nazwa wydarzenia <span class="star">*</span></label>

		<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>

	</fieldset>

	

	<fieldset>

		<label for="input_2">URL <span class="star"></span></label>

		<input type="text" id="input_2" name="url" value="'.stripslashes($url).'">

	</fieldset>

	

	<fieldset>

		<label for="input_3">Data rozpoczęcia [YYYY-MM-DD GG:MM] <span class="star">*</span></label>

		<input type="text" id="input_3" name="begin" value="'.stripslashes($begin).'" required>

	</fieldset>

	

	<fieldset>

		<label for="input_4">Data zakończenia [YYYY-MM-DD GG:MM] <span class="star">*</span></label>

		<input type="text" id="input_4" name="end" value="'.stripslashes($end).'" required>

	</fieldset>

	

	<fieldset>

		<label for="input_5">Gra <span class="star">*</span></label>

		<select id="input_5" name="game" required>');

			$games = $db->query('SELECT * FROM `games` ORDER BY `id` ASC');

			if($gra == '0')

				echo('<option disabled selected>Wybierz grę</option>');

			while($game = $games->fetch())

			{

				echo('<option value="'.$game['id'].'" ');if($gra == $game['id']){echo(' selected');}echo('>'.$game['name'].'</option>');

			}



	echo('</select>

	</fieldset>

	

	<fieldset>

		<label for="input_6">Krótki opis [PL] <span class="star">*</span></label>

		<textarea rows="4" id="input_6" name="opis_pl" required>'.stripslashes($opis_pl).'</textarea>

	</fieldset>

	

	<fieldset>

		<label for="input_7">Krótki opis [EN] <span class="star">*</span></label>

		<textarea rows="4" id="input_7" name="opis_en" required>'.stripslashes($opis_en).'</textarea>

	</fieldset>

	

	<fieldset>

		<label for="input_8">Obrazek <span class="star"></span></label>

		<input type="file" id="input_8" name="images" accept="image/jpeg,image/gif,image/png">

	</fieldset>

	

	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

	</form>');

}



?>