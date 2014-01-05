<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}


$prepare = $db->query('SELECT * FROM `langs` WHERE `category` = "bets"');

while($p = $prepare->fetch())

{

	$_LANG[ $p['label'] ] = $p['label-pl'];

}

$result = $db->query('SELECT * FROM `ad`');

$ad_settings = $result->fetch();

$sent = false;

$errors = array();

$showAll = true;

if( isset($_POST['submit']) && $_POST['submit'] == "Zapisz zmiany" )
{
	//Reklama jest zapisywana
	if(isset($_POST['input_ad_endabled']))
	{
		if($_POST['input_ad_endabled'] == 'yes')
		{
			$input_ad_endabled = 1;
		}
		else
		{
			$input_ad_endabled = 0;	
		}
	}
	else
	{
		$errors[] = 'Nie wysłano informacji czy włączyć reklame';
	}

	if(isset($_POST['input_ad_html']))
	{
		$input_ad_html = $_POST['input_ad_html'];
	}
	else
	{
		$input_ad_html = FALSE;
	}

	if($input_ad_html !== FALSE)
	{
		$stmt = $db->prepare("UPDATE `ad` SET `ad_html` = :input_ad_html, `ad_endabled` = :input_ad_endabled WHERE `id` = 0");
		$stmt->bindValue(':input_ad_html', $input_ad_html);
		$stmt->bindValue(':input_ad_endabled', $input_ad_endabled);
		$result = $stmt->execute();
		$result = $db->query('SELECT * FROM `ad`');
		$ad_settings = $result->fetch();
	}
	else
	{
		$errors[] = 'Nie wysłano kodu reklamy';
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

if($sent == true)
{

	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	echo('<ul>

		<li><a href="/">Przejdź do strony głównej</a></li>

		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

		<li><a href="/admin/matches/add">Dodaj nowy mecz</a></li>

		<li><a href="/admin/matches/edit">Zarządzaj meczami</a></li>

		<li><a href="/admin/bets/add">Dodaj zakłady do meczów</a></li>

		<li><a href="/admin/bets/edit">Zarządzaj zakładami do meczów</a></li>

	</ul>');

}
else
{
	echo '<form action="'.$_ACTION.'" method="post" class="post_message">';
	
	echo '<fieldset>';

	echo '<label for="input_ad_endabled">Włączyć reklamę?</label>';

	echo '<select name="input_ad_endabled" id="input_ad_endabled">';

		$yes_selected = ($ad_settings['ad_endabled'] == '1') ? 'selected="selected"' : "";
		$no_selected = ($ad_settings['ad_endabled'] == '0') ? 'selected="selected"' : "";

		echo '<option value="yes"'.$yes_selected.'>Tak</option>';

		echo '<option value="no"'.$no_selected.'>Nie</option>';

	echo '</select>';

	echo '</fieldset>';

	echo '<fieldset>';
	echo '<label for="input_ad_html">Kod HTML reklamy (zdjęcie, film YouTube itp.)</label>';
	echo '<textarea required="true" name="input_ad_html" id="input_ad_html" rows="6">'.$ad_settings['ad_html'].'</textarea>';		
	echo '</fieldset>';
	
	echo '<input type="submit" name="submit" class="alt_btn" value="Zapisz zmiany">';
	
	echo '</form>';
}