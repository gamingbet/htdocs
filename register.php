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
<div class="well-sm biale">
<h1>Rejestracja</h1>

<script type="text/javascript">

 var RecaptchaOptions = {

    theme : '<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'theme' ]);?>',

	lang : '<?php echo($_GLOBALS[ 'lang' ]) ?>'

 };

</script>

<?php

require_once( '_class/recaptchalib.php' );

$publickey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'publickey' ];



if( $_GLOBALS[ 'login' ][ 'login' ] == true )

{

	echo( $_LANG[ 'register' ][ 'register-too' ] );

	return false;

}



$register = false;



if( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'register' ][ 'register' ] == 'true' )

{

	$errors = array();

	$R = new Register;

	

	if( isset( $_POST[ 'reg_submit' ] ) && $_POST[ 'reg_submit' ] == $_LANG[ 'labels' ][ 'register' ] )

	{

		$inputs['01'] = trim( ( htmlspecialchars( $_POST[ 'register_01' ] ) ) );

		$inputs['02'] = trim( ( htmlspecialchars( $_POST[ 'register_02' ] ) ) );

		$inputs['03'] = trim( ( htmlspecialchars( $_POST[ 'register_03' ] ) ) );

		$inputs['04'] = trim( ( htmlspecialchars( $_POST[ 'register_04' ] ) ) );

		$inputs['05'] = trim( ( htmlspecialchars( $_POST[ 'register_08' ] ) ) );

		

		$privatekey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'privatekey' ];

		$resp = recaptcha_check_answer($privatekey,  $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		

		// If empty nick name

		if(empty($inputs['01']))

			$errors[] = $_LANG[ 'register' ][ 'empty_nick' ];

			

		// If nick name doens't valid (a-z 0-9 _-)

		if(!preg_match("#^[a-zA-Z0-9_]+$#", $inputs['01']))

			$errors[] = $_LANG[ 'register' ][ 'wrong_nick' ];

		

		// If nickname is the same like password

		if($inputs['01'] == $inputs['02'])

			$errors[] = $_LANG[ 'register' ][ 'same_nickPass' ];

		

		// If length password is less than 6

		if(strlen($inputs['02']) < 6)

			$errors[] = $_LANG[ 'register' ][ 'too_short_pw' ];

		

		// If password and re-password aren't the same

		if($inputs['02'] !== $inputs['03'] || empty($inputs['02']))

			$errors[] = $_LANG[ 'register' ][ 'not_same_pw' ];

			

		// If e-mail adress isn't valid

		if(!preg_match('/^[a-zA-Z0-9\.\-\_\+]+\@[a-zA-Z0-9\.\-\_]+\.[a-z]{2,}$/D', $inputs['04']))

			$errors[] = $_LANG[ 'register' ][ 'mail_not_valid' ];

			

		// If user has less than 18th years old

		if(!isset($_POST['register_05']) &&  $_POST['register_05'] != "true")

			$errors[] = $_LANG[ 'register' ][ 'need_18old' ];

			

		// If not accept rules

		if(!isset($_POST['register_06']) &&  $_POST['register_06'] != "true")

			$errors[] = $_LANG[ 'register' ][ 'rules_accept' ];

		

		// If E-mail adress is busy

		if($R->issetEMail($inputs['04']))

			$errors[] = $_LANG[ 'register' ][ 'busy_mail' ];

		

		// If nickname is busy

		if($R->issetAccount($inputs['01']))

			$errors[] = $_LANG[ 'register' ][ 'busy_nick' ];

			

		// If wrong catpcha

		if (!$resp->is_valid)

			$errors[] = $_LANG[ 'register' ][ 'wrong_catpcha' ];

		

		// If errors list is empty 

		if( empty( $errors ) ) 

		{

			$time = date( _SQLDate_ );

			$salt = substr( md5( $time ), 0, 10 );

			$active = ( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'register' ][ 'active-account' ] == "true" ) ? "false" : "true";

			$newsletter = ( isset($_POST['register_07']) &&  $_POST['register_07'] == "true") ? "true" : "false";

			$credits = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'bets' ][ 'start-credits' ];

			$password = $salt.sha1($inputs['02']);

			

			if($R->issetAccount($inputs['05']) && $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-active' ] == "true")

			{

				$l = new Login;

				$ref = $l->getIdByLogin( $inputs['05'] );

			}

			else

			{

				$ref = "0";

			}

			if( $R->createAccount($inputs['01'], $password, $inputs['04'], $active, $_GLOBALS[ 'lang' ], $credits, $time, $newsletter, $_SERVER['REMOTE_ADDR'], $ref) )

			{

				$register = true;

				if($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'register' ][ 'active-account' ] == "true")

				{

					$tpl["{source}"] = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ];

					$tpl["{username}"] = $inputs['01'];

					$tpl["{file}"] = "active";

					$tpl["{key}"] = md5(uniqid());

					

					$file = file_get_contents('files/lang/mails/account_'. $_GLOBALS[ 'lang' ] .'.html');

					$content = str_replace(array_keys($tpl), array_values($tpl), $file);

					

					$l = new Login;		

					$sql = $db->prepare('INSERT INTO `keys` VALUES(NULL, :uid, :key, "account", NOW(), :ip)');

					$sql->bindValue(':uid', $l->getIdByLogin( $tpl["{username}"] ), PDO::PARAM_STR);

					$sql->bindValue(':key', $tpl["{key}"], PDO::PARAM_STR);

					$sql->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

					$sql->execute();

					

					$mail = @sendMail($inputs['04'], $_LANG[ 'mails' ][ 'activate' ], $content);					

				}

				

				if( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-active' ] == "true" && $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'register' ][ 'active-account' ] == "false" )

				{

					if( $ref != "0" )

					{

						$sql = $db->prepare('UPDATE `users` SET `credits` = `credits` + :credits, `refCount` = `refCount` + 1 WHERE `id` = :id LIMIT 1');

						$sql->bindValue(':credits', $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-bonus' ], PDO::PARAM_STR);

						$sql->bindValue(':id', $ref, PDO::PARAM_STR);

						$sql->execute();

					}

				}

			}

		}

	}

	else

	{

		$inputs['01'] = NULL;

		$inputs['02'] = NULL;

		$inputs['03'] = NULL;

		$inputs['04'] = NULL;

		$inputs['05'] = (@isset($_PAGES[ 'type' ])) ? trim(htmlspecialchars(($_PAGES[ 'type' ]))) : '';

	}

	if( $register == true )

	{	

		if( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'register' ][ 'active-account' ] == "true" )

		{

			if( $mail == true )

			{

				echo( $_LANG[ 'register' ][ 'check_mail' ] );

			}

			else

			{

				echo( $_LANG[ 'register' ][ 'cannot_send_mail' ] );

			}

		}

		else

		{

			echo( $_LANG[ 'register' ][ 'register_done' ] );

		}

		

	}

	else

	{

		echo('<form action="register" method="post">

		<fieldset id="register">');

		

		if( !empty( $_LANG[ 'register' ][ 'info' ] ) )

		{

			echo('<p>'. $_LANG[ 'register' ][ 'info' ] .'</p>');

		}

		

		if( !empty($errors) )

		{

			echo('<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>'.$_LANG['register'][ 'errors' ].'</b><br><ul class="list-unstyled" >');
			foreach( $errors as $error )

			{

				echo('<li>'. $error. '</li>');

			}
			echo('</ul></div>');

		}

		

		echo('<dl>');

		echo('<dt><label for="register_01">'. $_LANG[ 'labels' ][ 'nick' ]. '</label></dt>');

		echo('<dd><input type="text" name="register_01" id="register_01" value="'. $inputs['01'] .'" class="nick" required><div class="status">&nbsp;</div></dd>');

		echo('<dt><label for="register_02">'. $_LANG[ 'labels' ][ 'pw' ]. '</label></dt>');

		echo('<dd><input type="password" name="register_02" id="register_02" class="pass1" required><div class="status">&nbsp;</div><br>

		<div id="passwordStrengthDiv" class="is0"></div></dd>');

		echo('<dt><label for="register_03">'. $_LANG[ 'labels' ][ 'repw' ]. '</label></dt>');

		echo('<dd><input type="password" name="register_03" id="register_03"class="pass1" required><div class="status">&nbsp;</div></dd>');

		echo('<dt><label for="register_04">'. $_LANG[ 'labels' ][ 'mail' ]. '</label></dt>');

		echo('<dd><input type="email" name="register_04" id="register_04" value="'. $inputs['04'] .'" class="mail" required><div class="status">&nbsp;</div></dd>');

		echo('<dd>'. recaptcha_get_html($publickey) .'</dd>');

		echo('<dd><input type="checkbox" name="register_05" value="true" id="register_05" required>

			<label for="register_05">'. $_LANG[ 'labels' ][ '18years' ]. '</label></dd>');

		echo('<dd><input type="checkbox" name="register_06" value="true" id="register_06" required>

			<label for="register_06">'. $_LANG[ 'labels' ][ 'rules' ]. ' <a href="' .$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'rules' ] .'">'. $_LANG[ 'labels' ][ 'show-rules' ]. '</a></label></dd>');

		echo('<dd><input type="checkbox" name="register_07" value="true" id="register_07"');

		if( isset($_POST['register_07']) &&  $_POST['register_07'] == "true" )

		{

			echo(' checked');

		}

		echo('> <label for="register_07">'. $_LANG[ 'labels' ][ 'newsletter' ]. '</label></dd>');

		echo('<dd><input type="submit" name="reg_submit" value="'. $_LANG[ 'labels' ][ 'register' ]. '" class="btn btn-success"></dd>');

		echo('<input type="hidden" name="register_08" value="'.$inputs['05'].'">');

		echo('</dl>');

		echo('</fieldset>

		</form><br>');

	}

}

else

{

	echo( $_LANG[ 'register' ][ 'no-register' ] );

}



?>

</div>