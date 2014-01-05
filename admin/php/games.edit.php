<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();

$showAll = true;



if( $_PAGES['more'] == "move" )

{

	$active = $db->query('SELECT * FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` ASC');

	@$game_id = (int) $_POST['game_id'];

	$game = $db->query('SELECT `id`, `lp` FROM `games` WHERE `menuDisplay` = "true" AND `id` = '.$game_id);

	

	if($game->rowCount() == 0)

	{

		$errors[] = "Gra o podanym ID nie istnieje";

	}

	else

	{

		$game = $game->fetch();

		

		if($_POST['action'] == "up")

		{

			$new = $game['lp']-1;

			if($new == 0)

			{

				$errors[] = "Tego elementu wyżej przenieść już nie można";

			}

		}

		else if($_POST['action'] == "bottom")

		{

			$new = $game['lp']+1;

			if($new > $active->rowCount())

			{

				$errors[] = "Tego elementu niżej przenieść już nie można";				

			}

		}

		else

		{

			$errors[] = "Nie wybrano odpowiedniej akcji";

		}

		

		if(empty($errors))

		{

			if( $_POST['action'] == "up" )

			{				

				$db->exec('UPDATE `games` SET `lp` = `lp`+1 WHERE `menuDisplay` = "true" AND `lp` = '.$new.' LIMIT 1');

				$db->exec('UPDATE `games` SET `lp` = `lp`-1 WHERE `menuDisplay` = "true" AND `id` = '.$game_id.' LIMIT 1');

			}

			else

			{

				$db->exec('UPDATE `games` SET `lp` = `lp`-1 WHERE `menuDisplay` = "true" AND `lp` = '.$new.' LIMIT 1');

				$db->exec('UPDATE `games` SET `lp` = `lp`+1 WHERE `menuDisplay` = "true" AND `id` = '.$game_id.' LIMIT 1');

			}

			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

		}

	}	

}

else if( $_PAGES['more'] == "remove" )

{

	@$game_id = (int) $_POST['game_id'];

	$game = $db->query('SELECT * FROM `games` WHERE `id` = '.$game_id);

	

	if($game->rowCount() == 0)

	{

		$errors[] = "Gra o podanym ID nie istnieje";

	}

	else

	{		

		$active = $db->query('SELECT * FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` ASC');

		$all = $active->rowCount();

		$game = $game->fetch();

		

		if($game['menuDisplay'] == "true")

		{

			$current = $game['lp'];

					

			for( $i = $current; $i <= $all; $i++)

			{

				$db->exec('UPDATE `games` SET `lp` = `lp` - 1 WHERE `menuDisplay` = "true" AND `lp` = '.$i.' LIMIT 1');

			}

		}

		

		@unlink('../files/images/icons/'.$game['logo']);

		@unlink('../files/images/logos/'.$game['images']);

		$db->exec('DELETE FROM `games` WHERE `id` = '.$game_id.' LIMIT 1');

		

		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	}

}

else

{

	if( !empty($_PAGES['more'] ) )

	{

		@$slide_id = (int) $_PAGES['more'];

		$slider = $db->query('SELECT `id` FROM `games` WHERE `id` = '.$slide_id);

		if( $slider->rowCount() == 1)

		{

			$showAll = false;

		}

		else

		{

			$errors[] = "Gra o podanym ID nie została odnaleziona";

		}

	}

}



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

	$access = array('image/gif', 'image/png', 'image/jpeg');

	$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));

	$tag = addslashes(trim(htmlspecialchars($_POST['tag'])));

	$status = addslashes(trim(htmlspecialchars($_POST['status'])));

	$icon = &$_FILES['icon'];

	$image = &$_FILES['images'];

	

	@$slide_id = (int) $_PAGES['more'];

	$slider = $db->query('SELECT * FROM `games` WHERE `id` = '.$slide_id);

	$game = $slider->fetch();	

	

	if( empty($nazwa) || empty($tag) || empty($status) )

		$errors[] = "Niewszystkie wymagane pola zostały wypełnione";

	

	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $tag))

		$errors[] = "Tag może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";

	

	if($_POST['old_nazwa'] != $nazwa || $_POST['old_tag'] != $tag)

	{

		if($_POST['old_nazwa'] == $nazwa)

		{

			$sel_tag = $db->query('SELECT `id` FROM `games` WHERE `short` = "'.$tag.'"');

			if($sel_tag->rowCount() == 1)

				$errors[] = "Gra o takim tagu już istnieje";

		}

		if($_POST['old_tag'] == $tag)

		{

			$sel_tag = $db->query('SELECT `id` FROM `games` WHERE `name` = "'.$nazwa.'"');

			if($sel_tag->rowCount() == 1)

				$errors[] = "Gra o takiej nazwie już istnieje";

		}

	}

	

	$upload_icon = false;

	if( !empty($icon['tmp_name']) )

	{

		$upload_icon = true;

		list($width, $height, $type, $attr) = getimagesize($icon['tmp_name']);

		if(!in_array($icon['type'], $access))

			$errors[] = "Plik ikony powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

			
		/*
		if($width > 20 || $height > 20)

			$errors[] = "Maksymalne rozmiary pliku graficznego z ikoną nie może przekraczać 20x20 [px]";
		*/
	}

	else

	{

		$sql_icon = '';

	}

	

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

	else

	{

		$sql_logo = '';

	}

	

	if( empty( $errors) )

	{

		if($sql_logo == '')

		{

			if(isset($_POST['delete_logo']) && $_POST['delete_logo'] == "true")

			{

				@unlink('../files/images/logos/'.$game['images']);

			}

		}

		

		if( $upload_icon == true )

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

			$sql_icon = ' `logo` = "'.$ikona.'",';

		}

		

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

			$sql_logo = ' `images` = "'.$logo.'",';

		}

		

		// jesli zmieniamy z nieaktywnego na aktywny, to ustaw na samym dole

		if( $game['menuDisplay'] == "false" && $status == "true")

		{

			$policz = $db->query('SELECT `lp` FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` DESC LIMIT 1');

			$policz = $policz->fetch();	

			$lp = $policz['lp']+1;

		}

		else

		{

			$lp = $game['lp'];

		}

		

		// jesli zmieniamy status na false, to wszystkie ponizej przesuwamy w gore

		if($game['menuDisplay'] == "true" && $status == "false")

		{

			$active = $db->query('SELECT * FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` ASC');

			$all = $active->rowCount();

			$current = $game['lp'];

			

			for( $i = $current; $i <= $all; $i++)

			{

				$db->exec('UPDATE `games` SET `lp` = `lp` - 1 WHERE `menuDisplay` = "true" AND `lp` = '.$i.' LIMIT 1');

			}

			$lp = 0;

		}

		

		$db->exec('UPDATE `games` SET '.$sql_icon . $sql_logo.' `name` = "'.$nazwa.'", `short` = "'.$tag.'", `lp` = "'.$lp.'", `menuDisplay` = "'.$status.'" WHERE `id` = "'.$_PAGES['more'].'" LIMIT 1');

		$sent = true;

	}

}

else

{

	@$slide_id = (int) $_PAGES['more'];

	$slider = $db->query('SELECT * FROM `games` WHERE `id` = '.$slide_id);

	$game = $slider->fetch();

	$nazwa = $game['name'];

	$tag = $game['short'];

	$status = $game['menuDisplay'];

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

	echo('<p>Wybierz grę, którą chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć grę należy kliknąć ikonkę kosza. 

		Miej na uwadze to, że operacja usunięcia jest nieodwracalna.</p>');

	echo('<h4>Gry aktywne w menu</h4>');

	$active = $db->query('SELECT * FROM `games` WHERE `menuDisplay` = "true"  ORDER BY `lp` ASC');

	if($active->rowCount() == 0)

	{

		echo('<h4 class="alert_info">Brak aktywnych gier w menu!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		

		<thead>

			<tr>

				<td style="width: 10%">LP</td>

				<td style="width: 10%">Ikona</td>

				<td style="width: 60%">Nazwa</td>

				<td style="width: 15%" colspan="2">Przesuń</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($game = $active->fetch())

		{

			echo('<tr>

				<td class="center">'.$game['lp'].'</td>

				<td><img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'"></td>

				<td><a href="'.$_ACTION.'/'.$game['id'].'">'.$game['name'].'</a></td>

				<td>

					<form action="'.$_ACTION.'/move" class="post_message" method="post">

						<input type="hidden" name="game_id" value="'.$game['id'].'">

						<input type="hidden" name="action" value="up">');

					if( $game['lp']-1 == 0 )

					{

						echo('<input type="image" src="images/top_noactive.png" title="UP" disabled>');

					}

					else

					{

						echo('<input type="image" src="images/top.png" title="UP">');

					}

			echo('</form>

				</td>

				<td>

					<form action="'.$_ACTION.'/move" class="post_message" method="post">

						<input type="hidden" name="game_id" value="'.$game['id'].'">

						<input type="hidden" name="action" value="bottom">');

					if( $game['lp']+1 > $active->rowCount() )

					{

						echo('<input type="image" src="images/bottom_noactive.png" title="BOTTOM" disabled>');

					}

					else

					{

						echo('<input type="image" src="images/bottom.png" title="BOTTOM">');

					}

			echo('</form>

				</td>

				<td class="center">

					<form action="'.$_ACTION.'/remove" class="post_message" method="post">

						<input type="hidden" name="game_id" value="'.$game['id'].'">

						<input type="image" src="images/icn_trash.png" title="Kosz">

					</form>

				</td>

			</tr>');

		}

		

		echo('</table>');

	}

	

	echo('<h4>Pozostałe gry</h4>');

	$active = $db->query('SELECT * FROM `games` WHERE `menuDisplay` = "false"  ORDER BY `id` ASC');

	if($active->rowCount() == 0)

	{

		echo('<h4 class="alert_info">Brak pozostałych gier!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		

		<thead>

			<tr>

				<td style="width: 10%">ID</td>

				<td style="width: 10%">Ikona</td>

				<td style="width: 75%">Nazwa</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($game = $active->fetch())

		{

			echo('<tr>

				<td class="center">'.$game['id'].'</td>

				<td><img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'"></td>

				<td><a href="'.$_ACTION.'/'.$game['id'].'">'.$game['name'].'</a></td>

				<td class="center">

					<form action="'.$_ACTION.'/remove" class="post_message" method="post">

						<input type="hidden" name="game_id" value="'.$game['id'].'">

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

		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message" enctype="multipart/form-data">

	

		<fieldset>

			<label for="input_1">Nazwa gry <span class="star">*</span></label>

			<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>

			<input type="hidden" name="old_nazwa" value="'.$game['name'].'">

		</fieldset>

		

		<fieldset>

			<label for="input_2">TAG (skrót)<span class="star">*</span></label>

			<input type="text" id="input_2" name="tag" value="'.stripslashes($tag).'" required>

			<input type="hidden" name="old_tag" value="'.$game['short'].'">

		</fieldset>

		

		<fieldset>

			<label for="input_3">Aktywna w menu? <span class="star">*</span></label>

			<select id="input_3" name="status" required>

				<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywna</option>

				<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywna</option>

			</select>

		</fieldset>

		

		<fieldset>

			<label for="input_4">Ikona gry <img src="/files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'"></label>

			<input type="file" id="input_4" name="icon" accept="image/jpeg,image/gif,image/png">

		</fieldset>



		<fieldset>

			<label for="input_5">Obrazek z logiem gry</label>

			<input type="file" id="input_5" name="images" accept="image/jpeg,image/gif,image/png">

			<div style="clear: both;"><br>

				<input type="checkbox" name="delete_logo" value="true" id="input_6"> <label for="input_6">usunąć logo gry</label>

			</div><br>');

			if(file_exists('../files/images/logos/'.$game['images']) && !empty($game['images']))

			{

				echo('

				<div style="clear: both;">

					<img src="/files/images/logos/'.$game['images'].'" alt="aktualny obraz (jeśli puste = brak obrazu)">

				</div>');

			}

			echo('

		</fieldset>

		

		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>');

	}

}



?>