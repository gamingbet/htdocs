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
	$bet = $db->query('SELECT * FROM `matches` WHERE `id` = "'.$id.'" AND `start` > NOW() AND `finish` = "false"');
	
	if($bet->rowCount() == 0)
		$errors[] = "Mecz o takim ID nie istnieje";
	else
	{
		$showAll = false;
		$_BETS = array();
		$bs = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$id.'" AND `active` = "true"');
		
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	for($i = 0, $all = count($_POST['bets']['typ']); $i < $all; $i++)
	{
		$type = $db->query('SELECT * FROM `bettypes` WHERE `id` = "'.$_POST['bets']['typ'][$i].'"');
		if($type->rowCount() == 0)
		{
			if( !isset($errors['bets']) )
				$errors['bets'] = "W twoim żądaniu znajdowały się błędne zakłady - zostały one usunięte";
		}
		else
		{
			$row = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$id.'" AND `active` = "true" AND `typeId` = "'.$_POST['bets']['typ'][$i].'"');
			if($row->rowCount() == 1)
			{
				if( !isset($errors['replace']) )
					$errors['replace'] = "Zakłady, które chciałeś dodać są już przypisane do tego meczu i zostały usunięte";
			}
			else
			{
				if( isset( $_BETS[ $_POST['bets']['typ'][$i] ] ) )
				{
					if( !isset($errors['replace2x']) )
						$errors['replace2x'] = "Część zakładów, które zostały wprowadzonych się powtarzały i zostały usunięte";
				}
				else
					$_BETS[$_POST['bets']['typ'][$i]] = array($_POST['bets']['typ'][$i], getScore($_POST['bets']['kurs-1'][$i]), getScore($_POST['bets']['kurs-2'][$i]));
			}
		}
	}
	
	if(empty($errors))
	{
		foreach($_BETS as $zaklady)
		{
			$db->exec('INSERT INTO `bets` VALUES(NULL, "'.$_PAGES['more'].'", "'.getScore($zaklady[0]).'", "'.getScore($zaklady[1]).'", "'.$zaklady[2].'", "0", "true", "0")');
		}
		$sent = true;
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
		<li><a href="/admin/matches/add">Dodaj nowy mecz</a></li>
		<li><a href="/admin/matches/edit">Zarządzaj meczami</a></li>
		<li><a href="/admin/bets/add">Dodaj zakłady do meczów</a></li>
		<li><a href="/admin/bets/edit">Zarządzaj zakładami do meczów</a></li>
	</ul>');
}
else
{
	if($showAll == true)
	{
		$matches = $db->query('SELECT * FROM `matches` WHERE `start` > NOW() AND `finish` = "false" ORDER BY `id` ASC');
		if($matches->rowCount() == 0)
		{
			echo('<h4 class="alert_info">Brak meczów, które można obstawić!</h4>');
		}
		else
		{
			echo('<p>Dzięki temu menu możesz dodać zakłady do meczów. Wybierz odpowiedni mecz klikając na klany, między którymi rozpocznie się mecz.
			Na tej liście wyświetlają się mecze, które się jeszcze nie rozpoczęły.</p>');
			
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
				$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
				$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
				$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['id'].'"');
				echo('<tr>
					<td class="center">'.$match['id'].'</td>
					<td><a href="'.$_ACTION.'/'.$match['id'].'">'.$klan1['fullname'].' <tt>vs.</tt> '.$klan2['fullname'].'</a></td>
					<td class="center">'.$bets->rowCount().'</td>
				</tr>');
			}
		}
	}
	else
	{
		$m = $db->query('SELECT * FROM `matches` WHERE `id` = "'.$_PAGES['more'].'"')->fetch();
		$k1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-1'].'"')->fetch();
		$k2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-2'].'"')->fetch();
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
		<p>Aby dodać nowy typ zakładu kliknij poniższy link.</p>
		<p>Klan 1: <tt>'.$k1['fullname'].'</tt><br>
		Klan 2: <tt>'.$k2['fullname'].'</tt></p>
		<a href="#" id="add_new_type"><b>Dodaj nowe pole zakładu</b></a><br>
		
		<div id="bets">
		');
		if( !empty($_BETS) )
		{
			foreach($_BETS as $typeid)
			{
				echo('<fieldset>
				<label for="">Zakład <span class="star">*</span></label>
				<select id="" name="bets[typ][]">');
					$zaklady = $db->query('SELECT * FROM `bettypes` ORDER BY `id` ASC');
					while($zaklad = $zaklady->fetch())
					{
						echo('<option value="'.$zaklad['id'].'" ');if($typeid[0] == $zaklad['id']){echo(' selected');}echo('>'.$_LANG[$zaklad['type']].'</option>');
					}
				echo('</select>
				<p><tt>Kurs 1:</tt> <input type="text" name="bets[kurs-1][]" value="'.$typeid[1].'"></p>
				<p><tt>Kurs 2:</tt>  <input type="text" name="bets[kurs-2][]" value="'.$typeid[2].'"></p>
				</fieldset>');
			}
		}
		echo('
		</div>
		<br><input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		</form>');
	}
}

?>