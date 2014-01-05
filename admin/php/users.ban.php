<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$showAll = true;
$errors = array();

if( !empty($_PAGES['more']) )
{
	$nick = $db->query('SELECT `id` FROM `users` WHERE `nick` = "'.$_PAGES['more'].'"');
	if($nick->rowCount() == 1 )
	{
		if($login->getRang($_PAGES['more']) == "user")
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "Nie można zablokować konta administratora.";
		}
	}
	else
	{
		$errors[] = "Użytkownik o podanym loginie nie istnieje";
	}
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

if($showAll == false)
{
	$sent = false;
	$user = $db->query('SELECT * FROM `users` WHERE `nick` = "'.$_PAGES['more'].'"');
	$user = $user->fetch();
	
	if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
	{
		$status = addslashes(trim(htmlspecialchars($_POST['ban'])));
		$czas = addslashes(trim(htmlspecialchars($_POST['czas'])));
		
		$time = explode(" ", $czas);
		$temp_date = explode('-', $time[0]);
		
		if( 
			  (int) $temp_date[0] <  (int) date('Y') || 
			( (int) $temp_date[0] <= (int) date('Y') && (int) $temp_date[1] < (int) date('m') ) ||
			( (int) $temp_date[0] <= (int) date('Y') && (int) $temp_date[1] <= (int) date('m') && (int) $temp_date[2] < date('j') ) 
		)
			$errors[] = "Nie można zablokować użytkownika z datą z przeszłości";

		if( !checkdate( (int)$temp_date[1], (int)$temp_date[2], (int)$temp_date[0] ) )
			$errors[] = "Nie wpisano poprawnego formatu daty";
		
		if($status == "false")
			$czas = "0000-00-00 00:00";
		
		if(empty($errors))
		{
			$db->exec('UPDATE `users` SET `banTime` = "'.$czas.'", `ban` = "'.$status.'" WHERE `nick` = "'.$_PAGES['more'].'"');
			$sent = true;
		}
	}
	else
	{
		$status = $user['ban'];
		$czas = $user['banTime'];
	}
	
	if($sent == true)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		echo('<ul>
			<li><a href="/">Przejdź do strony głównej</a></li>
			<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
			<li><a href="/admin/users/add">Dodaj nowego użytkownika</a></li>
			<li><a href="/admin/users/edit">Zarządzaj użytkownikami</a></li>		
			<li><a href="/admin/users/ban">Zablokuj użytkownika</a></li>
		</ul>');	
	}
	else
	{
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
		
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post">
		
		<fieldset>
			<label for="input_1">Nazwa użytkownika <span class="star">*</span></label>
			<input type="text" id="input_1" name="nick" value="'.$user['nick'].'" readonly disabled>
		</fieldset>
		
		<fieldset>
			<label for="input_2">Status <span class="star">*</span></label>
			<select id="input_2" name="ban" required>
				<option value="true"');if($status == "true"){echo(' selected');}echo('>Zablokowany</option>
				<option value="false"');if($status == "false"){echo(' selected');}echo('>Odblokowany</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_3">Do kiedy [YYYY-MM-DD GG:MM:SS] <span class="star">*</span></label>
			<input type="text" id="input_3" name="czas" value="'.$czas.'">
		</fieldset>
		
		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		
		</form>');
	}
}
else
{
	echo('<p>Wskaż profil, który chcesz zablokować. Poniższe menu wyświetla nazwy użytkowników zaczynające się na zaznaczony znak.</p>');
	$source = 'abcdefghijklmnopqrstuvwxyz_';
	$count = strlen($source);
	$i = 1;

	echo('<h4>Konta użytkowników</h4>
	<ul class="tabs">
	<li><a href="#tab'.$i++.'">0-9</a></li>');

	for($j = 0; $j < $count; $j++)
	{
		echo('<li><a href="#tab'.$i++.'">'.strtoupper($source[$j]).'</a></li>');
	}

	echo('</ul>
	<div style="clear: both" class="tab_container">');

	$numbers = $db->query('SELECT `nick`, `ban` FROM `users` WHERE `nick` REGEXP "^[0-9]+[a-z0-9_].*$"');
	$i = 1;

	echo('<div id="tab'.$i++.'" class="tab_content">');
	if($numbers->rowCount() > 0)
	{
		echo('<ul>');
		while($nick = $numbers->fetch())
		{
			echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a>'.(($nick['ban']=="true")?' <tt>BAN</tt>':'').'</li>');
		}
		echo('</ul>');
	}
	else
	{
		echo('<p>Brak zarejestrowanych użytkowników rozpoczynających się od tego znaku.</p>');
	}
	echo('</div>');

	for($j = 0; $j < $count; $j++)
	{
		echo('<div id="tab'.$i++.'" class="tab_content">');
		$nicks = $db->query('SELECT `nick`, `ban` FROM `users` WHERE `nick` REGEXP "^['.$source[$j].']+[a-z0-9_].*$"');
		if($nicks->rowCount())
		{
			echo('<ul>');
			while($nick = $nicks->fetch())
			{
				echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a>'.(($nick['ban']=="true")?' <tt>BAN</tt>':'').'</li>');
			}
			echo('</ul>');
		}
		else
		{
			echo('<p>Brak zarejestrowanych użytkowników rozpoczynających się od tego znaku.</p>');
		}
		echo('</div>');
	}

	echo('</div>');
	echo('<h4>Zablokowane konta</h4>');
	
	$bans = $db->query('SELECT `id`, `nick`, `banTime` FROM `users` WHERE `ban` = "true" AND `banTime` > NOW() ORDER BY `banTime` ASC');	
	if($bans->rowCount() > 0)
	{
		echo('<table class="tablesorter">
		
		<thead>
			<tr>
				<td style="width: 10%">ID</td>
				<td style="width: 70%">Nick użytkownika</td>
				<td style="width: 20%">Do kiedy</td>
			</tr>
		</thead>');
		
		while($ban = $bans->fetch())
		{
			echo('<tr>
				<td class="center">'.$ban['id'].'</td>
				<td><a href="'.$_ACTION.'/'.$ban['nick'].'">'.$ban['nick'].'</a></td>
				<td>'.$ban['banTime'].'</td>
			</tr>');
		}
		
		echo('</table>');
	}
	else
	{
		echo('<h4 class="alert_info">Żaden użytkownik nie jest zablokowany</h4>');
	}
}

?>