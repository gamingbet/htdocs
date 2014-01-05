<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}
if( __HEADADMIN__ == false )
{
	echo('<h4 class="alert_warning">Ta sekcja dozwolona jest TYLKO i wyłącznie dla głównego administratora!</h4>');
	exit();
}

$errors = array();
$login = new Login;

if(!empty($_PAGES['more']))
{
	$login = new Login;
	$adm = $db->query('SELECT * FROM `admins` WHERE `userId` = "'.$login->getIdByLogin($_PAGES['more']).'"');
	if($adm->rowCount() == 0)
		$errors[] = "Ten użytkownik nie jest administratorem";
	else
	{
		$adm = $adm->fetch();
		if($adm['headAdmin'] == "true")
			$errors[] = "Nie możesz usunąć głównego administratora";
	}
		
	if(empty($errors))
	{
		$db->exec('DELETE FROM `admins` WHERE `userId` = "'.$login->getIdByLogin($_PAGES['more']).'" LIMIT 1');
		echo('<h4 class="alert_success">Operacja zakończona sukcesem!</h4>');
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

echo('<h4>Osoby posiadające uprawnienia administratora:</h4>');
$admins = $db->query('SELECT * FROM `admins` WHERE `headAdmin` = "false" ORDER BY `add` ASC');
if($admins->rowCount() == 0)
{
	echo('<h4 class="alert_info">Brak administratorów</h4>');
}
else
{
	echo('<table class="tablesorter">
			<thead>
				<tr>
					<td style="width: 10%">User ID</td>
					<td style="width: 60%">Użytkownik</td>
					<td style="width: 25%">Admin od</td>
					<td style="width: 5%">Usuń</td>
				</tr>
			</thead>');
		
	while($admin = $admins->fetch())
	{
		echo('<tr>
			<td class="center">'.$admin['userId'].'</td>
			<td>'.$login->getLoginById($admin['userId']).'</td>
			<td>'.$admin['add'].'</td>
			<td class="center">
				<form action="'.$_ACTION.'/'.$login->getLoginById($admin['userId']).'" class="post_message" method="post">
					<input type="image" src="images/icn_trash.png" title="Kosz">
				</form>
			</td>
		</tr>');
	}
			
	echo('</table>');
}

?>