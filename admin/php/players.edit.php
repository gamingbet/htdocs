<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();
$showAll = true;

if( !empty($_PAGES['more'] ) && $_PAGES['more'] != "remove" )
{
	$team_id = (int) $_PAGES['more'];
	$team = $db->query('SELECT `id` FROM `players` WHERE `id` = '.$team_id);
	if($team->rowCount() == 1)
	{
		$showAll = false;
	}
	else
	{
		$errors[] = "Zawodnik o takim ID nie istnieje";
	}
}
else if( $_PAGES['more'] == "remove" )
{
	@$player_id = (int) $_POST['player_id'];
	$player = $db->query('SELECT * FROM `players` WHERE `id` = '.$player_id);
	
	if($player->rowCount() == 0)
	{
		$errors[] = "Gracz o podanym ID nie istnieje";
	}
	else
	{		
		$db->exec('DELETE FROM `players` WHERE `id` = "'.$player_id.'" LIMIT 1');
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
}

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
		
	if($_POST['old_imie'] != $imie || $_POST['old_nazwisko'] != $nazwisko)
	{
		$player = $db->query('SELECT * FROM `players` WHERE `teamId` = "'.$id.'" AND `firstName` = "'.$imie.'" AND `surname` = "'.$nazwisko.'"');
		if($player->rowCount() != 0)
			$errors[] = "Taki zawodnik jest już zarejestrowany w drużynie";
	}
	
	if(empty($errors))
	{
		$team = $check_team->fetch();
		$db->exec('UPDATE `players` SET `firstName` = "'.$imie.'", `surname` = "'.$nazwisko.'", `teamId` = "'.$id.'", `gamingId` = "'.$team['gamingId'].'" WHERE `id` = "'.$_PAGES['more'].'" LIMIT 1');
		$sent = true;
	}
}
else
{
	$team_id = (int) $_PAGES['more'];
	$team = $db->query('SELECT * FROM `players` WHERE `id` = '.$team_id);
	$team = $team->fetch();
	$id = $team['teamId'];
	$imie = $team['firstName'];
	$nazwisko = $team['surname'];
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
	$gamings = $db->query('SELECT * FROM `gamings` ORDER BY `id` ASC');
	if($gamings->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak klanów!</h4>');
	}
	else
	{
		$all = 0;
		echo('<p>Kliknij zawodnika, którego chcesz edytować.</p>');
		while($gaming = $gamings->fetch())
		{
			$teams = $db->query('SELECT * FROM `teams` WHERE `gamingId` = "'.$gaming['id'].'" ORDER BY `registrer` DESC');
			if($teams->rowCount() != 0)
			{
				while($team = $teams->fetch())
				{
					$game = $db->query('SELECT `name` FROM `games` WHERE `id` = "'.$team['gameId'].'"');
					$game = $game->fetch();
					$players = $db->query('SELECT * FROM `players` WHERE `gamingId` = "'.$team['gamingId'].'" AND `teamId` = "'.$team['id'].'"');
					
					if($players->rowCount() != 0)
					{
						echo('<h3>'.$gaming['fullname'].' - '.$game['name'].'</h3>');
						echo('<ul>');
					}
					if($players->rowCount() != 0)
					{				
						$all++;
						while($player = $players->fetch())
						{
							echo('<li>
							<form action="'.$_ACTION.'/remove" class="post_message" method="post" style="display: inline-block; vertical-align: middle; margin-right: 5px;">
								<input type="hidden" name="player_id" value="'.$player['id'].'">
								<input type="image" src="images/icn_trash.png" title="Kosz">
							</form>
							<a href="'.$_ACTION.'/'.$player['id'].'">'.$player['firstName'].' '.$player['surname'].'</a>
							</li>');
						}					
					}
					
					if($players->rowCount() != 0)
					{
						echo('</ul>');
					}
				}
				
			}
		}
		if($all == 0)
		{
			echo('<h4 class="alert_info">Brak zarejestrowanych graczy</h4>');
		}
		$players = $db->query('SELECT * FROM `players` WHERE `teamId` = "0"');
		if($players->rowCount() != 0)
		{
			echo('<h4>Gracze nieprzypisani do żadnego klanu</h4>
			<ul>');
			while($player = $players->fetch())
			{
				echo('<li>
				<form action="'.$_ACTION.'/remove" class="post_message" method="post" style="display: inline-block; vertical-align: middle; margin-right: 5px;">
					<input type="hidden" name="player_id" value="'.$player['id'].'">
					<input type="image" src="images/icn_trash.png" title="Kosz">
				</form>
				<a href="'.$_ACTION.'/'.$player['id'].'">'.$player['firstName'].' '.$player['surname'].'</a>
				</li>');
			}	
			echo('</ul>');
		}
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
			<li><a href="/admin/players/add">Dodaj nowego gracza</a></li>
			<li><a href="/admin/players/edit">Zarządzaj graczami</a></li>	
		</ul>');
	}
	else
	{
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">');
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
				<input type="hidden" name="old_imie" value="'.stripslashes($team['firstName']).'">
			</fieldset>
			
			<fieldset>
				<label for="input_3">Nazwisko <span class="star">*</span></label>
				<input type="text" id="input_3" name="nazwisko" value="'.stripslashes($nazwisko).'" required>
				<input type="hidden" name="old_nazwisko" value="'.stripslashes($team['surname']).'">
			</fieldset>');
			
			echo('<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">');
		}
		echo('</form>');
	}
}

?>