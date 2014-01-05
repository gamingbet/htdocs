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

	@$game_id = (int) $_POST['event_id'];

	$game = $db->query('SELECT * FROM `events` WHERE `id` = '.$game_id);

	

	if($game->rowCount() == 0)

	{

		$errors[] = "Event o podanym ID nie istnieje";

	}

	else

	{

		$game = $game->fetch();

		

		$matches = $db->query('SELECT * FROM `matches` WHERE `eventsId` = "'.$game_id.'"');

		if($matches->rowCount() != 0)

		{

			$db->exec('UPDATE `matches` SET `eventsId` = 0 WHERE `eventsId` = "'.$game_id.'"');

		}

		

		@unlink('../files/images/events/'.$game['images']);

		$db->exec('DELETE FROM `events` WHERE `id` = '.$game_id.' LIMIT 1');

		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	}

}

else

{

	if( !empty($_PAGES['more'] ) )

	{

		@$slide_id = (int) $_PAGES['more'];

		$slider = $db->query('SELECT `id` FROM `events` WHERE `id` = '.$slide_id);

		if( $slider->rowCount() == 1)

		{

			$showAll = false;

		}

		else

		{

			$errors[] = "Event o podanym ID nie został odnaleziony";

		}

	}

}



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

	

	$events = $db->query('SELECT * FROM `events` WHERE `dataBegin` > NOW() AND `dataEnd` > NOW() AND `id` = '.$_PAGES['more']);

	$events1 = $db->query('SELECT * FROM `events` WHERE `dataBegin` < NOW() AND `dataEnd` > NOW() AND `id` = '.$_PAGES['more']);

	$events2 = $db->query('SELECT * FROM `events` WHERE `dataBegin` < NOW() AND `dataEnd` < NOW() AND `id` = '.$_PAGES['more']);

	if($events->rowCount() == 1)

	{

		$type = "begin";

		$events = $events->fetch();

	}

	else if($events1->rowCount() == 1)

	{

		$type = "live";

		$events = $events1->fetch();

	}

	else if($events2->rowCount() == 1)

	{

		$type = "end";

		$events = $events2->fetch();

	}

	

	//$begin_true = str_replace(array_keys($change), array_values($change), $begin);

	//$end_true = str_replace(array_keys($change), array_values($change), $end);

	

	$begin_true = $begin;

	$end_true = $end;

	

	if(empty($nazwa) || empty($gra) || empty($opis_pl) || empty($opis_en))

		$errors [] = "Niewszystkie wymagane pola zostały wypełnione";

	

	if(!empty($image['tmp_name']) && !isset($_POST['delete_logo']))

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



	$isset_game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$gra.'"');

	if($isset_game->rowCount() == 0)

		$errors[] = "Wybrana gra nie jest zarejestrowana w bazie";

	

	if($begin_time > $end_time)

		$errors[] = "Wydarzenie nie może zaczynać się później niż się skończyło";

		

	/*

	if( $type == "begin" || $type == "live")

	{

		if($begin_time < time() )

			$errors[] = "Wydarzenie nie może odbyć się w przeszłości";

		

		if($end_time < time() )

			$errors[] = "Wydarzenie nie może kończyć się w przeszłości";

	}

	

	if( ($end_time - $begin_time) < 60*60*24 )

		$errors[] = "Wydarzenie musi trwać przynajmniej 24 godziny";

	*/

	

	if($_POST['old_nazwa'] != $nazwa)

	{

		$sel = $db->query('SELECT * FROM `events` WHERE `name` = "'.$nazwa.'" AND `dataBegin` = "'.$begin_true.'"');

		if($sel->rowCount() != 0)

			$errors[] = "Takie wydarzenie już istnieje";

	}

	

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

			if(isset($_POST['delete_logo']) && $_POST['delete_logo'] == "true")

			{

				$filename = '';

				@unlink('../files/images/events/'.$events['images']);

			}

			else

				$filename = $events['images'];

		}

		

		$db->exec('UPDATE `events` SET `gameId` = "'.$gra.'", `name` = "'.$nazwa.'", `dataBegin` = "'.$begin_true.'", `dataEnd` = "'.$end_true.'", `description-pl` = "'.$opis_pl.'",

			`description-en` = "'.$opis_en.'", `url` = "'.$url.'", `images` = "'.$filename.'" WHERE `id` = "'.$_PAGES['more'].'" LIMIT 1');

		$sent = true;

	}

	

}

else

{

	if($showAll == false)

	{

		$slider = $db->query('SELECT * FROM `events` WHERE `id` = '.$_PAGES['more']);

		$events = $slider->fetch();

		$nazwa = $events['name'];

		$url = $events['url'];

		$gra = $events['gameId'];

		$opis_pl = $events['description-pl'];

		$opis_en = $events['description-en'];

		$begin = $events['dataBegin'];

		$end = $events['dataEnd'];

		$begin = str_replace(" ", "T", $begin);

		$begin = $begin."Z";

		$end = str_replace(" ", "T", $end);

		$end = $end."Z";

	}

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

	echo('<p>Wybierz events, który chcesz edytować poprzez kliknięcie odpowiedniej nazwy.. Aby usunąć events należy kliknąć ikonkę kosza. 

	Miej na uwadze to, że operacja usunięcia jest nieodwracalna. Jeżeli do eventsu są przypięte mecze, to zostaną one rozdzielone.</p>

	

	<ul class="tabs">

		<li><a href="#tab-najblizsze">Najbliższe</a></li>

		<li><a href="#tab-trwajace">Trwające</a></li>

		<li><a href="#tab-zakonczone">Zakończone</a></li>

	</ul>

	

	<div style="clear: both" class="tab_container">



		<div id="tab-najblizsze" class="tab_content">');

			$events = $db->query('SELECT * FROM `events` WHERE `dataBegin` > NOW() AND `dataEnd` > NOW() ORDER BY `dataBegin` DESC');

			if($events->rowCount() == 0)

			{

				echo('<h4 class="alert_info">Brak nadchodzących wydarzeń</h4>');

			}

			else

			{

				echo('<table class="tablesorter">

				<thead>

					<tr>

						<td style="width: 10%">ID</td>

						<td style="width: 25%">Gra</td>

						<td style="width: 60%">Nazwa</td>

						<td style="width: 5%">Usuń</td>

					</tr>

				</thead>');

				while($event = $events->fetch())

				{

					$game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$event['gameId'].'"')->fetch();

					echo('<tr>

						<td class="center">'.$event['id'].'</td>

						<td><img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'" style="vertical-align: middle"> '.$game['name'].'</td>

						<td><a href="'.$_ACTION.'/'.$event['id'].'">'.$event['name'].'</a></td>

						<td class="center">

							<form action="'.$_ACTION.'/remove" class="post_message" method="post">

								<input type="hidden" name="event_id" value="'.$event['id'].'">

								<input type="image" src="images/icn_trash.png" title="Kosz">

							</form>

						</td>

					</tr>');

				}

				echo('</table>');

			}

		echo('</div>

		

		<div id="tab-trwajace" class="tab_content">');

			$events = $db->query('SELECT * FROM `events` WHERE `dataBegin` < NOW() AND `dataEnd` > NOW() ORDER BY `dataBegin` DESC');

			if($events->rowCount() == 0)

			{

				echo('<h4 class="alert_info">Brak nadchodzących wydarzeń</h4>');

			}

			else

			{

				echo('<table class="tablesorter">

				<thead>

					<tr>

						<td style="width: 10%">ID</td>

						<td style="width: 25%">Gra</td>

						<td style="width: 60%">Nazwa</td>

						<td style="width: 5%">Usuń</td>

					</tr>

				</thead>');

				while($event = $events->fetch())

				{

					$game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$event['gameId'].'"')->fetch();

					echo('<tr>

						<td class="center">'.$event['id'].'</td>

						<td><img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'" style="vertical-align: middle"> '.$game['name'].'</td>

						<td><a href="'.$_ACTION.'/'.$event['id'].'">'.$event['name'].'</a></td>

						<td class="center">

							<form action="'.$_ACTION.'/remove" class="post_message" method="post">

								<input type="hidden" name="event_id" value="'.$event['id'].'">

								<input type="image" src="images/icn_trash.png" title="Kosz">

							</form>

						</td>

					</tr>');

				}

				echo('</table>');

			}

		echo('</div>

		

		<div id="tab-zakonczone" class="tab_content">');

			$events = $db->query('SELECT * FROM `events` WHERE `dataBegin` < NOW() AND `dataEnd` < NOW() ORDER BY `dataEnd` DESC');

			if($events->rowCount() == 0)

			{

				echo('<h4 class="alert_info">Brak nadchodzących wydarzeń</h4>');

			}

			else

			{

				echo('<table class="tablesorter">

				<thead>

					<tr>

						<td style="width: 10%">ID</td>

						<td style="width: 25%">Gra</td>

						<td style="width: 60%">Nazwa</td>

						<td style="width: 5%">Usuń</td>

					</tr>

				</thead>');

				while($event = $events->fetch())

				{

					$game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$event['gameId'].'"')->fetch();

					echo('<tr>

						<td class="center">'.$event['id'].'</td>

						<td><img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'" style="vertical-align: middle"> '.$game['name'].'</td>

						<td><a href="'.$_ACTION.'/'.$event['id'].'">'.$event['name'].'</a></td>

						<td class="center">

							<form action="'.$_ACTION.'/remove" class="post_message" method="post">

								<input type="hidden" name="event_id" value="'.$event['id'].'">

								<input type="image" src="images/icn_trash.png" title="Kosz">

							</form>

						</td>

					</tr>');

				}

				echo('</table>');

			}

		echo('</div>

	</div>');

	

}

else

{

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

		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message" enctype="multipart/form-data">

	

		<fieldset>

			<label for="input_1">Nazwa wydarzenia <span class="star">*</span></label>

			<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>

			<input type="hidden" name="old_nazwa" value="'.stripslashes($events['name']).'" required>

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

			<div style="clear: both;"><br>

				<input type="checkbox" name="delete_logo" value="true" id="input_11"> <label for="input_11">usunąć logo turnieju</label>

			</div><br>');

			if(file_exists('../files/images/events/'.$events['images']) && !empty($events['images']))

			{

				echo('

				<div style="clear: both;">

					<img src="/files/images/events/'.$events['images'].'" alt="aktualny obraz (jeśli puste = brak obrazu)">

				</div>');

			}

			echo('

		</fieldset>

		

		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>');

	}

}



?>