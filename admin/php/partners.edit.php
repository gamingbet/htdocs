<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$showAll = true;
$errors = array();

if( $_PAGES['more'] == "move" )
{
	$partners = $db->query('SELECT * FROM `partners` ORDER BY `lp` ASC');
	@$partner_id = (int) $_POST['partner_id'];
	$partner = $db->query('SELECT `id`, `lp` FROM `partners` WHERE `id` = '.$partner_id);
	
	if($partner->rowCount() == 0)
	{
		$errors[] = "Partner o podanym ID nie istnieje";
	}
	else
	{
		$partner = $partner->fetch();
		
		if($_POST['action'] == "up")
		{
			$new = $partner['lp']-1;
			if($new == 0)
			{
				$errors[] = "Tego elementu wyżej przenieść już nie można";
			}
		}
		else if($_POST['action'] == "bottom")
		{
			$new = $partner['lp']+1;
			if($new > $partners->rowCount())
			{
				$errors[] = "Tego elementu niżej przenieść już nie można";				
			}
		}
		else
		{
			$errors[] = "Nie wybrano odpowiedniej akcji";
		}
		
		if(empty($errors))
		{
			if( $_POST['action'] == "up" )
			{				
				$db->exec('UPDATE `partners` SET `lp` = `lp`+1 WHERE `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `partners` SET `lp` = `lp`-1 WHERE `id` = '.$partner_id.' LIMIT 1');
			}
			else
			{
				$db->exec('UPDATE `partners` SET `lp` = `lp`-1 WHERE `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `partners` SET `lp` = `lp`+1 WHERE `id` = '.$partner_id.' LIMIT 1');
			}
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		}
	}	
}
else if( $_PAGES['more'] == "remove" )
{
	@$partner_id = (int) $_POST['partner_id'];
	$partner = $db->query('SELECT `id`, `lp`, `image` FROM `partners` WHERE `id` = '.$partner_id);
	
	if($partner->rowCount() == 0)
	{
		$errors[] = "Partner o podanym ID nie istnieje";
	}
	else
	{		
		$partners = $db->query('SELECT * FROM `partners` ORDER BY `lp` ASC');
		$all = $partners->rowCount();
		$partner = $partner->fetch();
		$current = $partner['lp'];
				
		for( $i = $current; $i <= $all; $i++)
		{
			$db->exec('UPDATE `partners` SET `lp` = `lp` - 1 WHERE `lp` = '.$i.' LIMIT 1');
		}
		
		unlink('../files/images/logos/'.$partner['image']);
		$db->exec('DELETE FROM `partners` WHERE `id` = '.$partner_id.' LIMIT 1');
		
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
}
else
{
	if( !empty($_PAGES['more'] ) )
	{
		@$partner_id = (int) $_PAGES['more'];
		$partner = $db->query('SELECT `id` FROM `slider` WHERE `id` = '.$partner_id);
		if( $partner->rowCount() == 1)
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "Partner o podanym ID nie został odnaleziony";
		}
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

if( $showAll == true )
{
	echo('<p>Wybierz stronę slidera, którą chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć stronę slidera, należy kliknąć ikonkę kosza. 
	Miej na uwadze to, że operacja usunięcia jest nieodwracalna. Zmiana statusu nieaktywnego na aktywny powoduje przesunięcie tego obiektu na sam koniec listy.</p>');
		
	echo('<h4>Partnerzy</h4>');
	$partners = $db->query('SELECT * FROM `partners` ORDER BY `lp` ASC');
	if( $partners->rowCount() == 0 ) 
	{
		echo('<h4 class="alert_info">Brak partnerów!</h4>');
	}
	else
	{
		echo('<table class="tablesorter">
		
		<thead>
			<tr>
				<td style="width: 10%">LP</td>
				<td style="width: 70%">Nazwa</td>
				<td style="width: 15%" colspan="2">Przesuń</td>
				<td style="width: 5%">Usuń</td>
			</tr>
		</thead>');
		
		while($partner = $partners->fetch() )
		{
			echo('<tr>
			<td class="center">'.$partner['lp'].'</td>
				<td><a href="'.$_ACTION.'/'.$partner['id'].'">'.$partner['name'].'</a>
			</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="partner_id" value="'.$partner['id'].'">
					<input type="hidden" name="action" value="up">');
				if( $partner['lp']-1 == 0 )
				{
					echo('<input type="image" src="images/top_noactive.png" title="UP" disabled>');
				}
				else
				{
					echo('<input type="image" src="images/top.png" title="UP">');
				}
		echo('</form>
			</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="partner_id" value="'.$partner['id'].'">
					<input type="hidden" name="action" value="bottom">');
				if( $partner['lp']+1 > $partners->rowCount() )
				{
					echo('<input type="image" src="images/bottom_noactive.png" title="BOTTOM" disabled>');
				}
				else
				{
					echo('<input type="image" src="images/bottom.png" title="BOTTOM">');
				}
		echo('</form>
			</td>
			<td class="center">
				<form action="'.$_ACTION.'/remove" class="post_message" method="post">
					<input type="hidden" name="partner_id" value="'.$partner['id'].'">
					<input type="image" src="images/icn_trash.png" title="Kosz">
				</form>
			</td>
		</tr>');
		}
		echo('</table>');
	}
}
else
{
	$sent = false;
	$partner_id = (int) $_PAGES['more'];
	$partner = $db->query('SELECT * FROM `partners` WHERE `id` = '.$partner_id);
	$partner = $partner->fetch();
		
	if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
	{
		$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));
		$url = addslashes(trim(htmlspecialchars($_POST['url'])));
		$old_image = addslashes(trim(htmlspecialchars($_POST['old_image'])));
		$image = &$_FILES['images'];
		
		$access = array('image/gif', 'image/png', 'image/jpeg');
		
		if( empty($nazwa) )
			$errors[] = "Nie zostały wypełnione wymagane pola";
			
		if( !empty($image['tmp_name']) )
		{
			list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);
			
			if(!in_array($image['type'], $access))
				$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";
				
			if($width > 150 || $height > 100)
				$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 150x100 [px]";
		}
		
		if( empty($errors) )
		{
			if( !empty($image['tmp_name']) )
			{
				$uniqId = uniqid();
		
				if($image['type'] == 'image/gif')
				{
					$ext = '.gif';
				}
				else if($image['type'] == 'image/png')
				{
					$ext = '.png';
				}
				else
				{
					$ext = '.jpg';
				}
				
				$filename = $uniqId.$ext;
				move_uploaded_file($image['tmp_name'], '../files/images/logos/' . $filename);
				unlink('../files/images/logos/'.$old_image);
			}
			else
			{
				$filename = $old_image;
			}
			
			$change = $db->prepare('UPDATE `partners` SET `name` = :name, `url` = :url, `image` = :image WHERE `id` = '.$partner_id.' LIMIT 1');
			$change->bindValue(':name', $nazwa, PDO::PARAM_STR);
			$change->bindValue(':url', $url, PDO::PARAM_STR);
			$change->bindValue(':image', $filename, PDO::PARAM_STR);
			$change->execute();
						
			$sent = true;
		}
	}
	else
	{
		$nazwa = $partner['name'];
		$url = $partner['url'];
	}
	
	if($sent == true)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		echo('<ul>
			<li><a href="/">Przejdź do strony głównej</a></li>
			<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
			<li><a href="/admin/partners/add">Dodaj nowego partnera</a></li>
			<li><a href="/admin/partners/edit">Zarządzaj partnerami</a></li>
		</ul>');
	}
	else
	{
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

		echo('
		<form action="'.$_ACTION.'/'.$partner_id.'" method="post" class="post_message" enctype="multipart/form-data">

		<fieldset>
			<label for="input_1">Nazwa<span class="star">*</span></label>
			<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>
		</fieldset>

		<fieldset>
			<label for="input_2">URL</label>
			<input type="text" id="input_2" name="url" value="'.stripslashes($url).'">
		</fieldset>

		<fieldset>
			<label for="input_3">Obrazek</label>
			<input type="hidden" name="old_image" value="'.$partner['image'].'">
			<input type="file" id="input_3" name="images" accept="image/jpeg,image/gif,image/png">
		</fieldset>
		
		<fieldset>
			<label>Aktualny obrazek</label>
			<img src="../files/images/logos/'.$partner['image'].'" alt="old image">
		</fieldset>

		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>

		');
	}
}

?>