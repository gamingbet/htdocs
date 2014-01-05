<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

?>


	
<div style="clear:both; margin-bottom: 10px;"></div>

<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-xs-12 col-sm-12 col-md-8">
<div class="well-sm biale">
<h1><?php echo $_LANG['titles']['coupon']; ?></h1>
<?php

if( $_GLOBALS[ 'login' ][ 'login' ] == false )

{

	echo( $_LANG[ 'auth' ][ 'need_login' ] );

	return false;

}



if( !empty( $_PAGES[ 'type' ] ) )

{

	$sql = $db->prepare('SELECT *, DATE_FORMAT(`date`,"%d.%m.%Y %H:%i") AS `date` FROM `coupons` WHERE `id` = :id AND `userId` = :uid LIMIT 1');

	$sql->bindValue(':id', (int) $_PAGES['type'], PDO::PARAM_INT);

	$sql->bindValue(':uid', (int) $_USER['id'], PDO::PARAM_INT);

	$sql->execute();

	$result = $sql->fetch();

	

	if($result == false)

	{

		echo( $_LANG[ 'coupon' ][ 'empty' ] );

	}

	else

	{

		$_INFO = array();

		$_INFO['cours'] = 1;

		$_INFO['result'] = true;

		$_INFO['matches'] = 0;

		$_INFO['finished'] = true;

		$bets = $db->prepare('SELECT * FROM `betusers` WHERE `couponId` = :cid AND `active` = "true" ORDER BY `id` DESC');

		$bets->bindValue(':cid', (int) $_PAGES['type'], PDO::PARAM_INT);

		$bets->execute();

		$_INFO['matchesAll'] = $bets->rowCount();

		echo('<table class="table table-striped table-hover">');
		echo('<tr>');
		echo('<th>'.$_LANG['history']['enemys'].'</th>');
		echo('<th>'.$_LANG['history']['course'].'</th>');
		echo('<th>'.$_LANG['history']['result'].'</th>');
		echo('</tr>');
		while( $bet_result = $bets->fetch() )
		{
			++$_INFO['matches'];
			$bet = getBet($bet_result['betId']);
			$match = getMatch($bet['matchId']);
			$game = getGame($match['gameId']);
			$enemys = array(
				getGaming($match['teamId-1']),
				getGaming($match['teamId-2'])
			);
			$type = getTypes($bet['typeId']);
			if($match['teamWinId'] != "-1")
			{
				$_INFO['cours'] *= $bet_result['course'];
			}
			echo('<tr>');
			echo('<td><a href="bets/'.$bet['matchId'].'">'.$enemys[0]['tag'].' vs '.$enemys[1]['tag'].'</a><br><span class="bid">'.$_LANG['labels']['type'].' '.$_LANG['bets'][$type['type']].' ('.$bet_result['type'].')</span></td>');
			echo('<td>'.getScore($bet_result['course']).'</td>');
			echo('<td class="last">');
			if( $match['teamWinId'] == "-1" )
			{
				$_INFO['matches'];
				echo('<span class="gray">'.$_LANG['labels']['canceled'].'</span>');
			}
			else if($bet['optionWin'] == "0")
			{
				--$_INFO['matches'];
				$_INFO['finished'] = false;
				echo($_LANG['labels']['notyet']);
			}
			else if($bet['optionWin'] == "1")
			{
				if( $bet_result['type'] == "1" )
					echo('<span class="green">'.$_LANG['labels']['win'].'</span>');
				else
				{
					echo('<span class="red">'.$_LANG['labels']['lose'].'</span>');
					$_INFO['result'] = false;
				}
			}
			else if($bet['optionWin'] == "2")
			{
				if( $bet_result['type'] == "2" )
					echo('<span class="green">'.$_LANG['labels']['win'].'</span>');
				else
				{
					echo('<span class="red">'.$_LANG['labels']['lose'].'</span>');
					$_INFO['result'] = false;
				}
			}
			else
			{
				if( $bet['type'] == "3" )
					echo('<span class="green">'.$_LANG['labels']['draw'].'</span>');
				else
				{
					echo('<span class="red">'.$_LANG['labels']['draw'].'</span>');
					$_INFO['result'] = false;
				}
			}
			echo('</td>');
			echo('</tr>');
		}
		$_INFO['cours'] = getScore(round($_INFO['cours'], 2));
		echo('</table>');
		echo('<div class="alert alert-info"><ul class="list-unstyled" >');		
		echo('<li>'.$_LANG['labels']['coupon'].' <b>'.$result['id'].'</b></li>');
		echo('<li>'.$_LANG['labels']['coupon-create'].' <time class="li">'.$result['date'].'</time></li>');
		echo('<li>'.$_LANG['labels']['credit'].' <b>'.$result['credits'].'</b></li>');
		echo('<li>Potencjalna wygrana: <b>'.getScore(round($result['credits'] * $_INFO['cours'], 2)).'</b></li>');
		echo('<li>Wynik: ');
		if($_INFO['matches'] == $_INFO['matchesAll'])
		{
			if($_INFO['result'] == true)
			{
				echo('<span class="label label-success"">'.$_LANG['labels']['win'].'</span>');
			}
			else
			{
				echo('<span class="label label-danger">'.$_LANG['labels']['lose'].'</span>');
			}
		}
		else if($_INFO['matches'] != $_INFO['matchesAll'] && $_INFO['result'] == false)
		{
			echo('<span class="label label-danger">'.$_LANG['labels']['lose'].'</span>');
		}
		else if($_INFO['matches'] == 0 && $_INFO['result'] == true && $_INFO['finished'] == true)
		{
			echo('<span class="label label-info">'.$_LANG['labels']['canceled'].'</span>');
		}
		else
		{
			echo('<span class="label label-default">W grze</span>');
		}
		echo('</li></ul></div><br>');


	}

}

else

{

	echo( $_LANG[ 'coupon' ][ 'empty' ] );

}



?>
</div>