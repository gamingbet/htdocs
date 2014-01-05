<?php
if( !defined("__LOAD__") )
{
	exit();
	return false;
}
?>

<?php


$showAll = true;

if( !empty($_PAGES['type']) )

{

	$game = $db->prepare('SELECT *, DATE_FORMAT(`release`,"%d.%m.%Y") AS `release` FROM `games` WHERE `short` = :short LIMIT 1');

	$game->bindValue(':short', $_PAGES['type'], PDO::PARAM_STR);

	$game->execute();

	$result = $game->fetch();

	if( $result != false )
	{
		$showAll = false;
	}
	//var_dump($result); - jaka gra
}
if($showAll == false){



	echo '
	<div class="mecz" style="background: url(files/images/logos/'.$result['images'].') no-repeat center; background-size: cover;">
  	<div class="mecz_info">
	
	<h2><img style="height: 40px; margin-right: 5px;" alt="'.$result['name'].'" src="img/'.$result['logo'].'">
		'.$result['name'].'
 	</h2>
	<br>
	<div class="kiedy">
	<a href="games/'.$result['short'].'/next">'.$_LANG['bets']['next'].'</a>
	</div>
	<div class="kiedy">
	<a href="games/'.$result['short'].'/live">'.$_LANG['bets']['live'].'</a>
	</div>
	<div class="kiedy">
	<a  href="games/'.$result['short'].'/finished">'.$_LANG['bets']['finished'].'</a>
	</div>
	
	</div>
	</div>';

	echo('');
	$_GAMES['count'] = 0;
	// EVENTS
	$events = $db->prepare('SELECT * FROM `events` WHERE `gameId` = :gId ORDER BY `dataBegin` ASC');
	$events->bindValue(':gId', (int) $result['id'], PDO::PARAM_INT);
	$events->execute();
	if( $events->rowCount() > 0 )
	{
		while($event = $events->fetch())
		{
			if($_PAGES['more'] == 'live')
			{
				$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `gameId` = :gId AND `eventsId` = :eId AND `finish` <> "true" AND `start` < NOW() ORDER BY `start` ASC');
			}
			else if($_PAGES['more'] == 'finished')
			{
				$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `gameId` = :gId AND `eventsId` = :eId AND `finish` = "true" AND TIMESTAMPDIFF(DAY, `start`, NOW()) < 14 ORDER BY `start` DESC');
			}
			else
			{
				$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `gameId` = :gId AND `eventsId` = :eId AND `finish` <> "true" AND `start` > NOW() ORDER BY `start` ASC');
			}
			$matches->bindValue(':gId', (int) $result['id'], PDO::PARAM_INT);
			$matches->bindValue(':eId', (int) $event['id'], PDO::PARAM_INT);
			$matches->execute();
			if( $matches->rowCount() > 0 )
			{
				echo('
				<div class="wybor-gry">
				<span class="zaklad-txt"><a href="events/'.$event['id'].'">'.$event['name'].'</a></span>
				</div>
				<div class="gra-mecze">
			');
				echo('<table>');
				while( $match = $matches->fetch() )
				{
					++$_GAMES['count'];
					$enemys = array(
						getGaming($match['teamId-1']),
						getGaming($match['teamId-2'])
					);
					$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mId AND `typeId` = 1 LIMIT 1');
					$bets->bindValue(':mId', (int) $match['0'], PDO::PARAM_INT);
					$bets->execute();
					$bet = $bets->fetch();
					$game = getGame($match['gameId']);
					echo '
					<tr>
			        	<td><time datetime="'.$match['start'].'">'.date('d.m.y H:i',strtotime($match['start'])).'</time><br /><img src="files/images/icons/'.$game['logo'].'" alt="'.$game['short'].'"></td>
			            <td><a href="teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a><br /><a class="btn btn-success" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">'. getScore( $bet[ 'score-1' ] ) .'</a></td>
		                <td>vs</td>
		                <td><a href="teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a><br /><a class="btn btn-danger" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">'. getScore( $bet[ 'score-2' ] ) .'</a></td>
		                <td><a href="'.$match['stream'].'">watch<span style="color: #fd7c04;">Live!</span>
		                                        <img style="vertical-align: middle;" src="img/twitch.png"> </a>
		                                        <br><a style="color:#525252;" href="/bets/'.$match['0'].'">przejdź do zakładu</a>
		                </td>
			        </tr>';

				}
				echo('</table>
				</div>');
			}
			
		}
	}
	$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches` WHERE `gameId` = :gId AND `eventsId` = 0 AND `finish` <> "true" AND `start` > NOW() ORDER BY `start` ASC');
	$matches->bindValue(':gId', (int) $result['id'], PDO::PARAM_INT);
	$matches->execute();
	if( $matches->rowCount() > 0 )
	{
		echo('<div class="wybor-gry">
				<span class="zaklad-txt">Inne mecze</span>
			</div>
			<div class="gra-mecze">
				<table>');
		while( $match = $matches->fetch() )
		{
			++$_GAMES['count'];
			$enemys = array(
				getGaming($match['teamId-1']),
				getGaming($match['teamId-2'])
			);
			$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mId AND `typeId` = 1 LIMIT 1');
			$bets->bindValue(':mId', (int) $match['id'], PDO::PARAM_INT);
			$bets->execute();
			$bet = $bets->fetch();
			$game = getGame($match['gameId']);
			echo '
			<tr>
	        	<td><time datetime="'.$match['start'].'">'.date('d.m.Y H:i',strtotime($match['start'])).'</time></td>
	            <td><a href="teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a><br /><a class="zaklad-1a" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">'. getScore( $bet[ 'score-1' ] ) .'</a></td>
                <td>vs</td>
                <td><a href="teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a><br /><a class="zaklad-2a" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">'. getScore( $bet[ 'score-2' ] ) .'</a></td>
                <td>
                    <a href="/bets/'.$match['0'].'">przejdź do zakładu</a>
                </td>
	        </tr>';

		}
		echo('</table>
		</div>');
	}
}



else{
	$games = $db->query('SELECT * FROM `games` ORDER BY `name` ASC');
	if( $games->rowCount() > 0 )
	{
		echo('<ul>');
		while($game = $games->fetch() )
		{
			echo('<li><a href="games/'.$game['short'].'">'.( (!empty($game['images'])) ? '<img src="files/images/logos/'.$game['images'].'" alt="'.$game['name'].'" class="small">' : $game['name']).'</a></li>');
		}
		echo('</ul>');
	}
	else
	{
		echo('<p>'.$_LANG['labels']['empty-gameList'].'</p>');
	}
}

?>