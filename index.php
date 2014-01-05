<?php 

session_start();

define('__LOAD__', true);



// Core file

require_once('_core.php');



// Header section

require_once('files/template/header.php');



if($_GLOBALS[ 'login' ][ 'login' ] == true)

{

	require_once('files/template/info.php');

}



// Top section - left

require_once('files/template/top.php');



// Top section - right - ads

if( !empty($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_top' ]) )

{

	require_once('files/template/ad.php');

}

// Content section
require_once('files/template/content.php');
// Aside section
require_once('files/template/aside.php');
// Footer section
require_once('files/template/footer.php');



?>