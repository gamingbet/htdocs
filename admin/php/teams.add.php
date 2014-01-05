<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$sent = false;

$errors = array();



if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

{

	$klan = addslashes(trim(htmlspecialchars($_POST['gaming'])));
	$gra = addslashes(trim(htmlspecialchars($_POST['game'])));

	$team = $db->query('SELECT * FROM `teams` WHERE `gameId` = "'.$gra.'" AND `gamingId` = "'.$klan.'"');
	if($team->rowCount() == 1)
		$errors[] = "Ten klan posiada już zarejestrowaną taką dywizję";
	$game = $db->query('SELECT * FROM `games` WHERE `id` = "'.$gra.'"');
	if($game->rowCount() == 0)
		$errors[] = "Gra, która została wybrana nie istnieje w systemie";
	$gaming = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$klan.'"');
	if($gaming->rowCount() == 0)
		$errors[] = "Organizacja, która zostałą wybrana nie istnieje w systemie";
	if(empty($errors)){
		$db->exec('INSERT INTO `teams` VALUES(NULL, "'.$gra.'", "'.$klan.'", 0, NOW())');
		$sent = true;
	}
}
else{
	$klan = '0';
	$gra = '0';
}

if($sent == true)

{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/teams/add">Dodaj nową drużynę</a></li>

		<li><a href="/admin/teams/edit">Zarządzaj drużynami</a></li>	

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

	

	echo('<fieldset>
		<label for="input_1">Gaming <span class="star">*</span></label>
		<select id="input_1" name="gaming" required>');
			$gamings = $db->query('SELECT * FROM `gamings` ORDER BY `fullname` ASC');
			if($klan == '0')
				echo('<option disabled selected>Wybierz organizację</option>');
			while($gaming = $gamings->fetch()){
				echo('<option value="'.$gaming['id'].'" ');if($klan == $gaming['id']){echo(' selected');}echo('>'.$gaming['fullname'].'</option>');
			}
		echo('</select>
	</fieldset>');

	echo('<fieldset>
		<label for="input_2">Gra <span class="star">*</span></label>
		<select id="input_2" name="game" required>');
			$games = $db->query('SELECT * FROM `games` ORDER BY `name` ASC');

			if($gra == '0')

				echo('<option disabled selected>Wybierz grę</option>');

			while($game = $games->fetch())

			{

				echo('<option value="'.$game['id'].'" ');if($gra == $game['id']){echo(' selected');}echo('>'.$game['name'].'</option>');

			}



		echo('</select>

	</fieldset>');

	

	echo('<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

	</form>');

}

?>

