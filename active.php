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

if( $_GLOBALS[ 'login' ][ 'login' ] == true )

{

	echo( $_LANG[ 'active' ][ 'active-too' ] );

	return false;

}



if( !empty($_PAGES[ 'type' ]) && !empty($_PAGES[ 'more' ]) )

{

    $active = false;

	$errors = array();

	$l = new Login();

	

	$sql = $db->prepare('SELECT * FROM `keys` WHERE `userId` = :uid AND `key` = :key AND `action` = "account" LIMIT 1');

	$sql->bindValue(':uid', $l->getIdByLogin( $_PAGES[ 'more' ] ), PDO::PARAM_STR);

	$sql->bindValue(':key', $_PAGES[ 'type' ], PDO::PARAM_STR);

	$sql->execute();

	$result = $sql->fetch();

	

	if($result === false)

	{

		echo($_LANG[ 'errors' ][ 'hack' ]);

		return false;

	}

	

	$sql = $db->prepare('UPDATE `users` SET `active` = "true" WHERE `nick` = :nick LIMIT 1');

	$sql->bindValue(':nick', $_PAGES[ 'more' ], PDO::PARAM_STR);

	$sql->execute();

			

	$sql = $db->prepare('DELETE FROM `keys` WHERE `id` = :id');

	$sql->bindValue(':id', $result["id"], PDO::PARAM_STR);

	$sql->execute();

	

	if( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-active' ] == "true" )

	{

		$sql = $db->prepare('SELECT `refId` FROM `users` WHERE `nick` = :nick LIMIT 1');

		$sql->bindValue(':nick', $_PAGES[ 'more' ], PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		

		if( $result[ 'refId' ] != "0" )

		{

			$sql = $db->prepare('UPDATE `users` SET `credits` = `credits` + :credits, `refCount` = `refCount` + 1 WHERE `id` = :id LIMIT 1');

			$sql->bindValue(':credits', $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'ref-bonus' ], PDO::PARAM_STR);

			$sql->bindValue(':id', $result[ 'refId' ], PDO::PARAM_STR);

			$sql->execute();

		}

	}		

	

	$active = true;

    

    if($active == true)

    {

        echo( $_LANG[ 'active' ][ 'active' ] );

    }

    else

    {

        echo( $_LANG[ 'active' ][ 'noActive' ] );

    }

}

else

{

    echo( $_LANG[ 'active' ][ 'not-keys' ] );

}



?>