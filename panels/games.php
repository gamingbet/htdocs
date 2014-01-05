<?php

if( !(__LOAD__) )
{
	exit();
	return false;
}


$_GLOBALS['games'] = $db->query('SELECT * FROM `games`');
if($_GLOBALS['games']->rowCount() > 0)
{
	echo('<dl class="games">');
	while($_GAMES = $_GLOBALS['games']->fetch())
	{
		echo('<div class="panel panel-default">
  			<div class="panel-heading"><h3 class="panel-title"><a href="games/'.$_GAMES['short'].'">'.$_GAMES['name'].'</a></h3></div><div class="list-group">');
		$events = $db->query('SELECT * FROM `events` WHERE `gameId` = "'.$_GAMES['id'].'" AND `dataEnd` > NOW()');
		while($event = $events->fetch())
		{
			echo(' <a class="list-group-item" href="events/'.$event['id'].'"><span class="glyphicon glyphicon-asterisk"></span> '.$event['name'].'</a>');
		}
		//echo('<dd><a href="events/'.$_GAMES['short'].'">'.$_LANG['labels']['nextTournaments'].'</a></dd>');
		//echo('<dd><a href="games/'.$_GAMES['short'].'/live">'.$_LANG['labels']['liveMatches'].'</a></dd>');
		echo('</div></div>');
	}
	

}
else
{
	echo($_LANG['labels']['noGames']);
}
?>