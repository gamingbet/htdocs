<?php /*<main> */ ?>



<?php

	echo(stripslashes($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_menu' ]));

	if( $_PAGES[ 'module' ] != "home" )

	{
		/*
		echo('<section class="box-big">

			<div class="h2">

				<h2>'. $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ].'</h2>');

		echo('</div>

			<div class="content">

				<div class="moveup">');
				*/

	}

	

	require_once( $_PAGES[ 'module' ] .'.php' );

	

	if( $_PAGES[ 'module' ] != "home" )

	{

		/*echo('</div>

			</div>

		</section>');
		*/

	}

?>



