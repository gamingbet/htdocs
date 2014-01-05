<?php

$showAll = true;
$errors = array();
$sent = false;

$prepare = $db->query('SELECT * FROM `langs` WHERE `category` = "bets"');
while($p = $prepare->fetch())
{
	$_LANG[ $p['label'] ] = $p['label-pl'];
}

if( !empty($_PAGES['more'] ) )
{
	@$match_id = (int) $_PAGES['more'];
	$match = $db->query('SELECT `id` FROM `matches` WHERE `finish` = "false" AND `start` < NOW() AND `id` = '.$match_id);
	if( $match->rowCount() == 1)
	{
		$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match_id.'"');
		$bets_over = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match_id.'" AND `optionWin` <> 0');
		if($bets->rowCount() == $bets_over->rowCount() && $bets->rowCount() > 0)
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "Mecz o podanym ID nie ma zakończonych wszystkich zakładów bądź nie ma ich w ogóle przypisanych";
		}
		
	}
	else
	{
		$errors[] = "Mecz o podanym ID nie został odnaleziony";
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Zakończ mecz i przyznaj punkty" )
{
	// pobierz zwyciezce:
	$match_id = (int) $_PAGES['more'];
	$match = $db->query('SELECT * FROM `matches` WHERE `finish` = "false" AND  `id` = '.$match_id)->fetch();
	$winner = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$_PAGES['more'].'" AND `typeId` = 1')->fetch();
	$klan = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-'.$winner['optionWin'] ].'"')->fetch();
	
	// Zakoncz mecz
	$db->exec('UPDATE `matches` SET `finishTime` = NOW(), `finish` = "true", `teamWinId` = "'.$klan['id'].'" WHERE `id` = "'.$_PAGES['more'].'"');
	
	$tables = new Tables();
	
	// Pobierz wszystkie zaklady, ktore nie sa zakonczone
	$tables_sql = 'SELECT * FROM tables WHERE player2Id = 0 AND status = "open" AND matchId = '.$match_id;
	$tables_query = $db->query($tables_sql);
	while($cbet = $tables_query->fetch()) {
		$course = $tables->getCourseByTableId( (int) $cbet['id'] );
		$tables->changeCustomerCreditsByCustomerId( Tables::AD, (int) $course, (int) $cbet['player1Id'] );
		$db->exec('UPDATE tables SET status = "finish" WHERE id = '.(int) $cbet['id']);
	}
	
	// Pobierz wszystkie zaklady, ktore sa zakonczone
	$tables_sql = 'SELECT * FROM tables WHERE player2Id <> 0 AND status = "open" AND matchId = '.$match_id;
	$tables_query = $db->query($tables_sql);
	while($cbet = $tables_query->fetch()) {
		$course = $tables->getCourseByTableId( (int) $cbet['id'] );
		
		$optionWin = $db->query("SELECT optionWin FROM	bets WHERE typeId = '".$cbet['betType']."' AND	matchId = ".$cbet['matchId'])->fetch();
		// sprawdz kto wygral
		if( $optionWin['optionWin'] == $cbet['ownerOption'] ) {
			$player = $cbet['player1Id'];
		} else {
			$player = $cbet['player2Id'];
		}
		
		// dodaj kredyty dla zwyciezcy
		$tables->changeCustomerCreditsByCustomerId( Tables::AD, 2*((int) $course), (int) $player );
		
		$db->exec('UPDATE tables SET status = "finish" WHERE id = '.(int) $cbet['id']);
	}
	
	// Zwróć kredyty dla:
	$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$_PAGES['more'].'"');
	if($bets->rowCount() != 0)
	{
		while($bet = $bets->fetch())
		{
			// pojedyncze zaklady
			$users = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet['id'].'" AND `couponId` = "0"');
			if($users->rowCount() != 0)
			{
				while($user = $users->fetch())
				{
					$bet_custom = getBet($bet['id']);
					if($bet_custom['optionWin'] == $user['type'])
					{
						$db->exec('UPDATE `betusers` SET `status` = "win" WHERE `id` = "'.$user['id'].'" AND `couponId` = "0" AND `userId` = "'.$user['userId'].'" LIMIT 1');
						$db->exec('UPDATE `users` SET `credits` = (`credits` + "'.($user['credits']*$user['course']).'"), `creditsWon` = (`creditsWon` + "'.($user['credits']*$user['course']).'") WHERE `id` = "'.$user['userId'].'" LIMIT 1');
					}
					else
					{
						$db->exec('UPDATE `betusers` SET `status` = "lose" WHERE `id` = "'.$user['id'].'" AND `couponId` = "0" AND `userId` = "'.$user['userId'].'" LIMIT 1');
					}
				}
			}
			// kupony
			$users = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet['id'].'" AND `couponId` != "0"');
			if($users->rowCount() != 0)
			{
				while($user = $users->fetch())
				{
					$coupons = $db->query('SELECT * FROM `betusers` WHERE `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'"');
					$_INFO['matchesAll'] = $coupons->rowCount();
					$_INFO['cours'] = 1;	
					$_INFO['result'] = true;
					$_INFO['matches'] = 0;
					$_INFO['finished'] = true;							
					while($coupon = $coupons->fetch())
					{
						++$_INFO['matches'];
						$_INFO['credits'] = $coupon['credits'];
						$bet_coupon = getBet($coupon['betId']);
						$match_coupon = getMatch($bet_coupon['matchId']);
						$type_coupon = getTypes($bet_coupon['typeId']);
								
						if($match_coupon['teamWinId'] != "-1")
						{
							$_INFO['cours'] *= $coupon['course'];
						}
								
						if( $match_coupon['teamWinId'] == "-1" )
						{
							//--$_INFO['matches'];
						}
						else if($bet_coupon['optionWin'] == "0")
						{
							$_INFO['finished'] = false;
							--$_INFO['matches'];
						}
						else if($bet_coupon['optionWin'] == "1")
						{
							if( $coupon['type'] != "1" )
							{
								$_INFO['result'] = false;
								$db->exec('UPDATE `betusers` SET `status` = "lose" WHERE `id` = "'.$user['id'].'" AND `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'" LIMIT 1');
							}
							else
							{
								$db->exec('UPDATE `betusers` SET `status` = "win" WHERE `id` = "'.$user['id'].'" AND `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'" LIMIT 1');
							}
						}
						else if($bet_coupon['optionWin'] == "2")
						{
							if( $coupon['type'] != "2" )
							{
								$_INFO['result'] = false;
								$db->exec('UPDATE `betusers` SET `status` = "lose" WHERE `id` = "'.$user['id'].'" AND `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'" LIMIT 1');
							}
							else
							{
								$db->exec('UPDATE `betusers` SET `status` = "win" WHERE `id` = "'.$user['id'].'" AND `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'" LIMIT 1');
							}
						}
					}
					$_INFO['cours'] = getScore(round($_INFO['cours'], 2));
					$_INFO['stawka'] = round($_INFO['credits'] * $_INFO['cours']);
					if($_INFO['matches'] == $_INFO['matchesAll'])
					{
						if($_INFO['result'] == true)
						{
							$db->exec('UPDATE `users` SET `credits` = (`credits` + "'.$_INFO['stawka'].'"), `creditsWon` = (`creditsWon` + "'.$_INFO['stawka'].'") WHERE `id` = "'.$user['userId'].'" LIMIT 1');
							$db->exec('UPDATE `coupons` SET `status` = "win" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
						}
						else
						{
							$db->exec('UPDATE `coupons` SET `status` = "lose" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
						}
					}
					else if($_INFO['matches'] != $_INFO['matchesAll'] && $_INFO['result'] == false)
					{
						$db->exec('UPDATE `coupons` SET `status` = "lose" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
					}
					else if($_INFO['matches'] == 0 && $_INFO['result'] == true && $_INFO['finished'] == true)
					{
						// nie bedzie remisu, poniewaz zakonczylismy jeden z tych meczy, wiec wynik juz znamy
					}
					else
					{
						// nie znamy rezultatu - nie rob nic
					}
				}
			}
		}
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


if( $showAll == true )
{
	$matches = $db->query('SELECT *, `matches`.`id` AS `mid` FROM `matches` JOIN `bets` ON `matches`.`id` = `bets`.`matchId` WHERE `finish` = "false" AND `start` < NOW() ORDER BY `start` ASC');
	if($matches->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak meczów, które można zakończyć!</h4>');
	}
	else
	{
		echo('<p>Wybierając odpowiedni mecz zyskujesz możliwość zakończenia meczu. Jeśli to zrobisz, to zostaną rozliczone wszystkie zakłady, w którym ten mecz 
		brał udział. W zakładach pojedyńczych zostaną przyznane odpowiednie punkty, a w zakładach złożonych zostanie sprawdzony cały kupon. Jeżeli wynik będzie pozytywny
		to zostaną przyznane kolejne punkty. Decyzja ta jest nieodwracalna. Mecz można zakończyć wtedy i tylko wtedy, kiedy liczba zakładów pokrywa się z liczbą zakładów zakończonych.</p>');
		echo('<table class="tablesorter">
		<thead>
			<tr>
				<td style="width: 10%">ID</td>
				<td style="width: 15%">Data rozpoczęcia</td>
				<td style="width: 40%">Mecz pomiędzy</td>
				<td style="width: 15%">Zakłady / Zakończone</td>
			</tr>
		</thead>');
		
		while($match = $matches->fetch())
		{
			$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
			$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
			$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['mid'].'"');
			$bets_over = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['mid'].'" AND `optionWin` <> 0');
			
			echo('<tr>
				<td class="center">'.$match['id'].'</td>
				<td class="center">'.$match['start'].'</td>
				<td><a href="'.$_ACTION.'/'.$match['mid'].'">'.$klan1['fullname'].' <tt>vs.</tt> '.$klan2['fullname'].'</a></td>
				<td class="center">'.$bets->rowCount().' / '.$bets_over->rowCount().'</td>
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
			<li><a href="/admin/matches/add">Dodaj nowy mecz</a></li>
			<li><a href="/admin/matches/edit">Zarządzaj meczami</a></li>
			<li><a href="/admin/bets/add">Dodaj zakłady do meczów</a></li>
			<li><a href="/admin/bets/edit">Zarządzaj zakładami do meczów</a></li>
			<li><a href="/admin/bets/finish">Wprowadź wyniki do zakładów</a></li>
			<li><a href="/admin/matches/finish">Zakończ mecze</a></li>
		</ul>');
	}
	else
	{
		$match_id = (int) $_PAGES['more'];
		$match = $db->query('SELECT * FROM `matches` WHERE `finish` = "false" AND  `id` = '.$match_id)->fetch();
		$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
		$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
		$events = $db->query('SELECT * FROM `events` WHERE `id` = "'.$match['eventsId'].'"');
		if($events->rowCount() != 0)
			$events = $events->fetch();
		$games = $db->query('SELECT * FROM `games` WHERE `id` = "'.$match['gameId'].'"')->fetch();
		$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match_id.'"');
		
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
		
		<h4>Przegląd meczu:</h4>
		<ul>
			<li>Gra: <b>'.$games['name'].'</b></li>
			<li>Data rozpoczęcia: <b>'.$match['start'].'</b></li>
			'.( (is_array($events)) ? '<li>Wydarzenie: <b>'.$events['name'].'</b></li>':'').'
			<li>Wszystkich zakładów: <b>'.$bets->rowCount().'</b></li>
		</ul>
		
		<table class="tablesorter">
		<tr>
			<td class="center"><b>'.$klan1['fullname'].'</b></td>
			<td class="center"><b>'.$klan2['fullname'].'</b></td>
		</tr>');
		while($bet = $bets->fetch())
		{
			$zaklad = $db->query('SELECT * FROM `bettypes` WHERE `id` = "'.$bet['typeId'].'" LIMIT 1')->fetch();
			echo('
			<tr>
				<td class="center" colspan="2">
					<b><tt>'.$_LANG[$zaklad['type']].'</tt> - wygrany: '.$bet['optionWin'].'</b>
				</td>
			</tr>
			<tr>
				<td class="center">'.getScore($bet['score-1']).'</td>
				<td class="center">'.getScore($bet['score-2']).'</td>
			</tr>');
		}
		echo('</table><br>
		<div class="center"><input type="submit" value="Zakończ mecz i przyznaj punkty" class="alt_btn" name="submit"></div>
		</form>');
	}
}

?>