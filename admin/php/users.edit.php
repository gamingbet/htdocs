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
		if($login->getRang($_PAGES['more']) == "user" || strtolower($_PAGES['more']) == strtolower($_SESSION[ 'auth' ][ 'login' ]))
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "Nie można edytować kont administratorów.";
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
		$nick = addslashes(trim(htmlspecialchars($_POST['nick'])));
		$haslo = addslashes(trim(htmlspecialchars($_POST['haslo'])));
		$rehaslo = addslashes(trim(htmlspecialchars($_POST['rehaslo'])));
		$mail = addslashes(trim(htmlspecialchars($_POST['mail'])));
		$status = addslashes(trim(htmlspecialchars($_POST['status'])));
		$kraj = (int) addslashes(trim(htmlspecialchars($_POST['kraj'])));
		$imie = addslashes(trim(htmlspecialchars($_POST['imie'])));
		$nazwisko = addslashes(trim(htmlspecialchars($_POST['nazwisko'])));
		$kod = addslashes(trim(htmlspecialchars($_POST['kod'])));
		$miasto = addslashes(trim(htmlspecialchars($_POST['miasto'])));
		$ulica = addslashes(trim(htmlspecialchars($_POST['ulica'])));
		$urodzony = addslashes(trim(htmlspecialchars($_POST['urodzony'])));
		$status = addslashes(trim(htmlspecialchars($_POST['status'])));
		$credits = (int) addslashes(trim(htmlspecialchars($_POST['credits'])));
		
		if( empty($nick ) || empty($mail) || empty($credits) || empty($status) )
			$errors[] = "Nie zostały wypełnione wszystkie pola";
		
		$R = new Register;
		
		if(!preg_match("#^[a-zA-Z0-9_]+$#", $nick))
			$errors[] = "Nick może zawierać tylko znaki A-Z, 0-9, pauza (-) oraz podkreślnik (_)";
		
		// If e-mail adress isn't valid
		if(!preg_match('/^[a-zA-Z0-9\.\-\_\+]+\@[a-zA-Z0-9\.\-\_]+\.[a-z]{2,}$/D', $mail))
			$errors[] = "Wprowadź poprawny adres e-mail";
		
		// zmiana nicku
		if($nick != $user['nick'])
		{
			// If nickname is busy
			if($R->issetAccount($nick))
				$errors[] = "Wprowadzona nazwa użytkownika jest już zajęta";
			
			$nick_change = true;
		}
		
		// zmiana hasla
		if( !empty($haslo) )
		{
			// If nickname is the same like password
			if($nick == $haslo)
				$errors[] = "Hasło i nick nie mogą byc identyczne";
			
			// If length password is less than 6
			if(strlen($haslo) < 6)
				$errors[] = "Hasło powinno zawierać conajmniej 6 znaków";
			
			if($haslo !== $rehaslo || empty($haslo))
				$errors[] = "Wpisane hasła nie są identyczne";
				
			$password_change = true;
		}
	
		// zmiana mailu
		if($mail != $user['email'])
		{
			// If E-mail adress is busy
			if($R->issetEMail($mail))
				$errors[] = "Wprowadzony adres e-mail jest już używany";
			
			$mail_change = true;
		}
		
		// data validation
		if( !empty($urodzony) && $urodzony != "0000-00-00" )
		{
			$temp_date = explode('-', $urodzony);
			if( $temp_date[0] < 1900 )
				$errors[] = "Użytkowniku, nie jesteś za stary? ;)";
				
			if( (date('Y')- $temp_date[0]) < 18 )
				$errors[] = "Użytkowniku, nie jesteś za młody? ;)";

			if( !checkdate( (int)$temp_date[1], (int)$temp_date[2], (int)$temp_date[0] ) )
				$errors[] = "Nie wpisano poprawnego formatu daty";
		}
		
		if(empty($errors))
		{
			$str_nick = $str_pass = $str_mail = NULL;
			
			if(isset($nick_change))
			{
				$str_nick = ' `nick` = "'.$nick.'",';
				$_SESSION[ 'auth' ][ 'login' ] = $nick;
			}
			
			if(isset($password_change))
			{
				$salt = $login->getSalt($user['nick']);
				$pass = $salt.sha1($haslo);
				$str_pass = ' `password` = "'.$pass.'",';
			}
			
			if(isset($mail_change))
			{
				$str_nick = ' `email` = "'.$mail.'",';
			}
			
			$edit = $db->prepare('UPDATE `users` SET '.$str_mail . $str_pass . $str_nick .' `firstName` = :firstName, `credits` = :credists, `surname` = :surname,
			`age` = :age, `street` = :street, `city` = :city, `code` = :code, `countryId` = :countryId, `active` = :status WHERE `id` = :nick LIMIT 1');
			
			$edit->bindValue(':firstName', $imie, PDO::PARAM_STR);
			$edit->bindValue(':credists', $credits, PDO::PARAM_STR);
			$edit->bindValue(':surname', $nazwisko, PDO::PARAM_STR);
			$edit->bindValue(':age', $urodzony, PDO::PARAM_STR);
			$edit->bindValue(':street', $ulica, PDO::PARAM_STR);
			$edit->bindValue(':city', $miasto, PDO::PARAM_STR);
			$edit->bindValue(':code', $kod, PDO::PARAM_STR);
			$edit->bindValue(':countryId', $kraj, PDO::PARAM_INT);
			$edit->bindValue(':status', $status, PDO::PARAM_STR);
			$edit->bindValue(':nick', $user['id'], PDO::PARAM_STR);
			$edit->execute();
			
			$sent = true;
		}
	}
	else
	{
		$nick = $user['nick'];
		$mail = $user['email'];
		$status = $user['active'];
		$kraj = $user['countryId'];
		$imie = $user['firstName'];
		$nazwisko = $user['surname'];
		$kod = $user['code'];
		$miasto = $user['city'];
		$ulica = $user['street'];
		$urodzony = $user['age'];
		$credits = $user['credits'];
		$ip = $user['ip'];
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
			<label for="input_20">Adres IP</label>
			<input type="text" id="input_20" value="'.stripslashes($ip).'" readonly>
		</fieldset>
		
		<fieldset>
			<label for="input_1">Nazwa użytkownika <span class="star">*</span></label>
			<input type="text" id="input_1" name="nick" value="'.stripslashes($nick).'" required>
			<input type="hidden" name="old_name" value="'.$user['nick'].'">
		</fieldset>
		
		<fieldset>
			<label for="input_2">Hasło <span class="star">*</span></label>
			<input type="password" id="input_2" name="haslo" value="">
		</fieldset>
		
		<fieldset>
			<label for="input_3">Powtórz hasło <span class="star">*</span></label>
			<input type="password" id="input_3" name="rehaslo" value="">
		</fieldset>
		
		<fieldset>
			<label for="input_4">E-mail <span class="star">*</span></label>
			<input type="text" id="input_4" name="mail" value="'.stripslashes($mail).'" required>
			<input type="hidden" name="old_mail" value="'.$user['email'].'">
		</fieldset>
		
		<fieldset>
			<label for="input_14">Kredyty <span class="star">*</span></label>
			<input type="text" id="input_14" name="credits" value="'.stripslashes($credits).'" required>
			
		</fieldset>
		
		<fieldset>
			<label for="input_5">Status <span class="star">*</span></label>
			<select id="input_5" name="status" required>
				<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
				<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_6">Imię <span class="star"></span></label>
			<input type="text" id="input_6" name="imie" value="'.stripslashes($imie).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_7">Nazwisko <span class="star"></span></label>
			<input type="text" id="input_7" name="nazwisko" value="'.stripslashes($nazwisko).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_8">Miasto <span class="star">*</span></label>
			<input type="text" id="input_8" name="miasto" value="'.stripslashes($miasto).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_12">Ulica <span class="star"></span></label>
			<input type="text" id="input_12" name="ulica" value="'.stripslashes($ulica).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_9">Kod pocztowy <span class="star"></span></label>
			<input type="text" id="input_9" name="kod" value="'.stripslashes($kod).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_10">Data urodzenia [YYYY-MM-DD] <span class="star"></span></label>
			<input type="text" id="input_10" name="urodzony" value="'.stripslashes($urodzony).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_11">Kraj <span class="star"></span></label>
			<select name="kraj" id="input_11" required>');
		
			$sql = $db->query('SELECT * FROM `countries` ORDER BY `id` ASC');
			while( $country = $sql->fetch() )
			{
				echo('<option value="'. $country['id'] .'"');
				if($country['id'] == $kraj)
				{
					echo(' selected');
				}
				echo('>'. $country[ 'name-pl' ] .'</option>');
			}
		
		echo('</select>
		</fieldset>
		
		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		
		</form>');
	}
}
else
{
echo('<p>Wskaż profil, który chcesz edytować. Poniższe menu wyświetla nazwy użytkowników zaczynające się na zaznaczony znak.</p>');
$source = 'abcdefghijklmnopqrstuvwxyz_';
$count = strlen($source);
$i = 1;

echo('<ul class="tabs">
<li><a href="#tab'.$i++.'">0-9</a></li>');

for($j = 0; $j < $count; $j++)
{
	echo('<li><a href="#tab'.$i++.'">'.strtoupper($source[$j]).'</a></li>');
}

echo('</ul>
<div style="clear: both" class="tab_container">');

$numbers = $db->query('SELECT `nick` FROM `users` WHERE `nick` REGEXP "^[0-9]+[a-z0-9_].*$"');
$i = 1;

echo('<div id="tab'.$i++.'" class="tab_content">');
if($numbers->rowCount() > 0)
{
	echo('<ul>');
	while($nick = $numbers->fetch())
	{
		echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a></li>');
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
	$nicks = $db->query('SELECT `nick` FROM `users` WHERE `nick` REGEXP "^['.$source[$j].']+[a-z0-9_].*$"');
	if($nicks->rowCount())
	{
		echo('<ul>');
		while($nick = $nicks->fetch())
		{
			echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a></li>');
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
}

?>