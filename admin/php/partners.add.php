<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	$nazwa = addslashes(trim(htmlspecialchars($_POST['nazwa'])));
	$url = addslashes(trim(htmlspecialchars($_POST['url'])));
	$image = &$_FILES['images'];
	
	$access = array('image/gif', 'image/png', 'image/jpeg');
	list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);
	
	if( empty($nazwa) || empty($image['tmp_name']) )
		$errors[] = "Nie zostały wypełnione wymagane pola";
		
	if(!in_array($image['type'], $access))
		$errors[] = "Plik graficzny powinien mieć rozszerzenie *.jpg, *.gif lub *.png";
		
	if($width > 150 || $height > 100)
		$errors[] = "Maksymalne rozmiary wgrywanego pliku nie mogą przekraczać 150x100 [px]";
		
	if(empty($errors))
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
		
		$policz = $db->query('SELECT `lp` FROM `partners` ORDER BY `lp` DESC LIMIT 1');
		$policz = $policz->fetch();		
		
		$sql = $db->prepare('INSERT INTO `partners` VALUES(NULL, :lp, :nazwa, :image, :url)');
		$sql->bindValue(':lp', (int) $policz['lp']+1, PDO::PARAM_INT);
		$sql->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
		$sql->bindValue(':image', $filename, PDO::PARAM_STR);
		$sql->bindValue(':url', $url, PDO::PARAM_STR);
		$sql->execute();
		
		$sent = true;
	}
}
else
{
	$nazwa = NULL;
	$url = NULL;
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
<form action="'.$_ACTION.'" method="post" class="post_message" enctype="multipart/form-data">

<fieldset>
	<label for="input_1">Nazwa<span class="star">*</span></label>
	<input type="text" id="input_1" name="nazwa" value="'.stripslashes($nazwa).'" required>
</fieldset>

<fieldset>
	<label for="input_2">URL</label>
	<input type="text" id="input_2" name="url" value="'.stripslashes($url).'">
</fieldset>

<fieldset>
	<label for="input_3">Obrazek <span class="star">*</span></label>
	<input type="file" id="input_3" name="images" accept="image/jpeg,image/gif,image/png" required>
</fieldset>

<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">

</form>

');
}

?>