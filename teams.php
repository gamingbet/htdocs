<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}


$showAll = true;

if( !empty($_PAGES['type']) ){
	$game = $db->prepare('SELECT *, DATE_FORMAT(`release`,"%d.%m.%Y") AS `release` FROM `gamings` WHERE `tag` = :tag LIMIT 1');
	$game->bindValue(':tag', $_PAGES['type'], PDO::PARAM_STR);
	$game->execute();
	$result = $game->fetch();
	if( $result != false ){
		$showAll = false;
	}
}

if($showAll == false){
	
	echo '
		<div class="mecz" style="background: url(files/images/logos/'.$result['images'].') no-repeat center; background-size: cover;">
  	<div class="mecz_info">

	<h2><img src="'.( (!empty($result['image'])) ? $result['image'] : 'files/images/logos/noclan.png').'" alt="'.$result['fullname'].'" style=" margin-right: 10px; height: 40px;">'.$result['fullname'].'</h2><br>';
	echo( ( (!empty($result['url']) && $result['url'] != "http://") ? '
	<div class="gra-mecz"><a href="'.$result['url'].'">'.$_LANG['labels']['look-www'].'</a></div> ' : '').'');
	
	if( $_GLOBALS[ 'login' ][ 'login' ] == true ){
		$fav = $db->prepare('SELECT * FROM `favouritesteams` WHERE `gamingId` = :gid AND `userId` = :uid LIMIT 1');
		$fav->bindValue(':gid', $result['id'], PDO::PARAM_STR);
		$fav->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);
		$fav->execute();
		if($fav->fetch() == false){
			$favourites = false;
		}
		else{
			$favourites = true;
		}
	}
		if( $_GLOBALS[ 'login' ][ 'login' ] == true ){
		if($favourites == true){
			echo('<div class="gra-ulubione"><a class="btn btn-danger" href="teams/'.$result['tag'].'/remove">'.$_LANG['labels']['removeFromFav'].'</a></div> ');
		}
		else{
			echo('<div class="gra-ulubione-add"><a class="btn btn-success" href="teams/'.$result['tag'].'/add">'.$_LANG['labels']['addToFav'].'</a></div> ');
		}
	}
	echo '</div></div>
	<div class="druzyna-txt">
	';
	//var_dump($result);
	$bbcode = new BBCode;
	$content = $bbcode->parse($result['description-'.$_GLOBALS['lang']]);
	if(!empty($content)) {
		echo '<div class="historia"><h4>'.$_LANG['labels']['history'].'</h4>';
		echo '<p>'.$content.'</p></div>';
	}
	$teams = $db->prepare('SELECT * FROM `teams` WHERE `gamingId` = :gid');
	$teams->bindValue(':gid', $result['id'], PDO::PARAM_STR);
	$teams->execute();
	echo '<div class="uczestniczy"><h4>'.$_LANG['labels']['take-part'].':</h4>';
	if($teams->rowCount() > 0){
		echo '<ul>';
		$gamesstr = '';
		while($team = $teams->fetch())
		{
			$game = getGame($team['gameId']);
			if(!empty($game['name'])) {
				$gamesstr .= '<li>'.$game['name'].'</li>';
			}
		}
		echo substr($gamesstr, 0, -1);
		echo '</ul></div>';

	}
	else
	{
		echo('<p>'.$_LANG['labels']['noTeams'].'</p></div>');
	}
	//LEFT JOIN `bets` ON `bets`.`matchId` = `matches`.`id` AND `bets`.`typeId` = 1 AND `bets`.`active` = "true"
	$next = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `start` FROM `matches` 
	
		WHERE (`teamId-1` = :id OR `teamId-2` = :id) AND `finish` <> "true" AND `start` > NOW() ORDER BY `start` ASC LIMIT 1');
	$next->bindValue(':id', (int) $result['id'], PDO::PARAM_INT);
	$next->execute();
	//var_dump($next->rowCount());
	$last = $db->prepare('SELECT * FROM `matches` WHERE (`teamId-1` = :id OR `teamId-2` = :id) AND `finish` = "true" ORDER BY `start` DESC LIMIT :limit');
	$last->bindValue(':id', (int) $result['id'], PDO::PARAM_INT);
	$last->bindValue(':limit', (int) $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'bets' ][ 'last-matches' ], PDO::PARAM_INT);
	$last->execute();
	if($next->rowCount() > 0)
	{
		$nextMatch = $next->fetch();

		

		$enemy = getGaming( ( $nextMatch['teamId-1'] == $result['id'] ) ? $nextMatch['teamId-2'] : $nextMatch['teamId-1'] );

		$stmt = $db->prepare('SELECT `logo` FROM `games` WHERE `id` = :gameId');
		$stmt->bindValue('gameId', $nextMatch['gameId']);
		$stmt->execute();

		$icon = $stmt->fetchAll();

		//var_dump($nextMatch);

		echo '<div class="nastepny-mecz"><h4>'.$_LANG['labels']['next-game'].'</h4>';
		echo '
		<table class="table table-striped table-hover  text-center">
		<tr>
        	<td>'.substr($nextMatch['start'], 0 , 10).' '.substr($nextMatch['start'], 11).'<br /><img style="vertical-align: middle; width: 15px;" src="/files/images/icons/'.$icon['0']['logo'].'"></td>
            <td><a href="/teams/tag/'.$result['tag'].'">'.$result['fullname'].'</a></td>
            <td>vs</td>
            <td><a href="/teams/tag/'.$enemy['tag'].'">'.$enemy['fullname'].'</a></td>
			<td></td>
        </tr></table>
		</div>
		';
	}

	echo('<div class="nastepny-mecz"><h4>'.$_LANG['labels']['last-matches'].':</h4>');
	if($last->rowCount() == 0)
	{
		echo('<p>'.$_LANG['labels']['empty-lastMatch'].'</p></div>');
	}
	else
	{
		echo('<table class="table table-striped table-hover text-center">');
		while($match = $last->fetch())
		{
			$game = getGame($match['gameId']);
			$enemys = array(
				getGaming($match['teamId-1']),
				getGaming($match['teamId-2'])
			);
			$match_result = ($match['teamWinId'] == $result['id']) ? '<span class="wygrana">'.$_LANG['labels']['win'].'</span>' : '<span class="porazka">'.$_LANG['labels']['lose'].'</span>';
			echo '<tr>
	        	<td><img src="files/images/icons/'. $game[ 'logo' ].'" alt="'. $game[ 'name' ].'"></td>
	            <td><a href="teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a></td>
	            <td>vs</td>
	            <td><a href="teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a></td>
	            <td>'.$match_result.'</td>
	        </tr>';

		}	
		echo('</table>
		</div>
		');	

	}
	echo '</div>';

}

else{
	$teams = $db->query('SELECT * FROM `gamings` ORDER BY `id` ASC');
	if($teams->rowCount() != 0){
		echo('<h1>Lista gamingów</h1><hr>');
		while($team = $teams->fetch()){
			echo('<a href="teams/'.$team['tag'].'"><img src="/'.$team['image'].'" alt="'.$team['tag'].'" style="width: 95px; height: 95px; vertical-align: middle; margin-right: 10px;"></a> <a href="teams/'.$team['tag'].'">'.$team['fullname'].'</a>');
		}
	}
}

?>