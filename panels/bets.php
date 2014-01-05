<?php
if( $_GLOBALS['login']['login'] == "true" ){
if( isset($_SESSION['bets']) && !empty($_SESSION['bets']) ){
	$_GLOBALS[ 'bets' ][ 'bets' ] = false;
	$_GLOBALS[ 'bets' ][ 'errors' ] = array();
	$_GLOBALS[ 'bets' ][ 'matches' ] = array();
	$_GLOBALS[ 'bets' ][ 'multiple' ] = false;
	$_GLOBALS[ 'bets' ][ 'sent' ] = false;
	$_GLOBALS[ 'bets' ][ 'unset' ] = false;
	$_GLOBALS[ 'bets' ][ 'count' ] = 0;
	// Check all bets
	foreach($_SESSION['bets'] as $key => $value){
		$bet = getBet($key);
		$match = getMatch($bet['matchId']);
		$game = getGame($match['gameId']);
		$enemys = array(
			getGaming($match['teamId-1']),
			getGaming($match['teamId-2'])
		);
		$type = getTypes($bet['typeId']);	
		// Delete no-active, bets have started or bad types
		if($bet['active'] == "false" || $type['options'] < $value || strtotime( $match['start'] ) < time() || $match['active'] == "false" || $match['teamWinId'] != '0' || $match['finish'] == "true"){
			$_GLOBALS[ 'bets' ][ 'unset' ] = true;
			unset($_SESSION['bets'][$key]);
		}
		else{
			++$_GLOBALS[ 'bets' ][ 'count' ];
			@(++$_GLOBALS[ 'bets' ][ 'matches' ][ $match['id'] ]);
		}
	}
	if($_GLOBALS[ 'bets' ][ 'unset' ] == true){
		$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['wrongBets'];
	}
	// If any bets are from the same match - coupon cant be make
	foreach($_GLOBALS[ 'bets' ][ 'matches' ] as $value){
		if($value > 1){
			$_GLOBALS[ 'bets' ][ 'multiple' ] = true;
			break;
		}
	}
	if( $_GLOBALS[ 'bets' ][ 'count' ] == 1 ){
		$_GLOBALS[ 'bets' ][ 'multiple' ] = true;
	}
	if( (isset( $_POST[ 'bet_once' ] ) && $_POST[ 'bet_once' ] == $_LANG[ 'labels' ][ 'once' ]) ||
		(isset( $_POST[ 'bet_multiply' ] ) && $_POST[ 'bet_multiply' ] == $_LANG[ 'labels' ][ 'multiply' ] && $_GLOBALS[ 'bets' ][ 'count' ] > 1) )
	{
		$_GLOBALS[ 'bets' ][ 'sent' ] = true;
	}
	if( $_GLOBALS[ 'bets' ][ 'sent' ] == true && $_GLOBALS[ 'bets' ][ 'unset' ] == false ){
		$credits = (int) trim ( htmlspecialchars ( $_POST[ 'bet_credits' ] ) );
		if( ($_GLOBALS[ 'bets' ][ 'multiple' ] == true && (isset( $_POST[ 'bet_multiply' ] ) && $_POST[ 'bet_multiply' ] == $_LANG[ 'labels' ][ 'multiply' ]) ) && $_GLOBALS[ 'bets' ][ 'count' ] == 1 ){
			$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['noMultiply'];
		}
		if( $credits < 0 || empty($credits) ){
			$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['getCredit'];
		}
		if( $_GLOBALS[ 'login' ][ 'login' ] == false ){
			$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['needLogin'];
		}
		else{
			if( $_GLOBALS[ 'bets' ][ 'multiple' ] == false ) {
				if(
					( (isset( $_POST[ 'bet_multiply' ] ) && $_POST[ 'bet_multiply' ] == $_LANG[ 'labels' ][ 'multiply' ] ) && 
					($credits) > $_USER['credits'] ) ||
					( (isset( $_POST[ 'bet_once' ] ) && $_POST[ 'bet_once' ] == $_LANG[ 'labels' ][ 'once' ]) && 
					($credits * $_GLOBALS[ 'bets' ][ 'count' ]) > $_USER['credits'] )
				)
					$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['needCredits'];

			}
			else{
				if( ($credits * $_GLOBALS[ 'bets' ][ 'count' ]) > $_USER['credits'] ){
					$_GLOBALS[ 'bets' ][ 'errors' ][] = $_LANG['bets']['needCredits'];
				}
			}
		}
		if( empty( $_GLOBALS[ 'bets' ][ 'errors' ] ) )
		{
			$l = new Login();
			// multi
			if( $_GLOBALS[ 'bets' ][ 'multiple' ] == false && (isset( $_POST[ 'bet_multiply' ] ) && $_POST[ 'bet_multiply' ] == $_LANG[ 'labels' ][ 'multiply' ]) ){
				$bets = $db->prepare('INSERT INTO `coupons` VALUES(NULL, :id, NOW(), :credits, "", "true")');
				$bets->bindValue(":id", $l->getIdByLogin($_USER['nick']), PDO::PARAM_STR);
				$bets->bindValue(":credits", $credits, PDO::PARAM_STR);
				$bets->execute();
				$_GLOBALS[ 'bets' ][ 'coupon' ] = $db->lastInsertId();
				$_GLOBALS[ 'bets' ][ 'course' ] = $credits;
			}
			// once
			else{
				$_GLOBALS[ 'bets' ][ 'coupon' ] = false;
				$_GLOBALS[ 'bets' ][ 'course' ] = $credits * $_GLOBALS[ 'bets' ][ 'count' ];
			}
			foreach($_SESSION['bets'] as $key => $value)
			{
				$bet = getBet($key);
				$match = getMatch($bet['matchId']);
				$game = getGame($match['gameId']);
				$enemys = array(
					getGaming($match['teamId-1']),
					getGaming($match['teamId-2'])
				);
				$type = getTypes($bet['typeId']);
				$bets = $db->prepare('INSERT INTO `betusers` VALUES(NULL, '.(int) $_GLOBALS[ 'bets' ][ 'coupon' ].', :uid, :betId, :type, :course, :credits, NOW(), "", "true")');
				$bets->bindValue(':uid', $l->getIdByLogin($_USER['nick']), PDO::PARAM_STR);
				$bets->bindValue(':betId', $key, PDO::PARAM_STR);
				$bets->bindValue(':type', $value, PDO::PARAM_STR);
				$bets->bindValue(':course', $bet['score-'.$value], PDO::PARAM_STR);
				$bets->bindValue(':credits', $credits, PDO::PARAM_STR);
				$bets->execute();
				unset($_SESSION['bets'][$key]);
			}
			// change gold
			$minus = $db->prepare('UPDATE `users` SET `credits` = (`credits` - :credit) WHERE `id` = :id LIMIT 1');
			$minus->bindValue(':credit', $_GLOBALS[ 'bets' ][ 'course' ], PDO::PARAM_STR);
			$minus->bindValue(':id', $l->getIdByLogin($_USER['nick']), PDO::PARAM_STR);
			$minus->execute();
			$_USER['credits'] -= $_GLOBALS[ 'bets' ][ 'course' ];
			$_GLOBALS[ 'bets' ][ 'bets' ] = true;
		}
	}
	// If created 
	if( $_GLOBALS[ 'bets' ][ 'bets' ] == true ){
		echo '';
		echo($_LANG['bets']['added']);
		echo '';
	}
	else{
		if( !empty( $_GLOBALS[ 'bets' ][ 'errors' ] ) )
		{
			echo('<ul class="errors">');
			foreach( $_GLOBALS[ 'bets' ][ 'errors' ] as $error )
			{
				echo('<li>'. $error. '</li>');
			}
			echo('</ul>');
		}
		echo('<div class="twoje-typy-naglowek">'.$_LANG['bets']['active-bets'].'</div>
		<form action="'.$_PAGES[ 'lang' ].'" method="post"> <ul class="twoje-typy">');
		foreach($_SESSION['bets'] as $key => $value){
			$bet = getBet($key);
			$match = getMatch($bet['matchId']);
			$game = getGame($match['gameId']);
			$enemys = array(
				getGaming($match['teamId-1']),
				getGaming($match['teamId-2'])
			);
			$type = getTypes($bet['typeId']);
			echo '<li>';
				echo '<a href="/bets/'.$bet['id'].'">'.$enemys[0]['fullname'].' vs '.$enemys[1]['fullname'].'</a><br />';
				echo $_LANG['bets']['twoj-typ'].' <strong>'.$enemys[$value - 1]['fullname'].'</strong>';
				echo ' <a href="'.$_PAGES['lang'].'/'.$key.'-'.$value.'-delete"> <img style="vertical-align: middle; float: right;" src="img/trash.png"></a>';
			echo '</li>';
		}
		echo '<li>	
	    	<form method="post" action="'.$_PAGES[ 'lang' ].'">
			<label for="bet_01">'.$_LANG['bets']['credits'].'</label> <input type="number" class="stawka" required="" name="bet_credits" id="bet_01" min="1" placeholder="'.$_LANG['bets']['za-ile'].'">
				<input type="submit" class="jeden" value="'.$_LANG[ 'labels' ][ 'once' ].'" name="bet_once">
				<input type="submit" class="multi" value="'. $_LANG[ 'labels' ][ 'multiply' ]. '" name="bet_multiply" '.(($_GLOBALS[ 'bets' ][ 'multiple' ]) ? 'disabled' : '').'>
			
			</form>
	   </li>';
	}
}
else{
	echo '<div class="twoje-typy-naglowek">';
	echo($_LANG['bets']['empty']);
	echo '</div>';
}
}
?>