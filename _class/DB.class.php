<?php

/**
 * Autor: Kamil `pOmek` Piechaczek
 * WWW:   http://pomek.pl
 * GG:    6519101
 * Plik:  Klasa DB - Singleton - wzorzec projektowy
		  Funkcja tworzy pojedyńczą instancję połączenia
		  z bazą danych na podstawie danych zawartych
		  w pliku /_config.php
 * Edit:  03.11.2012 21:03
 */

class DB
{
	public  static $source = '';
	private static $pdo;
	private function __clone(){}      // Uniemożliwia utworzenie kopii obiektu
	private function __construct() {} // Blokujemy domyślny konstruktor publiczny	
 
	/**
	 * Sprawdzamy, czy połączenie zostało już utworzone. Jeśli tak to je zwracamy,
	 * jeśli nie - tworzymy nowe i je zwracamy
	 */
	 
	public static function getConnect()
	{
		if (self::$pdo === null)
		{
			try
			{
				$_DB = require_once(self::$source.'_config.php');
				return self::$pdo = new PDO('mysql:host='.$_DB["host"].';dbname='.$_DB["base"].';port='.$_DB["port"], $_DB["user"], $_DB["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			catch(PDOException $e)
			{
				return self::$pdo = $e->getMessage();
			}
		}
		return self::$pdo;
    }
	
	/**
	 * Podstawowy setter
	 * @return (bool) true/false
	 */

	public function __set($var, $value)
	{
		if(isset($this->$var))
		{
			return $this->$var = $value;
		}
		else
		{
			return false;
		}
	}
	
}
?>