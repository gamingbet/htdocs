<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( $_PAGES['more'] == "remove" )
{
	@$page_id = (int) $_POST['bet_id'];
	$page = $db->query('SELECT * FROM `bettypes` WHERE `id` = '.$page_id);
	if( $page->rowCount() == 1)
	{
		$types = $db->query('SELECT * FROM `bets`WHERE `typeId` = "'.$page_id.'"');
		if($types->rowCount() == 0)
		{
			$bet = $page->fetch();
			$db->exec('DELETE FROM `langs` WHERE `category` = "bets" AND `label` = "'.$bet['type'].'" LIMIT 1');
			$db->exec('DELETE FROM `bettypes` WHERE `id` = "'.$page_id.'" LIMIT 1');
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		}
		else
		{
			$errors[] = "Ten zakład jest przypisany do meczu. Dla poprawnego wyświetlania informacji na stronie nie może zostać usunięty";
		}
	}
	else
	{
		$errors[] = "Zakład o podanym ID nie został odnaleziony";
	}
}


if( !empty($errors ) ) 
{
	echo('<h4 class="alert_error">Podczas operacji wystąpiły błędy!</h4>');
	echo('<ul>');
	foreach($errors as $error)
	{
		echo('<li>'.$error.'</li>');
	
	echo('</ul>');
	}
}

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/admin/bets_add">Dodaj nowy typ zakładu</a></li>
		<li><a href="/admin/admin/bets_edit">Edytuj typy zakładów</a></li>
		<li><a href="/admin/admin/bets_remove">Usuń typ zakładu</a></li>
	</ul>');
}
else
{
	$bets = $db->query('SELECT * FROM `bettypes` ORDER BY `id` ASC');
	if($bets->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak typów zakładów do usunięcia!</h4>');
	}
	else
	{
		echo('<p>Aby edytować typ zakładu kliknij na jego tytuł.</p>');
		
		echo('<table class="tablesorter">
	
		<thead>
			<tr>
				<td style="width: 10%">ID</td>
				<td style="width: 60%">Nazwa</td>
				<td style="width: 25%">Uniq ID</td>
				<td style="width: 5%">Usuń</td>
			</tr>
		</thead>');
		
		while( $bet = $bets->fetch() )
		{
			$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "bets" AND `label` = "'.$bet['type'].'"');
			$lang = $lang->fetch();
			echo('<tr>
				<td class="center">'.$bet['id'].'</td>
				<td><tt>PL:</tt> '.$lang['label-pl'].'<br>
					<tt>EN:</tt> '.$lang['label-en'].'
				</td>
				<td>'.$bet['type'].'</td>
				<td class="center">
					<form action="'.$_ACTION.'/remove" class="post_message" method="post">
						<input type="hidden" name="bet_id" value="'.$bet['id'].'">
						<input type="image" src="images/icn_trash.png" title="Kosz">
					</form>
				</td>
			</tr>');
		}
		
		echo('</table>');
	}
}

?>