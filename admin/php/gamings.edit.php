<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();

$showAll = true;



if( $_PAGES['more'] == "remove" )

{

	@$gaming_id = (int) $_POST['gaming_id'];

	$gaming = $db->query('SELECT `id` FROM `gamings` WHERE `id` = '.$gaming_id);

	

	if($gaming->rowCount() == 0)

	{

		$errors[] = "Gaming o podanym ID nie istnieje";

	}

	else

	{	

		$matches = $db->query('SELECT * FROM `matches` WHERE `teamId-1` = "'.$gaming_id.'" OR `teamId-2` = "'.$gaming_id.'"');

		if($matches->rowCount() == 0)

		{

			$db->exec('DELETE FROM `gamings` WHERE `id` = '.$gaming_id.' LIMIT 1');

			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');	

		}		

		else

		{

			$errors[] = "Ten klan jest przypisany do jakiegoś meczu. W celu poprawnego wyświetlania informacji na stronie operacja usunięcia nie jest możliwa";

		}

	}

}

else

{

	if( !empty($_PAGES['more'] ) )

	{

		@$gaming_id = (int) $_PAGES['more'];

		$gaming = $db->query('SELECT `id` FROM `gamings` WHERE `id` = '.$gaming_id);

		if( $gaming->rowCount() == 1)

		{

			$showAll = false;

		}

		else

		{

			$errors[] = "Gaming o podanym ID nie został odnaleziony";

		}

	}

}



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

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

	

	if(!empty($image['tmp_name']))

	{

		$access = array('image/gif', 'image/png', 'image/jpeg');

		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

		

		if(!in_array($image['type'], $access))

			$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

			
		/*
		if($width > 135 || $height > 135)

			$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 135x135 [px]";
		*/

	}

	

	if($_POST['old_nazwa'] != $nazwa || $_POST['old_tag'] != $tag)

	{

		if($_POST['old_nazwa'] == $nazwa)

		{

			$sel_tag = $db->query('SELECT `id` FROM `gamings` WHERE `tag` = "'.$tag.'"');

			if($sel_tag->rowCount() == 1)

				$errors[] = "Klan o takim tagu już istnieje";

		}

		if($_POST['old_tag'] == $tag)

		{

			$sel_tag = $db->query('SELECT `id` FROM `gamings` WHERE `fullname` = "'.$nazwa.'"');

			if($sel_tag->rowCount() == 1)

				$errors[] = "Klan o takiej nazwie już istnieje";

		}

	}

	

	if(empty($errors))

	{

		if(!empty($image['tmp_name']))

		{

			

			$uniqId = $_POST['old_image'];

			$ex = explode('.', $uniqId);

			

			if($ex[0] == "noclan")

				$ex[0] = uniqid();

			

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

			@unlink('../files/images/logos/'.$ex[0].'.jpg');

			@unlink('../files/images/logos/'.$ex[0].'.gif');

			@unlink('../files/images/logos/'.$ex[0].'.png');

			

			$filename = $ex[0].$ext;

			//move_uploaded_file($image['tmp_name'], '../files/images/logos/' . $filename);

			require 'SimpleImage.php';

			$img = new abeautifulsite\SimpleImage($image['tmp_name']);
			
			$img->resize(135, 135)->save('../files/images/logos/' . $filename);

			$source = 'files/images/logos/'.$filename;

		}

		else

		{

			$source = 'files/images/logos/'.$_POST['old_image'];

		}

		

		$db->exec('UPDATE `gamings` SET `fullname` = "'.$nazwa.'", `tag` = "'.$tag.'", `release` = "'.$utworzony.'", 

			`url` = "'.$www.'", `description-pl` = "'.$opis_pl.'", `description-en` = "'.$opis_en.'", `active` = "'.$status.'", `image` = "'.$source.'" WHERE

			`id` = "'.$gaming_id.'"');

		

		$sent = true;

		

	}

}

else

{

	@$gaming_id = (int) $_PAGES['more'];

	$gaming = $db->query('SELECT * FROM `gamings` WHERE `id` = '.$gaming_id);

	$gaming = $gaming->fetch();

	$nazwa = $gaming['fullname'];

	$tag = $gaming['tag'];

	$utworzony = ($gaming['release'] == "0000-00-00")?NULL:$gaming['release'];

	$www = $gaming['url'];

	$opis_pl = $gaming['description-pl'];

	$opis_en = $gaming['description-en'];

	$status = $gaming['active'];

}



if($sent == true)

{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/gamings/add">Dodaj nowy klan</a></li>

		<li><a href="/admin/gamings/edit">Zarządzaj klanami</a></li>	

	</ul>');

}



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



if($showAll == true)

{

	echo('<p>Aby edytować klan należy kliknąć jego pełną nazwe. Aby usunąć klan, należy kliknąć ikonkę kosza. 

	Miej na uwadze to, że operacja usunięcia jest nieodwracalna, a jeżeli klan jest przypisany do jakiegokolwiek meczu to operacja usunięcia nie jest możliwa.</p>');

	

	echo('<h4>Aktywne klany</h4>');

	

	$active = $db->query('SELECT * FROM `gamings` WHERE `active` = "true"');

	if($active->rowCount() == 0)

	{

		echo('<h4 class="alert_info">Brak aktywnych klanów!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		<thead>

			<tr>

				<td style="width: 10%">ID</td>

				<td style="width: 70%">Nazwa</td>

				<td style="width: 15%">Tag</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($team = $active->fetch())

		{

			echo('<tr>

				<td>'.$team['id'].'</td>

				<td><a href="'.$_ACTION.'/'.$team['id'].'">'.$team['fullname'].'</a></td>

				<td>'.$team['tag'].'</td>

				<td class="center">

				<form action="'.$_ACTION.'/remove" class="post_message" method="post">

					<input type="hidden" name="gaming_id" value="'.$team['id'].'">

					<input type="image" src="images/icn_trash.png" title="Kosz">

				</form>

			</td>

			</tr>');

		}

		

		echo('</table>');

	}

	

	echo('<h4>Nieaktywne klany</h4>');

	

	$noactive = $db->query('SELECT * FROM `gamings` WHERE `active` = "false"');

	if($noactive->rowCount() == 0)

	{

		echo('<h4 class="alert_info">Brak nieaktywnych klanów!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		<thead>

			<tr>

				<td style="width: 10%">ID</td>

				<td style="width: 70%">Nazwa</td>

				<td style="width: 15%">Tag</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($team = $noactive->fetch())

		{

			echo('<tr>

				<td>'.$team['id'].'</td>

				<td><a href="'.$_ACTION.'/'.$team['id'].'">'.$team['fullname'].'</a></td>

				<td>'.$team['tag'].'</td>

				<td class="center">

				<form action="'.$_ACTION.'/remove" class="post_message" method="post">

					<input type="hidden" name="gaming_id" value="'.$team['id'].'">

					<input type="image" src="images/icn_trash.png" title="Kosz">

				</form>

			</td>

			</tr>');

		}

		

		echo('</table>');

	}

	

	

}

else

{

	if($sent == false)

		{

		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message" enctype="multipart/form-data">

		

		<fieldset>

			<label for="input_1">Pełna nazwa <span class="star">*</span></label>

			<input type="text" id="input_1" name="nazwa" value="'.$nazwa.'" required>

			<input type="hidden" name="old_nazwa" value="'.$gaming['fullname'].'">

		</fieldset>



		<fieldset>

			<label for="input_2">TAG <span class="star">*</span></label>

			<input type="text" id="input_2" name="tag" value="'.$tag.'" required>

			<input type="hidden" name="old_tag" value="'.$gaming['tag'].'">

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

			<label for="input_6">Utworzony [YYYY-MM-DD] <span class="star"></span></label>

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

		

		');

		if(!empty($gaming['image']))

		{

			$ex = explode("/", $gaming['image']);

			

		echo('<fieldset>

			<label>Aktualne logo</label><br>

			<img src="/'.$gaming['image'].'" alt="old image">

			<input type="hidden" name="old_image" value="'.end($ex).'">

		</fieldset>');

		}

		echo('

		

		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>');

	}

}





?>