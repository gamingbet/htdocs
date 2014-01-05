<?php

if( !defined("__LOAD__") )
{
	exit();
	return false;
}

/**
 * __autoload classes
 */
function __autoload($class) {
    if(file_exists('_class/'.$class.'.class.php'))
    {
        return(require_once('_class/'.$class.'.class.php'));
    }
    else if(file_exists('../_class/'.$class.'.class.php'))
    {
        return(require_once('../_class/'.$class.'.class.php'));
    }
}

/**
 * Return microtime loads page
 */
function getMicrotime()

{

	list($usec, $sec) = explode(' ', microtime());

	return ((float)$usec + (float)$sec);

}





/**

 * Return div-string with class t1 or t2 - background for bets on starting page tables

 */

 

function getBackgroundClass($bets1, $bets2, $lp, $class1 = 't1', $class2 = 't2', $class3 = 't3')

{

	if($lp == 1)

	{

		if( $bets1 > $bets2 )

		{

			return $class1;

		}

		else if( $bets1 < $bets2 )

		{

			return $class2;

		}

		{

			return $class3;

		}

	}

	else if($lp == 2)

	{

		if( $bets1 < $bets2 )

		{

			return $class1;

		}

		else if( $bets1 > $bets2 )

		{

			return $class2;

		}

		{

			return $class3;

		}

	}

	else

	{

		return $class3;

	}

}



/**

 * Function return (double) number from (int) number

 */

function getScore( $input )

{

	if( strpos( $input, "." ) )

	{

		list($real, $mod) = explode(".", $input);

		if( strlen( $mod ) == 1 )

		{

			$mod .= "0";

		}

		return $real . "." . $mod;

	}

	else

	{

		return $input. ".00";

	}

}



/**

 * Function send mail with custom headers

 */

function sendMail($to, $topic, $content)

{

	$headers = 'MIME-Version: 1.0'. "\n";

	$headers .= 'Content-Type: text/html; charset=utf-8'."\n";

	$headers .= 'From: no-reply@gamingbit.eu'."\n";

	

	return( mail($to, $topic, $content, $headers) );

}



function getBet($id)

{

	global $db;

	$bet = $db->prepare('SELECT * FROM `bets` WHERE `id` = :id LIMIT 1');

	$bet->bindValue(':id', $id, PDO::PARAM_STR);

	$bet->execute();

	return($bet->fetch());

}



function getMatch($id)

{

	global $db;

	$bet = $db->prepare('SELECT * FROM `matches` WHERE `id` = :id LIMIT 1');

	$bet->bindValue(':id', $id, PDO::PARAM_STR);

	$bet->execute();

	return($bet->fetch());

}



function getGame($id)

{

	global $db;

	$bet = $db->prepare('SELECT * FROM `games` WHERE `id` = :id LIMIT 1');

	$bet->bindValue(':id', $id, PDO::PARAM_STR);

	$bet->execute();

	return($bet->fetch());

}



function getGaming($id)

{

	global $db;

	$bet = $db->prepare('SELECT * FROM `gamings` WHERE `id` = :id LIMIT 1');

	$bet->bindValue(':id', $id, PDO::PARAM_STR);

	$bet->execute();

	return($bet->fetch());

}


function getTypes($id)
{
	global $db;
	$bet = $db->prepare('SELECT * FROM `bettypes` WHERE `id` = :id LIMIT 1');
	$bet->bindValue(':id', $id, PDO::PARAM_STR);
	$bet->execute();
	return($bet->fetch());
}



function getEvent($id)

{

	global $db;

	$bet = $db->prepare('SELECT * FROM `events` WHERE `id` = :id LIMIT 1');

	$bet->bindValue(':id', $id, PDO::PARAM_STR);

	$bet->execute();

	return($bet->fetch());

}

/**
 * Format field DATETIME in MySQL Datebase
 */
define('_SQLDate_', 'Y-m-d H:i:s');
?>