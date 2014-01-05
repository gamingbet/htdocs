<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( $_PAGES['more'] == "remove" )
{
	@$game_id = (int) $_POST['ban_id'];
	$game = $db->query('SELECT * FROM `bans` WHERE `id` = '.$game_id);
	
	if($game->rowCount() == 0)
	{
		$errors[] = "Ban o podanym ID nie istnieje";
	}
	else
	{
		$db->exec('DELETE FROM `bans` WHERE `id` = '.$game_id.' LIMIT 1');
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$ip = addslashes(trim(htmlspecialchars($_POST['ip'])));
	$time = addslashes(trim(htmlspecialchars($_POST['time'])));

	if(empty($ip) || empty($time))
		$errors[] = "Proszę wypełnić wszystkie pola";
	
	if(strtotime($time) < time() )
		$errors[] = "Nie można zbanować kogoś w przeszłości";
	
	if(empty($errors))
	{
		$ban = $db->query('SELECT * FROM `bans` WHERE `ip` = "'.$ip.'"');
		if($ban->rowCount() == 1)
		{
			$db->exec('UPDATE `bans` SET `time` = "'.$time.'" WHERE `ip` = "'.$ip.'"');
			$ip = NULL;
		}
		else
		{
			$db->exec('INSERT INTO `bans` VALUES(NULL, "'.$ip.'", "'.$time.'", "", "")');
			$ip = NULL;
		}
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
}
else
{
	$ip = NULL;
	$time = NULL;
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

echo('<h4>Zablokuj IP</h4>');
echo('<form action="'.$_ACTION.'" method="post" class="post_message" enctype="multipart/form-data">
	<fieldset>
		<label for="input_2">IP <span class="star"></span></label>
		<input type="text" id="input_2" name="ip" value="'.stripslashes($ip).'">
	</fieldset>
		
	<fieldset>
		<label for="input_3">Do kiedy? [YYYY-MM-DD GG:MM] <span class="star">*</span></label>
		<input type="text" id="input_3" name="time" value="'.stripslashes($time).'" required>
	</fieldset>
		
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
</form>');
		
echo('<h4>Zablokowane dostępy</h4>');
$bans = $db->query('SELECT * FROM `bans` WHERE `time` > NOW()');
if($bans->rowCount() == 0)
{
	echo('<h4 class="alert_info">Jeszcze nikt nie został zablokowany</h4>');
}
else
{
	echo('<table class="tablesorter">
		<thead>
			<tr>
				<td style="width: 15%">ID</td>
				<td style="width: 35%">IP</td>
				<td style="width: 25%">Do kiedy?</td>
				<td style="width: 25%">Usuń</td>
			</tr>
		</thead>');
	
	while($ban = $bans->fetch())
	{
		echo('<tr>
			<td class="center">'.$ban['id'].'</td>
			<td class="center">'.$ban['ip'].'</td>
			<td class="center">'.$ban['time'].'</td>
			<td class="center">
				<form action="'.$_ACTION.'/remove" class="post_message" method="post">
					<input type="hidden" name="ban_id" value="'.$ban['id'].'">
					<input type="image" src="images/icn_trash.png" title="Kosz">
				</form>
			</td>
		</tr>');
	}
	
	echo('</table>');
}

?>