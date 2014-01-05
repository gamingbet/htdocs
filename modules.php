<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

	$modules = $db->query('SELECT * FROM `panels` WHERE `active` = "true" ORDER BY `lp` ASC');

	while( $module = $modules->fetch() )

	{

		//echo('<div class="panel" id="panel-'.$module[ 'name' ].'"><h2>');
		echo('');

		/*
		if( isset( $_LANG[ 'panels' ][ $module[ 'name' ] . '-in' ] ) && isset( $_LANG[ 'panels' ][ $module[ 'name' ] . '-out' ] ) )

		{

			if( $_GLOBALS[ $module[ 'name' ] ][ $module[ 'name' ] ] == true )

			{

				echo( sprintf( $_LANG[ 'panels' ][ $module[ 'name' ] . '-in' ], $_GLOBALS[ $module[ 'name' ] ][ 'info' ] ) );

			}

			else

			{

				echo( $_LANG[ 'panels' ][ $module[ 'name' ] . '-out' ] );

			}

		}

		else

		{

			echo( $_LANG[ 'panels' ][ $module[ 'name' ] ] );

		}

		echo('</h2>
		*/
		echo('');

		

		if( preg_match("/file:([a-z0-9A-Z_]+\.php|html|js)/", $module[ 'content' ], $pattern) ) 

		{

			if( file_exists( 'panels/' . $pattern[ 1 ] ) )

			{

				require_once( 'panels/' . $pattern[ 1 ] );

			}

			else

			{

				echo('File <b>'.$pattern[ 1 ].'</b> doesn\'t exists!');

			}

		}

		else

		{

			if( $module[ 'type' ] == "html" )

			{

				echo ( stripslashes( $module[ 'content' ] ) );

			}

			else

			{

				$bbcode = new BBCode();

				echo( $bbcode->parse( stripslashes ( $module[ 'content' ] ) ) );

			}

		}

		echo('');
}

	echo(stripslashes($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_panels' ]));

?>