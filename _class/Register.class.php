<?php

class Register
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
	 * create a new account
	 */
	public function createAccount($nick, $password, $email, $active, $lang, $credits, $time, $newsletter, $ip, $ref)
	{
		try
		{
			$sql = $this->db->prepare('INSERT INTO `users` VALUES ( NULL, "", :active, :nick, :password, :email, "", :lang, :credits, 0, 0, :time, "", "", "", "", "", "", "", "", 178, "none.jpg", :ip, "false", "", :newsletter, :ref, 0)');
			$sql->bindValue(':active', $active, PDO::PARAM_STR);
			$sql->bindValue(':nick', $nick, PDO::PARAM_STR);
			$sql->bindValue(':password', $password, PDO::PARAM_STR);
			$sql->bindValue(':email', $email, PDO::PARAM_STR);
			$sql->bindValue(':lang', $lang, PDO::PARAM_STR);
			$sql->bindValue(':credits', $credits, PDO::PARAM_STR);
			$sql->bindValue(':time', $time, PDO::PARAM_STR);
			$sql->bindValue(':newsletter', $newsletter, PDO::PARAM_STR);
			$sql->bindValue(':ip', $ip, PDO::PARAM_STR);
			$sql->bindValue(':ref', $ref, PDO::PARAM_STR);
			$sql->execute();
			return true;
		}
		catch(PDOException $e)
        {
			return($e->getMessage());
        }
	}
	/**
	 * If isset account
	 */
	public function issetAccount($nick)
	{
		$sql = $this->db->prepare('SELECT COUNT(`nick`) FROM `users` WHERE `nick` = :nick');
		$sql->bindValue(':nick', $nick, PDO::PARAM_STR);
		$sql->execute();
		$result = $sql->fetch();
		if($result[0] == 0)
		{
			return false;
		}
		return true;
	}
	/**
	 * If isset e-mail adress
	 */
	public function issetEMail($email)
	{
		$sql = $this->db->prepare('SELECT COUNT(`email`) FROM `users` WHERE `email` = :email');
		$sql->bindValue(':email', $email, PDO::PARAM_STR);
		$sql->execute();
		$result = $sql->fetch();
		if($result[0] == 0)
		{
			return false;
		}
		return true;
	}
}
?>