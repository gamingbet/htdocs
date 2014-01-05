<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$prepare = $db->query('SELECT * FROM `langs` WHERE `category` = "bets"');
while($p = $prepare->fetch())
{
	$_LANG[ $p['label'] ] = $p['label-pl'];
}

$sent = false;
$errors = array();
$showAll = true;

if( !empty($_PAGES['more']) )
{
	$id = @$_PAGES['more'];
	$bet = $db->query('SELECT * FROM `matches` WHERE `start` < NOW()  AND  `id` = "'.$id.'"');
	
	if($bet->rowCount() == 0)
		$errors[] = "Mecz o takim ID nie istnieje";
	else
	{
		$bs = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$id.'"');
		if($bs->rowCount() == 0)
		{
			$errors[] = "Ten mecz nie ma żadnych zakładów";
		}
		else
		{
			$showAll = false;
		}
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	foreach($_POST['bets'] as $bet => $values)
	{
		$db->exec('UPDATE `bets` SET `optionWin` = "'.$values['win'].'" WHERE `id` = "'.$bet.'" LIMIT 1');
	}
	$sent = true;
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
		<li><a href="/admin/matches/add">Dodaj nowy mecz</a></li>
		<li><a href="/admin/matches/edit">Zarządzaj meczami</a></li>
		<li><a href="/admin/bets/add">Dodaj zakłady do meczów</a></li>
		<li><a href="/admin/bets/edit">Zarządzaj zakładami do meczów</a></li>
		<li><a href="/admin/bets/finish">Wprowadź wyniki do zakładów</a></li>
		<li><a href="/admin/matches/finish/'.$_PAGES['more'].'"><b>Zakończ ten mecz</b></a></li>
	</ul>');
}
else
{
	if($showAll == true)
	{
		$matches = $db->query('SELECT * FROM `matches` WHERE `finish` = "false" AND `start` < NOW() ORDER BY `id` ASC');
		if($matches->rowCount() == 0)
		{
			echo('<h4 class="alert_info">Brak meczów, w których możesz wprowadzić wyniki!</h4>');
		}
		else
		{
			echo('<p>Dzięki temu menu możesz wprowadzić wyniki zakłady do meczów. Wybierz odpowiedni mecz klikając na klany, między którymi rozpocznie się mecz.
			Na tej liście wyświetlają się mecze, które się jeszcze nie rozpoczęły i posiadają conajmniej 1 zakład przypisany do meczu.</p>');
			
			echo('<table class="tablesorter">
			<thead>
				<tr>
					<td style="width: 10%">ID</td>
					<td style="width: 70%">Mecz pomiędzy</td>
					<td style="width: 20%">Ilość zakładów</td>
				</tr>
			</thead>');
			while($match = $matches->fetch())
			{
				$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['id'].'"');
				if($bets->rowCount() != 0)
				{
					$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
					$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
					echo('<tr>
						<td class="center">'.$match['id'].'</td>
						<td><a href="'.$_ACTION.'/'.$match['id'].'">'.$klan1['fullname'].' <tt>vs.</tt> '.$klan2['fullname'].'</a></td>
						<td class="center">'.$bets->rowCount().'</td>
					</tr>');
				}
			}
		}
	}
	else
	{
		$m = $db->query('SELECT * FROM `matches` WHERE `id` = "'.$_PAGES['more'].'"')->fetch();
		$k1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-1'].'"')->fetch();
		$k2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-2'].'"')->fetch();
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
	
		<div id="bets">
		');
		$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$_PAGES['more'].'" ORDER BY `active` ASC');
		while($bet = $bets->fetch())
		{
			$zaklad = $db->query('SELECT * FROM `bettypes` WHERE `id` = "'.$bet['typeId'].'" LIMIT 1')->fetch();
			echo('<fieldset>
			<input type="hidden" name="bets['.$bet['id'].'][id]" value="'.$bet['typeId'].'">
			<table class="tablesorter">
			
			<tr>
				<td colspan="2">
					<p>Która drużyna zwyciężyła zakład <tt>'.$_LANG[$zaklad['type']].'</tt></p>
					<select name="bets['.$bet['id'].'][win]" required>
						<option value="0"');if($bet['optionWin'] == "0"){echo(' selected');}echo('>Zakład nierozstrzygnięty</option>
						<option value="1"');if($bet['optionWin'] == "1"){echo(' selected');}echo('>'.$k1['fullname'].'</option>
						<option value="2"');if($bet['optionWin'] == "2"){echo(' selected');}echo('>'.$k2['fullname'].'</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<p>Status zakładu</p>
					<select name="empty_value" required disabled>
						<option value="true"');if($bet['active'] == "true"){echo(' selected');}echo('>Aktywny</option>
						<option value="false"');if($bet['active'] == "false"){echo(' selected');}echo('>Nieaktywny</option>
					</select>
				</td>
			</tr>
			
			</table>
			</fieldset>');
		}
		echo('
		</div>
		<br><input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		</form>');
	}
}

?>