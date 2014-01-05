<?php

if( !(__LOAD__) )

{

	exit();

	return false;

}



if( $_GLOBALS[ 'login' ][ 'login' ] == "true" )

{
	echo('<div class="co-oferuje">'.sprintf( $_LANG[ 'labels' ][ 'zalogowany' ], strtolower($_USER['nick']), $_USER['nick']).'</div>');	
}

else{

	echo('<div class="co-oferuje">'.sprintf( $_LANG['general']['register-offer']).'</div>');	

}

?>