<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

?>

<div id="mid_top"></div>

<div id="mid_cont"><div class="left">

<?php


if( !empty($_PAGES[ 'type' ]) && !empty($_PAGES[ 'more' ]) )

{

    $change = false;

	$errors = array();

	$delete = false;

	$l = new Login();

	$R = new Register();

	

	$sql = $db->prepare('SELECT *, TIMESTAMPDIFF(DAY, `date`, NOW()) AS `diff` FROM `keys` WHERE `userId` = :uid AND `key` = :key AND `action` = "email" LIMIT 1');

	$sql->bindValue(':uid', $l->getIdByLogin( $_PAGES[ 'more' ] ), PDO::PARAM_STR);

	$sql->bindValue(':key', $_PAGES[ 'type' ], PDO::PARAM_STR);

	$sql->execute();

	$result = $sql->fetch();

	

	if($result === false)

	{

		echo($_LANG[ 'errors' ][ 'hack' ]);

		return false;

	}

	

	$mail = $db->prepare('SELECT `new-email` FROM `users` WHERE `id` = :id LIMIT 1');

	$mail->bindValue(':id', $result['userId'], PDO::PARAM_STR);

	$mail->execute();

	$return = $mail->fetch();

	

	if($R->issetEMail($return['new-email']))

	{

		echo('<p>'. $_LANG[ 'active' ][ 'busy_mail' ] .'</p>');

		$delete = true;	

	}

	

	if( $result['diff'] > $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'mail-days' ])

	{

		echo('<p>'. $_LANG[ 'active' ][ 'link_noActive' ] .'</p>');

		$delete = true;	

	}

	

	if( $delete == false )

	{

		$change = true;

	}

	

	$delete = $db->prepare('DELETE FROM `keys` WHERE `id` = :id LIMIT 1');

	$delete->bindValue(':id', $result['id'], PDO::PARAM_STR);

	$delete->execute();

	

	if( $change == true )

	{

		$change = $db->prepare('UPDATE `users` SET `email` = `new-email`, `new-email` = "" WHERE `id` = :uid LIMIT 1');

		$change->bindValue(':uid', $result['userId'], PDO::PARAM_STR);

		$change->execute();

		echo('<p>'. $_LANG[ 'active' ][ 'changed' ] .'</p>');

	}

	

}

else

{

	@header('Location: index.php');

	return false;

}



?>