<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") ){
	exit();
}
$sent = false;
$randm = rand();
$errors = array();
if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$change["T"] = " ";
	$change["Z"] = NULL;
	$gra = addslashes(trim(htmlspecialchars($_POST['game'])));
	$wydarzenie = addslashes(trim(htmlspecialchars($_POST['event'])));
	$enemy1 = addslashes(trim(htmlspecialchars($_POST['enemy1'])));
	$enemy2 = addslashes(trim(htmlspecialchars($_POST['enemy2'])));
	$url = addslashes(trim(htmlspecialchars($_POST['url'])));
	$begin = addslashes(trim(htmlspecialchars($_POST['begin'])));
	$begin_true = $begin;
	$image = &$_FILES['images'];
	if(!empty($image['tmp_name'])){
		$access = array('image/gif', 'image/png', 'image/jpeg');
		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);
		if(!in_array($image['type'], $access))
			$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";
	}
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
				$filename = $randm.$ext;
				require 'SimpleImage.php';
				$img = new abeautifulsite\SimpleImage($image['tmp_name']);
				$img->resize(1000, 600)->save('../files/images/matches/' . $filename);
				$source = 'files/images/matches/'.$filename;
		}
	//$begin_true = str_replace(array_keys($change), array_values($change), $begin);
	if( $gra == '0' && $wydarzenie == '0' )
		$errors[] = "Nie wybrano gry i wydarzenia - nie można stworzyć meczu";
	if(empty($begin) || empty($enemy1) || empty($enemy2))
		$errors[] = "Niewypełniono wymaganych pól";
	if($enemy1 === $enemy2)
		$errors[] = "Nie można rozegrać meczu pomiędzy tymi samymi klanami";
	$klany = $db->query('SELECT `id` FROM `gamings` WHERE `id` = "'.$enemy1.'" OR `id` = "'.$enemy2.'"');
	if($klany->rowCount() != 2)
		$errors[] = "Wybrane klany nie istnieją w bazie";
	if( $wydarzenie != '0' ){
		$check_event = $db->query('SELECT * FROM `events` WHERE `dataEnd` > NOW() AND `id` = "'.$wydarzenie.'"');
		if($check_event->rowCount() == 0)
		{
			$errors[] = "Takie wydarzenie nie istnieje";
		}
		else{
			$event = $check_event->fetch();
			if( strtotime($begin_true) < strtotime($event['dataBegin']) )
				$errors[] = "Mecz nie może rozpocząć się szybciej niż wydarzenie";
			if( strtotime($begin_true) > strtotime($event['dataEnd']) )
				$errors[] = "Mecz nie może rozpocząć się później niż koniec wydarzenia";
			$gra = $event['gameId'];
		}
	}
	else{
		if($gra == '0')
			$errors[] = "Proszę wybrać grę";
		else{
			$check_game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$gra.'"');
			if($check_game->rowCount() == 0)
				$errors[] = "Gra, która została zaznaczona w systemie nie istnieje";
		}
		if(strtotime($begin_true) < time())
			$errors[] = "Wybrana data jest nieprawidłowa";
	}
	$double = $db->query('SELECT * FROM `matches` WHERE `gameId` = "'.$gra.'" AND ( ( `teamId-1` = "'.$enemy1.'" AND `teamId-2` = "'.$enemy2.'") OR ( `teamId-2` = "'.$enemy1.'" AND `teamId-1` = "'.$enemy2.'") ) AND `start` = "'.$begin_true.'"');
	if($double->rowCount() != 0)
		$errors[] = "Taki mecz już istnieje";
	if(empty($errors)){
		$db->exec('INSERT INTO `matches` VALUES(NULL, "'.$wydarzenie.'", "'.$gra.'", "'.$enemy1.'", "'.$enemy2.'", "'.$source.'", "true", NOW(), "'.$begin_true.'", "0000-00-00 00:00:00", "false", "0", "'.$url.'", "", "")');
		$sent = true;
	}
}
else{
	$gra = '0';
	$wydarzenie = '0';
	$enemy1 = '0';
	$enemy2 = '0';
	$begin = date(_SQLDate_);
	$url = "http://";
}

if($sent == true){
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/matches/add">Dodaj nowy mecz</a></li>
		<li><a href="/admin/matches/edit">Zarządzaj meczami</a></li>
		<li><a href="/admin/bets/add">Dodaj zakłady do meczów</a></li>
		<li><a href="/admin/bets/edit">Zarządzaj zakładami do meczów</a></li>
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
		<label for="input_1">Wydarzenie</label>
		<select id="input_1" name="event">');
			$events = $db->query('SELECT * FROM `events` WHERE `dataEnd` > NOW() ORDER BY `name` ASC');
			echo('<option value="0" ');if($wydarzenie == '0'){echo(' selected');}echo('>Wybierz wydarzenie</option>');
			while($event = $events->fetch()){
				echo('<option value="'.$event['id'].'" ');if($wydarzenie == $event['id']){echo(' selected');}echo('>'.$event['name'].'</option>');
			}
	echo('</select>
	<p class="center">Wartość tego pola pozwala zignorować następne. Jeżeli nie zostanie wybrane, to mecz nie zostanie przypisany do żadnego eventu.</p>
	</fieldset>

	

	<fieldset>
		<label for="input_2">Gra</label>
		<select id="input_2" name="game">');
			$games = $db->query('SELECT * FROM `games` ORDER BY `name` ASC');
			echo('<option value="0" ');if($gra == '0'){echo(' selected');}echo('>Wybierz grę jeśli nie wybrano wydarzenia</option>');
			while($game = $games->fetch()){
				echo('<option value="'.$game['id'].'" ');if($gra == $game['id']){echo(' selected');}echo('>'.$game['name'].'</option>');
			}



	echo('</select>
	<p class="center">Wartość tego pola zostanie zapomniana jeżeli zostanie wybrane wydarzenie, do którego zostanie przypisany mecz.</p>
	</fieldset>

	

	<fieldset>
		<label for="input_3">Gospodarze <span class="star">*</span></label>
		<select id="input_3" name="enemy1" required>');
			$teams = $db->query('SELECT * FROM `gamings` ORDER BY `fullname` ASC');
			if($enemy1 == '0')
				echo('<option disabled selected>Wybierz gospodarza meczu</option>');
			while($team = $teams->fetch()){
				echo('<option value="'.$team['id'].'" ');if($enemy2 == $team['id']){echo(' selected');}echo('>'.$team['fullname'].' - '.$team['tag'].'</option>');
			}
	echo('</select>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Goście <span class="star">*</span></label>
		<select id="input_4" name="enemy2" required>');
			$teams = $db->query('SELECT * FROM `gamings` ORDER BY `fullname` ASC');
			if($enemy2 == '0')
				echo('<option disabled selected>Wybierz gości meczu</option>');
			while($team = $teams->fetch()){
				echo('<option value="'.$team['id'].'" ');if($enemy2 == $team['id']){echo(' selected');}echo('>'.$team['fullname'].' - '.$team['tag'].'</option>');
			}
	echo('</select>
	</fieldset>

	<fieldset>

		<label for="input_5">Data rozpoczęcia <span class="star">*</span></label>
		<input type="text" id="input_5" name="begin" value="'.stripslashes($begin).'" required>
		<p class="center"><br>Jeżeli zostało wybrane wydarzenie, do którego ma zostać przypisany mecz, to upewnij się, że data tego meczu znajduje się pomiędzy rozpoczęciem, a zakończeniem 
		tego wydarzenia. W przeciwnym wypadku system nie zaakceptuje meczu.</p>
	</fieldset>

	<fieldset>
		<label for="input_6">Stream WWW <span class="star"></span></label>
		<input type="text" id="input_6" name="url" value="'.stripslashes($url).'">
	</fieldset>
	
	<fieldset>
		<label for="input_9">zdjęcie nagłowek<span class="star">**</span></label>
		<input type="file" id="input_9" name="images" accept="image/jpeg,image/gif,image/png">
	</fieldset>

	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>');

}



?>