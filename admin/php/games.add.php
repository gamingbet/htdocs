<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

	$access = array('image/gif', 'image/png', 'image/jpeg');

	$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));

	$tag = addslashes(trim(htmlspecialchars($_POST['tag'])));

	$status = addslashes(trim(htmlspecialchars($_POST['status'])));

	$icon = &$_FILES['icon'];

	$image = &$_FILES['images'];

	

	if( empty($nazwa) || empty($tag) || empty($status) || empty($icon['tmp_name']) )

		$errors[] = "Niewszystkie wymagane pola zostały wypełnione";

	

	$check = $db->query('SELECT * FROM `games` WHERE `name` = "'.$nazwa.'" OR `short` = "'.$tag.'"');

	if($check->rowCount() != 0)

		$errors[] = "Gra o takej nazwie bądź tagu już jest zarejestrowana";

	

	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $tag))

		$errors[] = "Tag może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";

	

	list($width, $height, $type, $attr) = getimagesize($icon['tmp_name']);

	

	if(!in_array($icon['type'], $access))

		$errors[] = "Plik ikony powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

		
	/*
	if($width > 20 || $height > 20)

		$errors[] = "Maksymalne rozmiary pliku graficznego z ikoną nie może przekraczać 20x20 [px]";
	*/
	

	$upload_image = false;

	if(!empty($image['tmp_name']))

	{

		$upload_image = true;

		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

		if(!in_array($image['type'], $access))

			$errors[] = "Plik z logiem gry powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

			

		if($width > 2000 || $height > 2000)

			$errors[] = "Maksymalne rozmiary pliku graficznego z logiem gry nie może przekraczać 2000x2000 [px]";

	}

	

	if(empty($errors))

	{

		if($icon['type'] == 'image/gif')

		{

			$ext = '.gif';

		}

		else if($icon['type'] == 'image/png')

		{

			$ext = '.png';

		}

		else

		{

			$ext = '.jpg';

		}

		$ikona = $tag.$ext;

		//move_uploaded_file($icon['tmp_name'], '../files/images/icons/' . $ikona);

		require 'SimpleImage.php';

		$img = new abeautifulsite\SimpleImage($icon['tmp_name']);
		
		$img->resize(20, 20)->save('../files/images/icons/' . $ikona);
		

		if($upload_image == true)

		{

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

			$logo = $tag.$ext;

			move_uploaded_file($image['tmp_name'], '../files/images/logos/' . $logo);

		}

		else

		{

			$logo = '';

		}

		

		if($status == "true")

		{

			$policz = $db->query('SELECT `lp` FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` DESC LIMIT 1');

			$policz = $policz->fetch();	

			$lp = $policz['lp'] + 1;

		}

		else

		{

			$lp = 0;

		}

		

		$db->exec('INSERT INTO `games` VALUES(NULL, "'.$nazwa.'", "'.$tag.'", "'.$ikona.'", "'.$status.'", "'.$lp.'", NOW(), "0000-00-00", "'.$logo.'")');

		$sent = true;

	}

}

else

{

	$nazwa = NULL;

	$tag = NULL;

	$status = 'true';

}



if($sent == true)

{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/games/add">Dodaj nową grę</a></li>

		<li><a href="/admin/games/edit">Zarządzaj grami</a></li>

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

		<label for="input_1">Nazwa gry <span class="star">*</span></label>

		<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>

	</fieldset>

	

	<fieldset>

		<label for="input_2">TAG (skrót)<span class="star">*</span></label>

		<input type="text" id="input_2" name="tag" value="'.stripslashes($tag).'" required>

	</fieldset>

	

	<fieldset>

		<label for="input_3">Aktywna w menu? <span class="star">*</span></label>

		<select id="input_3" name="status" required>

			<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywna</option>

			<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywna</option>

		</select>

	</fieldset>

	

	<fieldset>

		<label for="input_4">Ikokna gry <span class="star">*</span></label>

		<input type="file" id="input_4" name="icon" accept="image/jpeg,image/gif,image/png" required>

	</fieldset>



	<fieldset>

		<label for="input_5">Obrazek z logiem gry</label>

		<input type="file" id="input_5" name="images" accept="image/jpeg,image/gif,image/png">

	</fieldset>

	

	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

	</form>');

}



?>