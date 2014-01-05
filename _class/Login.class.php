<?php
/**
 * Author:		Kamil `pOmek` Piechaczek
 * Website:		http://pomek.pl
 * Gadu-Gadu:	6519101
 * File:		Login Class
 * Edit:		10.02.2013 23:30
 */
class Login
{
	/**
	 * Connect with Datebase
	 */
	private $db = NULL;
	/**
	 * __constructor
	 */
	public function __construct()
	{
		$this->db = DB::getConnect();
	}
	/** 
	 * Return (bool) true if isset user or (bool) false if user don't exists
	 */

	 

	public function checkUser($nick, $password, $sha1 = false)

	{

		

		$salt = $this->getSalt($nick);

		

		if($sha1 == true)

		{

			$pass = $salt.sha1($password);

		}

		else

		{

			$pass = $salt.$password;

		}

		

		$sql = $this->db->prepare('SELECT COUNT(`id`) FROM `users` WHERE `nick` = :nick AND `password` = :password');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->bindValue(':password', $pass, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		if($result[0] == 1)
		{

			return true;

		}

		return false;

	}

	

	/** 

	 * Return (bool) true if isset user or (bool) false if user don't exists

	 */

	 

	public function checkAuth($nick, $sid)

	{

		$sql = $this->db->prepare('SELECT COUNT(`id`) FROM `users` WHERE `nick` = :nick AND `session-id` = :sid');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->bindValue(':sid', $sid, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		

		if($result[0] == 1)

		{

			return true;

		}

		return false;

	}

	

	/**

	 * Function set UniqID() and return this value

	 */

	 

	public function setUniqID($nick)

	{

		$uniqId = uniqid();

		$sql = $this->db->prepare('UPDATE `users` SET `session-id` = :sessionID WHERE `nick` = :nick');

		$sql->bindValue(':sessionID', $uniqId, PDO::PARAM_STR);

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		return( $uniqId );

	}

	

	/**

	 * Functions return (string) rang user

	 */

	public function getRang($nick, $more = true)

	{

		$sql = $this->db->prepare('SELECT `headAdmin` FROM `admins` WHERE `userId` = :nick');

		$sql->bindValue(':nick', $this->getIdByLogin($nick), PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		if( $sql->rowCount() == 1 )

		{

			if( $more == true )

			{

				if( $result[ 'headAdmin' ] == "true" )

				{

					return( "head-admin" );

				}

			}

			return( "admin" );

		}

		else

		{

			return( "user" );

		}

	}

	

	/**

	 * Function set login time

	 */

	 

	public function setLoginTime($nick)

	{

		$sql = $this->db->prepare('UPDATE `users` SET `logged` = NOW() WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$sql->fetch();

	}

	

	/**

	 * Function set last action time

	 */

	 

	public function setLastActionTime($nick)

	{

		$sql = $this->db->prepare('UPDATE `users` SET `lastAction` = NOW() WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$sql->fetch();

	}

	

	/**

	 * Function set lang

	 */

	 

	public function setLang($nick, $lang)

	{

		$sql = $this->db->prepare('UPDATE `users` SET `lang` = :lang WHERE `nick` = :nick');

		$sql->bindValue(':lang', $lang, PDO::PARAM_STR);

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$sql->fetch();

	}

	

	/**

	 * Return (string) md5 datetime from datebase (salt)

	 */

	 

	public function getSalt($nick)

	{

		$sql = $this->db->prepare('SELECT `register` FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		return( substr( md5( $result[0] ), 0, 10 ) );

	}

	

	/**

	 * Return (string) datetime diff between NOW() and last action [minutes]

	 */

	 

	public function getTimeDiff($nick)

	{

		$sql = $this->db->prepare('SELECT TIMESTAMPDIFF(MINUTE, `lastAction`, NOW()) FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_INT);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (string) datetime last login from datebase 

	 */

	 

	public function getLastLogin($nick)

	{

		$sql = $this->db->prepare('SELECT `logged` FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_INT);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (string) lang

	 */

	

	public function getLang($nick)

	{

		$sql = $this->db->prepare('SELECT `lang` FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_INT);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (string) username by (int) id

	 */

	

	public function getLoginById($id)

	{

		$sql = $this->db->prepare('SELECT `nick` FROM `users` WHERE `id` = :id');

		$sql->bindValue(':id', $id, PDO::PARAM_INT);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (int) id by (string) username

	 */

	

	public function getIdByLogin($nick)

	{

		$sql = $this->db->prepare('SELECT `id` FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (string) nick by (string) e-mail

	 */

	

	public function getNickByMail($email)

	{

		$sql = $this->db->prepare('SELECT `nick` FROM `users` WHERE `email` = :email');

		$sql->bindValue(':email', $email, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		return( $result[0] );

	}

	

	/**

	 * Return (array) info about user

	 */

	

	public function getInfoUsers($nick)

	{

		$sql = $this->db->prepare('SELECT `users`.`id`, `nick`, `email`, `lang`, `credits`, `creditsWon`, 

			`creditsBought`, `register`, `firstName`, `surname`, `street`, `city`, `code`, `avatar`, `age`, `refId`, `refCount`, `countryId`, `name-pl`, 

			`name-en`, `newsletter`, 

			TIMESTAMPDIFF(YEAR, `age`, NOW()) as `age-now` FROM `users` JOIN `countries` ON `countries`.`id` = `users`.`countryId` WHERE `nick` = :nick LIMIT 1');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		return( $sql->fetch() );

	}

	

	/**

	 * Return (bool) true if active or (bool) false if user isn't active

	 */

	

	static public function getActive($nick)

	{

		$db = DB::getConnect();

		$sql = $db->prepare('SELECT `active` FROM `users` WHERE `nick` = :nick');

		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);

		$sql->execute();

		$result = $sql->fetch();

		

		if($result[ 0 ] == 'true')

		{

			return true;

		}

		return false;

	}

	

}



?>