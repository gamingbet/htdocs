<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

require_once('_functions.php');



// notices do not show communicates

error_reporting(E_ALL ^ E_NOTICE);

$_PRIVATE = false;



/*********************************************************************************************************************************************

 * Settings section

 */
$db = DB::getConnect();
$sql = $db->query('SELECT * FROM `settings`');
while( $fetch = $sql->fetch() )
{
	$_SETTINGS[ 'pl' ][ $fetch[ 'category' ] ][ $fetch[ 'name' ] ] = $fetch[ 'value-pl' ];
	$_SETTINGS[ 'en' ][ $fetch[ 'category' ] ][ $fetch[ 'name' ] ] = $fetch[ 'value-en' ];

}

/******************************************************************************************************************************************

 * Language start section

 */


$_GLOBALS[ 'lang' ] = $_SETTINGS[ 'pl' ][ 'general' ][ 'default-lang' ];
if( $_SETTINGS[ 'pl' ][ 'general' ][ 'change-lang' ] == "true" )
{
	if( isset( $_COOKIE[ 'lang' ] ) && !empty( $_COOKIE[ 'lang' ] ) && in_array( $_COOKIE[ 'lang' ], array( "pl", "en" ) ) )
	{
		$_GLOBALS[ 'lang' ] = $_COOKIE[ 'lang' ];
	}
	else
	{
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		if( $lang == "en" )
		{
			$_GLOBALS[ 'lang' ] = "en";
		}
		else if( $lang == "pl" )
		{
			$_GLOBALS[ 'lang' ] = "pl";
		}
		else
		{
			$_GLOBALS[ 'lang' ] = $_SETTINGS[ 'pl' ][ 'general' ][ 'default-lang' ];
		}
	}
}
else
{
	$_GLOBALS[ 'lang' ] = $_SETTINGS[ 'pl' ][ 'general' ][ 'default-lang' ];
} 





/*********************************************************************************************************************************************

 * Core - engines - includes pages

 */



$_PAGES[ 'module' ] = isset( $_GET[ 'module' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'module' ] ) ) ) : 'home';

$_PAGES[ 'type' ] = isset( $_GET[ 'type' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'type' ] ) ) ) : '';

$_PAGES[ 'more' ] = isset( $_GET[ 'more' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'more' ] ) ) ) : '';



if( isset( $_GET[ 'lang' ] ) && !empty( $_GET[ 'lang' ] ) && $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'change-lang' ] == "true" )

{

	$_PAGES[ 'lang' ] = $_GET[ 'lang' ];

	if( in_array( $_PAGES[ 'lang' ], array( "pl", "en" ) ) )

	{

		$setLang = true;

		$_GLOBALS[ 'lang' ] = $_PAGES[ 'lang' ];

	}

}



/* Send cookie, set lang and get lang */

new Language( $_GLOBALS[ 'lang' ] );

Language::$lang = $_GLOBALS[ 'lang' ];

$_LANG = Language::getLang();



if( !file_exists( $_PAGES[ 'module' ]. '.php' ) )

{

	$_GLOBALS[ 'url' ] = $_PAGES[ 'module' ];

	$custom = $db->prepare('SELECT * FROM `pages` WHERE `short-url` = :short');

	$custom->bindValue(':short', $_PAGES[ 'module' ], PDO::PARAM_STR);

	$custom->execute();

	$_CUSTOM = $custom->fetch();

	if( !empty( $_CUSTOM ) )

	{

		$_PAGES[ 'module' ] = "viewpage";

		// $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_CUSTOM[ 'title' . '-' . $_GLOBALS[ 'lang' ] ];

	}

	else

	{

		// $_SETTINGS[ $_GLOBALS[ 'lang' ]][ 'general' ][ 'title' ] = $_LANG[ 'errors' ][ '404' ];

		$_PAGES[ 'module' ] = "files/errors/404";

	}

}

else

{

	// $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG[ 'titles' ][ $_PAGES[ 'module' ] ];

}







if( !empty( $_PAGES[ 'module' ] ) )

{

	$_PAGES[ 'lang' ] = ( isset ( $_GLOBALS[ 'url' ] ) ) ? $_GLOBALS[ 'url' ] : $_PAGES[ 'module' ];

	if( !empty( $_PAGES[ 'type' ] ) )

	{

		$_PAGES[ 'lang' ] .= '/' . $_PAGES[ 'type' ];

		if( !empty( $_PAGES[ 'more' ] ) )

		{

			$_PAGES[ 'lang' ] .= '/' . $_PAGES[ 'more' ];

		}

	}

}


/*********************************************************************************************************************************************

 * Calendar Section

 */

if($_PAGES['module'] == "calendar")
{
	$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG[ 'titles' ][ "calendar" ];
}


/*********************************************************************************************************************************************

 * Login Section

 */



$_GLOBALS[ 'login' ][ 'login' ] = false;

$_GLOBALS[ 'login' ][ 'access' ] = false;



 

// If not login and send login request

if( ( isset( $_POST[ 'auth_nick' ] ) && isset( $_POST[ 'auth_pass' ] ) && isset( $_POST[ 'auth_submit' ] ) ) || isset( $_COOKIE[ 'auth' ] ) )

{

	if( isset( $_COOKIE[ 'auth' ][ 'nick' ] ) && isset( $_COOKIE[ 'auth' ][ 'pass' ] ) )

	{

		$auth_nick = ( trim( $_COOKIE[ 'auth' ][ 'nick' ] ) );

		$auth_pass = ( trim( $_COOKIE[ 'auth' ][ 'pass' ] ) );

		$autologin = true;

	}

	else

	{

		$auth_nick = ( trim( $_POST[ 'auth_nick' ] ) );

		$auth_pass = ( trim( $_POST[ 'auth_pass' ] ) );

		setcookie("auth[nick]", NULL, 1);

		setcookie("auth[pass]", NULL, 1);

		$autologin = false;

	}

	

	$login = new Login();

	if( $login->checkUser( $auth_nick, $auth_pass, ($autologin) ? false : true ) )

	{

		if( $login->getActive( $auth_nick ) )

		{

			$_GLOBALS[ 'login' ][ 'access' ] = $login->getRang( $auth_nick );

			if( $_SETTINGS [ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'login' ] == "false" )

			{

				if($_GLOBALS[ 'login' ][ 'access' ] == "user")

					$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'system_off' ];

			}

			else

			{

				if( isset( $_POST[ 'auth_remember' ] ) && $_POST[ 'auth_remember' ] == "true" )

				{

					setcookie("auth[nick]", $auth_nick, time()+14*24*60*60);

					setcookie('auth[pass]', sha1($auth_pass), time()+14*24*60*60);

				}

				

				$_SESSION[ 'auth' ][ 'login' ] = $auth_nick;

				$_SESSION[ 'auth' ][ 'last_visit' ] = $login->getLastLogin( $auth_nick );

				$_SESSION[ 'auth' ][ 'sid' ] = $login->setUniqID( $auth_nick );

				$login->setLoginTime( $auth_nick );

				$login->setLastActionTime( $auth_nick );

				$_USER = $login->getInfoUsers( $auth_nick );

				

				$_GLOBALS[ 'login' ][ 'login' ] = true;

				$_GLOBALS[ 'login' ][ 'info' ] = $_USER[ 'firstName' ];

				

				

				if( empty($_USER[ 'firstName' ] ) )

				{

					$firstLogin = true;

					$_GLOBALS[ 'login' ][ 'info' ] = $_USER[ 'nick' ];

				}

			}

		}

		else

		{

			$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'notActive' ];

		}

	}

	else

	{

		if( !isset( $_COOKIE[ 'auth' ] ) )

		{

			$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'wrong_post' ];

		}

	}

}

// If isset session logins

else if( isset( $_SESSION[ 'auth' ] ) )

{

	$auth_nick = ( trim( $_SESSION[ 'auth' ][ 'login' ] ) );

	$auth_sid  = ( trim( $_SESSION[ 'auth' ][ 'sid' ] ) );

	

	$login = new Login();

	if( $login->checkAuth( $auth_nick, $auth_sid ) )

	{

		if( $login->getActive( $auth_nick ) )

		{

			if( $login->getTimeDiff( $auth_nick ) <= $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'unActiveTime' ] || $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'unActiveTime' ] == '0' )

			{

				$_GLOBALS[ 'login' ][ 'access' ] = $login->getRang( $auth_nick );

				

				if( $_SETTINGS [ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'login' ] == "false" )

				{

					if($_GLOBALS[ 'login' ][ 'access' ] == "user")

						$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'system_off' ];	

				}

				else

				{

					$login->setLastActionTime( $auth_nick );

					$_USER = $login->getInfoUsers( $auth_nick );

					

					$_GLOBALS[ 'login' ][ 'login' ] = true;

					$_GLOBALS[ 'login' ][ 'info' ] = $_USER[ 'firstName' ];

					

					if( empty( $_USER[ 'firstName'] ) )

					{

						$firstLogin = true;

						$_GLOBALS[ 'login' ][ 'info' ] = $_USER[ 'nick' ];

					}

				}

			}

			else

			{

				unset($_SESSION['auth']);

				$_GLOBALS[ 'login' ][ 'errors' ][] = sprintf($_LANG[ 'auth' ][ 'too_unactive' ], $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'auth' ][ 'unActiveTime' ]);

			}

		}

		else

		{

			$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'notActive' ];

		}

	}

	else

	{

		unset($_SESSION['auth']);

		$_GLOBALS[ 'login' ][ 'errors' ][] = $_LANG[ 'auth' ][ 'session_failes' ];

	}

}

// Not login and session doesn't exists

else

{

	$_GLOBALS[ 'login' ][ 'login' ] = false;

}



/*********************************************************************************************************************************************

 * Section for login user

 */

 

if( $_GLOBALS[ 'login' ][ 'login' ] == true )

{

	if( isset( $setLang ) && $setLang == true )

	{

		$login->setLang( $_USER['nick'], $_GLOBALS[ 'lang' ] );

		$_USER[ 'lang' ] = $_GLOBALS[ 'lang' ];

	}



	$_GLOBALS[ 'lang' ] = $_USER[ 'lang' ];

	Language::$lang = $_GLOBALS[ 'lang' ];

	$_LANG = Language::getLang();

	

	if( isset($firstLogin) && $firstLogin == true )

	{

		$_PAGES[ 'module' ] = "edit-profile";

		$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG[ 'titles' ][ "edit-profile" ];

	}

}



/*********************************************************************************************************************************************

 * BETS SECTION

 */

 

if( isset($_GET['bId']) && !empty($_GET['bId']) )

{

	$_PAGES[ 'bId' ] = trim(htmlspecialchars($_GET['bId']));

}

else if( isset($_PAGES[ 'more' ]) && !empty($_PAGES[ 'more' ]) )

{

	$_PAGES[ 'bId' ] = trim(htmlspecialchars($_PAGES[ 'more' ]));

}

else if( isset($_PAGES[ 'type' ]) && !empty($_PAGES[ 'type' ]) )

{

	$_PAGES[ 'bId' ] = trim(htmlspecialchars($_PAGES[ 'type' ]));

}

else

{

	$_PAGES[ 'bId' ] = trim(htmlspecialchars($_PAGES[ 'module' ]));

}

if( preg_match('/^([0-9]+)-([0-9]{1,3})-delete$/D', $_PAGES['bId']) )

{

	$dump = explode('-', $_PAGES['bId']);

	unset($_SESSION['bets'][$dump[0]]);

	header('Location: /'.str_replace( '/'.$_PAGES['bId'], '', $_PAGES['lang'] ));

}



if( preg_match('/^([0-9]+)-([0-9]{1,3})$/D', $_PAGES['bId']) )

{

	$dump = explode('-', $_PAGES['bId']);	

	$_SESSION['bets'][$dump[0]] = $dump[1];

	

	header('Location: /'.str_replace( '/'.$_PAGES['bId'], '', $_PAGES['lang'] ));

}



/*********************************************************************************************************************************************

 * GAME SECTION

 */

 

if($_PAGES['module'] == "games" && !empty($_PAGES['type']))

{

	$temp['game'] = $db->prepare('SELECT `name` FROM `games` WHERE `short` = :short LIMIT 1');

	$temp['game']->bindValue(':short', $_PAGES['type'], PDO::PARAM_STR);

	$temp['game']->execute();

	$temp['result'] = $temp['game']->fetch();

	if( $temp['result'] != false )

	{

		$_PRIVATE = true;

		$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $temp['result'][0];

	}

}



/*********************************************************************************************************************************************

 * TEAM SECTION

 */

 

if($_PAGES['module'] == "teams" && !empty($_PAGES['type']))

{

	$temp['team'] = $db->prepare('SELECT `fullname`, `id` FROM `gamings` WHERE `tag` = :tag LIMIT 1');

	$temp['team']->bindValue(':tag', $_PAGES['type'], PDO::PARAM_STR);

	$temp['team']->execute();

	$temp['result'] = $temp['team']->fetch();

	if( $temp['result'] != false )

	{

		$_PRIVATE = true;

		$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $temp['result'][0];

		

		if($_PAGES['more'] == "add" && $_GLOBALS[ 'login' ][ 'login' ] == true)

		{

			$temp['fav'] = $db->prepare('SELECT * FROM `favouritesteams` WHERE `gamingId` = :gid AND `userId` = :uid LIMIT 1');

			$temp['fav']->bindValue(':gid', $temp['result']['id'], PDO::PARAM_STR);

			$temp['fav']->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);

			$temp['fav']->execute();

			$temp['favResult'] = $temp['fav']->fetch();

			if($temp['favResult'] == false)

			{

				$temp['add'] = $db->prepare('INSERT INTO `favouritesteams` VALUES(NULL, :gid, :uid, NOW())');

				$temp['add']->bindValue(':gid', $temp['result']['id'], PDO::PARAM_STR);

				$temp['add']->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);

				$temp['add']->execute();

				header('Location: /'.str_replace( '/add', '', $_PAGES['lang'] ));

			}

		}

		else if($_PAGES['more'] == "remove" && $_GLOBALS[ 'login' ][ 'login' ] == true)

		{

			$temp['fav'] = $db->prepare('SELECT * FROM `favouritesteams` WHERE `gamingId` = :gid AND `userId` = :uid LIMIT 1');

			$temp['fav']->bindValue(':gid', $temp['result']['id'], PDO::PARAM_STR);

			$temp['fav']->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);

			$temp['fav']->execute();

			$temp['favResult'] = $temp['fav']->fetch();

			if($temp['favResult'] == true)

			{

				$db->query('DELETE FROM `favouritesteams` WHERE `id` = '.$temp['favResult']['id'].' LIMIT 1');

				header('Location: /'.str_replace( '/remove', '', $_PAGES['lang'] ));

			}

		}

	}

}



/*********************************************************************************************************************************************

 * EVENTS SECTION

 */

 

if($_PAGES['module'] == "events" && !empty($_PAGES['type']))

{	

	$temp['game'] = $db->prepare('SELECT `name`, `id` FROM `games` WHERE `short` = :short LIMIT 1');

	$temp['game']->bindValue(':short', $_PAGES['type'], PDO::PARAM_STR);

	$temp['game']->execute();

	$temp['game_result'] = $temp['game']->fetch();

	// Events from any game

	if( $temp['game_result'] != false )

	{

		$_PRIVATE = true;

		$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG['titles']['events'].' - '.$temp['game_result'][0];

		$showGame = true;

		$_GLOBALS['game']['id'] = $temp['game_result'][1];

	}

	// Events from all

	else

	{

		$showGame = false;

		$temp['team'] = $db->prepare('SELECT `name` FROM `events` WHERE `id` = :id LIMIT 1');

		$temp['team']->bindValue(':id', $_PAGES['type'], PDO::PARAM_STR);

		$temp['team']->execute();

		$temp['result'] = $temp['team']->fetch();

		

		if( $temp['result'] != false )

		{

			$_PRIVATE = true;

			$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $temp['result'][0];

		}

	}

}



// for Google Chrome from Tomasz :)

// Section for titles (h2 & title)

if($_PRIVATE == false)

{

	if( file_exists( $_PAGES[ 'module' ]. '.php' ) && $_PAGES['module'] != "games" && $_PAGES['module'] != "teams" && $_PAGES['module'] != "events" )

	{

		if( isset($_CUSTOM ) )

		{

			if( empty( $_CUSTOM ) )

			{	

				$_SETTINGS[ $_GLOBALS[ 'lang' ]][ 'general' ][ 'title' ] = $_LANG[ 'errors' ][ '404' ];

			}

			else

			{

				$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_CUSTOM[ 'title' . '-' . $_GLOBALS[ 'lang' ] ];

			}

		}

		else

		{

			$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG[ 'titles' ][ $_PAGES[ 'module' ] ];

		}

	}

	else

	{

		if($_PAGES['module'] == "games" || $_PAGES['module'] == "teams" || $_PAGES['module'] == "events")

			if(empty($_PAGES['type']) || (isset($temp['result']) && $temp['result'] != false))

				$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG['titles'][ $_PAGES[ 'module' ] ];

	}

}



// Customers Table

$customerTableShow = 'false';

$betsErrors = array();

$betsCreateInfo = NULL;

if($_PAGES['module'] == 'bets' && !empty($_PAGES['more']) && $_GLOBALS[ 'login' ][ 'login' ] == true)

{

	$tables = new Tables();

	$tables->setCustomerId( (int) $_USER['id']);

	if( preg_match('/^([a-zA-Z]+)-([0-9]+)$/D', $_PAGES['more']) )

	{

		list($action, $tableId) = explode('-', $_PAGES['more']);

		

		if( $tables->isIssetTable( (int) $tableId) ) {

			if($action == "join") {

				if($tables->isAllowBetMatch((int) $_PAGES['type'])) {

					if( !$tables->isOwnerTable((int) $tableId) ) {

						if($tables->canJoinToTable( (int) $tableId) ) {

$customerBetsCredit = $tables->getCourseByTableId( (int) $tableId );

							if( $_USER['credits'] >= $customerBetsCredit ) {	 							$course = $tables->getCourseByTableId( (int) $tableId );

								$tables->saveCustomerToTable( (int) $tableId );

								$tables->changeCustomerCreditsByCustomerId( Tables::SUBTRACT, (int) $course );

								$_USER['credits'] -= $course;								

							} else {

								$betsErrors[] = $_LANG['tables']['notEnoughCredits'];

							}

						} else {

							$betsErrors[] = $_LANG['tables']['noFreePlaces'];

						}

					} else {

						$betsErrors[] = $_LANG['tables']['cantJoinToYourBet'];

					}

				} else {

					$betsErrors[] = $_LANG['tables']['disable'];

				}

			} else if($action == "leave") {

				if($tables->isCustomerAssignToTable( $tables->getCustomerId(), (int) $tableId) && $tables->isAllowBetMatch((int) $_PAGES['type'])) {

					$course = $tables->getCourseByTableId( (int) $tableId );

					$tables->removeCustomerToTable( (int) $tableId);

					$tables->changeCustomerCreditsByCustomerId( Tables::ADD, (int) $course );

					$_USER['credits'] += $course;

					$betsCreateInfo = $_LANG['tables']['removeFromTable'];

				} else {

					$betsErrors[] = $_LANG['tables']['wrongTableId'];

				}

			

			} else if($action == "remove") {

				if($tables->isOwnerTable( (int) $tableId) && $tables->isAllowBetMatch((int) $_PAGES['type'])) {

					$course = $tables->getCourseByTableId( (int) $tableId );

					

					if( !$tables->canJoinToTable( (int) $tableId) ) {

						$player2Id = $tables->getSecondPlayerInTable((int) $tableId);

						$tables->changeCustomerCreditsByCustomerId( Tables::ADD, (int) $course, (int) $player2Id );						

					}

					

					$tables->changeCustomerCreditsByCustomerId( Tables::ADD, (int) $course);

					$tables->removeTable( (int) $tableId );

					$_USER['credits'] += $course;

					

					$betsCreateInfo = $_LANG['tables']['removeTable'];

					

				} else {

					$betsErrors[] = $_LANG['tables']['wrongTableId'];

				}

			} 

		} else {

			$betsErrors[] = $_LANG['tables']['tableDoesntExists'];

		}

	}

	else 

	{

		if($_PAGES['more'] == 'create') {

			$sent = false;

			$customerTableShow = 'true';

			

			if( isset($_POST['customBetsSend']) && $_POST['customBetsSend'] == $_LANG['tables']['sendType']) 

			{

				$customerBetsCredit = trim(htmlspecialchars($_POST['customBetsCredit']));

				$customBetsType = trim(htmlspecialchars($_POST['customBetsType']));

				$customBetsResult = trim(htmlspecialchars($_POST['customBetsResult']));

				

				$customerCountBets = $tables->getCountUserTables();

				$tablePerUser = $_SETTINGS[$_GLOBALS['lang']]['bets']['tpe'];

				

				if( $tables->isAllowBetMatch( (int) $_PAGES['type'] )) {								

 					if( $customerCountBets >= $tablePerUser && $_USER['credits'] > 0 ) {

	  						$betsErrors[] = sprintf($_LANG['tables']['max_custom_tables'], $tablePerUser);

						$customerTableShow = 'false';

					} else {

						

						// Czy s¹ kredyty

						if( !$tables->isAllowCreateBet( (int) $customerBetsCredit) && $customerBetsCredit > 0)

								$betsErrors[] = $_LANG['tables']['notEnoughCredits'];

						

						// Czy zak³ad siê zgadza

						if( !$tables->isBetTypeInBetsMatch( (int) $customBetsType, (int) $_PAGES['type'] ) )

							$betsErrors[] = $_LANG['tables']['wrongTypeInBets'];

						

						// Czy zwyciezca jest 1/2

						if( (int) $customBetsResult < 0  || (int) $customBetsResult > 2  ) 	

							$betsErrors[] = $_LANG['tables']['wrongTypeScore'];

						

						// dodaj do bazy

						if(empty($betsErrors)) {

							$sent = true;

							$customerTableShow = 'false';

							$tables->changeCustomerCreditsByCustomerId(Tables::SUBTRACT, (int) $customerBetsCredit);

							$_USER['credits'] -= $customerBetsCredit;

							$sql = "

								INSERT INTO

									tables

									(id, matchId, player1Id, player2Id, status, course, createTime, createdBy, betType, ownerOption)

								VALUES

									(

										NULL, 

										".(int) $_PAGES['type'].",

										".$tables->getCustomerId().",

										0,

										'open',

										".(int) $customerBetsCredit.",

										NOW(),

										".$tables->getCustomerId().",

										".(int) $customBetsType.",

										".(int) $customBetsResult."

									)

							";

							$db->exec($sql);

							$customerBetsCredit = NULL;

							$customBetsType = NULL;

							$customBetsResult = NULL;

						}

					}

				} else {

					$betsErrors[] = $_LANG['tables']['disable'];

				}

			}

			else

			{

				$customerBetsCredit = NULL;

				$customBetsType = NULL;

				$customBetsResult = NULL;

			}

			

			if($sent == true) {

				$betsCreateInfo = $_LANG['tables']['createdBet'];

			}

		}

	}

	

}





// Check bans

$banned = false;

if( $_GLOBALS[ 'login' ][ 'login' ] == true )

{

	$ban = $db->query('SELECT `id` FROM `users` WHERE `ban` = "true" AND `banTime` > NOW() AND `id` = "'.$_USER['id'].'" LIMIT 1');

	if($ban->rowCount() == 1)

	{

		$banned = true;

	}

}

else

{

	$ip = $_SERVER['REMOTE_ADDR'];

	$ban = $db->query('SELECT `id` FROM `bans` WHERE `ip` = "'.$ip.'" AND `time` > NOW()');

	if($ban->rowCount() != 0)

	{

		$banned = true;

	}

}



if($banned == true)

{

	unset($_SESSION['auth']);

	setcookie("auth[nick]", NULL, 1);

	setcookie("auth[pass]", NULL, 1);

	$_GLOBALS[ 'login' ][ 'login' ] = false;

	$_PAGES[ 'module' ] = "bans";

	$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] = $_LANG['titles'][ $_PAGES[ 'module' ] ];

}

?>