<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$id = addslashes(trim(htmlspecialchars($_POST['team'])));
	$imie = addslashes(trim(htmlspecialchars($_POST['imie'])));
	$nazwisko = addslashes(trim(htmlspecialchars($_POST['nazwisko'])));
	if( empty($id) || empty($imie) || empty($nazwisko) )
		$errors[] = "Niewszystkie pola zostały wypełnione";
	
	$check_team = $db->query('SELECT * FROM `teams` WHERE `id` = "'.$id.'"');
	if($check_team->rowCount() == 0)
		$errors[] = "Zaznaczony Team-ID nie jest zarejestrowany w bazie";
		
	$player = $db->query('SELECT * FROM `players` WHERE `teamId` = "'.$id.'" AND `firstName` = "'.$imie.'" AND `surname` = "'.$nazwisko.'"');
	if($player->rowCount() != 0)
		$errors[] = "Taki zawodnik jest już zarejestrowany w drużynie";
	
	if(empty($errors))
	{
		$team = $check_team->fetch();
		$db->exec('INSERT INTO `players` VALUES(NULL, "'.$imie.'", "'.$nazwisko.'", "0000-00-00", "'.$team['gamingId'].'", "'.$id.'", "0", NOW(), "")');
		$sent = true;
	}
}
else
{
	$id = '0';
	$imie = NULL;
	$nazwisko = NULL;
}

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/players/add">Dodaj nowego gracza</a></li>
		<li><a href="/admin/players/edit">Zarządzaj graczami</a></li>
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
	echo('<form action="'.$_ACTION.'" method="post" class="post_message">');
	$gamings = $db->query('SELECT * FROM `teams` ORDER BY `gamingId` ASC');
	if($gamings->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak zarejestrowanych drużyn</h4>');
	}
	else
	{
		echo('<fieldset>
		<label for="input_1">Wybierz dywizję zawodnika <span class="star">*</span></label>
		<select id="input_1" name="team" required>');
		if($id == '0')
			echo('<option value="0" disabled selected>Wybierz klan oraz grę</option>');
		while($gaming = $gamings->fetch())
		{
			$game = $db->query('SELECT `name` FROM `games` WHERE `id` = "'.$gaming['gameId'].'"');
			$klan = $db->query('SELECT `fullname` FROM `gamings` WHERE `id` = "'.$gaming['gamingId'].'"');
			$game = $game->fetch();
			$klan = $klan->fetch();
			echo('<option value="'.$gaming['id'].'"');if($id == $gaming['id']){echo(' selected');}echo('>'.$klan['fullname'].' - '.$game['name'].'</option>');
		}
		
		echo('</select>
		</fieldset>');
		
		echo('<fieldset>
			<label for="input_2">Imię <span class="star">*</span></label>
			<input type="text" id="input_2" name="imie" value="'.stripslashes($imie).'" required>
		</fieldset>
		
		<fieldset>
			<label for="input_3">Nazwisko <span class="star">*</span></label>
			<input type="text" id="input_3" name="nazwisko" value="'.stripslashes($nazwisko).'" required>
		</fieldset>');
		
		echo('<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">');
	}
	echo('</form>');
}

?>