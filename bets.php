<?php
if( !defined("__LOAD__") )
{
	exit();
	return false;
}

$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `start`, UNIX_TIMESTAMP(`start`) AS `diff` FROM `matches` WHERE `id` = :id LIMIT 1');
$matches->bindValue(":id", $_PAGES['type'], PDO::PARAM_STR);
$matches->execute();
if( $matches->rowCount() )
{
	//Pojedynczy
	$match = $matches->fetch();
	$diff = $match['diff']-time();
	$image = $match['image'];
	$game = getGame($match['gameId']);
	$enemys = array(
		getGaming($match['teamId-1']),
		getGaming($match['teamId-2'])
	);
	$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mid AND `active` = "true"');
	$bets->bindValue(':mid', $match['id'], PDO::PARAM_STR);
	$bets->execute();
	$betsArray = array();
	if($match['stream'] != 'http://') {
		$stream = '<div class="stream"><a href="'.$match['stream'].'">stream</a></div>';
	}
	if(!empty($match['eventsId']))
	{
		$event = getEvent($match['eventsId']);
		$event_html = '<div class="miejsce"><a href="events/'.$event['id'].'">'.$event['name'].'</a></div>';
	}
    ?>
	<?php
	echo('
	<div class="mecz" style="background: url('.$image.') no-repeat center; background-size: cover;">
	<div class="mecz_info">
	<h2><a href="/teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a> 
    vs <a href="/teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a></h2>
	<br>
	<div class="kiedy">
    '.$match['start'].'
	</div>
	'.$event_html.'
		<div class="gra-mecz">
		'.$game['name'].'
		</div>
		'.$stream.'
	</div>
	</div>
	');
	if($match['teamWinId'] != '0' && $match['teamWinId'] != "-1")
	{
		$winner = ($match['teamWinId'] == $enemys[0]['id']) ? ($enemys[0]) : ($enemys[1]);
		echo('<br><span class="label label-warning">'.$_LANG['labels']['winner'].' <a href="teams/'.$winner['tag'].'"><b>'.$winner['fullname'].'</b></a></span>');
	}
	elseif($match['teamWinId'] == "-1")
	{
		echo('<br><span class="label label-info">'.$_LANG['labels']['cancel-match'].'<br>'.$match['reason-'.$_GLOBALS['lang']].'</span');
	}
	echo('');
	if($bets->rowCount() > 0)
	{
		echo '
			<div class="wybor-gry">
			<span class="zaklad-txt">'.$_LANG['events']['bets'].'</span>
			</div>';
		$game = getGame($match['gameId']);
		while($bet = $bets->fetch())
		{
			$type = getTypes($bet['typeId']);
			$betsArray[$bet['typeId']] = $_LANG['bets'][$type['type']];
			echo '<span class="typ-zakladu">'.$_LANG['bets'][$type['type']].'</span>';
			echo '<div class="druzyny-center"><div class="druzyna-1">';
	        echo'<a href="/teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a><br>';
	            if( $match['finish'] == "true" || $diff < 0){
					echo getScore( $bet[ 'score-1' ] );
				}
				else{
					echo '<a class="zaklad-1a" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">'. getScore( $bet[ 'score-1' ] ) .'</a>';
				}
				echo '</div><div class="druzyna-2">';
	            echo '<a href="/teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a><br>'; 
	            if( $match['finish'] == "true" || $diff < 0){
					echo getScore( $bet[ 'score-2' ] );
				}
				else{
					echo '<a class="zaklad-2a" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">'. getScore( $bet[ 'score-2' ] ) .'</a>';
				}
				echo '</div>
				</div>';
		}
		echo '<div class="wybor-gry">
			<span class="zaklad-txt">'.$_LANG['tables']['header'].'</span>
			</div>
			<div class="zaklady-wzajemne">
			';
		if(isset($betsErrors) && !empty($betsErrors)) {
			echo('<p>'.$_LANG['tables']['errors'].'</p>');
			echo('<div class="alert alert-danger">');
			foreach($betsErrors as $error) {
				echo(''.$error.'<br>');
			}
			echo('</div>');
		} else if( isset($betsCreateInfo) ) {
			echo('<div class="alert alert-success">'.$betsCreateInfo.'</div>');
		}
// Utwórz nowy obiekt ze sto³ami oraz przypisz zalogowanego u¿ytkownika do sto³ów
		$tables = new Tables();
		$tables->setCustomerId((int) $_USER['id']);
		
		if( $match['finish'] == "true" || $diff < 0) {  // locked
			$result = $tables->getTablesByMatchID((int) $_PAGES['type'], Tables::DISABLED);
			
		} else {										// enabled
			$result = $tables->getTablesByMatchID((int) $_PAGES['type'], Tables::ENABLED);
		}
		
		echo('<p>'.$_LANG['tables']['description'].'</p><br>');
		
		if( $tables->getCountTables() == 0 ) { // je¿eli nie ma ¿adnych zak³adów
			
			// Link do utworzenia nowego zak³adu jeœli mo¿na to zrobiæ
			if( $tables->isAllowBetMatch( (int) $_PAGES['type'] ) ) {
			
				// U¿ytkownik jest zalogowany oraz rozwijana opcja tej opcji
				if( $_GLOBALS['login']['login'] ) {           																	// FORMULARZ
					echo('<p><a href="#" id="create-table">'.$_LANG['tables']['create'].'</a></p>');
					echo('<div id="create-table-form" data-show="'.$customerTableShow.'">
				<form action="bets/'.$_PAGES['type'].'/create" method="post">
					<fieldset id="register">
							<div class="pole1"><input type="text" name="customBetsCredit" value="'.$customerBetsCredit.'" id="custom-bets-input-01" class="stoly-input" placeholder="'.$_LANG['tables']['credits'].'" required></div>
							<div class="pole1">
							<label for="custom-bets-input-02">'.$_LANG['tables']['type'].'</label>
							<select name="customBetsType" id="custom-bets-input-02" class="empty" required>
							');
							foreach($betsArray as $key => $value) {
								echo('<option value="'.$key.'"'); if($customBetsType == $key) echo(' selected'); echo('>'.$value.'</option>');
							}
							echo('
							</select>
							</div>
							<div class="pole1">
							<label for="custom-bets-input-03">'.$_LANG['tables']['userType'].'</label>
						
							<select name="customBetsResult" id="custom-bets-input-03" class="empty" required>
								<option value="1"'); if($customBetsResult == '1') echo(' selected'); echo('>'.$enemys[0]['fullname'].'</option>
								<option value="2"'); if($customBetsResult == '1') echo(' selected'); echo('>'.$enemys[1]['fullname'].'</option>
							</select>
							</div>
						<input type="submit" name="customBetsSend" value="'. $_LANG[ 'tables' ][ 'sendType' ]. '" class="zaklad-2aa">
						
					</fieldset>
				
				</form></div>');
				} else {
					echo('<p>'.$_LANG['tables']['need-login'].'</p>');
				}
				
			} else {
				echo('<p>'.$_LANG['tables']['disable'].'</p>');
			}
					
		} else {
		
			// Link do utworzenia nowego zak³adu jeœli mo¿na to zrobiæ i u¿ytkownik jest zalogowany oraz rozwijana opcja tej opcji
			if( $tables->isAllowBetMatch( (int) $_PAGES['type'] ) && $_GLOBALS['login']['login'] ) {							// FORMULARZ
				echo('<p>'.$_LANG['tables']['create'].'</p>');
				echo('<div id="create-table-form" data-show="'.$customerTableShow.'">
				<form action="bets/'.$_PAGES['type'].'/create" method="post">
					<fieldset id="register">
							<div class="pole1"><input type="text" name="customBetsCredit" value="'.$customerBetsCredit.'" id="custom-bets-input-01" class="stoly-input" placeholder="'.$_LANG['tables']['credits'].'" required></div>
							<div class="pole1">
							<label for="custom-bets-input-02">'.$_LANG['tables']['type'].'</label>
							<select name="customBetsType" id="custom-bets-input-02" class="empty" required>
							');
							foreach($betsArray as $key => $value) {
								echo('<option value="'.$key.'"'); if($customBetsType == $key) echo(' selected'); echo('>'.$value.'</option>');
							}
							echo('
							</select>
							</div>
							<div class="pole1">
							<label for="custom-bets-input-03">'.$_LANG['tables']['userType'].'</label>
						
							<select name="customBetsResult" id="custom-bets-input-03" class="empty" required>
								<option value="1"'); if($customBetsResult == '1') echo(' selected'); echo('>'.$enemys[0]['fullname'].'</option>
								<option value="2"'); if($customBetsResult == '1') echo(' selected'); echo('>'.$enemys[1]['fullname'].'</option>
							</select>
							</div>
						<input type="submit" name="customBetsSend" value="'. $_LANG[ 'tables' ][ 'sendType' ]. '" class="zaklad-2aa">
						
					</fieldset>
				
				</form></div>');
			} else {
				echo('<p>'.$_LANG['tables']['need-login'].'</p>');
			}
			
			// poka¿ sto³y
			echo('<br><table class="history">');
			echo('<tr>');
			echo('<td class="enemy">'.$enemys[0]['fullname'].'</td>');
			echo('<td class="enemy">'.$enemys[1]['fullname'].'</td>');
			echo('<td class="stawka1">'.$_LANG['tables']['rate'].'</td>');
			echo('</tr>');
			
			foreach($result as $row) {
				echo('<tr class="border">');
					echo('<td colspan="2">
					<table class="betsMatch">
					<tr>
						<td colspan="2" class="bmf">Typ zakładu: <b>'.$row['lang'.$_GLOBALS['lang']].'</b></td>
					</tr>
					<tr>');
					
					echo('<td class="polowa">');
					// sprawdz czy istnieje id [zawsze istnieje]
					if( (int) $row['player1Id'] != 0 ) {
						// sprawdz, czy customerId zalozyl ID 1, jesli tak = to pokaz jego nick
						if( $row['ownerOption'] == '1' ) {
							echo($row['player1']);
						} else {
						// jesli nie, to pokaz nick drugiego goscia - jezeli istnieje
							if( (int) $row['player2Id'] == 0 ) { // jezeli nie, to pokaz opcje dolaczenia
								echo('<a class="zaklad-1a" href="bets/'.$_PAGES['type'].'/join-'.$row['id'].'">'.$_LANG['tables']['join-txt'].'</a>');	
							} else {
								// echo($row['player2'].($row['player2Id']==$_USER['id']&&$tables->isAllowBetMatch( (int) $_PAGES['type'] )?' <a href="bets/'.$_PAGES['type'].'/leave-'.$row['id'].'">'.$_LANG['tables']['leave'].'</a>':''));
								echo($row['player2']);
							}
						}
						
					}	
					// /sprawdz czy istnieje...					
					echo('</td>');
					
					echo('<td class="polowa">');
					// sprawdz czy istnieje id
					if( (int) $row['player2Id'] != 0 ) {
						// jezeli istnieje - sprawdz, czy wlasciciel zalozyl opcje 1
						if( $row['ownerOption'] == '2' ) {
							echo($row['player1']);
						} else {
						// zalozyl opcje 1, wiec pokaz drugiego gracza
							// echo($row['player2'].($row['player2Id']==$_USER['id']&&$tables->isAllowBetMatch( (int) $_PAGES['type'] )?' <a href="bets/'.$_PAGES['type'].'/leave-'.$row['id'].'">'.$_LANG['tables']['leave'].'</a>':''));
							echo($row['player2']);
						}
						
					} else {
					// id nie istnieje - sprawdz, czy mozna dolaczyc
						if( $row['ownerOption'] == '2' ) {
							echo($row['player1']);
						} else {
							if( $tables->isAllowBetMatch( (int) $_PAGES['type'] ) ) { 							
								echo('<a class="zaklad-2a" href="bets/'.$_PAGES['type'].'/join-'.$row['id'].'">'.$_LANG['tables']['join-txt'].'</a>');						
							} else {
							// nie mozna dolaczyc
								echo($_LANG['tables']['join']);
							}
						}
					}		
					// /sprawdz czy istnieje...
					echo('</td>');
					
					echo('</tr></table></td>');
					echo('<td><span class="kurs">'.$row['course'].'</span><br>'. // je¿eli jestem w³aœcicielem sto³u i mo¿na je edytowaæ, to 'table-admin' mo¿e go usun¹æ
							( 	$tables->isOwnerTable((int) $row['id']) && 
								$tables->isAllowBetMatch( (int) $_PAGES['type'] ) ? 
								'<a href="bets/'.$_PAGES['type'].'/remove-'.$row['id'].'">'.$_LANG['tables']['remove'].'</a>' : 
								''
							) 
						.
					
					'</td>');
				echo('</tr>');
			}
			echo('</table><br>');
			// echo('<p class="r">'.$_LANG['tables']['count'].' <b>'.$tables->getCountTables().'</b></p>');
		}
		echo '</div>';
	}
}

else
{
	//Wiele zakładów
	?>
	<?php
	echo('<ul class="links noleft">
	<li class="first"><a href="bets/next">'.$_LANG['bets']['next'].'</a></li>
	<li><a href="bets/live">'.$_LANG['bets']['live'].'</a></li>
	<li class="last"><a href="bets/finished">'.$_LANG['bets']['finished'].'</a></li>
	</ul>');
	if($_PAGES['type'] == 'live')
	{
		$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `finish` <> "true" AND `start` < NOW() ORDER BY `start` ASC');
	}
	else if($_PAGES['type'] == 'finished')
	{
		$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `finish` = "true" AND TIMESTAMPDIFF(DAY, `start`, NOW()) < 14 ORDER BY `start` DESC');
	}
	else
	{
		$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `finish` <> "true" AND `start` > NOW() ORDER BY `start` ASC');
	}
	$matches->execute();
	$_GAMES['count'] = 0;
	if($matches->rowCount())
	{
		echo('<table class="table table-striped table-hover  text-center">');
		while( $match = $matches->fetch() )
		{
			++$_GAMES['count'];
			$enemys = array(
				getGaming($match['teamId-1']),
				getGaming($match['teamId-2'])
			);
			$game = getGame($match['gameId']);
			$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mId AND `typeId` = 1 LIMIT 1');
			$bets->bindValue(':mId', (int) $match['0'], PDO::PARAM_INT);
			$bets->execute();
			$bet = $bets->fetch();
			echo '
			<tr>
	        	<td><time datetime="'.$match['start'].'">'.date('d.m.y', strtotime($match['start'])).'<br />'.date('H:i', strtotime($match['start'])).'</time><br /><img src="files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'"></div>
	            <td><a href="teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a><br />';
	            echo (($match['teamWinId'] == '0')?'<a class="kurs-button" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">':'').getScore( $bet[ 'score-1' ] )
				. (($match['teamWinId'] == '0')?'</a>':'');
	            echo '</td>
	            <td>vs</td>
	            <td><a href="teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a><br />';
	            echo (($match['teamWinId'] == '0')?'<a class="kurs-button" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">':'').getScore( $bet[ 'score-2' ] )
				. (($match['teamWinId'] == '0')?'</a>':'');
				if($match['stream'] == 'http://'){
					$match['stream'] = '#';

				}
	            echo '</td>
	            <td><a href="'.$match['stream'].'">watch<span style="color: #fd7c04;">Live!</span></a>
	                    				<img style="vertical-align: middle;" src="img/twitch.png"> <br><a style="color:#525252;" href="/bets/'.$match['0'].'">przejdź do zakładu</a>
	            </td>
	        </tr>
			';
		}
		echo('</table><br>');
	}
	else
	{
		if($_GAMES['count'] > 0 )
		{
			// echo('<p class="r">'.$_LANG['labels']['all-bets'].' <b>'.$_GAMES['count'].'</b></p>');
		}
		else
		{
			echo('<p>'.$_LANG['labels']['empty-box'].'</p>');
		}
	}
}
?>
