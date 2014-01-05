<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$uniqid = addslashes(trim(htmlspecialchars($_POST['uniq_id'])));
	$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
	$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
	
	if( empty( $uniqid ) || empty($nazwa_pl) || empty($nazwa_en) )
		$errors[] = "Niewszystkie pola zostały wypełnione";
	
	$bet = $db->query('SELECT * FROM `bettypes` WHERE `type` = "'.$uniqid.'"');
	if( $bet->rowCount() == 1 )
		$errors[] = "Zakład o takim ID już istnieje";
	else
	{
		$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "bets" AND `label` = "'.$uniqid.'"');
		if( $lang->rowCount() == 1 )
			$errors[] = "Nazwy tego zakładu nie możesz użyć";
	}
	
	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $uniqid))
		$errors[] = "Identyfikator może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";
	
	if( empty($errors) )
	{
		$db->exec('INSERT INTO `langs` VALUES(NULL, "bets", "'.$uniqid.'", "'.$nazwa_pl.'", "'.$nazwa_en.'")');
		$db->exec('INSERT INTO `bettypes` VALUES(NULL, "'.$uniqid.'", 2)');
		$sent = true;
	}
}
else
{
	$uniqid = uniqid();
	$nazwa_pl = NULL;
	$nazwa_en = NULL;
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
	echo('<p>ID zakładu może zawierać tylko i wyłącznie znaki A-Z, 0-9, pauzę (-) oraz podkreślnik (_).</p>
	<form action="'.$_ACTION.'" method="post" class="post_message">
	
	<fieldset>
		<label for="input_1">ID zakładu <span class="star">*</span></label>
		<input type="text" id="input_1" name="uniq_id" value="'.stripslashes($uniqid).'" required>
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

?>