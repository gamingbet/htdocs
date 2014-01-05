<?php
if( !defined("__LOAD__") )
{
	exit();
	return false;
}

$partners[ 'sql' ] = $db->query('SELECT * FROM `partners` ORDER BY `lp` ASC');
$partners[ 'count' ] = $partners[ 'sql' ]->rowCount();
if( $partners[ 'count' ] > 0 )
{
	echo('<h2>'. $_LANG[ 'general' ][ 'partners' ] .'</h2>
	<div class="logos">');
	while( $partner = $partners[ 'sql' ]->fetch() )
	{
		if( !empty( $partner[ 'url' ] ) )
		{
			echo('<a href="'. $partner[ 'url' ] .'">');
		}
		echo('<img src="files/images/logos/'. $partner[ 'image' ].'" alt="'. $partner[ 'name' ].'">');
		if( !empty( $partner[ 'url' ] ) )
		{
			echo('</a>');
		}
	}
	echo('</div>');
}
echo(stripslashes($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_partners' ]));
?>