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
	@$page_id = (int) $_POST['news_id'];
	$page = $db->query('SELECT `id` FROM `news` WHERE `id` = '.$page_id);
	
	if($page->rowCount() == 0)
	{
		$errors[] = "News o podanym ID nie istnieje";
	}
	else
	{	
		$db->exec('DELETE FROM `news` WHERE `id` = '.$page_id.' LIMIT 1');
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');		
	}
}
else
{
	if( !empty($_PAGES['more'] ) )
	{
		@$page_id = (int) $_PAGES['more'];
		$page = $db->query('SELECT `id` FROM `news` WHERE `id` = '.$page_id);
		if( $page->rowCount() == 1)
		{
			$showAll = false;
		}
		else
		{
			$errors[] = "News o podanym ID nie została odnaleziona";
		}
	}
}


if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$tytul_pl = addslashes(trim(htmlspecialchars($_POST['tytul_pl'])));
	$tytul_en = addslashes(trim(htmlspecialchars($_POST['tytul_en'])));
	$head_pl = addslashes(trim(htmlspecialchars($_POST['head_pl'])));
	$head_en = addslashes(trim(htmlspecialchars($_POST['head_en'])));
	$foot_pl = addslashes(trim(htmlspecialchars($_POST['foot_pl'])));
	$foot_en = addslashes(trim(htmlspecialchars($_POST['foot_en'])));
	$date = addslashes(trim(htmlspecialchars($_POST['date'])));
	$position = addslashes(trim(htmlspecialchars($_POST['position'])));
	$html = addslashes(trim($_POST['html']));
	$content_pl = addslashes(trim(htmlspecialchars($_POST['content_pl'])));
	$content_en = addslashes(trim(htmlspecialchars($_POST['content_en'])));
	$status = addslashes(trim(htmlspecialchars($_POST['status'])));
	
	if( empty($tytul_pl) || empty($tytul_en) || empty($date) || empty($status) )
		$errors[] = "Niewszystkie wymagane pola zostały wypełnione";
	
	if( !empty($html) )
	{
		if(empty($head_pl) || empty($head_en))
		{
			$errors[] = "Niewszystkie pola z górnej części newsa zostały wypełnione";
		}
	}
	
	if( !empty($content_pl) || !empty($content_en) )
	{
		if(empty($content_pl) || empty($content_en) || empty($foot_pl) || empty($foot_en))
		{
			$errors[] = "Niewszystkie pola z dolnej części newsa zostały wypełnione";
		}
	}
	
	if( empty( $errors) )
	{
		$db->exec('UPDATE `news` SET
		`title-pl` = "'.$tytul_pl.'",
		`title-en` 	= "'.$tytul_en.'",
		`short1-pl` = "'.$head_pl.'",
		`short1-en` = "'.$head_en.'",
		`short2-pl` = "'.$foot_pl.'",
		`short2-en` = "'.$foot_en.'",
		`html` = "'.$html.'",
		`news-pl` = "'.$content_pl.'",
		`news-en` = "'.$content_en.'",
		`active` = "'.$status.'",
		`showDate` = "'.$date.'",
		`position`= "'.$position.'" WHERE `id` = "'.$_PAGES['more'].'"');
		$sent = true;
	}
	
}
else
{
	@$page_id = (int) $_PAGES['more'];
	$page = $db->query('SELECT * FROM `news` WHERE `id` = '.$page_id);
	$news = $page->fetch();
	$tytul_pl = $news['title-pl'];
	$tytul_en = $news['title-en'];
	$head_pl = $news['short1-pl'];
	$head_en = $news['short1-en'];
	$foot_pl = $news['short2-pl'];
	$foot_en = $news['short2-en'];
	$date = $news['showDate'];
	$position = $news['position'];
	$html = $news['html'];
	$content_pl = $news['news-pl'];
	$content_en = $news['news-en'];
	$status = $news['active'];
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
		<li><a href="/admin/news/add">Dodaj nowego newsa</a></li>
		<li><a href="/admin/news/edit">Zarządzaj newsami</a></li>
	</ul>');
}
else
{
	if($showAll == true)
	{
		echo('<p>Wybierz tego newsa, którego chcesz edytować poprzez kliknięcie odpowiedniego tytułu. Aby usunąć newsa należy kliknąć ikonkę kosza. 
		Miej na uwadze to, że operacja usunięcia jest nieodwracalna.</p>');
		
		$newses = $db->query('SELECT * FROM `news` ORDER BY `id` DESC');
		if($newses->rowCount() == 0)
		{
			echo('<h4 class="alert_info">Brak newsów!</h4>');
		}
		else
		{
			echo('<table class="tablesorter">
		
			<thead>
				<tr>
					<td style="width: 10%">ID</td>
					<td style="width: 65%">Nazwa</td>
					<td style="width: 20%">Autor</td>
					<td style="width: 5%">Usuń</td>
				</tr>
			</thead>');
			
			while($news = $newses->fetch())
			{
				echo('<tr>
				<td class="center">'.$news['id'].'</td>
				<td>
					<tt>PL:</tt> <a href="'.$_ACTION.'/'.$news['id'].'">'.$news['title-pl'].'</a><br>
					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$news['id'].'">'.$news['title-en'].'</a>
				</td>
				<td class="center">'.$login->getLoginById($news['userId']).'</td>
				<td class="center">
					<form action="'.$_ACTION.'/remove" class="post_message" method="post">
						<input type="hidden" name="news_id" value="'.$news['id'].'">
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
		echo('<form action="'.$_ACTION.'/'.$_PAGES['more'].'" method="post" class="post_message">
		<p>W polu <tt>Kod HTML</tt> można wprowadzić kod HTML. Część ta przeznaczona jest na film wideo. Dla obu wersji językowych jest taka sama. Nie trzeba wypełniać wszystkich pól.
		Obowiązkowe są pola oznaczone jedną gwiazdką <span class="star">*</span>, natomiast pozostałe gwiazdki oznaczają powiązane ze sobą pola, tzn. jeżeli wypełnisz pole <i>Kod HTML</i>
		to musisz również wypełnić pola oznaczone nagłówkami górnymi. Analogicznie z dolną cześcią newsa. W dolnej cześci newsa można stosować tagi <b>BBCode</b>.</p>
		<fieldset>
			<label for="input_1">Tytuł [PL] <span class="star">*</span></label>
			<input type="text" id="input_1" name="tytul_pl" value="'.stripslashes($tytul_pl).'" required>
		</fieldset>
		
		<fieldset>
			<label for="input_2">Tytuł [EN] <span class="star">*</span></label>
			<input type="text" id="input_2" name="tytul_en" value="'.stripslashes($tytul_en).'" required>
		</fieldset>
		
		<fieldset>
			<label for="input_3">Nagłówek górny [PL] <span class="star">**</span></label>
			<input type="text" id="input_3" name="head_pl" value="'.stripslashes($head_pl).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_4">Nagłówek górny [EN] <span class="star">**</span></label>
			<input type="text" id="input_4" name="head_en" value="'.stripslashes($head_en).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_9">Kod HTML (góra) <span class="star">**</span></label>
			<textarea rows="4" id="input_9" name="html" required>'.stripslashes($html).'</textarea>
		</fieldset>
		
		<fieldset>
			<label for="input_5">Nagłówek dolny [PL] <span class="star">***</span></label>
			<input type="text" id="input_5" name="foot_pl" value="'.stripslashes($foot_pl).'" >
		</fieldset>
		
		<fieldset>
			<label for="input_6">Nagłówek dolny [EN] <span class="star">***</span></label>
			<input type="text" id="input_6" name="foot_en" value="'.stripslashes($foot_en).'" >
		</fieldset>
			
		<fieldset>
			<label for="input_10">Treść dolna newsa [PL] <span class="star">***</span></label>
			<textarea rows="7" id="input_10" name="content_pl" >'.stripslashes($content_pl).'</textarea>
		</fieldset>
			
		<fieldset>
			<label for="input_11">Treść dolna newsa [EN] <span class="star">***</span></label>
			<textarea rows="7" id="input_11" name="content_en" >'.stripslashes($content_en).'</textarea>
		</fieldset>
		
		<fieldset>
			<label for="input_7">Czy pokazać datę? <span class="star">*</span></label>
			<select id="input_7" name="date" required>
				<option value="true"');if($date == "true"){echo(' selected');}echo('>Tak</option>
				<option value="false"');if($date == "false"){echo(' selected');}echo('>Nie</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_8">Miejsce wyświetlenia daty</label>
			<select id="input_8" name="position" >
				<option value="up"');if($position == "up"){echo(' selected');}echo('>Góra</option>
				<option value="down"');if($position == "down"){echo(' selected');}echo('>Dół</option>
			</select>
		</fieldset>
		
		<fieldset>
			<label for="input_12">Status <span class="star">*</span></label>
			<select id="input_12" name="status" required>
				<option value="true"');if($status == "true"){echo(' selected');}echo('>Aktywny</option>
				<option value="false"');if($status == "false"){echo(' selected');}echo('>Nieaktywny</option>
			</select>
		</fieldset>
		
		<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
		</form>');
	}
}

?>