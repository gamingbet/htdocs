<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

// 0 - nic
// 1 - nowy link
// 2 - edycja linku
// 3 - usuniecie linku
// 4 - przesuniecie

$sent = false;
$menu = false;
$what = 0;
$errors = array();
$showAll = true;

if( $_PAGES['more'] == "move" )
{
	$position = addslashes(trim(htmlspecialchars($_POST['position'])));
	$links = $db->query('SELECT * FROM `menu` WHERE `position` = "'.$position.'" ORDER BY `lp` ASC');
	@$link_id = (int) $_POST['menu_id'];
	$link = $db->query('SELECT `id`, `lp` FROM `menu` WHERE `position` = "'.$position.'" AND `id` = '.$link_id);
	if($link->rowCount() == 0)
	{
		$errors[] = "Link o podanym ID nie istnieje";
	}
	else
	{
		$link = $link->fetch();
		
		if($_POST['action'] == "up")
		{
			$new = $link['lp']-1;
			if($new == 0)
			{
				$errors[] = "Tego elementu wyżej przenieść już nie można";
			}
		}
		else if($_POST['action'] == "bottom")
		{
			$new = $link['lp']+1;
			if($new > $links->rowCount())
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
				$db->exec('UPDATE `menu` SET `lp` = `lp`+1 WHERE `position` = "'.$position.'" AND `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `menu` SET `lp` = `lp`-1 WHERE `position` = "'.$position.'" AND `id` = '.$link_id.' LIMIT 1');
			}
			else
			{
				$db->exec('UPDATE `menu` SET `lp` = `lp`-1 WHERE `position` = "'.$position.'" AND `lp` = '.$new.' LIMIT 1');
				$db->exec('UPDATE `menu` SET `lp` = `lp`+1 WHERE `position` = "'.$position.'" AND `id` = '.$link_id.' LIMIT 1');
			}
			$sent = true;
			$what = 4;
		}
	}
}
else if( $_PAGES['more'] == "remove" )
{
	@$link_id = (int) $_POST['menu_id'];
	$link = $db->query('SELECT `id`, `lp`, `name` FROM `menu` WHERE `id` = '.$link_id);
	
	if($link->rowCount() == 0)
	{
		$errors[] = "Link o podanym ID nie istnieje";
	}
	else
	{		
		$position = addslashes(trim(htmlspecialchars($_POST['position'])));
		$links = $db->query('SELECT * FROM `menu` WHERE `position` = "'.$position.'" ORDER BY `lp` ASC');
		
		$all = $links->rowCount();
		$link = $link->fetch();
		$current = $link['lp'];
				
		for( $i = $current; $i <= $all; $i++)
		{
			$db->exec('UPDATE `menu` SET `lp` = `lp` - 1 WHERE `position` = "'.$position.'" AND `lp` = '.$i.' LIMIT 1');
		}
		
		if($position == 'footer')
			$category = 'footer';
		else
			$category = 'menu';
			
		$db->exec('DELETE FROM `langs` WHERE `label` = "'.$link['name'].'" AND `category` = "'.$category.'" LIMIT 1');
		$db->exec('DELETE FROM `menu` WHERE `id` = "'.$link_id.'" LIMIT 1');
		$sent = true;
		$what = 3;
	}
}
else
{
	if( !empty($_PAGES['more'] ) )
	{
		@$link_id = (int) $_PAGES['more'];
		$link = $db->query('SELECT `id`, `position` FROM `menu` WHERE `id` = '.$link_id);
		if( $link->rowCount() == 1)
		{
			$link = $link->fetch();
			$showAll = false;
			
			if($link['position'] != 'footer')
				$menu = true;
		}
		else
		{
			$errors[] = "Link o podanym ID nie został odnaleziony";
		}
	}
}

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	if($_POST['action'] == "add_new_link")
	{
		$uniqid = addslashes(trim(htmlspecialchars($_POST['uniqid'])));
		$url = addslashes(trim(htmlspecialchars($_POST['url'])));
		$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
		$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
		$status = addslashes(trim(htmlspecialchars($_POST['status'])));
		
		if( empty($uniqid) || empty($url) || empty($nazwa_pl) || empty($nazwa_en) || empty($status) )
			$errors[] = "Niewszystkie pola zostały wypełnione";
		
		$check_id = $db->query('SELECT `id` FROM `menu` WHERE `name` = "'.$uniqid.'"');
		if($check_id->rowCount() == 1)
			$errors[] = "Wybrane ID jest już używane";
			
		if(empty($errors))
		{
			$position = $db->query('SELECT `id` FROM `menu` WHERE `position` = "'.$status.'"');
			if($status == 'footer')
				$category = 'footer';
			else
				$category = 'menu';
				
			$what = 1;
			$sent = true;
			$db->query('INSERT INTO `langs` VALUES(NULL, "'.$category.'", "'.$uniqid.'", "'.$nazwa_pl.'", "'.$nazwa_en.'")');
			$db->query('INSERT INTO `menu` VALUES(NULL, "'.$status.'", "'.($position->rowCount()+1).'", "'.$uniqid.'", "'.$url.'")');
		}
	}
	else if($_POST['action'] == "edit_menu_link" || $_POST['action'] == "edit_footer_link")
	{
		$url = addslashes(trim(htmlspecialchars($_POST['url'])));
		$nazwa_pl = addslashes(trim(htmlspecialchars($_POST['nazwa_pl'])));
		$nazwa_en = addslashes(trim(htmlspecialchars($_POST['nazwa_en'])));
		$status = addslashes(trim(htmlspecialchars($_POST['status'])));
		
		if( empty($url) || empty($nazwa_pl) || empty($nazwa_en) || empty($status) )
			$errors[] = "Niewszystkie pola zostały wypełnione";
		
		if(empty($errors))
		{
			@$link_id = (int) $_PAGES['more'];
			$link = $db->query('SELECT * FROM `menu` WHERE `id` = '.$link_id);
			$link = $link->fetch();
			
			if($status == 'footer')
				$category = 'footer';
			else
				$category = 'menu';
			
			if($status != $link['position'])
			{
				$position = $db->query('SELECT `id` FROM `menu` WHERE `position` = "'.$status.'"');
				$position = $position->rowCount() + 1;				
			}
			else
			{
				$position = $link['lp'];
			}
			$db->exec('UPDATE `menu` SET `link` = "'.$url.'", `lp` = "'.$position.'", `position` = "'.$status.'" WHERE `id` = "'.$link_id.'" LIMIT 1');
			$db->exec('UPDATE langs SET `label-pl` = "'.$nazwa_pl.'", `label-en` = "'.$nazwa_en.'" WHERE `category` = "'.@$category.'" AND `label` = "'.$link['name'].'" LIMIT 1');
			$what = 2;
			$sent = true;
		}
	}
	
}
else
{
	$uniqid = uniqid();
	$url = NULL;
	$nazwa_pl = NULL;
	$nazwa_en = NULL;
	$status = 'before';
}

if($sent == true)
{
	if($what == 1)
	{
		echo('<h4 class="alert_success">Nowy link został dodany do menu!</h4>');
		$uniqid = uniqid();
		$url = NULL;
		$nazwa_pl = NULL;
		$nazwa_en = NULL;
		$status = 'before';
	}
	else if($what == 2)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
		$uniqid = uniqid();
		$url = NULL;
		$nazwa_pl = NULL;
		$nazwa_en = NULL;
		$status = 'before';
		$showAll = true;
		$menu = false;
	}
	else if($what == 3)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
	}
	else if($what == 4)
	{
		echo('<h4 class="alert_success">Operacja zakończyła się powodzeniem!</h4>');
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

echo('<h4>Menu</h4>

<ul class="tabs">
<li><a href="#tab-new">Dodaj nowy link</a></li>
<li><a href="#tab-menu">Menu poziome (header)</a></li>
<li><a href="#tab-footer">Menu w stopce (footer)</a></li>
</ul>

<div style="clear: both" class="tab_container">

<div id="tab-new" class="tab_content">
	<h5>Utworz nowy link w menu</h5>
	<form action="'.$_ACTION.'" method="post">
	<input type="hidden" name="action" value="add_new_link">
	
	<fieldset>
		<label for="input_1">ID Menu <span class="star">*</span></label>
		<input type="text" id="input_1" name="uniqid" value="'.$uniqid.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_2">URL <span class="star">*</span></label>
		<input type="text" id="input_2" name="url" value="'.$url.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Nazwa [PL] <span class="star">*</span></label>
		<input type="text" id="input_3" name="nazwa_pl" value="'.$nazwa_pl.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Nazwa [EN] <span class="star">*</span></label>
		<input type="text" id="input_4" name="nazwa_en" value="'.$nazwa_en.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_5">Pozycja <span class="star">*</span></label>
		<select id="input_5" name="status" required>
			<option value="before"');if($status == "before"){echo(' selected');}echo('>Przed listą gier</option>
			<option value="after"');if($status == "after"){echo(' selected');}echo('>Po liście gier</option>
			<option value="footer"');if($status == "footer"){echo(' selected');}echo('>Stopka</option>
		</select>
	</fieldset>
	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>
</div>

<div id="tab-menu" class="tab_content">');

if($showAll == false && $menu == true)
{
	@$link_id = (int) $_PAGES['more'];
	$link = $db->query('SELECT * FROM `menu` WHERE `id` = '.$link_id);
	$link = $link->fetch();
	if( !(isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie"))
	{
		$uniqid = $link['name'];
		$url = $link['link'];
		$status = $link['position'];
		
		$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "menu" AND `label` = "'.$link['name'].'"');
		$lang = $lang->fetch();
		
		$nazwa_pl = $lang['label-pl'];
		$nazwa_en = $lang['label-en'];
	}
	
	echo('<form action="'.$_ACTION.'/'.$link_id.'" method="post">
	<input type="hidden" name="action" value="edit_menu_link">
	
	<fieldset>
		<label for="input_1">ID Menu <span class="star">*</span></label>
		<input type="text" id="input_1" name="uniqid" value="'.$uniqid.'" disabled>
	</fieldset>
	
	<fieldset>
		<label for="input_2">URL <span class="star">*</span></label>
		<input type="text" id="input_2" name="url" value="'.$url.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Nazwa [PL] <span class="star">*</span></label>
		<input type="text" id="input_3" name="nazwa_pl" value="'.$nazwa_pl.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Nazwa [EN] <span class="star">*</span></label>
		<input type="text" id="input_4" name="nazwa_en" value="'.$nazwa_en.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_5">Pozycja <span class="star">*</span></label>
		<select id="input_5" name="status" required>
			<option value="before"');if($status == "before"){echo(' selected');}echo('>Przed listą gier</option>
			<option value="after"');if($status == "after"){echo(' selected');}echo('>Po liście gier</option>
			<option value="footer"');if($status == "footer"){echo(' selected');}echo('>Stopka</option>
		</select>
	</fieldset>
	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>');
}
else
{
	echo('<p>Kliknięcie w nagłówek otworzy menu edycji poszczególnego linku. Aby usunąć link należy kliknąć ikonkę kosza. 
	Miej na uwadze to, że operacja usunięcia jest nieodwracalna.</p>');
	$before = $db->query('SELECT * FROM `menu` WHERE `position` = "before" ORDER BY `lp` ASC');
	if($before->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak linków w menu przed grami!</h4>');
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
		while( $link = $before->fetch() )
		{
			$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "menu" AND `label` = "'.$link['name'].'"');
			$lang = $lang->fetch();
			echo('<tr>
				<td>'.$link['lp'].'</td>
				<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-pl'].'</a><br>
					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-en'].'</a>
				</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="position" value="before">
					<input type="hidden" name="menu_id" value="'.$link['id'].'">
					<input type="hidden" name="action" value="up">');
				if( $link['lp']-1 == 0 )
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
						<input type="hidden" name="position" value="before">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="hidden" name="action" value="bottom">');
					if( $link['lp']+1 > $before->rowCount() )
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
						<input type="hidden" name="position" value="before">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="image" src="images/icn_trash.png" title="Kosz">
					</form>
				</td>
			</tr>');
		}
		echo('</table>');
	}
	
	echo('<h4 class="alert_warning">Tutaj znajduje się menu z grami. Zarządzać nim możesz w zakładce Gry.</h4><br>');
	
	$after = $db->query('SELECT * FROM `menu` WHERE `position` = "after" ORDER BY `lp` ASC');
	if($after->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak linków w menu po grach!</h4>');
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
		while( $link = $after->fetch() )
		{
			$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "menu" AND `label` = "'.$link['name'].'"');
			$lang = $lang->fetch();
			echo('<tr>
				<td>'.$link['lp'].'</td>
				<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-pl'].'</a><br>
					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-en'].'</a>
				</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="position" value="after">
					<input type="hidden" name="menu_id" value="'.$link['id'].'">
					<input type="hidden" name="action" value="up">');
				if( $link['lp']-1 == 0 )
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
						<input type="hidden" name="position" value="after">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="hidden" name="action" value="bottom">');
					if( $link['lp']+1 > $after->rowCount() )
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
						<input type="hidden" name="position" value="after">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="image" src="images/icn_trash.png" title="Kosz">
					</form>
				</td>
			</tr>');
		}
		echo('</table>');
	}
}

echo('</div>

<div id="tab-footer" class="tab_content">');

if($showAll == false && $menu == false)
{
	@$link_id = (int) $_PAGES['more'];
	$link = $db->query('SELECT * FROM `menu` WHERE `id` = '.$link_id);
	$link = $link->fetch();
	if( !(isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie"))
	{
		$uniqid = $link['name'];
		$url = $link['link'];
		$status = $link['position'];
		
		$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "footer" AND `label` = "'.$link['name'].'"');
		$lang = $lang->fetch();
		
		$nazwa_pl = $lang['label-pl'];
		$nazwa_en = $lang['label-en'];
	}
	
	echo('<form action="'.$_ACTION.'/'.$link_id.'" method="post">
	<input type="hidden" name="action" value="edit_footer_link">
	
	<fieldset>
		<label for="input_1">ID Menu <span class="star">*</span></label>
		<input type="text" id="input_1" name="uniqid" value="'.$uniqid.'" disabled>
	</fieldset>
	
	<fieldset>
		<label for="input_2">URL <span class="star">*</span></label>
		<input type="text" id="input_2" name="url" value="'.$url.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_3">Nazwa [PL] <span class="star">*</span></label>
		<input type="text" id="input_3" name="nazwa_pl" value="'.$nazwa_pl.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_4">Nazwa [EN] <span class="star">*</span></label>
		<input type="text" id="input_4" name="nazwa_en" value="'.$nazwa_en.'" required>
	</fieldset>
	
	<fieldset>
		<label for="input_5">Pozycja <span class="star">*</span></label>
		<select id="input_5" name="status" required>
			<option value="before"');if($status == "before"){echo(' selected');}echo('>Przed listą gier</option>
			<option value="after"');if($status == "after"){echo(' selected');}echo('>Po liście gier</option>
			<option value="footer"');if($status == "footer"){echo(' selected');}echo('>Stopka</option>
		</select>
	</fieldset>
	
	<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
	</form>');
}
else
{
	$footer = $db->query('SELECT * FROM `menu` WHERE `position` = "footer" ORDER BY `lp` ASC');
	if($footer->rowCount() == 0)
	{
		echo('<h4 class="alert_info">Brak linków w menu w stopce!</h4>');
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
		while( $link = $footer->fetch() )
		{
			$lang = $db->query('SELECT * FROM `langs` WHERE `category` = "footer" AND `label` = "'.$link['name'].'"');
			$lang = $lang->fetch();
			echo('<tr>
				<td>'.$link['lp'].'</td>
				<td><tt>PL:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-pl'].'</a><br>
					<tt>EN:</tt> <a href="'.$_ACTION.'/'.$link['id'].'">'.$lang['label-en'].'</a>
				</td>
			<td>
				<form action="'.$_ACTION.'/move" class="post_message" method="post">
					<input type="hidden" name="position" value="footer">
					<input type="hidden" name="menu_id" value="'.$link['id'].'">
					<input type="hidden" name="action" value="up">');
				if( $link['lp']-1 == 0 )
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
						<input type="hidden" name="position" value="footer">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="hidden" name="action" value="bottom">');
					if( $link['lp']+1 > $footer->rowCount() )
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
						<input type="hidden" name="position" value="footer">
						<input type="hidden" name="menu_id" value="'.$link['id'].'">
						<input type="image" src="images/icn_trash.png" title="Kosz">
					</form>
				</td>
			</tr>');
		}
		echo('</table>');
	}
}

echo('</div>

</div>');

?>