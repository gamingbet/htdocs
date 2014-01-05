<?php
if( !defined("__LOAD__") )
{
	exit();
	return false;
}
?>
<h1><?php echo($_LANG[ 'general' ][ 'matches' ]); ?></h1>
<?php /*<span class="time"><?php echo($_LANG[ 'general' ][ 'time' ]); ?> <time datetime="<?php echo(date(_SQLDate_)); ?>"><?php echo(date('G:i', time())); ?></time></span>*/ ?>
<?php /*<table class="bets"> */ ?>
<div class="mecze" style="overflow-x:hidden">
	<table class="table table-striped table-hover text-center">
<?php
	$matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%H:%i") AS `start-time`, 
	(SELECT `fullname` FROM `gamings` WHERE `gamings`.`id` = `matches`.`teamId-1`) AS `fullname-1`,
	(SELECT `tag` FROM `gamings` WHERE `gamings`.`id` = `matches`.`teamId-1`) AS `tag-1`,
	(SELECT `fullname` FROM `gamings` WHERE `gamings`.`id` = `matches`.`teamId-2`) AS `fullname-2`,
	(SELECT `tag` FROM `gamings` WHERE `gamings`.`id` = `matches`.`teamId-2`) AS `tag-2`,
	(SELECT `name` FROM `games` WHERE `games`.`id` = `matches`.`gameId`) AS `name`,
	(SELECT `logo` FROM `games` WHERE `games`.`id` = `matches`.`gameId`) AS `logo`
	FROM `matches` JOIN `bets` ON `bets`.`matchId` = `matches`.`id` AND `bets`.`typeId` = 1 AND `bets`.`active` = "true" AND `matches`.`finish` <> "true"
	WHERE UNIX_TIMESTAMP(`start`) > UNIX_TIMESTAMP(NOW()) AND `optionWin` = 0 ORDER BY `start` ASC LIMIT :limit');
	$matches->bindValue(':limit', (int) $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'match' ][ 'next-match' ], PDO::PARAM_INT);
	$matches->execute();
	
	if($matches->rowCount() > 0)
	{
		while($match = $matches->fetch())
		{
			echo('<tr>
				<td style="width: 20%" title="'. $match[ 'start' ].'">'. $match[ 'start' ].'<br><img src="files/images/icons/'. $match[ 'logo' ].'" alt="'. $match[ 'name' ].'"></td>
				<td title="'. $match[ 'fullname-1' ] .'"><a href="teams/'.$match['tag-1'].'">'.$match[ 'tag-1' ].'</a><br> <a class="btn btn-success btn-xs"  href="'.$_PAGES['lang'].'/'.$match['id'].'-1">'. getScore( $match[ 'score-1' ] ) .'</a></td>
				<td><a href="bets/'.$match['matchId'].'">vs</a></td>
				<td title="'. $match[ 'fullname-2' ] .'"><a href="teams/'.$match['tag-2'].'">'.$match[ 'tag-2' ].'</a> <br><a class="btn btn-danger btn-xs"  href="'.$_PAGES['lang'].'/'.$match['id'].'-2">'. getScore( $match[ 'score-2' ] ) .'</a></td>
			</tr>');
			
			/*if ($match['stream'] == 'http://') $match['stream'] = '#';

			$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mId AND `typeId` = 1 LIMIT 1');

			$bets->bindValue(':mId', (int) $match['0'], PDO::PARAM_INT);

			$bets->execute();

			$bet = $bets->fetch();	

			echo('<li>
				<div class="time">'.'<img src="files/images/icons/'. $match[ 'logo' ].'" alt="'. $match[ 'name' ].'"></div>
				<div class="typy"><a href="teams/'.$match['tag-1'].'">'.$match[ 'tag-1' ].'</a><br />');

				echo (($match['teamWinId'] == '0')?'<a class="kurs-button" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">':'').getScore( $bet[ 'score-1' ] )

				. (($match['teamWinId'] == '0')?'</a>':'');

				echo '</a></div>
				<div class="vs">vs</div>
				<div class="typy"><a href="teams/'.$match['tag-2'].'">'.$match[ 'tag-2' ].'</a><br />';

				echo (($match['teamWinId'] == '0')?'<a class="kurs-button" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">':'').getScore( $bet[ 'score-2' ] )

				. (($match['teamWinId'] == '0')?'</a>':'');

				echo '</div>
				<div class="live">
					<a href="'.$match['stream'].'">watch<span style="color: #fd7c04;">Live!</span></a>
                   	<img style="vertical-align: middle;" src="img/twitch.png"> 
                    
                </div>
			</li>';
*/
		} 
	}
	else
	{
		/*echo('<tr>
			<td>
				'.$_LANG['labels']['empty-box'].'
			</td>
		</tr>');*/
		echo '<div class="alert alert-info">
			Brak meczy
		</div>';
	}
?>
<?php /* </table> */ ?>
	</table>
</div>

<?php /*
<h2><?php echo($_LANG['labels']['konkurs']); ?></h2>
<table class="bets">
<?php
	$i = 1;
	$konkurs = $db->query('SELECT `nick`, `credits` FROM `users` WHERE `id` NOT IN ( SELECT `userId` FROM `admins` ) AND `creditsWon` > 0 ORDER BY `credits` DESC LIMIT 10');
	
	while($user = $konkurs->fetch())
	{
		echo('<tr style="height: 30px; line-height: 30px;">
			<td class="time">'.$i.'.</td>
			<td style="text-align: left; width: 140px; text-indent: 5px;">'.$user['nick'].'</td>
			
		</tr>');
		++$i;
		
	}
?>
</table>
*/ ?>
