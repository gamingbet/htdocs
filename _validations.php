<?php
define('__LOAD__', true);

if( !empty( $_GET[ 'type' ] ) && !empty( $_GET[ 'value'] ) )
{
	require_once('_functions.php');
	$db = DB::getConnect();
}
else
{
	exit();
}

if( $_GET[ 'type' ] == 'nick' )
{
	$sql = $db->prepare('SELECT COUNT(`id`) FROM `users` WHERE `nick` = :nick');
	$sql->bindValue(':nick', $_GET['value'], PDO::PARAM_STR);
	$sql->execute();
	$nick = $sql->fetch();
	if( strlen($_GET['value']) > 0 )
	{
		if( $nick[0] == 1)
		{
			echo(1);
		}
		else
		{
			echo(0);
		}
	}
	else
	{
		echo(1);
	}
}
else if( $_GET[ 'type' ] == 'mail' )
{
	$sql = $db->prepare('SELECT COUNT(`id`) FROM `users` WHERE `email` = :mail');
	$sql->bindValue(':mail', $_GET['value'], PDO::PARAM_STR);
	$sql->execute();
	$mail = $sql->fetch();
	if( strlen($_GET['value']) > 0 )
	{
		if( $mail[0] == 1 )
		{
			echo(1);
		}
		else
		{
			echo(0);
		}
	}
	else
	{
		echo(1);
	}
}

?>