<?php

/**
 * Author:		Kamil `pOmek` Piechaczek
 * Website:		http://pomek.pl
 * Gadu-Gadu:	6519101
 * File:		Language Class
 * Edit:		04.02.2013 18:32
 */

class Language
{
	/**
	 * Set language
	 */
	 
	public static $lang = NULL;
	
	/**
	 * Dir with language files
	 */
	 
	public static $dir = "files/lang";
	
	/**
	 * Cookie name with information about the language
	 */
	 
	public static $cookie = "lang";
	
	/**
	 * File extension with languages
	 */
	
	public static $ext = ".ini";
	
	/**
	 * __constructor
	 */
	
	public function __construct($lang = '')
	{
		if( !empty( $lang ) )
		{
			$this->setLang($lang);
		}
	}
	
	/**
	 * Function changing language
	 */
	 
	public function setLang($lang)
	{
		// Check exists language file and set language 
		if( $this->checkLangFile($lang) )
		{
			self::$lang = $lang;
			@setcookie( self::$cookie, self::$lang, (time() + 7*24*60*60), "/" );
		}
		else
		{
			return("Can't open then file - " . $lang);
		}
	}
	
	/**
	 * Function checking isset language file
	 */
	
	public function checkLangFile($lang)
	{
		if( file_exists(self::$dir . "/" . $lang . self::$ext) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Function return parse ini file with language array
	 */
	
	public static function getLang()
	{
		$db = DB::getConnect();
		$sql = $db->query('SELECT `category`, `label`, `label-'.self::$lang.'` AS `lang`  FROM `langs`');
		while($lang = $sql->fetch())
		{
			$_LANG[ trim($lang['category']) ][ trim($lang['label']) ] = trim($lang['lang']);
		}
		return($_LANG);
	}
}
?>