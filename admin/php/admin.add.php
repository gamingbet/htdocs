<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}
if( __HEADADMIN__ == false )
{
	echo('<h4 class="alert_warning">Ta sekcja dozwolona jest TYLKO i wyłącznie dla głównego administratora!</h4>');
	exit();
}

$errors = array();

if(!empty($_PAGES['more']))
{
	$login = new Login;
	$adm = $db->query('SELECT * FROM `admins` WHERE `userId` = "'.$login->getIdByLogin($_PAGES['more']).'"');
	if($adm->rowCount() == 1)
		$errors[] = "Ten użytkownik jest już administratorem";
		
	if(empty($errors))
	{
		$db->exec('INSERT INTO `admins` VALUES(NULL, "'.$login->getIdByLogin($_PAGES['more']).'", NOW(), "false")');
		echo('<h4 class="alert_success">Operacja zakończona sukcesem!</h4>');
	}
}

if( !empty($errors ) ) 
{
	echo('<h4 class="alert_error">Podczas operacji wystąpiły błędy!</h4>');
	echo('<ul>');
	foreach($errors as $error)
	{
		echo('<li>'.$error.'</li>');
	}
	echo('</ul>');
}

$source = 'abcdefghijklmnopqrstuvwxyz_';
$count = strlen($source);
$i = 1;

echo('<p>Wskaż profil, który chcesz mianować administratorem. Poniższe menu wyświetla nazwy użytkowników zaczynające się na zaznaczony znak.</p>');
echo('<ul class="tabs">
<li><a href="#tab'.$i++.'">0-9</a></li>');

for($j = 0; $j < $count; $j++)
{
	echo('<li><a href="#tab'.$i++.'">'.strtoupper($source[$j]).'</a></li>');
}

echo('</ul>
<div style="clear: both" class="tab_container">');

$numbers = $db->query('SELECT `nick` FROM `users` WHERE `nick` REGEXP "^[0-9]+[a-z0-9_].*$"');
$i = 1;

echo('<div id="tab'.$i++.'" class="tab_content">');
if($numbers->rowCount() > 0)
{
	echo('<ul>');
	while($nick = $numbers->fetch())
	{
		$adm = $db->query('SELECT * FROM `admins` WHERE `userId` = "'.$nick['id'].'"');
		if($adm->rowCount() == 0)
			echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a></li>');
	}
	echo('</ul>');
}
else
{
	echo('<p>Brak zarejestrowanych użytkowników rozpoczynających się od tego znaku.</p>');
}
echo('</div>');

for($j = 0; $j < $count; $j++)
{
	echo('<div id="tab'.$i++.'" class="tab_content">');
	$nicks = $db->query('SELECT `id`, `nick` FROM `users` WHERE `nick` REGEXP "^['.$source[$j].']+[a-z0-9_].*$"');
	if($nicks->rowCount())
	{
		echo('<ul>');
		while($nick = $nicks->fetch())
		{
			$adm = $db->query('SELECT * FROM `admins` WHERE `userId` = "'.$nick['id'].'"');
			if($adm->rowCount() == 0)
				echo('<li><a href="'.$_ACTION.'/'.$nick['nick'].'">'.$nick['nick'].'</a></li>');
		}
		echo('</ul>');
	}
	else
	{
		echo('<p>Brak zarejestrowanych użytkowników rozpoczynających się od tego znaku.</p>');
	}
	echo('</div>');
}
echo('</div>');


?>