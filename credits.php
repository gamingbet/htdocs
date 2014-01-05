<?php

// Wynik

// Ustawienie URLC MUSI wskazywać w ten adres: http://gamingbet.eu/credits.php - ponieważ wtedy skrypt uruchomi tylko sekcję odpowiedzialną

// za dodawanie punktów do konta. Aby tak się stało, suma kontrolna musi się zgadzać. 100% szansy, że hacker jej nie złamie.

?>

<div id="mid_top"></div>

<div id="mid_cont"><div class="left">

<?php

if( isset($_POST) && !empty($_POST) )

{

	if( $_POST["t_status"] == 2 )

	{

		$PIN = 'ToMEK123098ToMEK';

		$md5 = md5($PIN.':45445:'.$_POST["control"].':'.$_POST["t_id"].':'.$_POST['amount'].':'.$_POST["email"].':::::'.$_POST['t_status']);

		

		if($md5 == $_POST['md5']) 

		{ 

			// transakcja zaakceptowana

			$user = $db->query('SELECT id, nick FROM users WHERE `email` = "'.$_POST['email'].'" LIMIT 1')->fetch();

			$kwota = explode(' ', $_POST["orginal_amount"]);

			

			$punkty = $db->query('SELECT `points` FROM `credits` WHERE `pln` = '.$kwota[0].' OR `eur` = '.$kwota[0].' LIMIT 1')->fetch();

			$points = $punkty['points'];

			

			$sql = 'INSERT INTO `credits_history` VALUES(

				NULL, 

				'.$user['id'].', 

				"'.$points.'", 

				"'.$kwota[1].'",

				"'.$_POST['amount'].'", 

				"finish",

				"'.$_POST["control"].'"

				)';

				

			$db->exec($sql);

			

			$sql = 'UPDATE `users` SET `credits` = `credits` + '.$points.', `creditsBought` = `creditsBought` + '.$points.' WHERE `id` = '.$user['id'].' LIMIT 1';

			$db->exec($sql);

		}

	}

	

}

else

{ 

	

	if( !defined("__LOAD__") )

	{

		exit();

		return false;

	}



	if( $_GLOBALS[ 'login' ][ 'login' ] == false )

	{

		echo( $_LANG[ 'auth' ][ 'need_login' ] );

		return false;

	}

	// Kod

	$street = explode('/', $_USER['street']);

	$street_left = explode(' ', $street[0]);

	$street_n1 = end($street_left);

	unset( $street_left[ count($street_left) - 1 ] );

	$street_left = implode(' ', $street_left);

	

	$_SESSION['dotpay_sid'] = uniqid();

?>



<form action="https://ssl.dotpay.pl/" method="post">



<!-- Opcje admina -->

<input type="hidden" name="id" value="45445">

<input type="hidden" name="control" value="<?php echo($_SESSION['dotpay_sid']); ?>">

<input type="hidden" name="url" value="http://gamingbet.eu/credits-bought">

<input type="hidden" name="type" value="3">

<input type="hidden" name="buttontext" value="Kliknij aby potwierdzić dokonanie płatności">

<input type="hidden" name="txtguzik" value="Powrót do serwisu">

<input type="hidden" name="lang" value="<?php echo($_GLOBALS['lang']); ?>">

<input type="hidden" name="potw" value="1">

<input type="hidden" name="description" value="Gamingbet.eu - Zakup punktów">



<span class="b">Kwota</span>

<ul>

<?php

	$waluta = ($_GLOBALS['lang'] == 'pl') ? 'PLN' : 'EUR';

	$query = $db->query('SELECT id, points, '.$waluta.' FROM `credits` ORDER BY id ASC');

	while($row = $query->fetch())

	{

		echo('<li><input type="radio" name="amount" value="'.$row[$waluta].'"> Punkty: '.$row['points'].' - '.$row[$waluta].' '.$waluta.'</li>');

	}

?>

</ul>



<?php // PLN EUR USD // ?>

<input type="hidden" name="currency" value="<?php echo($waluta); ?>">



<span class="b">Wybierz formę płatności:</span>

<ul>

	<li><input value="0" name="kanal" checked="checked" type="radio"> Karta VISA, MasterCard, EuroCard, JCB, Diners Club</li>

	<li><input value="1" name="kanal" type="radio"> mTransfer</li>

	<li><li><input value="2" name="kanal" type="radio"> Płacę z Inteligo</li>

	<li><input value="3" name="kanal" type="radio"> Multitransfer</li>

	<li><input value="6" name="kanal" type="radio"> Przelew24 (BZWBK)</li>

	<li><input value="7" name="kanal" type="radio"> ING Bank Śląski "ING Online"</li>

	<li><input value="8" name="kanal" type="radio"> Bank BPH "Sezam"</li>

	<li><input value="11" name="kanal" type="radio"> Przelew lub przekaz pocztowy</li>

	<li><input value="10" name="kanal" type="radio"> Bank Millenium "Millenet"</li>

	<li><input value="17" name="kanal" type="radio"> Płać z Nordea</li>

	<li><input value="9" name="kanal" type="radio"> Pekao24 (Bank Pekao S.A.)</li>

	<li><input value="13" name="kanal" type="radio"> Deutsche Bank PBC S.A.</li>

	<li><input value="14" name="kanal" type="radio"> Kredyt Bank S.A. (KB24)</li>

	<li><input value="15" name="kanal" type="radio"> Inteligo (Bank PKO BP)</li>

	<li><input value="16" name="kanal" type="radio"> Credit Agricole</li>

</ul>



<!-- User Info -->

<input type="hidden" name="forename" value="<?php echo($_USER['firstName']); ?>">

<input type="hidden" name="surname" value="<?php echo($_USER['surname']); ?>">

<input type="hidden" name="street" value="<?php echo($street_left); ?>">

<input type="hidden" name="street_n1" value="<?php echo($street_n1); ?>">

<input type="hidden" name="street_n2" value="<?php echo($street[1]); ?>">

<input type="hidden" name="city" value="<?php echo($_USER['city']); ?>">

<input type="hidden" name="postcode" value="<?php echo($_USER['code']); ?>">

<input type="hidden" name="emial" value="<?php echo($_USER['email']); ?>">



<input type="submit" value="Zapłać" />



</form>



<?php

}

?>