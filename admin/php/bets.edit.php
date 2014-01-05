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

		if( $values['delete'] == "true" )

		{

			$check = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet.'"');

			if($check->rowCount() != 0)

			{

				$zaklad = $db->query('SELECT * FROM `bettypes` WHERE `id` = "'.$values['id'].'" LIMIT 1')->fetch();

				$errors[] = 'Zakład <tt>'.$_LANG[$zaklad['type']].'</tt> nie może zostać usunięty, ponieważ jest już obstawiony';

			}

			else

			{

				$_BETS[] = array($bet, "delete");

			}

		}

		else

		{

			$_BETS[] = array($bet, "modify", $values['kurs-1'], $values['kurs-2'], $values['status'], $values['id']);

		}

	}

	

	if(empty($errors))

	{

		foreach($_BETS as $zaklady)

		{

			if($zaklady[1] == "delete")

			{

				$db->exec('DELETE FROM `bets` WHERE `id` = "'.$zaklady[0].'" LIMIT 1');

			}

			else

			{

				$db->exec('UPDATE `bets` SET `score-1` = "'.getScore($zaklady[2]).'", `score-2` = "'.getScore($zaklady[3]).'", `active` = "'.$zaklady[4].'" WHERE `id` = "'.$zaklady[0].'" LIMIT 1');

			}

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

			echo('<p>Dzięki temu menu możesz edytować zakłady do meczów. Wybierz odpowiedni mecz klikając na klany, między którymi rozpocznie się mecz.

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

				$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['id'].'"');

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

	else

	{

		$m = $db->query('SELECT * FROM `matches` WHERE `id` = "'.$_PAGES['more'].'"')->fetch();

		$k1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-1'].'"')->fetch();

		$k2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$m['teamId-2'].'"')->fetch();

		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">

		<p>Zablokowanie zakładu oznacza, że nie będzie można go już więcej razy obstawić. Nie będzie on również widoczny na liście. Przy rozdawaniu nagród punktowych będzie trzeba

		wprowadzić jego wynik, ponieważ ten sam zakład mógł być obstawiony przed zmianąjego statusu.</p>

		<p>Usunięcie zakładu jest możliwe tylko i wyłącznie wtedy, kiedy żaden użytkownik nie wyraził chęci obstawienia jego.</p>

		<p>W najgorszym wypadku, możesz za pomocą menu <tt>Mecze</tt> zablokować mecz i wszystkie zakłady z niego zostaną unieważnione.</p>

		<p>Sortowanie zakładów - od aktywnych do nieaktywnych.</p>

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

				<tt>'.$_LANG[$zaklad['type']].'</tt>

				</td>

			</tr>

			<tr>

				<td class="center">

					<b>'.$k1['fullname'].'</b>

				</td>

				<td class="center">

					<b>'.$k2['fullname'].'</b>

				</td>

			</tr>

			<tr>

				<td>

					<input type="text" name="bets['.$bet['id'].'][kurs-1]" value="'.getScore($bet['score-1']).'">

				</td>

				<td>

					<input type="text" name="bets['.$bet['id'].'][kurs-2]" value="'.getScore($bet['score-2']).'">

				</td>

			</tr>

			<tr>

				<td colspan="2">

					<p>Status zakładu</p>

					<select name="bets['.$bet['id'].'][status]" required>

						<option value="true"');if($bet['active'] == "true"){echo(' selected');}echo('>Aktywny</option>

						<option value="false"');if($bet['active'] == "false"){echo(' selected');}echo('>Nieaktywny</option>

					</select>

				</td>

			</tr>

			<tr>

				<td colspan="2">

					<p>Czy chcesz usunąć ten zakład?</p>

					<select name="bets['.$bet['id'].'][delete]">

						<option value="false" selected>Nie</option>

						<option value="true">Tak</option>

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