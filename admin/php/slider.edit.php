<?php

if( !defined("__ADMIN__") || !defined("__LOAD__") )

{

	exit();

}



$showAll = true;

$errors = array();



if( $_PAGES['more'] == "move" )

{

	$active = $db->query('SELECT * FROM `slider` WHERE `active` = "true" ORDER BY `lp` ASC');

	@$slide_id = (int) $_POST['slide_id'];

	$slider = $db->query('SELECT `id`, `lp` FROM `slider` WHERE `id` = '.$slide_id);

	

	if($slider->rowCount() == 0)

	{

		$errors[] = "Slider o podanym ID nie istnieje";

	}

	else

	{

		$slide = $slider->fetch();

		

		if($_POST['action'] == "up")

		{

			$new = $slide['lp']-1;

			if($new == 0)

			{

				$errors[] = "Tego elementu wyżej przenieść już nie można";

			}

		}

		else if($_POST['action'] == "bottom")

		{

			$new = $slide['lp']+1;

			if($new > $active->rowCount())

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

				$db->exec('UPDATE `slider` SET `lp` = `lp`+1 WHERE `active` = "true" AND `lp` = '.$new.' LIMIT 1');

				$db->exec('UPDATE `slider` SET `lp` = `lp`-1 WHERE `active` = "true" AND `id` = '.$slide_id.' LIMIT 1');

			}

			else

			{

				$db->exec('UPDATE `slider` SET `lp` = `lp`-1 WHERE `active` = "true" AND `lp` = '.$new.' LIMIT 1');

				$db->exec('UPDATE `slider` SET `lp` = `lp`+1 WHERE `active` = "true" AND `id` = '.$slide_id.' LIMIT 1');

			}

			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

		}

	}	

}

else if( $_PAGES['more'] == "remove" )

{

	@$slide_id = (int) $_POST['slide_id'];

	$slider = $db->query('SELECT `id`, `lp`, `image`, `active` FROM `slider` WHERE `id` = '.$slide_id);

	

	if($slider->rowCount() == 0)

	{

		$errors[] = "Slider o podanym ID nie istnieje";

	}

	else

	{		

		$active = $db->query('SELECT * FROM `slider` WHERE `active` = "true" ORDER BY `lp` ASC');

		$all = $active->rowCount();

		$slider = $slider->fetch();

		

		if($slider['status'] == "true")

		{

			$current = $slider['lp'];

					

			for( $i = $current; $i <= $all; $i++)

			{

				$db->exec('UPDATE `slider` SET `lp` = `lp` - 1 WHERE `active` = "true" AND `lp` = '.$i.' LIMIT 1');

			}

		}

		

		unlink('../files/images/slider/'.$slider['image']);

		$db->exec('DELETE FROM `slider` WHERE `id` = '.$slide_id.' LIMIT 1');

		

		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

	}

}

else

{

	if( !empty($_PAGES['more'] ) )

	{

		@$slide_id = (int) $_PAGES['more'];

		$slider = $db->query('SELECT `id` FROM `slider` WHERE `id` = '.$slide_id);

		if( $slider->rowCount() == 1)

		{

			$showAll = false;

		}

		else

		{

			$errors[] = "Slider o podanym ID nie został odnaleziony";

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



// pokaz wszystkie

if( $showAll == true )

{

	echo('<p>Wybierz stronę slidera, którą chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć stronę slidera, należy kliknąć ikonkę kosza. 

	Miej na uwadze to, że operacja usunięcia jest nieodwracalna. Zmiana statusu nieaktywnego na aktywny powoduje przesunięcie tego obiektu na sam koniec listy.</p>');

		

	echo('<h4>Aktywne</h4>');

	$active = $db->query('SELECT * FROM `slider` WHERE `active` = "true" ORDER BY `lp` ASC');

	if( $active->rowCount() == 0 ) 

	{

		echo('<h4 class="alert_info">Brak aktywnych stron w sliderze!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		

		<thead>

			<tr>

				<td style="width: 10%">LP</td>

				<td style="width: 70%">Tytuł</td>

				<td style="width: 15%" colspan="2">Przesuń</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($slide = $active->fetch() )

		{

			echo('<tr>

				<td class="center">'.$slide['lp'].'</td>

				<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$slide['id'].'">'.$slide['name-pl'].'</a><br>

					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$slide['id'].'">'.$slide['name-en'].'</a>

				</td>

				<td>

					<form action="'.$_ACTION.'/move" class="post_message" method="post">

						<input type="hidden" name="slide_id" value="'.$slide['id'].'">

						<input type="hidden" name="action" value="up">');

					if( $slide['lp']-1 == 0 )

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

						<input type="hidden" name="slide_id" value="'.$slide['id'].'">

						<input type="hidden" name="action" value="bottom">');

					if( $slide['lp']+1 > $active->rowCount() )

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

						<input type="hidden" name="slide_id" value="'.$slide['id'].'">

						<input type="image" src="images/icn_trash.png" title="Kosz">

					</form>

				</td>

			</tr>');

		}

		echo('</table>');

	}

	echo('<h4>Nieaktywne</h4>');

	$noactive = $db->query('SELECT * FROM `slider` WHERE `active` = "false" ORDER BY `lp` ASC');			

	if( $noactive->rowCount() == 0 ) 

	{

		echo('<h4 class="alert_info">Brak nieaktywnych stron w sliderze!</h4>');

	}

	else

	{

		echo('<table class="tablesorter">

		

		<thead>

			<tr>

				<td style="width: 10%">ID</td>

				<td style="width: 70%">Tytuł</td>

				<td style="width: 5%">Usuń</td>

			</tr>

		</thead>');

		

		while($slide = $noactive->fetch() )

		{

			echo('<tr>

				<td class="center">'.$slide['id'].'</td>

				<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$slide['id'].'">'.$slide['name-pl'].'</a><br>

					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$slide['id'].'">'.$slide['name-en'].'</a>

				</td>

				<td class="center">

					<form action="'.$_ACTION.'/remove" class="post_message" method="post">

						<input type="hidden" name="slide_id" value="'.$slide['id'].'">

						<input type="image" src="images/icn_trash.png" title="Kosz">

					</form>

				</td>

			</tr>');

		}

		echo('</table>');

	}

}

// wybrano pojedynczy

else

{

	$sent = false;

	$slide_id = (int) $_PAGES['more'];

	$slider = $db->query('SELECT * FROM `slider` WHERE `id` = '.$slide_id);

	$slide = $slider->fetch();

	

	if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )

	{

		$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));

		$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));

		$opis_pl = addslashes(trim(htmlspecialchars($_POST['opis_pl'])));

		$opis_en = addslashes(trim(htmlspecialchars($_POST['opis_en'])));

		$status = addslashes(trim(htmlspecialchars($_POST['status'])));

		$url = addslashes(trim(htmlspecialchars($_POST['url'])));

		$image = &$_FILES['images'];

		$old_image = addslashes(trim(htmlspecialchars($_POST['old_image'])));

		$access = array('image/gif', 'image/png', 'image/jpeg');

		

		if( empty($tytul_pl) || empty($tytul_en) || empty($opis_pl) || empty($opis_en) || empty($status) || empty($url) )

			$errors[] = "Nie zostały wypełnione wymagane pola";

		

		if( !empty($image['tmp_name']) )

		{

			list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

			

			if(!in_array($image['type'], $access))

				$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";

			
			/*
			if($width > 455 || $height > 198)

				$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 445x198 [px]";*/

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

				//move_uploaded_file($image['tmp_name'], '../files/images/slider/' . $filename);

				require 'SimpleImage.php';

				$img = new abeautifulsite\SimpleImage($image['tmp_name']);
				
				$img->resize(438, 247)->save('../files/images/slider/' . $filename);

				unlink('../files/images/slider/'.$old_image);

			}

			else

			{

				$filename = $old_image;

			}

			

			// jesli zmieniamy z nieaktywnego na aktywny, to ustaw na samym dole

			if( $slide['active'] == "false" && $status == "true")

			{

				$policz = $db->query('SELECT `lp` FROM `slider` WHERE `active` = "true" ORDER BY `lp` DESC LIMIT 1');

				$policz = $policz->fetch();	

				$lp = $policz['lp']+1;

			}

			else

			{

				$lp = $slide['lp'];

			}

			

			// jesli zmieniamy status na false, to wszystkie ponizej przesuwamy w gore

			if($slide['active'] == "true" && $status == "false")

			{

				$active = $db->query('SELECT * FROM `slider` WHERE `active` = "true" ORDER BY `lp` ASC');

				$all = $active->rowCount();

				$current = $slide['lp'];

				

				for( $i = $current; $i <= $all; $i++)

				{

					$db->exec('UPDATE `slider` SET `lp` = `lp` - 1 WHERE `lp` = '.$i.' LIMIT 1');

				}

			}

			

			$change = $db->prepare('UPDATE `slider` SET `lp` = :lp, `name-pl` = :namepl, `name-en` = :nameen, `image` = :image, `description-pl` = :descriptionpl,

				`description-en` = :descriptionen, `url` = :url, `active` = :active WHERE `id` = :id LIMIT 1');

			$change->bindValue(':lp', $lp, PDO::PARAM_INT);

			$change->bindValue(':namepl', $tytul_pl, PDO::PARAM_STR);

			$change->bindValue(':nameen', $tytul_en, PDO::PARAM_STR);

			$change->bindValue(':image', $filename, PDO::PARAM_STR);

			$change->bindValue(':descriptionpl', $opis_pl, PDO::PARAM_STR);

			$change->bindValue(':descriptionen', $opis_en, PDO::PARAM_STR);

			$change->bindValue(':url', $url, PDO::PARAM_STR);

			$change->bindValue(':active', $status, PDO::PARAM_STR);

			$change->bindValue(':id', $slide_id, PDO::PARAM_STR);

			$change->execute();

			$sent = true;

		}		

	}

	else

	{

		$tytul_pl = $slide['name-pl'];

		$tytul_en = $slide['name-en'];

		$opis_pl = $slide['description-pl'];

		$opis_en = $slide['description-en'];

		$status = $slide['active'];

		$url = $slide['url'];

	}

	

	if($sent == true)

	{

		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');

		echo('<ul>

			<li><a href="/">Przejdź do strony głównej</a></li>

			<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>

			<li><a href="/admin/slider/add">Dodaj nową stronę do slidera</a></li>

			<li><a href="/admin/slider/edit">Zarządzaj stronami slidera</a></li>

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

		<form action="'.$_ACTION.'/'.$slide_id.'" method="post" class="post_message" enctype="multipart/form-data">



		<fieldset>

			<label for="input_1">Tytuł [PL] <span class="star">*</span></label>

			<input type="text" id="input_1" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>

		</fieldset>



		<fieldset>

			<label for="input_2">Opis [PL] <span class="star">*</span></label>

			<textarea rows="3" id="input_2" name="opis_pl" required>'.stripslashes($opis_pl).'</textarea>

		</fieldset>



		<fieldset>

			<label for="input_3">Tytuł [EN] <span class="star">*</span></label>

			<input type="text" id="input_3" name="tytul_en" value="'.stripslashes($tytul_en).'" required>

		</fieldset>



		<fieldset>

			<label for="input_4">Opis [EN] <span class="star">*</span></label>

			<textarea rows="3" id="input_4" name="opis_en" required>'.stripslashes($opis_en).'</textarea>

		</fieldset>



		<fieldset>

			<label for="input_5">Status <span class="star">*</span></label>

			<select id="input_5" name="status" required>

				<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>

				<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>

			</select>

		</fieldset>



		<fieldset>

			<label for="input_6">URL <span class="star">*</span></label>

			<input type="text" id="input_6" name="url" value="'.stripslashes($url).'" required>

		</fieldset>



		<fieldset>

			<label for="input_7">Nowy obrazek</label>

			<input type="hidden" name="old_image" value="'.$slide['image'].'">

			<input type="file" id="input_7" name="images" accept="image/jpeg,image/gif,image/png">			

		</fieldset>

		

		<fieldset>

			<label>Aktualny obrazek</label>

			<img src="../files/images/slider/'.$slide['image'].'" alt="old image">

		</fieldset>



		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">



		</form>



		');

	}

}



?>