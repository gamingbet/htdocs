<?php
if( !defined("__LOAD__") ){
	exit();
	return false;
}
?>
<div class="druzyna-txt">
  <h4><?php echo $_LANG['labels']['edit-profile']; ?></h4> 
<script type="text/javascript">
 var RecaptchaOptions = {
    theme : '<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'theme' ]);?>',
	lang : '<?php echo($_GLOBALS[ 'lang' ]) ?>'
 };
</script>



<?php
if( $_GLOBALS[ 'login' ][ 'login' ] == false ){
	echo( $_LANG[ 'auth' ][ 'need_login' ] );
	return false;
}
require_once( '_class/recaptchalib.php' );
$publickey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'publickey' ];
$change = false;
$errors = array();
$l = new Login;
if( isset( $_POST[ 'edit_sub' ] ) && $_POST[ 'edit_sub' ] == $_LANG[ 'labels' ][ 'send-form' ] ){
	$privatekey = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'privatekey' ];
	$resp = recaptcha_check_answer($privatekey,  $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	$delete_avatar = false;
	$pass_change = false;
	$mail_change = false;	
	$upload = false;
	// primary	
	$inputs['firstname'] = ( trim ( htmlspecialchars( $_POST[ 'firstname' ] ) ) );
	$inputs['surname'] = ( trim ( htmlspecialchars( $_POST[ 'surname' ] ) ) );
	$inputs['age'] = ( trim ( htmlspecialchars( $_POST[ 'age' ] ) ) );
	$inputs['country'] = (int) ( trim ( htmlspecialchars( $_POST[ 'country' ] ) ) );
	$inputs['city'] = ( trim ( htmlspecialchars( $_POST[ 'register_10' ] ) ) );
	$inputs['code'] = ( trim ( htmlspecialchars( $_POST[ 'register_11' ] ) ) );
	$inputs['street'] = ( trim ( htmlspecialchars( $_POST[ 'register_12' ] ) ) );
	$inputs['newsletter'] = (isset($_POST['register_08']) && $_POST['register_08'] == "true") ? 'true' : 'false'; 
	$inputs['new_mail'] = (isset($_POST['mail_change']) && $_POST['mail_change'] == "true") ? ( trim ( htmlspecialchars( $_POST[ 'register_04' ] ) ) ) : '';
	// new mail
	if(isset($_POST['mail_change']) && $_POST['mail_change'] == "true"){
		$mail_change = true;
		$inputs['new_mail'] = ( trim ( htmlspecialchars( $_POST[ 'register_04' ] ) ) );
		$inputs['old_mail'] = ( trim ( htmlspecialchars( $_POST[ 'old_mail' ] ) ) );
	}
	// new password
	if(isset($_POST['pass_change']) && $_POST['pass_change'] == "true"){
		$pass_change = true;
		$inputs['new_pw'] = ( trim ( htmlspecialchars( $_POST[ 'register_02' ] ) ) );
		$inputs['new_repw'] = ( trim ( htmlspecialchars( $_POST[ 'register_03' ] ) ) );
	}
	// if isset avatar check delete current 
	if( $_USER[ 'avatar' ] != "none.jpg" ){
		if(isset($_POST['register_14']) && $_POST['register_14'] == "true"){
			$delete_avatar = true;
		}
	}
	if( $pass_change == true ){		
		// If nickname is the same like password
		if($_USER['nick'] == $inputs['new_pw'])
			$errors[] = $_LANG[ 'register' ][ 'same_nickPass' ];
		// If length password is less than 6
		if(strlen($inputs['new_pw']) < 6)
			$errors[] = $_LANG[ 'register' ][ 'too_short_pw' ];
		// If password and re-password aren't the same
		if($inputs['new_pw'] !== $inputs['new_repw'] || empty($inputs['new_repw']))
			$errors[] = $_LANG[ 'register' ][ 'not_same_pw' ];			
	}
	if( $mail_change == true && $inputs['new_mail'] != $_USER['email']){
		// If e-mail adress isn't valid
		if(!preg_match('/^[a-zA-Z0-9\.\-\_\+]+\@[a-zA-Z0-9\.\-\_]+\.[a-z]{2,}$/D', $inputs['new_mail']))
			$errors[] = $_LANG[ 'register' ][ 'mail_not_valid' ];
		$R = new Register;
		// If E-mail adress is busy
		if($R->issetEMail($inputs['new_mail']))
			$errors[] = $_LANG[ 'register' ][ 'busy_mail' ];
	}
	// data validation
	$temp_date = explode('-', $inputs['age']);
	if( $temp_date[0] < 1900 )
		$errors[] = $_LANG[ 'register' ][ 'too_old' ];
	if( (date('Y')- $temp_date[0]) < 18 )
		$errors[] = $_LANG[ 'register' ][ 'too_young' ];
	if( !checkdate( (int)$temp_date[1], (int)$temp_date[2], (int)$temp_date[0] ) )
		$errors[] = $_LANG[ 'register' ][ 'wrong_date' ];
	// If wrong catpcha
	if (!$resp->is_valid)
		$errors[] = $_LANG[ 'register' ][ 'wrong_catpcha' ];
	// primary
	if(strlen($inputs['firstname']) < 3)
		$errors[] = $_LANG[ 'register' ][ 'too_short_firstname' ];
	if(strlen($inputs['surname']) < 3)
		$errors[] = $_LANG[ 'register' ][ 'too_short_surname' ];
	if(strlen($inputs['city']) < 3)
		$errors[] = $_LANG[ 'register' ][ 'too_short_city' ];
	if(strlen($inputs['street']) < 3)
		$errors[] = $_LANG[ 'register' ][ 'too_short_street' ];
	if(strlen($inputs['code']) < 3)
		$errors[] = $_LANG[ 'register' ][ 'wrong_code' ];
	if( $_FILES['file']['error'] == 0 && $delete_avatar == false ){
		$access = array('image/gif', 'image/png', 'image/jpeg');
		list($width, $height, $type, $attr) = getimagesize($_FILES['file']['tmp_name']);
		if($width > $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ] || $height > $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ])
			$errors[] = sprintf( $_LANG[ 'register' ][ 'max_size' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ]);
		if(!in_array($_FILES['file']['type'], $access))
			$errors[] = $_LANG[ 'register' ][ 'access_format' ];
		if($_FILES['file']['size'] > $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar-size' ])
			$errors[] = sprintf( $_LANG[ 'register' ][ 'max_weight' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar-size' ]);
		$upload = true;
	}
	if( empty( $errors ) ){
		$str_mail = NULL;
		$str_pass = NULL;
		$str_avatar = NULL;
		if( $mail_change == true ){
			$str_mail = ' `new-email` = "'.$inputs['new_mail'].'",';
		}
		else{
			$str_mail = ' `new-email` = "",';
		}
		if( $pass_change == true ){
			$salt = $l->getSalt($_USER['nick']);
			$pass = $salt.sha1($inputs['new_pw']);
			$str_pass = ' `password` = "'.$pass.'",';
		}
		if($upload == true && $delete_avatar == false){
			if($_FILES['file']['type'] == 'image/gif'){
				$ext = '.gif';
			}
			else if($_FILES['file']['type'] == 'image/png'){
				$ext = '.png';
			}
			else{
				$ext = '.jpg';
			}
			$avatar = md5($_USER['nick']).$ext;
			move_uploaded_file($_FILES['file']['tmp_name'], 'files/images/avatars/' . $avatar);
			$str_avatar = ' `avatar` = "'.$avatar.'",';
		}
		if( $delete_avatar == true ){
			unlink('files/images/avatars/'.$_USER['avatar']);
			$str_avatar = ' `avatar` = "none.jpg",';
		}
		$edit = $db->prepare('UPDATE `users` SET '.$str_mail . $str_pass . $str_avatar .' `firstName` = :firstName, `surname` = :surname,
		`age` = :age, `street` = :street, `city` = :city, `code` = :code, `countryId` = :countryId, `newsletter` = :newsletter WHERE `nick` = :nick LIMIT 1');
		$edit->bindValue(':firstName', $inputs['firstname'], PDO::PARAM_STR);
		$edit->bindValue(':surname', $inputs['surname'], PDO::PARAM_STR);
		$edit->bindValue(':age', $inputs['age'], PDO::PARAM_STR);
		$edit->bindValue(':street', $inputs['street'], PDO::PARAM_STR);
		$edit->bindValue(':city', $inputs['city'], PDO::PARAM_STR);
		$edit->bindValue(':code', $inputs['code'], PDO::PARAM_STR);
		$edit->bindValue(':countryId', $inputs['country'], PDO::PARAM_INT);
		$edit->bindValue(':newsletter', $inputs['newsletter'], PDO::PARAM_STR);
		$edit->bindValue(':nick', $_USER['nick'], PDO::PARAM_STR);
		if( $edit->execute() ){
			$change = true;
		}
		if( $mail_change == true ){
			$tpl["{source}"] = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ];
			$tpl["{username}"] = $_USER['nick'];
			$tpl["{email}"] = $inputs['new_mail'];
			$tpl["{file}"] = "email";
			$tpl["{key}"] = md5(uniqid());
			$tpl["{time}"] = $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'mail-days' ];
			$tpl["{date_in}"] = date(_SQLDate_);
			$tpl["{date_off}"] = date(_SQLDate_, ( time() + 60*60*24*$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'mail-days' ] ) );
			$file = file_get_contents('files/lang/mails/email_'. $_GLOBALS[ 'lang' ] .'.html');
			$content = str_replace(array_keys($tpl), array_values($tpl), $file);
			$delete = $db->prepare('DELETE FROM `keys` WHERE `action` = "email" AND `id` = :id');
			$delete->bindValue(':id', $l->getIdByLogin( $tpl["{username}"] ), PDO::PARAM_STR);
			$delete->execute();
			$sql = $db->prepare('INSERT INTO `keys` VALUES(NULL, :uid, :key, "email", :date, :ip)');
			$sql->bindValue(':uid', $l->getIdByLogin( $tpl["{username}"] ), PDO::PARAM_STR);
			$sql->bindValue(':key', $tpl["{key}"], PDO::PARAM_STR);
			$sql->bindValue(':date', $tpl["{date_in}"], PDO::PARAM_STR);
			$sql->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
			$sql->execute();
			$mail = @sendMail($inputs['new_mail'], $_LANG[ 'mails' ][ 'email' ], $content);
		}
	}

}

else{
	$inputs['firstname'] = $_USER['firstName'];
	$inputs['surname'] = $_USER['surname'];
	$inputs['age'] = $_USER['age'];
	$inputs['country'] = $_USER['countryId'];
	$inputs['city'] = $_USER['city'];
	$inputs['code'] = $_USER['code'];
	$inputs['street'] = $_USER['street'];	
	$inputs['newsletter'] = $_USER['newsletter'];
	$inputs['new_mail'] = NULL;
}
if($change == true){
	echo('<p class="success">'.$_LANG[ 'edit-profile' ][ 'changed' ].'</p>');
	if( $mail_change == true ){
		echo('<p>'.($mail) ? $_LANG[ 'edit-profile' ][ 'changed-mail' ] : $_LANG[ 'edit-profile' ][ 'changed-Nomail' ].'</p>');
	}
}
else{
	if( isset($firstLogin) && $firstLogin == true ){
		echo('<p class="info">'. $_LANG[ 'edit-profile' ][ 'firstLogin' ] .'</p>');
	}
	else{
		echo('<p class="info">'. $_LANG[ 'edit-profile' ][ 'changeAcc' ] .'</p>');
	}
	echo('<form action="edit-profile" method="post" enctype="multipart/form-data">
	<fieldset id="register">');
	if( !empty($errors) ){
		echo('<div class="bledy"><b>'.$_LANG['edit-profile'][ 'errors' ].'</b><br><ul class="list-unstyled" >');
		foreach( $errors as $error )
		{
			echo('<li>'. $error. '</li>');
		}
		echo('</ul></div>');

	}
	//-------------------------------------------------------------------------------------------------------------------------------------------
	echo('<div class="historia"><h5>'. $_LANG[ 'labels' ][ '1st' ]. '</h5>');
	// Nick
	//echo('<div class="calosc">'. $_LANG[ 'labels' ][ 'nick' ]. '');
	//echo('<input type="text" name="nick" value="'. $_USER[ 'nick' ] .'" class="empty" disabled></div>');
	// First name
	echo('<div class="polowa"><label for="register_05">'. $_LANG[ 'labels' ][ 'firstname' ]. '</label> ');
	echo('<input type="text" id="register_05" value="'. $inputs['firstname'] .'" name="firstname" class="empty" required></div>');
	// Surname
	echo('<div class="polowa"><label for="register_06">'. $_LANG[ 'labels' ][ 'surname' ]. '</label> ');
	echo('<input type="text" id="register_06" value="'. $inputs['surname'] .'" name="surname" class="empty" required></div>');
	// Age
	echo('<div class="calosc"><label for="register_07">'. $_LANG[ 'labels' ][ 'date' ]. '</label> ');
	echo('<input type="date" id="register_07" value="'. $inputs['age'] .'" name="age" class="empty" required></div>');
	// County
	echo('<div class="calosc"><label for="register_09">'. $_LANG[ 'labels' ][ 'country' ]. '</label> ');
	echo('<select name="country" class="empty" id="register_09">');
	$sql = $db->query('SELECT * FROM `countries` ORDER BY `id` ASC');
	while( $country = $sql->fetch() ){
		echo('<option value="'. $country['id'] .'"');
		if($country['id'] == $inputs['country']){
			echo(' selected');
		}
		echo('>'. $country[ 'name-' . $_GLOBALS[ 'lang' ] ] .'</option>');
	}
	echo('</select></div>');
	// City + code
	echo('<div class="calosc"><label for="register_10">'. $_LANG[ 'labels' ][ 'city-code' ]. '</label><br>');
	echo('<input type="text" id="register_10" name="register_10" value="'. $inputs['city'] .'" class="empty city" required> <input type="text" name="register_11" value="'. $inputs['code'] .'" class="empty code" required></div>');
	// Street
	echo('<div class="calosc"><label for="register_12">'. $_LANG[ 'labels' ][ 'street' ]. '</label>');
	echo('<input type="text" id="register_12" name="register_12" value="'. $inputs['street'] .'" class="empty" required></div>');
	// Mail change
	echo('<div class="calosc"><input type="checkbox" id="mail_01" name="mail_change"  value="true"');
	if( isset($_POST['mail_change']) && $_POST['mail_change'] == "true" ){
		echo(' checked');
	}
	echo('> <label for="mail_01">'. $_LANG[ 'labels' ][ 'mail-change' ]. '</label>');
	echo('<br><label for="register_04">'. $_LANG[ 'labels' ][ 'new-mail' ]. '</label>');
	echo('<input type="text" value="'.$inputs['new_mail'].'" class="empty" id="register_04" name="register_04"><div class="status">&nbsp;</div>
		<input type="hidden" name="old_mail" value="'. $_USER[ 'email' ] .'"></div>'); 
	// Password change
	echo('<div class="calosc"><input type="checkbox" id="pass_01" name="pass_change" value="true"');
	if( isset($_POST['pass_change']) && $_POST['pass_change'] == "true" ){
		echo(' checked');
	}
	echo('> <label for="pass_01">'. $_LANG[ 'labels' ][ 'pass-change' ]. '</label><br>');
	echo('<label for="register_02">'. $_LANG[ 'labels' ][ 'new-pw' ]. '</label>');
	echo('<input type="password" value="" class="empty" id="register_02" name="register_02">');
	echo('<label for="register_03">'. $_LANG[ 'labels' ][ 'new-repw' ]. '</label>');
	echo('<input type="password" value="" class="empty" id="register_03" name="register_03"></div>');
	//-------------------------------------------------------------------------------------------------------------------------------------------

	echo('</div><div class="historia"><h5>'. $_LANG[ 'labels' ][ '2nd' ]. '</h5>');
	// Newsletter
	echo('<div class="calosc"><input type="checkbox" name="register_08" value="true" id="register_08"');
	if( isset($inputs['newsletter']) && $inputs['newsletter'] == "true" ){
		echo(' checked');
	}
	echo('> <label for="register_08">'. $_LANG[ 'labels' ][ 'newsletter' ]. '</label></div>');
	// avatar
	echo('<div class="calosc"><label for="register_13">'. $_LANG[ 'labels' ][ 'avatar' ]. '</label><br>');
	echo('<input type="file" id="register_13" class="nick" name="file" accept="image/jpeg,image/gif,image/png"><br>');
	if( $_USER[ 'avatar' ] != "none.jpg" ){
		echo('<input type="checkbox" id="register_14" name="register_14" value="true">  <label for="register_14">'. $_LANG[ 'labels' ][ 'del-avatar' ]. '</label>');
		echo('<br><img src="files/images/avatars/'.$_USER['avatar'].'" alt="avatar">');
	}
	if( !empty( $_LANG[ 'edit-profile' ][ 'info' ] ) ){
		echo('<br>'. sprintf($_LANG[ 'edit-profile' ][ 'avatar-info' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'avatar' ])  .'');
	}
	//-------------------------------------------------------------------------------------------------------------------------------------------
	echo('</div><hr><h5>'. $_LANG[ 'labels' ][ '3rd' ]. '</h5><div class="calosc">');
	// Ref By
	if( $_USER['refId'] != '0' ){
		echo(''. $_LANG[ 'labels' ][ 'refBy' ]. '');
		echo('<input type="text" value="'. strtolower($l->getLoginById($_USER[ 'refId' ])) .'" class="empty" disabled><br>');
	}
	// Ref Our
	echo(''. $_LANG[ 'labels' ][ 'refOur' ]. '<br>');
	if( !empty( $_LANG[ 'edit-profile' ][ 'ref-info' ] ) ){
		echo(''. sprintf($_LANG[ 'edit-profile' ][ 'ref-info' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-bonus' ]) .'<br>');
	}
	echo('<input type="text" value="'. $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ]. '/register/'.strtolower($_USER['nick']). '" id="ref-nick" class="empty" readonly>');
	// Ref Count	
	if( $_USER['refCount'] != '0' ){
		echo(''. $_LANG[ 'labels' ][ 'refCount' ]. '<br>');
		echo('<input type="text" value="'. $_USER['refCount']. '" class="empty" readonly>');	
	}
	echo '</div>';
	// Catpcha
	echo('</div>
	<div class="wyslij-form">'. recaptcha_get_html($publickey) .'');
	// Send
	echo('<input type="submit" name="edit_sub" value="'. $_LANG[ 'labels' ][ 'send-form' ]. '" class="przycisk-login">');
	echo('</div>');
	echo('</fieldset>

	</form>');

	if( !empty( $_LANG[ 'edit-profile' ][ 'info' ] ) ){
		echo('<p>'. $_LANG[ 'edit-profile' ][ 'info' ] .'</p>');
	}

}



?>
</div>