<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

?>

<div class="clearfix" style="margin-top: 10px"></div>


<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-xs-12 col-sm-12 col-md-8">
<div class="well-sm biale"><p>
<h3>Przypomnij hasło</h3>
<?php
if( $_GLOBALS[ 'login' ][ 'login' ] == true )
{
	echo( $_LANG[ 'forgot' ][ 'forgot-too' ] );
	return false;
}
if( !empty($_PAGES[ 'type' ]) && !empty($_PAGES[ 'more' ]) )
{
	$change = false;
	$errors = array();
	$l = new Login();
	$sql = $db->prepare('SELECT *, TIMESTAMPDIFF(MINUTE, `date`, NOW()) AS `diff` FROM `keys` WHERE `userId` = :uid AND `key` = :key AND `action` = "password" LIMIT 1');
	$sql->bindValue(':uid', $l->getIdByLogin( $_PAGES[ 'more' ] ), PDO::PARAM_STR);
	$sql->bindValue(':key', $_PAGES[ 'type' ], PDO::PARAM_STR);
	$sql->execute();
	$result = $sql->fetch();
	if($result === false)

	{

		echo($_LANG[ 'errors' ][ 'hack' ]);

		return false;

	}

	else

	{

		if( $result["diff"] > $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'hash-key' ] || $_SERVER['REMOTE_ADDR'] != $result["ip"] )

		{

			echo($_LANG[ 'forgot' ][ 'key-noActive' ]);

			return false;

		}

	}

	

	if( isset( $_POST[ 'for_submit' ] ) && $_POST[ 'for_submit' ] == $_LANG[ 'labels' ][ 'change' ] )

	{

		$inputs['01'] = trim( mysql_escape_string( htmlspecialchars( $_POST[ 'register_02' ] ) ) );

		$inputs['02'] = trim( mysql_escape_string( htmlspecialchars( $_POST[ 'register_03' ] ) ) );

		

		// If length password is less than 6

		if(strlen($inputs['01']) < 6)

			$errors[] = $_LANG[ 'register' ][ 'too_short_pw' ];

		

		// If password and re-password aren't the same

		if($inputs['01'] !== $inputs['02'] || empty($inputs['01']))

			$errors[] = $_LANG[ 'register' ][ 'not_same_pw' ];



		if( empty($errors) )

		{

			$salt = $l->getSalt( $_PAGES[ 'more' ] );

			$password = $salt.sha1($inputs['01']);

			

			$sql = $db->prepare('UPDATE `users` SET `password` = :password WHERE `nick` = :nick LIMIT 1');

			$sql->bindValue(':password', $password, PDO::PARAM_STR);

			$sql->bindValue(':nick', $_PAGES[ 'more' ], PDO::PARAM_STR);

			$sql->execute();

			

			$sql = $db->prepare('DELETE FROM `keys` WHERE `id` = :id');

			$sql->bindValue(':id', $result["id"], PDO::PARAM_STR);

			$sql->execute();

			

			$change = true;

		}

	}

	

	if( $change == true )

	{

		echo(sprintf($_LANG[ 'forgot' ][ 'changed' ], $_PAGES[ 'more' ]));

	}

	else

	{

		if( !empty( $_LANG[ 'forgot' ][ 'info2' ] ) )

		{

			echo('<p>'. sprintf($_LANG[ 'forgot' ][ 'info2' ], $_PAGES[ 'more' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'hash-key' ]) .'</p>');

		}

		

		echo('<form action="forgot/'. $_PAGES[ 'type' ] .'/'. $_PAGES[ 'more' ] .'" method="post">

		<fieldset id="register">');

		echo('<dl>');

		

		if( !empty($errors) )

		{

			echo('<p class="hn">'.$_LANG['forgot'][ 'errors' ].'</p>');

			echo('<ul class="errors tekst">');

			foreach( $errors as $error )

			{

				echo('<li>'. $error. '</li>');

			}

			echo('</ul>');

		}

		echo('<dt><label for="register_02">'. $_LANG[ 'labels' ][ 'pw' ]. '</label></dt>');

		echo('<dd><input type="password" name="register_02" id="register_02" class="pass1" required><div class="status">&nbsp;</div><br>

		<div id="passwordStrengthDiv" class="is0"></div></dd>');

		echo('<dt><label for="register_03">'. $_LANG[ 'labels' ][ 'repw' ]. '</label></dt>');

		echo('<dd><input type="password" name="register_03" id="register_03"class="pass1" required><div class="status">&nbsp;</div></dd>');

		echo('<dd><input type="submit" name="for_submit" value="'. $_LANG[ 'labels' ][ 'change' ]. '" class="button"></dd>');

		echo('</dl>');

		echo('</fieldset>

		</form>');

		

	}

}

else

{

	require_once( '_class/recaptchalib.php' );

	$publickey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'publickey' ];

	

	$sent = false;

	$errors = array();

	

	// If sent form

	if(isset( $_POST[ 'lab_forgot' ] ) && $_POST[ 'lab_forgot' ] == $_LANG[ 'labels' ][ 'forgot' ])

	{

		$R = new Register;

		$l = new Login();

	

		$inputs['01'] = trim( mysql_escape_string( htmlspecialchars( $_POST[ 'forgot_01' ] ) ) );

		

		$privatekey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'privatekey' ];

		$resp = recaptcha_check_answer($privatekey,  $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		

		// If wrong catpcha

		if (!$resp->is_valid)

			$errors[] = $_LANG[ 'register' ][ 'wrong_catpcha' ];

			

		// If e-mail adress isn't valid

		if(!preg_match('/^[a-zA-Z0-9\.\-\_]+\@[a-zA-Z0-9\.\-\_]+\.[a-z]{2,4}$/D', $inputs['01']))

			$errors[] = $_LANG[ 'register' ][ 'mail_not_valid' ];

		

		// If E-mail adress isn't busy

		if(!$R->issetEMail($inputs['01']))

			$errors[] = $_LANG[ 'forgot' ][ 'free_mail' ];

			

		// Antispam

		$sql = $db->prepare('SELECT TIMESTAMPDIFF(MINUTE, `date`, NOW()) AS `diff` FROM `keys` WHERE `userId` = :uid ORDER BY `id` DESC LIMIT 1');

		$sql->bindValue(':uid', $l->getIdByLogin( $l->getNickByMail($inputs['01']) ), PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		if($sql->rowCount() > 0 && $result["diff"] < $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'antispam' ] )

			$errors[] = sprintf($_LANG[ 'forgot' ][ 'antispam' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'antispam' ]);

			

		if( empty($errors) )

		{

			$tpl["{source}"] = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ];

			$tpl["{username}"] = $l->getNickByMail($inputs['01']);

			$tpl["{email}"] = $inputs['01'];

			$tpl["{file}"] = "forgot";

			$tpl["{key}"] = md5(uniqid());

			$tpl["{time}"] = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'hash-key' ];

			$tpl["{date_in}"] = date(_SQLDate_);

			$tpl["{date_off}"] = date(_SQLDate_, ( time() + 60*$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'hash-key' ] ) );

			

			$file = file_get_contents('files/lang/mails/password_'. $_GLOBALS[ 'lang' ] .'.html');

			$content = str_replace(array_keys($tpl), array_values($tpl), $file);

			

			$delete = $db->prepare('DELETE FROM `keys` WHERE `action` = "password" AND `id` = :id');

			$delete->bindValue(':id', $l->getIdByLogin( $tpl["{username}"] ), PDO::PARAM_STR);

			$delete->execute();



			$sql = $db->prepare('INSERT INTO `keys` VALUES(NULL, :uid, :key, "password", :date, :ip)');

			$sql->bindValue(':uid', $l->getIdByLogin( $tpl["{username}"] ), PDO::PARAM_STR);

			$sql->bindValue(':key', $tpl["{key}"], PDO::PARAM_STR);

			$sql->bindValue(':date', $tpl["{date_in}"], PDO::PARAM_STR);

			$sql->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

			$sql->execute();

			

			$mail = @sendMail($inputs['01'], $_LANG[ 'mails' ][ 'password' ], $content);

			

			$sent = true;

		}

		

	}

	else

	{

		$inputs['01'] = NULL;

	}

	

	// If $sent (mail) is true

	if($sent == true)

	{

		if( $mail == true )

		{

			echo( $_LANG[ 'forgot' ][ 'check_mail' ] );

		}

		else

		{

			echo( $_LANG[ 'forgot' ][ 'cannot_send_mail' ] );

		}

	}

	else

	{

		echo('<script type="text/javascript">

		 var RecaptchaOptions = {

			theme : \''.($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'theme' ]).'\',

			lang : \''.($_GLOBALS[ 'lang' ]).'\'

		 };

		</script>');

		

		echo('<form action="forgot" method="post">

		<fieldset id="register">');

		

		if( !empty( $_LANG[ 'forgot' ][ 'info' ] ) )

		{

			echo('<p>'. sprintf($_LANG[ 'forgot' ][ 'info' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'hash-key' ]) .'</p>');

		}

		

		if( !empty($errors) )

		{

			echo('<p class="hn">'.$_LANG['forgot'][ 'errors' ].'</p>');

			echo('<ul class="errors tekst">');

			foreach( $errors as $error )

			{

				echo('<li>'. $error. '</li>');

			}

			echo('</ul>');

		}

		

		echo('<dl>');

		echo('<dt><label for="forgot_01">'. $_LANG[ 'labels' ][ 'mail' ]. '</label></dt>');

		echo('<dd><input type="email" name="forgot_01" id="forgot_01" value="'. $inputs['01'] .'" class="mail" required></dd>');

		echo('<dd>'. recaptcha_get_html($publickey) .'</dd>');

		echo('<dd><input type="submit" name="lab_forgot" value="'. $_LANG[ 'labels' ][ 'forgot' ]. '" class="btn btn-primary"></dd>');

		echo('</dl>

		</fieldset>

		</form><br>');

	}

}



?>
</p></div>