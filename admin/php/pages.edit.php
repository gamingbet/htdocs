<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();
$showAll = true;
$login = new Login;


if( $_PAGES['more'] == "remove" )
{
	@$page_id = (int) $_POST['page_id'];
	$page = $db->query('SELECT `id` FROM `pages` WHERE `id` = '.$page_id);
	
	if($page->rowCount() == 0)
	{
		$errors[] = "Strona o podanym ID nie istnieje";
	}
	else
	{	
		$db->exec('DELETE FROM `pages` WHERE `id` = '.$page_id.' LIMIT 1');
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');		
	}
}
else
{
	if( !empty($_PAGES['more'] ) )
	{
		@$page_id = (int) $_PAGES['more'];
		$page = $db->query('SELECT `id` FROM `pages` WHERE `id` = '.$page_id);
		if( $page->rowCount() == 1)
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "Strona o podanym ID nie została odnaleziona";
		}
	}
}


if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$url = addslashes(trim(htmlspecialchars($_POST['url'])));
	$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));
	$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));
	$access = addslashes(trim(htmlspecialchars($_POST['access'])));
	$type = addslashes(trim(htmlspecialchars($_POST['type'])));
	
	if($type == 'bbcode')
	{
		$content_pl = addslashes(trim(htmlspecialchars($_POST['content_pl'])));
		$content_en = addslashes(trim(htmlspecialchars($_POST['content_en'])));
	}
	else
	{
		$content_pl = addslashes(trim($_POST['content_pl']));
		$content_en = addslashes(trim($_POST['content_en']));
	}
	
	if( empty( $url ) || empty( $tytul_pl ) || empty( $tytul_en ) || empty( $access ) || empty( $type ) || empty( $content_pl ) || empty( $content_en ) )
		$errors[] = "Nie zostały wypełnione wszystkie pola";
	
	if(in_array($url, $_FILES))
		$errors[] = "URL pliku zawiera jedną z niedozwolonych nazw";
	
	if(!preg_match("#^[a-zA-Z0-9_-]+$#", $url))
		$errors[] = "URL zawiera niedozwolone znaki";
	
	if($_POST['old_url'] != $url)
	{
		$busy_url = $db->query('SELECT `id` FROM `pages` WHERE `short-url` = "'.$url.'"');
		if($busy_url->rowCount() == 1)
			$errors[] = "Strona o takim adresie url jest już zajęta";
	}
	
	if( empty( $errors) )
	{
		$db->exec('UPDATE `pages` SET `title-pl` = "'.$tytul_pl.'", `title-en` = "'.$tytul_en.'", `short-url` = "'.$url.'", `access` = "'.$access.'", 
			`content-pl` = "'.$content_pl.'", `content-en` = "'.$content_en.'", `type` = "'.$type.'" WHERE `id` = "'.$_PAGES['more'].'" LIMIT 1');
		$sent = true;
	}
	
}
else
{
	@$page_id = (int) $_PAGES['more'];
	$page = $db->query('SELECT * FROM `pages` WHERE `id` = '.$page_id);
	$page = $page->fetch();
	$tytul_pl = $page['title-pl'];
	$tytul_en = $page['title-en'];
	$url = $page['short-url'];
	$content_pl = $page['content-pl'];
	$content_en = $page['content-en'];
	$type = $page['type'];
	$access = $page['access'];
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

if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	echo('<ul>
		<li><a href="/">Przejdź do strony głównej</a></li>
		<li><a href="/admin/">Wróć do strony głównej panelu administracyjnego</a></li>
		<li><a href="/admin/pages/add">Dodaj nową stronę</a></li>
		<li><a href="/admin/pages/edit">Zarządzaj stronami</a></li>
	</ul>');
}
else
{
	if($showAll == true)
	{
		echo('<p>Wybierz stronę, którą chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć stronę należy kliknąć ikonkę kosza. 
		Miej na uwadze to, że operacja usunięcia jest nieodwracalna.</p>');
		
		$pages = $db->query('SELECT * FROM `pages` ORDER BY `id` ASC');
		if($pages->rowCount() == 0)
		{
			echo('<h4 class="alert_info">Brak własnych stron!</h4>');
		}
		else
		{
			echo('<table class="tablesorter">
		
			<thead>
				<tr>
					<td style="width: 10%">ID</td>
					<td style="width: 50%">Nazwa</td>
					<td style="width: 15%">URL</td>
					<td style="width: 20%">Autor</td>
					<td style="width: 5%">Usuń</td>
				</tr>
			</thead>');
			
			while($page = $pages->fetch())
			{
				echo('<tr>
				<td class="center">'.$page['id'].'</td>
				<td>
					<tt>PL:</tt> <a href="'.$_ACTION.'/'.$page['id'].'">'.$page['title-pl'].'</a><br>
					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$page['id'].'">'.$page['title-en'].'</a>
				</td>
				<td class="center">'.$page['short-url'].'</td>
				<td class="center">'.$login->getLoginById($page['userId']).'</td>
				<td class="center">
					<form action="'.$_ACTION.'/remove" class="post_message" method="post">
						<input type="hidden" name="page_id" value="'.$page['id'].'">
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
		echo('<p>Pole URL może zawierać tylko i wyłącznie znaki A-Z (a-z), 0-9, pauza (-) oraz podkreślnik (_). Poniżej wypisane są nazwy, których w tym polu wpisać nie można.</p><p class="files">');
		foreach($_FILES as $file)
		{
			echo('<span class="file">'.(($url==$file)?'<b>'.$file.'</b>':$file).'</span> ');
		}
		echo('</p>
		<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
		
		<fieldset>
			<label for="input_1">URL [A-Z, 0-9, -, _] <span class="star">*</span></label>
			<input type="text" id="input_1" name="url" value="'.stripslashes($url).'" required>
			<input type="hidden" name="old_url" value="'.stripslashes($url).'">
		</fieldset>
		
		<fieldset>
			<label for="input_2">Tytuł [PL] <span class="star">*</span></label>
			<input type="text" id="input_2" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>
		</fieldset>
		
		<fieldset>
			<label for="input_3">Tytuł [EN] <span class="star">*</span></label>
			<input type="text" id="input_3" name="tytul_en" value="'.stripslashes($tytul_en).'" required>
		</fieldset>
		
		<fieldset>
			<label for="input_4">Dostęp <span class="star">*</span></label>
			<select id="input_4" name="access" required>
				<option value="all"');if($access == "all"){echo(' selected');}echo('>Wszyscy</option>
				<option value="users"');if($access == "users"){echo(' selected');}echo('>Użytkownicy zalogowani</option>
				<option value="admins"');if($access == "admins"){echo(' selected');}echo('>Tylko administratorzy</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_5">Typ formatowania <span class="star">*</span></label>
			<select id="input_5" name="type" required>
				<option value="bbcode"');if($type == "bbcode"){echo(' selected');}echo('>BBCode</option>
				<option value="html"');if($type == "html"){echo(' selected');}echo('>HTML</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_6">Treść [PL] <span class="star">*</span></label>
			<textarea rows="25" id="input_6" name="content_pl" required>'.stripslashes($content_pl).'</textarea>
		</fieldset>

		<fieldset>
			<label for="input_7">Treść [EN] <span class="star">*</span></label>
			<textarea rows="25" id="input_7" name="content_en" required>'.stripslashes($content_en).'</textarea>
		</fieldset>
		
		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		</form>');
	}
}

?>