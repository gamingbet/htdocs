<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$nick = addslashes(trim(htmlspecialchars($_POST['nick'])));
	$haslo = addslashes(trim(htmlspecialchars($_POST['haslo'])));
	$rehaslo = addslashes(trim(htmlspecialchars($_POST['rehaslo'])));
	$mail = addslashes(trim(htmlspecialchars($_POST['mail'])));
	
	$R = new Register;
	
	// If empty nick name
	if(empty($nick))
		$errors[] = "Pole nick nie zostało wypełnione";
			
	// If nick name doens't valid (a-z 0-9 _-)
	if(!preg_match("#^[a-zA-Z0-9_]+$#", $nick))
		$errors[] = "Nick może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";
	
	// If nickname is the same like password
	if($nick == $haslo)
		$errors[] = "Hasło i nick nie mogą byc identyczne";
	
	// If length password is less than 6
	if(strlen($haslo) < 6)
		$errors[] = "Hasło powinno zawierać conajmniej 6 znaków";
	
	// If password and re-password aren't the same
	if($haslo !== $rehaslo || empty($haslo))
		$errors[] = "Wpisane hasła nie są identyczne";
		
	// If e-mail adress isn't valid
	if(!preg_match('/^[a-zA-Z0-9\.\-\_\+]+\@[a-zA-Z0-9\.\-\_]+\.[a-z]{2,}$/D', $mail))
		$errors[] = "Wprowadź poprawny adres e-mail";
	
	// If E-mail adress is busy
	if($R->issetEMail($mail))
		$errors[] = "Wprowadzony adres e-mail jest już używany";
	
	// If nickname is busy
	if($R->issetAccount($nick))
		$errors[] = "Wprowadzona nazwa użytkownika jest już zajęta";
		
	if(empty($errors))
	{
		$time = date( _SQLDate_ );
		$salt = substr( md5( $time ), 0, 10 );
		$active = "true";
		$newsletter = "false";
		$credits = $_SETTINGS[ 'bets' ][ 'start-credits' ];
		$password = $salt.sha1($haslo);
		$ref = 0;
		
		if( $R->createAccount($nick, $password, $mail, $active, "pl", $credits, $time, $newsletter, $_SERVER['REMOTE_ADDR'], $ref))
		{
			$sent = true;
		}
	}
}
else
{
	$nick = NULL;
	$haslo = NULL;
	$rehaslo = NULL;
	$mail = NULL;
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
	
	echo('<p>Konto, które zostanie utworzone z tego miejsca zostanie aktywowane automatycznie.</p>
	<form action="'.$_ACTION.'" method="post" class="post_message">
	
	<fieldset>
		<label for="input_1">Nick <span class="star">*</span></label>
		<input type="text" id="input_1" name="nick" value="'.stripslashes($nick).'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_2">Hasło <span class="star">*</span></label>
		<input type="password" id="input_2" name="haslo" value="" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Powtórz hasło <span class="star">*</span></label>
		<input type="password" id="input_3" name="rehaslo" value="" required>
	</fieldset>
	
	<fieldset>
		<label for="input_4">E-mail <span class="star">*</span></label>
		<input type="text" id="input_4" name="mail" value="'.stripslashes($mail).'" required>
	</fieldset>
	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	
	</form>');
}

?>