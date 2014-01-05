<?php

if( !defined("__LOAD__") )

{

	exit();

	return false;

}

?>

<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-xs-12 col-sm-12 col-md-8">
<div class="well-sm biale">
<h1>Ulubione drużyny</h1>
<?php

if( $_GLOBALS[ 'login' ][ 'login' ] == false )

{

	echo( $_LANG[ 'auth' ][ 'need_login' ] );

	return false;

}



if(isset($_POST['delete']) && $_POST['delete'] == $_LANG['history']['deleteSelect'])

{

	$errors = NULL;

	$delete = false;

	$count = 0;

	

	if($_POST['teams'] == NULL)

		$errors[] = $_LANG['history']['noSelected'];

		

	if(empty($errors))

	{

		foreach($_POST['teams'] as $item)

		{

			$item = (int)($item);

			if( $db->query('DELETE FROM `favouritesteams` WHERE `userId` = '.$_USER['id'].' AND `gamingId` = '.$item.' LIMIT 1') )

				++$count;

		}

		$delete = true;

	}

}



if(isset($errors) && !empty($errors))

{

	echo('<div class="alert alert-info"><p class="hn">'.$_LANG['history'][ 'errors' ].'</p>');

	echo('');

	foreach( $errors as $error )

	{

		echo(''. $error. '');

	}

	echo('</div>');

}

else if($delete == true)

{

	echo('<p>'.sprintf($_LANG['history']['deleted'], $count).'</p>');

}





$fav = $db->prepare('SELECT *, DATE_FORMAT(`add`, "%d.%m.%Y %H:%i") AS `add` FROM `favouritesteams` WHERE `userId` = :uid ORDER BY `add` ASC');

$fav->bindValue(':uid', $_USER['id'], PDO::PARAM_STR);

$fav->execute();



if($fav->rowCount() > 0)

{

	

	echo('<form action="favourites" method="post">

	<table class="table table-striped table-hover">');

	

	echo('<tr>');

	echo('<th class="date">'.$_LANG['history']['date-add'].'</th>');

	echo('<th class="enemys2">'.$_LANG['history']['enemy'].'</th>');

	echo('<th class="result last">'.$_LANG['history']['delete'].'</th>');

	echo('</tr>');

	

	while($team = $fav->fetch())

	{

		$gaming = getGaming($team['gamingId']);

		echo('<tr>');

		echo('<td>'.$team['add'].'</td>');

		echo('<td><a href="teams/'.$gaming['tag'].'">'.$gaming['fullname'].'</a></td>');

		echo('<td><input type="checkbox" value="'.$gaming['id'].'" name="teams[]"></td>');

		echo('</tr>');

	}

	echo('<tr><td></td>');

	echo('<td></td>');

	echo('<td><input type="submit" name="delete" value="'.$_LANG['history']['deleteSelect'].'" class="btn btn-danger"></td>');

	echo('</tr></table>

	</form>');

}

else

{

	echo( '<div class="alert alert-info">'.$_LANG[ 'labels' ][ 'noFavourites' ].'</div>' );

}



?></div>