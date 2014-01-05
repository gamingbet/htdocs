<?php
	$result = $db->query("SELECT * FROM `ad` WHERE id = 0");
	$ad = $result->fetch();
	$ad_endabled = (bool) $ad['ad_endabled'];
	if($ad_endabled) {
		if($_COOKIE['ad_watched'] == 1) {
			$ad_endabled = FALSE;
		} else {
			setcookie('ad_watched', 1, time() + 3600*16);
		}
	}
?>
<!DOCTYPE html>
<html lang="pl">  
<head>  
	<meta charset="UTF-8">
	<meta name="keywords" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'keywords' ]); ?>">
	<meta name="description" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'description' ]); ?>">
	<title><?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] . ' // ' . $_SETTINGS [ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'sitename' ] ); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/style.css" rel="stylesheet" media="screen">
	<meta charset="UTF-8">
	<base href="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ]); ?>/">
	<meta property="og:title" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'title' ] . ' // ' . $_SETTINGS [ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'sitename' ] ); ?>">
	<meta property="og:url" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ]); ?>">
	<meta property="og:site_name" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'sitename' ]); ?>">
	<meta property="og:type" content="website">
	<meta property="og:description" content="<?php echo($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'description' ]); ?>">
    <script src="https://code.jquery.com/jquery.js"></script>
	<script src="/js/bootstrap.min.js"></script>
    <script src="/js/modernizr.custom.js"></script>
</head>

<body class="cbp-spmenu-push <?php echo ($ad_endabled) ? 'rekl_active' : ''; ?>">
<a href="test.html" id="bg"></a><?php
	if($ad_endabled)
	{
		echo '<div id="rekl_overlay"><div id="rekl_wrapper">';
			echo '<header class="group">';
				echo '<div id="rekl_logo"><a href="'.$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'url' ].'">'.$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'sitename' ].'</a></div>';
				echo '<div id="rekl_exit"><a href="'.$_SERVER['REQUEST_URI'].'">Przejdź do '.$_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'sitename' ].'</a></div>';
			echo '</header>';
			echo '<section>'.$ad['ad_html'].'</section>';
		echo '</div></div>';
	}
?>

