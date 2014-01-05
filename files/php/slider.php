<?php
	if( !defined("__LOAD__") ){
		exit();
		return false;
	}
	
	$slider[ 'sql' ] = $db->query( 'SELECT * FROM `slider` WHERE `active` = "true" ORDER BY `lp` ASC' );
	$slider[ 'count' ] = $slider[ 'sql' ]->rowCount();
	if( $slider[ 'count' ] > 0 ){
		echo('		
		<div id="coin-slider">');
		while( $slider[ 'slide' ] = $slider[ 'sql' ]->fetch() )
		{
			echo('<a href="'. $slider[ 'slide' ][ 'url' ] .'">
				<img src="files/images/slider/'. $slider[ 'slide' ][ 'image' ] .'" alt="'. $slider[ 'slide' ][ 'name' . '-' . $_GLOBALS[ 'lang' ] ] .'">
				<span>
					<span class="slider_h1">'. stripslashes($slider[ 'slide' ][ 'name' . '-' . $_GLOBALS[ 'lang' ] ]) .'</span>
					<span class="slider_date">'. stripslashes($slider[ 'slide' ][ 'description' . '-' . $_GLOBALS[ 'lang' ] ]) .'</span>
				</span>
			</a>');
		}
		echo('
		</div>       
		');
	}
?>