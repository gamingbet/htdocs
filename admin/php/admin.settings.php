<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	foreach($_POST['social'] as $nazwa => $wartosc)
	{
		$db->exec('UPDATE `social` SET `url` = "'.addslashes($wartosc).'" WHERE `name` = "'.$nazwa.'" LIMIT 1');
	}
	
	unset($_POST['submit']);
	unset($_POST['social']);
	
	foreach($_POST['pl'] as $category => $values)
	{
		foreach($values as $label => $value)
		{
			$pl = $value;
			$en = ($_POST['double'][$category][$label] == "true") ? $_POST['en'][$category][$label] : $pl;
			
			$db->exec('UPDATE `settings` SET `value-pl` = "'.addslashes($pl).'", `value-en` = "'.addslashes($en).'" WHERE `category` = "'.$category.'" AND `name` = "'.$label.'" LIMIT 1');
		}
	}
	$sent = true;
}

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się sukcesem!</h4>');
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

$sql = $db->query('SELECT * FROM `settings`');
while( $fetch = $sql->fetch() )
{
	$_SET[ 'pl' ][ $fetch[ 'category' ] ][ $fetch[ 'name' ] ] = $fetch[ 'value-pl' ];
	$_SET[ 'en' ][ $fetch[ 'category' ] ][ $fetch[ 'name' ] ] = $fetch[ 'value-en' ];
}

echo('<ul class="tabs">
<li><a href="#tab-glowne">Główne</a></li>
<li><a href="#tab-rejestracja">Rejestracja &amp; logowanie</a></li>
<li><a href="#tab-reklamy">Reklamy</a></li>
<li><a href="#tab-pozostale">Pozostałe</a></li>
<li><a href="#tab-social">Social</a></li>
</ul>');

echo('<form action="'.$_ACTION.'" method="post" class="post_message">
<div style="clear: both" class="tab_container">

<div id="tab-glowne" class="tab_content">
<h5>Główne</h5>
'.showSettings("string", "general", "keywords", "Słowa kluczowe strony", NULL, true).'
'.showSettings("string", "general", "description", "Opis strony", NULL, true).'
'.showSettings("string", "general", "url", "Adres URL strony (http://) bez / na końcu!").'
'.showSettings("bool", "general", "change-lang", "Czy można zmieniać język użytkownika?").'
'.showSettings("bool", "general", "default-lang", "Domyślny język użytkownika po wejściu na stronę", array('pl', 'en')).'
<h5>Wygląd</h5>
'.showSettings("int", "match", "next-match", "Ilość meczy, które wyświetlają się na stronie głównej").'
'.showSettings("int", "news", "index", "Ilośc newsów, które widoczne są na stronie głównej").'
</div>

<div id="tab-reklamy" class="tab_content">
<h5>Reklamy</h5>
'.showSettings("html", "ads", "ads_top", "Kod reklam wyświetlających się w topie").'
'.showSettings("html", "ads", "ads_menu", "Kod reklam wyświetlających się pod menu").'
'.showSettings("html", "ads", "ads_partners", "Kod reklam wyświetlających się pod sekcją partners").'
'.showSettings("html", "ads", "ads_panels", "Kod reklam wyświetlających się pod panelami bocznymi").'
'.showSettings("html", "ads", "ads_footer", "Kod reklam wyświetlających się nad stopką").'
</div>

<div id="tab-rejestracja" class="tab_content">
<h5>Rejestracja</h5>
'.showSettings("bool", "register", "register", "Czy można się rejestrować?").'
'.showSettings("bool", "register", "active-account", "Czy konta wymagają aktywacji?").'
'.showSettings("bool", "general", "login", "Czy można się logować?").'
'.showSettings("int", "bets", "start-credits", "Startowa ilość kredytów przyznawana użytkownikowi podczas rejestracji").'
<h5>Logowanie</h5>
'.showSettings("int", "auth", "unActiveTime", "Czas w minutach, po którym użytkownik zostanie automatycznie wylogowany z powodu nieaktywności").'
'.showSettings("int", "auth", "hash-key", "Czas w minutach żywotności kluczów pozwalających stworzyć nowe hasło dla konta").'
'.showSettings("int", "auth", "antispam", "Odstęp w minutach w jakich można wysłać na pocztę żądanie zmiany hasła").'
<h5>Avatar</h5>
'.showSettings("int", "auth", "avatar", "Maksymalne rozmiary avatara (kwadrat)").'
'.showSettings("int", "auth", "avatar-size", "Rozmiar w bajtach maksymalnej wagi obrazka (avatar)").'
<h5>Referencje</h5>
'.showSettings("int", "auth", "mail-days", "Czas w dniach żywotności żądania zmiany adresu e-mail").'
'.showSettings("bool", "auth", "ref-active", "Czy można polecać serwis poprzez swój link?").'
'.showSettings("int", "auth", "ref-bonus", "Ilość kredytów, które zostaną przyznane kiedy użytkownik polecony aktywuje konto").'
</div>

<div id="tab-pozostale" class="tab_content">
<h5>Zakłady</h5>
'.showSettings("int", "bets", "last-matches", "Ilość ostatnich meczy wyświetlanych na stronie z drużyną (teams)").'
'.showSettings("int", "bets", "tpe", "Ilość (aktywnych) zakładów, do których użytkownik może przypisać się jednocześnie.").'
<h5>reCAPTCHA</h5>
'.showSettings("bool", "general", "theme", "Wygląd reCAPTCHA", array('red', 'white', 'blackglass')).'
'.showSettings("string", "general", "rules", "Nazwa strony (link) strony z zasadami (podczas rejestracji)").'
'.showSettings("string", "general", "sitename", "Nazwa strony (wyświetlana w title obok tytuły podstrony)").'
'.showSettings("string", "general", "publickey", "Publiczny kod do reCAPTCHA").'
'.showSettings("string", "general", "privatekey", "Prywatny kod do reCAPTCHA").'
</div>

<div id="tab-social" class="tab_content">
<h5>Social</h5>');

$socials = $db->query('SELECT * FROM `social`');
while( $social = $socials->fetch() )
{
	echo('<fieldset>
		<table class="tablesorter">
		<tr>
			<td colspan="2">
			<tt>'.$social['name'].'</tt><br>
			Wprowadź adres URL do powyższego Sociala (puste == nieaktywne)
			</td>
		</tr>
		<tr>
			<td>
				<textarea name="social['.$social['name'].']" rows="2">'.$social['url'].'</textarea>
			</td>
		</tr>
		</table>
		</fieldset>
	');
}

echo('</div>

<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
</div>');



?>