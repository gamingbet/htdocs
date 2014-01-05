<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") ){
	exit();
}
$sent = false;
$errors = array();
$showAll = true;
$randm = rand();


if( $_PAGES['more'] == "remove" ){
	@$match_id = (int) $_POST['match_id'];
	$match = $db->query('SELECT `id` FROM `matches` WHERE `finish` = "false" AND `id` = '.$match_id);
	if($match->rowCount() == 0){
		$errors[] = "Mecz o podanym ID nie istnieje";
	}
	else{	
		$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match_id.'"');
		if($bets->rowCount() != 0){
			$isset_bet = false;
			while($bet = $bets->fetch()){
				$isset = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet['id'].'"');
				if($isset->rowCount() != 0){
					$isset_bet = true;
					break;
				}
			}
			if( $isset_bet == true ){
				$errors[] = "Ten mecz ma już obstawione przez użytkowników zakłady. Możesz jedynie go anulować zwracając koszty, które użytkownicy ponieśli za ten mecz. W 
					przypadku zakładu grupowego zostanie on w całości pominięty";
			}
			else{
				$db->exec('DELETE FROM `matches` WHERE `id` = "'.$match_id.'" AND `finish` = "false" LIMIT 1');
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
			}
		}
		else{
			$db->exec('DELETE FROM `matches` WHERE `id` = "'.$match_id.'" AND `finish` = "false" LIMIT 1');
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		}			
	}
}

else{

	if( !empty($_PAGES['more'] ) ){
		@$match_id = (int) $_PAGES['more'];
		$match = $db->query('SELECT `id` FROM `matches` WHERE `finish` = "false" AND  `id` = '.$match_id);
		if( $match->rowCount() == 1){
			$showAll = false;
		}
		else{
			$errors[] = "Mecz o podanym ID nie został odnaleziony";
		}
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" ){
	$change["T"] = " ";
	$change["Z"] = NULL;
	$gra = addslashes(trim(htmlspecialchars($_POST['game'])));
	$wydarzenie = addslashes(trim(htmlspecialchars($_POST['event'])));
	$enemy1 = addslashes(trim(htmlspecialchars($_POST['enemy1'])));
	$enemy2 = addslashes(trim(htmlspecialchars($_POST['enemy2'])));
	$url = addslashes(trim(htmlspecialchars($_POST['url'])));
	$begin = addslashes(trim(htmlspecialchars($_POST['begin'])));
	$begin_true = $begin;	
	//$begin_true = str_replace(array_keys($change), array_values($change), $begin);
	if(isset($_POST['ban']) && $_POST['ban'] == "true"){
		$content_pl = addslashes(trim(htmlspecialchars($_POST['content_pl'])));
		$content_en = addslashes(trim(htmlspecialchars($_POST['content_en'])));
		if( empty($content_pl) || empty($content_en))
			$errors[] = "Jeżeli chcesz zablokować mecz - musisz podać powód";
		if(empty($errors)){
			// Anuluj mecz
			$db->exec('UPDATE `matches` SET `finishTime` = NOW(), `reason-pl` = "'.$content_pl.'", `reason-en` = "'.$content_en.'", `finish` = "true", `teamWinId` = "-1" WHERE `id` = "'.$_PAGES['more'].'"');

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
				// zwroc kredyty dla oby dwoch graczy
				$tables->changeCustomerCreditsByCustomerId( Tables::AD, 2*((int) $course), (int) $cbet['player1Id'] );
				$tables->changeCustomerCreditsByCustomerId( Tables::AD, 2*((int) $course), (int) $cbet['player2Id'] );
				$db->exec('UPDATE tables SET status = "finish" WHERE id = '.(int) $cbet['id']);
			}
			// Zwróć kredyty dla:
			$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$_PAGES['more'].'"');
			if($bets->rowCount() != 0){
				while($bet = $bets->fetch()){
					// pojedyncze zaklady
					$users = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet['id'].'" AND `couponId` = "0"');
					if($users->rowCount() != 0){
						while($user = $users->fetch()){
							$db->exec('UPDATE `users` SET `credits` = (`credits` + "'.$user['credits'].'") WHERE `id` = "'.$user['userId'].'" LIMIT 1');
						}
					}
					// kupony
					$users = $db->query('SELECT * FROM `betusers` WHERE `betId` = "'.$bet['id'].'" AND `couponId` != "0"');
					if($users->rowCount() != 0){
						while($user = $users->fetch()){
							$coupons = $db->query('SELECT * FROM `betusers` WHERE `couponId` = "'.$user['couponId'].'" AND `userId` = "'.$user['userId'].'"');
							$_INFO['matchesAll'] = $coupons->rowCount();
							$_INFO['cours'] = 1;	
							$_INFO['result'] = true;
							$_INFO['matches'] = 0;
							$_INFO['finished'] = true;							
							while($coupon = $coupons->fetch()){
								++$_INFO['matches'];
								$_INFO['credits'] = $coupon['credits'];
								$bet_coupon = getBet($coupon['betId']);
								$match_coupon = getMatch($bet_coupon['matchId']);
								$type_coupon = getTypes($bet_coupon['typeId']);
								if($match_coupon['teamWinId'] != "-1"){
									$_INFO['cours'] *= $coupon['course'];
								}
								if( $match_coupon['teamWinId'] == "-1" ){
									//$_INFO['matches'];
								}
								else if($bet_coupon['optionWin'] == "0"){
									$_INFO['finished'] = false;
									--$_INFO['matches'];
								}
								else if($bet_coupon['optionWin'] == "1"){
									if( $coupon['type'] != "1" )
										$_INFO['result'] = false;
								}
								else if($bet_coupon['optionWin'] == "2"){
									if( $coupon['type'] != "2" )
										$_INFO['result'] = false;
								}
							}
							$_INFO['cours'] = getScore(round($_INFO['cours'], 2));
							$_INFO['stawka'] = round($_INFO['credits'] * $_INFO['cours']);
							if($_INFO['matches'] == $_INFO['matchesAll']){
								if($_INFO['result'] == true){
									$db->exec('UPDATE `users` SET `credits` = (`credits` + "'.$_INFO['stawka'].'"), `creditsWon` = (`creditsWon` + "'.$_INFO['stawka'].'") WHERE `id` = "'.$user['userId'].'" LIMIT 1');
									$db->exec('UPDATE `coupons` SET `status` = "win" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
								}
								else{
									$db->exec('UPDATE `coupons` SET `status` = "lose" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
								}
							}
							else if($_INFO['matches'] != $_INFO['matchesAll'] && $_INFO['result'] == false){
								$db->exec('UPDATE `coupons` SET `status` = "lose" WHERE `id` = "'.$user['couponId'].'" LIMIT 1');
							}
							else if($_INFO['matches'] == 0 && $_INFO['result'] == true && $_INFO['finished'] == true){
								$db->exec('UPDATE `users` SET `credits` = (`credits` + "'.$_INFO['credits'].'") WHERE `id` = "'.$user['userId'].'" LIMIT 1');
							}
							else{
								// nie znamy rezultatu - nie rob nic
							}
						}
					}
				}
			}
			$sent = true;
		}
	}
	else{
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
			if($check_event->rowCount() == 0){
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
		if($_POST['old_gra'] != $gra || $_POST['old_gosp'] != $enemy1 || $_POST['old_goscie'] != $enemy2 || $_POST['old_data'] != $begin_true){
			$double = $db->query('SELECT * FROM `matches` WHERE `gameId` = "'.$gra.'" AND ( ( `teamId-1` = "'.$enemy1.'" AND `teamId-2` = "'.$enemy2.'") OR ( `teamId-2` = "'.$enemy1.'" AND `teamId-1` = "'.$enemy2.'") ) AND `start` = "'.$begin_true.'"');
			if($double->rowCount() != 0)
				$errors[] = "Taki mecz już istnieje";
		}
		if(empty($errors)){

			$db->exec('UPDATE `matches` SET `eventsId` = "'.$wydarzenie.'", `gameId` = "'.$gra.'", `teamId-1` = "'.$enemy1.'", `teamId-2` = "'.$enemy2.'", `start` = "'.$begin_true.'", `stream` = "'.$url.'" WHERE `id` = "'.$_PAGES['more'].'" LIMIT 1');
			$sent = true;
		}
	}
}
else{
	@$match_id = (int) $_PAGES['more'];
	$match = $db->query('SELECT * FROM `matches` WHERE `finish` = "false" AND  `id` = '.$match_id)->fetch();
	$gra = $match['gameId'];
	$wydarzenie = $match['eventsId'];
	$enemy1 = $match['teamId-1'];
	$enemy2 = $match['teamId-2'];
	$begin = substr($match['start'], 0, strlen($match['start'])-3);
	$url = $match['stream'];
	$content_pl = NULL;
	$content_en = NULL;
}
if( !empty($errors ) ) {
	echo('<h4 class="alert_error">Podczas operacji wystąpiły błędy!</h4>');
	echo('<ul>');
	foreach($errors as $error){
		echo('<li>'.$error.'</li>');

	}
	echo('</ul>');
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
		<li><a href="/admin/bets/finish">Wprowadź wyniki do zakładów</a></li>
		<li><a href="/admin/matches/finish">Zakończ mecze</a></li>
	</ul>');
}
else{
	if($showAll == true){
		echo('<p>Wybierz mecz, który chcesz edytować poprzez kliknięcie odpowiedniej nazwy. Aby usunąć mecz należy kliknąć ikonkę kosza. 
		Miej na uwadze to, że operacja usunięcia jest nieodwracalna. Jeżeli do eventsu są przypięte mecze, to zostaną one rozdzielone.</p>
		<ul class="tabs">
			<li><a href="#tab1">Najbliższe</a></li>
			<li><a href="#tab2">Trwające</a></li>
		</ul><br>&nbsp;
		<div style="clear: both" class="tab_container">
			<div id="tab1" class="tab_content">');
			$matches = $db->query('SELECT * FROM `matches` WHERE `start` > NOW() AND `finish` = "false" ORDER BY `start` DESC');
			if($matches->rowCount() == 0){
				echo('<h4 class="alert_info">Brak nadchodzących meczów!</h4>');
			}
			else{
				echo('<table class="tablesorter">
				<thead>
					<tr>
						<td style="width: 10%">ID</td>
						<td style="width: 70%">Mecz pomiędzy</td>
						<td style="width: 15%">Ilość zakładów</td>
						<td class="center">Usuń</td>
					</tr>
				</thead>');
				while($match = $matches->fetch()){
					$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
					$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
					$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['id'].'"');
					echo('<tr>
						<td class="center">'.$match['id'].'</td>
						<td><a href="'.$_ACTION.'/'.$match['id'].'">'.$klan1['fullname'].' <tt>vs.</tt> '.$klan2['fullname'].'</a></td>
						<td class="center">'.$bets->rowCount().'</td>
						<td class="center">
							<form action="'.$_ACTION.'/remove" class="post_message" method="post">
								<input type="hidden" name="match_id" value="'.$match['id'].'">
								<input type="image" src="images/icn_trash.png" title="Kosz">
							</form>
						</td>
					</tr>');
				}
				echo('</table>');
			}
			echo('</div>
			<div id="tab2" class="tab_content">');
			$matches = $db->query('SELECT * FROM `matches` WHERE `start` < NOW() AND `finish` = "false" ORDER BY `start` DESC');
			if($matches->rowCount() == 0){
				echo('<h4 class="alert_info">Brak rozpoczętych meczów!</h4>');
			}
			else{
				echo('<table class="tablesorter">
				<thead>
					<tr>
						<td style="width: 10%">ID</td>
						<td style="width: 70%">Mecz pomiędzy</td>
						<td style="width: 15%">Ilość zakładów</td>
						<td class="center">Usuń</td>
					</tr>
				</thead>');
				while($match = $matches->fetch()){
					$klan1 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-1'].'"')->fetch();
					$klan2 = $db->query('SELECT * FROM `gamings` WHERE `id` = "'.$match['teamId-2'].'"')->fetch();
					$bets = $db->query('SELECT * FROM `bets` WHERE `matchId` = "'.$match['id'].'"');
					echo('<tr>
						<td class="center">'.$match['id'].'</td>
						<td><a href="'.$_ACTION.'/'.$match['id'].'">'.$klan1['fullname'].' <tt>vs.</tt> '.$klan2['fullname'].'</a></td>
						<td class="center">'.$bets->rowCount().'</td>
						<td class="center">
							<form action="'.$_ACTION.'/remove" class="post_message" method="post">
								<input type="hidden" name="match_id" value="'.$match['id'].'">
								<input type="image" src="images/icn_trash.png" title="Kosz">
							</form>
						</td>
					</tr>');
				}
				echo('</table>');
			}
			echo('</div>
		</div>');
	}
	else{
		$match_id = (int) $_PAGES['more'];
		$match = $db->query('SELECT * FROM `matches` WHERE `finish` = "false" AND  `id` = '.$match_id)->fetch();
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message" enctype="multipart/form-data">
		<fieldset>
			<label for="input_1">Wydarzenie</label>
			<select id="input_1" name="event">');
				$events = $db->query('SELECT * FROM `events` WHERE `dataEnd` > NOW()');
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
		<input type="hidden" name="old_gra" value="'.$match['gameId'].'">
		<input type="hidden" name="old_gosp" value="'.$match['teamId-1'].'">
		<input type="hidden" name="old_goscie" value="'.$match['teamId-2'].'">
		<input type="hidden" name="old_data" value="'.substr($match['start'], 0, strlen($match['start'])-3).'">
		<p class="center">Wartość tego pola zostanie zapomniana jeżeli zostanie wybrane wydarzenie, do którego zostanie przypisany mecz.</p>
		</fieldset>

		<fieldset>
			<label for="input_3">Gospodarze <span class="star">*</span></label>
			<select id="input_3" name="enemy1" required>');
				$teams = $db->query('SELECT * FROM `gamings` ORDER BY `fullname` ASC');
				if($enemy1 == '0')
					echo('<option disabled selected>Wybierz gospodarza meczu</option>');
				while($team = $teams->fetch()){
					echo('<option value="'.$team['id'].'" ');if($enemy1 == $team['id']){echo(' selected');}echo('>'.$team['tag'].' - '.$team['fullname'].'</option>');
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
					echo('<option value="'.$team['id'].'" ');if($enemy2 == $team['id']){echo(' selected');}echo('>'.$team['tag'].' - '.$team['fullname'].'</option>');
				}
		echo('</select>
		</fieldset>

		<fieldset>
			<label for="input_5">Data rozpoczęcia [YYYY-MM-DD GG:MM] <span class="star">*</span></label>
			<input type="text" id="input_5" name="begin" value="'.stripslashes($begin).'" required>
			<p class="center"><br>Jeżeli zostało wybrane wydarzenie, do którego ma zostać przypisany mecz, to upewnij się, że data tego meczu znajduje się pomiędzy rozpoczęciem, a zakończeniem 
			tego wydarzenia. W przeciwnym wypadku system nie zaakceptuje meczu.</p>
		</fieldset>

		<fieldset>
			<label for="input_6">Stream WWW <span class="star"></span></label>
			<input type="text" id="input_6" name="url" value="'.stripslashes($url).'">
		</fieldset>

		<p><b>Jeżeli zdecydujesz się zablokować mecz, to nie będzie możliwości odblokowania go. Kredyty, które zostały założone na zakłady z tego meczu zostaną zwrócone,
			a zakłady w kuponie, w którym ten mecz brał udział zostaną zignorowane.</b></p>
			
		<fieldset>
			<label for="input_7">Zablokować mecz? <span class="star"></span></label>
			<select id="input_7" name="ban" required>
				<option value="true">Tak</option>
				<option value="false" selected>Nie</option>
			</select>
		</fieldset>

		<fieldset>
			<label for="input_8">Powód odwołania meczu [PL] <span class="star"></span></label>
			<textarea rows="7" id="input_8" name="content_pl" >'.stripslashes($content_pl).'</textarea>
		</fieldset>

		<fieldset>
			<label for="input_9">Powód odwołania meczu [EN] <span class="star"></span></label>
			<textarea rows="7" id="input_9" name="content_en" >'.stripslashes($content_en).'</textarea>
		</fieldset>


		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>');

	}

}



?>