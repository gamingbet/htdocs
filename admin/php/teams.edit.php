<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( !empty($_PAGES['more'] ) )
{
	@$team_id = (int) $_PAGES['more'];
	$team = $db->query('SELECT `id` FROM `teams` WHERE `id` = '.$team_id);
	if( $team->rowCount() == 1)
	{
		$players = $db->query('SELECT `id` FROM `players` WHERE `teamId` = "'.$team_id.'"');
		if($players->rowCount() != 0)
		{
			while($player = $players->fetch())
			{
				$db->exec('UPDATE `players` SET `teamId` = "0" WHERE `id` = "'.$player['id'].'"');
			}
		}
		$db->exec('DELETE FROM `teams` WHERE `id` = "'.$team_id.'"');
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
	else
	{
		$errors[] = "Drużyna o podanym ID nie został odnaleziony";
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

$gamings = $db->query('SELECT * FROM `gamings` ORDER BY `id` ASC');
if($gamings->rowCount() == 0)
{
	echo('<h4 class="alert_info">Brak klanów!</h4>');
}
else
{
	$all = 0;
	echo('<p>Usunięcie dywizji jeśli ma przypisanych graczy przesunie tych graczy do grupy graczy "bezklanowej". Aby usunąć dywizję należy kliknąć nazwę gry przy odpowiednim klanie.</p>');
	while($gaming = $gamings->fetch())
	{
		$teams = $db->query('SELECT * FROM `teams` WHERE `gamingId` = "'.$gaming['id'].'" ORDER BY `registrer` DESC');
		if($teams->rowCount() != 0)
		{
			echo('<h3>'.$gaming['fullname'].'</h3>');
			echo('<ul>');
			while($team = $teams->fetch())
			{
				$all++;
				$game = $db->query('SELECT `name` FROM `games` WHERE `id` = "'.$team['gameId'].'"');
				$game = $game->fetch();
				$players = $db->query('SELECT `id` FROM `players` WHERE `gamingId` = "'.$team['gamingId'].'" AND `teamId` = "'.$team['id'].'"');
				echo('<li><tt>'.$team['registrer'].'</tt> - <a href="'.$_ACTION.'/'.$team['id'].'">'.$game['name'].'</a> - graczy: <tt>'.$players->rowCount().'</tt></li>');
			}
			echo('</ul>');
		}
	}
	if($all == 0)
	{
		echo('<h4 class="alert_info">Brak zarejestrowanych drużyn</h4>');
	}
}

?>