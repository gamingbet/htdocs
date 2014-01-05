<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$showAll = true;
$errors = array();

if( $_PAGES['more'] == "move" )
{
	$panels = $db->query('SELECT * FROM `panels` WHERE `active` = "true" ORDER BY `lp` ASC');
	@$panel_id = (int) $_POST['panel_id'];
	$panel = $db->query('SELECT `id`, `lp` FROM `panels` WHERE `id` = '.$panel_id);
	
	if($panel->rowCount() == 0)
	{
		$errors[] = "Panel o podanym ID nie istnieje";
	}
	else
	{
		$panel = $panel->fetch();
		
		if($_POST['action'] == "up")
		{
			$new = $panel['lp']-1;
			if($new == 0)
			{
				$errors[] = "Tego elementu wyżej przenieść już nie można";
			}
		}
		else if($_POST['action'] == "bottom")
		{
			$new = $panel['lp']+1;
			if($new > $panels->rowCount())
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
				$db->exec('UPDATE `panels` SET `lp` = `lp`+1 WHERE `active` = "true" AND `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `panels` SET `lp` = `lp`-1 WHERE `active` = "true" AND `id` = '.$panel_id.' LIMIT 1');
			}
			else
			{
				$db->exec('UPDATE `panels` SET `lp` = `lp`-1 WHERE `active` = "true" AND `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `panels` SET `lp` = `lp`+1 WHERE `active` = "true" AND `id` = '.$panel_id.' LIMIT 1');
			}
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		}
	}	
}
else if( $_PAGES['more'] == "remove" )
{
	@$panel_id = (int) $_POST['panel_id'];
	$panel = $db->query('SELECT `id`, `lp`, `name`, `active` FROM `panels` WHERE `id` = '.$panel_id);
	
	if($panel->rowCount() == 0)
	{
		$errors[] = "Panel o podanym ID nie istnieje";
	}
	else
	{	
		if($panel_id == 1 )
				$errors[] = "Panel logowania nie może być usunięty";
		else
			{	
			$panels = $db->query('SELECT * FROM `panels` WHERE `active` = "true" ORDER BY `lp` ASC');
			$all = $panels->rowCount();
			$panel = $panel->fetch();
			$current = $panel['lp'];
			
			if($panel['active'] == "true")
			{
				for( $i = $current; $i <= $all; $i++)
				{
					$db->exec('UPDATE `panels` SET `lp` = `lp` - 1 WHERE `active` = "true" AND `lp` = '.$i.' LIMIT 1');
				}
			}
			
			@unlink('../panels/'.$panel['name'].'.php');
			$db->exec('DELETE FROM `langs` WHERE `category` = "panels" AND `label` = "'.$panel['name'].'" LIMIT 1');
			$db->exec('DELETE FROM `panels` WHERE `id` = '.$panel_id.' LIMIT 1');
			
			echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		}
	}
}
else
{
	if( !empty($_PAGES['more'] ) )
	{
		@$panel_id = (int) $_PAGES['more'];
		$partner = $db->query('SELECT `id` FROM `panels` WHERE `id` = '.$panel_id);
		if( $partner->rowCount() == 1)
		{
			if($panel_id == 1 )
				$errors[] = "Panel logowania nie może być edytowany";
			else
				$showAll = false;
		}
		else
		{
			$errors[] = "Panel o podanym ID nie został odnaleziony";
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
	echo('<p>Wybierz panel, który chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć panel należy kliknąć ikonkę kosza. 
	Miej na uwadze to, że operacja usunięcia jest nieodwracalna. Zmiana statusu nieaktywnego na aktywny powoduje przesunięcie tego obiektu na sam koniec listy.</p>');
		
	echo('<h4>Panele aktywne</h4>');
	$panels = $db->query('SELECT * FROM `panels` WHERE `active` = "true" ORDER BY `lp` ASC');
	if( $panels->rowCount() == 0 ) 
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
		
		while($panel = $panels->fetch() )
		{
			echo('<tr>
			<td class="center">'.$panel['lp'].'</td>
				<td><a href="'.$_ACTION.'/'.$panel['id'].'">'.$panel['name'].'</a>
			</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="panel_id" value="'.$panel['id'].'">
					<input type="hidden" name="action" value="up">');
				if( $panel['lp']-1 == 0 )
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
					<input type="hidden" name="panel_id" value="'.$panel['id'].'">
					<input type="hidden" name="action" value="bottom">');
				if( $panel['lp']+1 > $panels->rowCount() )
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
					<input type="hidden" name="panel_id" value="'.$panel['id'].'">
					<input type="image" src="images/icn_trash.png" title="Kosz">
				</form>
			</td>
		</tr>');
		}
		echo('</table>');
		
	echo('<h4>Nieaktywne</h4>');
	$noactive = $db->query('SELECT * FROM `panels` WHERE `active` = "false" ORDER BY `lp` ASC');			
	if( $noactive->rowCount() == 0 ) 
	{
		echo('<h4 class="alert_info">Brak nieaktywnych panelów!</h4>');
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
		
		while($panel = $noactive->fetch() )
		{
			echo('<tr>
				<td class="center">'.$panel['id'].'</td>
				<td><a href="'.$_ACTION.'/'.$panel['id'].'">'.$panel['name'].'</a>
				</td>
				<td class="center">
					<form action="'.$_ACTION.'/remove" class="post_message" method="post">
						<input type="hidden" name="panel_id" value="'.$panel['id'].'">
						<input type="image" src="images/icn_trash.png" title="Kosz">
					</form>
				</td>
			</tr>');
		}
		echo('</table>');
	}
	}
}
else
{
	$sent = false;
	$panel_id = (int) $_PAGES['more'];
	$panel = $db->query('SELECT * FROM `panels` WHERE `id` = '.$panel_id);
	$panel = $panel->fetch();
	
	if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
	{
		$uniq_id = $panel['name'];
		$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
		$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
		$typ = addslashes(trim(htmlspecialchars($_POST['typ'])));
		$content = addslashes(trim($_POST['content']));
		$status = addslashes(trim(htmlspecialchars($_POST['status'])));
		$file = &$_FILES['file'];
		$upload = false;

		if( empty($nazwa_pl) || empty($nazwa_en) )
			$errors[] = "Nie zostały wypełnione wymagane pola";
		
		if( !empty($file['tmp_name']) )
		{
			$files = fopen($file['tmp_name'], 'r');
			$bytes = bin2hex(fread($files, 3));
			
			if($bytes != '3c3f70') // '3c3f70' = php
				$errors[] = "Wgrywany plik nie jest plikiem PHP";
			
			fclose($files); 

			if(empty($errors))
			{
				$content = 'file:'.$uniq_id.'.php';
				$typ = '';
				$upload = true;
			}
		}
		else
		{
			if(empty($content))
				$errors[] = "Treść panelu nie może być pusta";
			else if($status == "")
				$content = 'file:'.$uniq_id.'.php';
		}
		
		if(empty($errors))
		{
			if($panel['type'] == '' && $panel['type'] != $typ)
			{
				unlink('../panels/'.$panel['name'].'.php');
			}
			if($upload == true)
			{
				move_uploaded_file($file['tmp_name'], '../panels/' . $uniq_id . '.php');
			}
			
			if( $panel['active'] == "false" && $status == "true")
			{
				$policz = $db->query('SELECT `lp` FROM `panels` WHERE `active` = "true" ORDER BY `lp` DESC LIMIT 1');
				$policz = $policz->fetch();	
				$lp = $policz['lp']+1;
			}
			else
			{
				$lp = $panel['lp'];
			}
			
			if($panel['active'] == "true" && $status == "false")
			{
				$active = $db->query('SELECT * FROM `panels` WHERE `active` = "true" ORDER BY `lp` ASC');
				$all = $active->rowCount();
				$current = $panel['lp'];
				
				for( $i = $current; $i <= $all; $i++)
				{
					$db->exec('UPDATE `panels` SET `lp` = `lp` - 1 WHERE `active` = "true" AND `lp` = '.$i.' LIMIT 1');
				}
				$lp = 0;
			}
			
			$lang = $db->exec('UPDATE `langs` SET `label-pl` = "'.$nazwa_pl.'", `label-en` = "'.$nazwa_en.'" WHERE `category` = "panels" AND `label` = "'.$panel['name'].'"');
			$change = $db->exec('UPDATE `panels` SET `lp` = "'.$lp.'", `content` = "'.$content.'", `active` = "'.$status.'", `type` = "'.$typ.'" WHERE `id` = "'.$panel_id.'" LIMIT 1');
			
			$sent = true;
		}
		
	}
	else
	{
		$labels = $db->query('SELECT * FROM `langs` WHERE `category` = "panels" AND `label` = "'.$panel['name'].'"');
		$labels = $labels->fetch();
		$uniq_id = $panel['name'];
		$nazwa_pl = $labels['label-pl'];
		$nazwa_en = $labels['label-en'];
		$typ = $panel['type'];
		$content = $panel['content'];
		$status = $panel['active'];
	}
	
	if($sent == true)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		echo('<ul>
			<li><a href="/">Przejdź do strony głównej</a></li>
			<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
			<li><a href="/admin/panels/add">Dodaj nowy panel</a></li>
			<li><a href="/admin/panels/edit">Zarządzaj panelami</a></li>
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
		<form action="'.$_ACTION.'/'.$panel_id.'" method="post" class="post_message" enctype="multipart/form-data">

		<p>Na tej stronie możesz edytować panel, który zostanie umieszczony po prawej stronie. Jeżeli posiadasz plik <tt>*.php</tt> z własnym modułem, to możesz 
		go wgrać w odpowiednim polu. Pamiętaj o tym, że jeśli prześlesz plik, to zawartość pola tekstowego zostanie pominięta, a typ automatycznie ustawi się na
		plik <u>*.php</u>. Jeżeli zmienisz typ z <i>plik *.php</i> to wgrywany wcześniej plik zostanie usunięty.</p>

		<fieldset>
			<label for="input_1">ID Panelu <span class="star">*</span></label>
			<input type="text" id="input_1" name="uniq_id" value="'.stripslashes($uniq_id).'" readolny disabled>
		</fieldset>

		<fieldset>
			<label for="input_2">Nazwa panelu [PL] <span class="star">*</span></label>
			<input type="text" id="input_2" name="nazwa_pl" value="'.stripslashes($nazwa_pl).'" required>
		</fieldset>

		<fieldset>
			<label for="input_3">Nazwa panelu [EN] <span class="star">*</span></label>
			<input type="text" id="input_3" name="nazwa_en" value="'.stripslashes($nazwa_en).'" required>
		</fieldset>

		<fieldset>
			<label for="input_4">Typ <span class="star">*</span></label>
			<select id="input_4" name="typ" required>
				<option value="html"');if($typ == "html"){echo(' selected');}echo('>Kod HTML</option>
				<option value="bbcode"');if($typ == "bbcode"){echo(' selected');}echo('>BBCode</option>
				<option value=""');if($typ == ""){echo(' selected');}echo('>Plik *.php</option>
			</select>
		</fieldset>

		<fieldset>
			<label for="input_5">Status <span class="star">*</span></label>
			<select id="input_5" name="status" required>
				<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
				<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
			</select>
		</fieldset>

		<fieldset>
			<label for="input_6">Treść panelu <span class="star">**</span></label>
			<textarea rows="20" id="input_6" name="content">'.stripslashes($content).'</textarea>
		</fieldset>

		<fieldset>
			<label for="input_7">Plik *.php <span class="star">**</span></label>
			<input type="file" id="input_7" name="file" accept="application/x-php">
		</fieldset>

		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

		</form>
		');
	}
}

?>