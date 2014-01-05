<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();
$showAll = true;

if( !empty($_PAGES['more']) )
{
	@$page_id = (int) $_PAGES['more'];
	$page = $db->query('SELECT `id` FROM `bettypes` WHERE `id` = '.$page_id);
	if( $page->rowCount() == 1)
	{
		$showAll = false;
	}
	else
	{
		$errors[] = "Zakład o podanym ID nie został odnaleziony";
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
	$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
	
	if( empty($nazwa_pl) || empty($nazwa_en) )
		$errors[] = "Niewszystkie pola zostały wypełnione";
	
	if(empty($errors))
	{
		@$page_id = (int) $_PAGES['more'];
		$page = $db->query('SELECT * FROM `bettypes` WHERE `id` = '.$page_id);
		$bet = $page->fetch();
		
		$db->exec('UPDATE `langs` SET `label-pl` = "'.$nazwa_pl.'", `label-en` = "'.$nazwa_en.'" WHERE `category` = "bets" AND `label` = "'.$bet['type'].'"');
		$sent = true;
	}
}
else
{
	@$page_id = (int) $_PAGES['more'];
	$page = $db->query('SELECT * FROM `bettypes` WHERE `id` = '.$page_id);
	$bet = $page->fetch();
	$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "bets" AND `label` = "'.$bet['type'].'"');
	$lang = $lang->fetch();
	$uniqid = $bet['type'];
	$nazwa_pl = $lang['label-pl'];
	$nazwa_en = $lang['label-en'];
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
	if( $showAll == true )
	{
		$bets = $db->query('SELECT * FROM `bettypes` ORDER BY `id` ASC');
		if($bets->rowCount() == 0)
		{
			echo('<h4 class="alert_info">Brak typów zakładów do edycji!</h4>');
		}
		else
		{
			echo('<p>Aby edytować typ zakładu kliknij na jego tytuł.</p>');
			
			echo('<table class="tablesorter">
		
			<thead>
				<tr>
					<td style="width: 10%">ID</td>
					<td style="width: 65%">Nazwa</td>
					<td style="width: 25%">Uniq ID</td>
				</tr>
			</thead>');
			
			while( $bet = $bets->fetch() )
			{
				$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "bets" AND `label` = "'.$bet['type'].'"');
				$lang = $lang->fetch();
				echo('<tr>
					<td class="center">'.$bet['id'].'</td>
					<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$bet['id'].'">'.$lang['label-pl'].'</a><br>
						<tt>EN:</tt> <a href="'.$_ACTION.'/'.$bet['id'].'">'.$lang['label-en'].'</a>
					</td>
					<td>'.$bet['type'].'</td>
				</tr>');
			}
			
			echo('</table>');
		}
	}
	else
	{
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
		
		<fieldset>
			<label for="input_1">ID zakładu <span class="star">*</span></label>
			<input type="text" id="input_1" name="uniq_id" value="'.stripslashes($uniqid).'" disabled>
		</fieldset>

		<fieldset>
			<label for="input_2">Nazwa zakładu [PL] <span class="star">*</span></label>
			<input type="text" id="input_2" name="nazwa_pl" value="'.stripslashes($nazwa_pl).'" required>
		</fieldset>

		<fieldset>
			<label for="input_3">Nazwa zakładu [EN] <span class="star">*</span></label>
			<input type="text" id="input_3" name="nazwa_en" value="'.stripslashes($nazwa_en).'" required>
		</fieldset>
		
		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		</form>');
	}
}

?>