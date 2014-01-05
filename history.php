<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

?>
<div class="clearfix" style="margin-top: 10px"></div>
<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-xs-12 col-sm-12 col-md-8">
<div class="well-sm biale">
<h1>Kupony</h1>
<div class="alert alert-info">
W tym miejscu znajdziesz wszystkie kupony, które obstawiłeś</div>
<?php
if( $_GLOBALS[ 'login' ][ 'login' ] == false )

{
	echo( $_LANG[ 'auth' ][ 'need_login' ] );
	return false;
}

$sql = $db->prepare('SELECT *, DATE_FORMAT(`date`,"%d.%m.%Y %H:%i") AS `date-format` FROM `betusers` WHERE `userId` = :uid AND `active` = "true" ORDER BY `date` DESC, `id` DESC ');
$sql->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);
$sql->execute();

if($sql->rowCount() > 0 )
{
	echo('<table class="table table-striped table-hover">');
	echo('<tr>');
	echo('<th class="course">'.$_LANG['history']['bid'].'</th>');
	echo('<th class="date">'.$_LANG['history']['date'].'</th>');
	echo('<th class="credits">'.$_LANG['history']['credits'].'</th>');
	echo('<th class="type">'.$_LANG['history']['type'].'</th>');
	echo('<th class="result last">'.$_LANG['history']['result'].'</th>');
	echo('</tr>');
	$i = 1; // variable for coupon border bottom
	$j = 0; // variable for delete border bottom
	$all = $sql->rowCount();
	$last_coupon_id = NULL;
	while( $result = $sql->fetch() )
	{
		++$j;
		$coupon_id = $result['couponId'];
		$count_coupon = $db->prepare('SELECT COUNT(*) AS `count` FROM `betusers` WHERE `couponId` = :bid');
		$count_coupon->bindValue(':bid', $coupon_id, PDO::PARAM_STR);
		$count_coupon->execute();
		$count_coupon = $count_coupon->fetch();
		$count = $count_coupon['count'];
		if($coupon_id == $last_coupon_id)
		{
			++$i;
		}
		else
		{
			$i = 1;
		}
		$bet = getBet($result['betId']);
		$match = getMatch($bet['matchId']);
		$game = getGame($match['gameId']);
		$enemys = array(
			getGaming($match['teamId-1']),
			getGaming($match['teamId-2'])
			);
		$type = getTypes($bet['typeId']);
		echo('<tr>');
		echo('<td class="bid'.( ($i==$count && $j != $all)?' last-count':'').'"><a href="coupon/'.$result['couponId'].'">'.$result['couponId'].'</a></td>');
		echo('<td'.( ($i==$count && $j != $all)?' class="last-count"':'').'><time datetime="'.$result['date'].'">'.date('d.m.y', strtotime($result['date'])).'</time></td>');
		echo('<td'.( ($i==$count && $j != $all)?' class="last-count"':'').'>'.$result['credits'].'</td>');
		echo('<td'.( ($i==$count && $j != $all)?' class="last-count"':'').'>'.strtolower( ($result['couponId'] == "0") ? $_LANG['labels']['once'] : '<a href="coupon/'.$result['couponId'].'">'.$_LANG['labels']['multiply'].'</a><br />'.$count.' '.$_LANG['labels']['other-bets']).'</td>');
		echo('<td class="last'.( ($i==$count && $j != $all)?' last-count':'').'">');
			if($match['teamWinId'] == "-1")
			{
				echo('<span class="gray">'.$_LANG['labels']['canceled'].'</span>');
			}
			else if($bet['optionWin'] == "0")
			{
				echo($_LANG['labels']['notyet']);
			}
			else if($bet['optionWin'] == "1")

			{
				if( $result['type'] == "1" )
					echo('<span class="green">'.$_LANG['labels']['win'].'</span>');
				else
					echo('<span class="red">'.$_LANG['labels']['lose'].'</span>');
			}
			else if($bet['optionWin'] == "2")
			{
				if( $result['type'] == "2" )
					echo('<span class="green">'.$_LANG['labels']['win'].'</span>');
				else
					echo('<span class="red">'.$_LANG['labels']['lose'].'</span>');
			}
			else
			{
				if( $bet['type'] == "3" )
					echo('<span class="green">'.$_LANG['labels']['draw'].'</span>');
				else
					echo('<span class="red">'.$_LANG['labels']['draw'].'</span>');
			}
		echo('</td>');
		echo('</tr>');

		
		
		$last_coupon_id = $coupon_id;
	}
	echo('</table>');
}
else
{
	echo($_LANG['history']['empty']);
}



?>
</div>