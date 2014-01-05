<?php

session_start();

define('__LOAD__', true);



require_once('../_functions.php');
DB::$source = '../';
$db = DB::getConnect();
$sql = $db->query('SELECT * FROM `settings`');
while( $fetch = $sql->fetch() ){

	$_SETTINGS[ $fetch[ 'category' ] ][ $fetch[ 'name' ] ] = $fetch[ 'value-pl' ];
}
$header = false;
if( isset( $_SESSION[ 'auth' ] ) ){
	$auth_nick = ( trim( $_SESSION[ 'auth' ][ 'login' ] ) );
	$auth_sid  = ( trim( $_SESSION[ 'auth' ][ 'sid' ] ) );
	$login = new Login();
	if( $login->checkAuth( $auth_nick, $auth_sid ) ){
		if( $login->getActive( $auth_nick ) ){
			if( $login->getTimeDiff( $auth_nick ) <= 2*$_SETTINGS[ 'auth' ][ 'unActiveTime' ] || $_SETTINGS[ 'auth' ][ 'unActiveTime' ] == '0' ){
				$login->setLastActionTime( $auth_nick );
				$_USER = $login->getInfoUsers( $auth_nick );
				$_GLOBALS[ 'login' ][ 'login' ] = true;
				$_GLOBALS[ 'login' ][ 'info' ] = $_USER[ 'firstName' ];
				$_GLOBALS[ 'login' ][ 'userId' ] = $login->getIdByLogin($auth_nick);
				$_GLOBALS[ 'login' ][ 'access' ] = $login->getRang( $auth_nick );				
			}
			else{
				$header = true;
			}
		}
		else{
            $header = true;
		}
	}
}
else{
	$header = true;
}
if( !isset($_GLOBALS) || $_GLOBALS[ 'login' ][ 'access' ] == "user" || $header == true ){
    session_destroy();
    header("Location: /");
    exit();
}

$_MENU = array(
	'admin' => array(
        'title' => 'Zarządzanie stroną',
        'links' => array(
            'add' => "Dodaj administratora",
			'remove' => "Usuń administratora",
			'settings' => "Ustawienia",
			'bets_add' => "Dodaj nowy typ zakładu",
			'bets_edit' => "Edytuj typy zakładów",
			'bets_remove' => "Usuń typy zakładów",
			'bans' => "Zablokowane dostępy",
			'menu' => "Zarządzaj menu",
			//'newsletter' => "Newsletter",
			'langs' => "Zarządzaj komunikatami językowymi"
        )
    ),

	'bets' => array(
        'title' => 'Zakłady',
        'links' => array(
            'add' => "Dodaj nowy zakład",
            'edit' => "Zarządzaj zakładami",
			'finish' => "Wprowadź wynik zakładu"
        )
    ),

	'matches' => array(
        'title' => 'Mecze',
        'links' => array(
            'add' => "Dodaj nowy mecz",
            'edit' => "Zarządzaj meczami",
			'finish' => "Zakończ mecz i rozdaj punkty"
        )
    ),
	
	'gamings' => array(
        'title' => 'Gamingi',
        'links' => array(
            'add' => "Dodaj nowy Gaming",
            'edit' => "Zarządzaj gamingami"
        )
    ),

	'teams' => array(
        'title' => 'Drużyny',
        'links' => array(
            'add' => "Dodaj nową drużynę",
            'edit' => "Zarządzaj drużynami"
        )
    ),

	'events' => array(
        'title' => 'Wydarzenia',
        'links' => array(
            'add' => "Dodaj nowe wydarzenie",
            'edit' => "Zarządzaj wydarzeniami"

        )

    ),

	'games' => array(
        'title' => 'Gry',
        'links' => array(
            'add' => "Dodaj nową grę",
            'edit' => "Zarządzaj grami"
        )
    ),

	'users' => array(
        'title' => 'Użytkownicy',
        'links' => array(
          	//  'add' => "Dodaj nowego użytkownika",
			 'edit' => "Edytuj profil użytkownika",
          	 'ban' => "Zablokuj konto"
        )
    ),	
	'panels' => array(
        'title' => 'Panele',
        'links' => array(
            'add' => "Dodaj nowy panel",
            'edit' => "Zarządzaj panelami"
        )
    ),

	'partners' => array(
        'title' => 'Partnerzy',
        'links' => array(
            'add' => "Dodaj nowego partnera",
            'edit' => "Zarządzaj partnerami"
        )
    ),

	

	'slider' => array(
        'title' => 'Slider',
        'links' => array(
            'add' => "Dodaj stronę slider",
            'edit' => "Zarządzaj stronami slidera"

        )

    ),
	
	'ad' => array(
        'title' => 'Reklama',
        'links' => array(
            'menage' => "Zarządzaj reklamą"
        )
    ),
	
		'pages' => array(
        'title' => 'Podstrony',
        'links' => array(
            'add' => "Dodaj nową stronę",
            'edit' => "Zarządzaj stronami"
        )
    ),
	
	'players' => array(
        'title' => 'Gracze',
        'links' => array(
           // 'add' => "Dodaj nowego gracza",
           // 'edit' => "Zarządzaj graczami"
        )

    ),
	
	'news' => array(
        'title' => 'Newsy',
        'links' => array(
            //'add' => "Dodaj nowego newsa",
            //'edit' => "Zarządzaj newsami"
        )
    )
);



if($handle = opendir('../')){
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $ex = explode(".", $entry);
			if( end($ex) == "php" )
				$_FILES[] = $ex[0];
        }
    }
    closedir($handle);
}



function showInput($category, $label, $describe)

{

	global $_LANG;

	return('<fieldset>

		<table class="tablesorter">

		<tr>

			<td colspan="2">

			<tt>'.$label.'</tt><br>

			'.$describe.'

			</td>

		</tr>

		<tr>

			<td>

				<textarea name="pl['.$category.']['.$label.']" rows="2">'.$_LANG['pl'][$category][$label].'</textarea>

			</td>

			<td>

				<textarea name="en['.$category.']['.$label.']" rows="2">'.$_LANG['en'][$category][$label].'</textarea>

			</td>

		</tr>

		</table>

		</fieldset>');

}



function showSettings($type, $category, $name, $describe, $options = array("true", "false"), $en = false)

{

	global $_SET;

	$return = ('<fieldset>

		<input type="hidden" name="double['.$category.']['.$name.']" value="'.(($en)?'true':'false').'">

		<table class="tablesorter">

		<tr>

			<td colspan="2">

			<tt>'.$name.'</tt><br>

			'.$describe.'

			</td>

		</tr>

		<tr>

			<td>');

			

			if($type == "string" || $type == "html")

			{

				$return .= ('<textarea name="pl['.$category.']['.$name.']" rows="'.(($type=="string")?'2':'5').'">'.$_SET['pl'][$category][$name].'</textarea>');

			}

			else if($type == "int" || $type == "number")

			{

				$return .= ('<input type="text" name="pl['.$category.']['.$name.']" value="'.$_SET['pl'][$category][$name].'">');

			}

			else if($type == "bool")

			{

				$return .= ('<select name="pl['.$category.']['.$name.']">');

				foreach($options as $opcja)

				{

					$return .= ('<option value="'.$opcja.'"'.(($opcja == $_SET['pl'][$category][$name])?' selected':'').'>'.$opcja.'</option>.');

				}

				$return .= ('</select>');

			}

			

			$return .= ('</td>');

			

			if( $en )

			{

				$return .= ('<td>

					<textarea name="en['.$category.']['.$name.']" rows="2">'.$_SET['en'][$category][$name].'</textarea>

				</td>');

			}

		$return .= ('</tr>

		</table>

		</fieldset>');

		return $return;

}



$_PAGES[ 'module' ] = isset( $_GET[ 'module' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'module' ] ) ) ) : 'home';

$_PAGES[ 'type' ] = isset( $_GET[ 'type' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'type' ] ) ) ) : '';

$_PAGES[ 'more' ] = isset( $_GET[ 'more' ] ) ?  ( trim ( htmlspecialchars ( $_GET[ 'more' ] ) ) ) : '';



$_title = @$_MENU[ $_PAGES[ 'module' ] ][ 'title' ];

$_link = @$_MENU[ $_PAGES[ 'module' ] ][ 'links' ][ $_PAGES['type'] ];



if( $_title == NULL )

	$_title = "Zarządzanie stroną";

if( $_link == NULL )

	$_link = "Strona główna";

	

if( $_GLOBALS[ 'login' ][ 'access' ] == "head-admin" )

{

	define('__HEADADMIN__', true);

}

else

{

	define('__HEADADMIN__', false);

}

define('__ADMIN__', true);

?>

<!doctype html>

<html lang="pl">

<head>

	<meta charset="UTF-8">

	<meta name="keywords" content="<?php echo($_SETTINGS[ 'general' ][ 'keywords' ]); ?>">

	<title>Panel administracyjny :: <?php echo($_SETTINGS['general']['sitename']); ?></title>	

	<link rel="stylesheet" href="/admin/css/layout.css?<?php echo(date("Ymdgis", filemtime('css/layout.css'))); ?>">

	<base href="/admin/">

	<!--[if lt IE 9]>

	    <link rel="stylesheet" href="/admin/css/ie.css?<?php echo(date("Ymdgis", filemtime('css/ie.css'))); ?>">

	    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>

	<![endif]-->	

</head>

<body>



	<header id="header">

		<hgroup>

			<h1 class="site_title"><a href="index.php"><?php echo($_SETTINGS['general']['sitename']); ?></a></h1>

			<h2 class="section_title">Panel administracyjny</h2>

            <div class="btn_view_site"><a href="<?php echo($_SETTINGS['general']['url']); ?>">zobacz stronę</a></div>

		</hgroup>

	</header>

	

	<section id="secondary_bar">

		<div class="user">

			<p>Witaj, <?php echo($_GLOBALS['login']['info']); ?> (<a href="../edit-profile"><?php echo($_USER['nick']); ?></a>)</p>

			<a class="logout_user" href="../logout.php" title="Logout">wyloguj</a>

		</div>

		<div class="breadcrumbs_container">

			<article class="breadcrumbs">

				<a href="../admin/">Panel administracyjny</a>

				<div class="breadcrumb_divider"></div><a class="current"><?php echo($_title); ?></a>

				<div class="breadcrumb_divider"></div><a class="current"><?php echo($_link); ?></a>

			</article>

		</div>

	</section>

	

	<aside id="sidebar" class="column">

		<?php

        foreach($_MENU as $key => $value)

        {

            echo('<h3>'.$value['title'].'</h3>');

            echo('<ul class="toggle">');

            foreach($value['links'] as $link => $label)

            {

                echo('<li class="'.$key.'_'.$link.'"><a href="'.$key.'/'.$link.'">'.$label.'</a></li>');

            }

            echo('</ul>');

        }

        ?>

		<footer>

			<hr><p><strong>Copyright &copy; <?php echo(date('Y') . ' ' . $_SETTINGS['general']['sitename']); ?></strong></p>

			<p>Theme by <a href="http://www.medialoot.com">MediaLoot</a></p>

		</footer>

	</aside>

	

	<section id="main" class="column">

		<article class="module width_full">

			<header><h3><?php echo($_title. ' - '. $_link); ?></h3></header>

				<div class="module_content">

					<?php

						if( file_exists( 'php/'.$_PAGES['module'].'.'.$_PAGES['type'].'.php' ) )

						{

							$_ACTION = '/admin/'.$_PAGES['module'].'/'.$_PAGES['type'];

							require_once('php/'.$_PAGES['module'].'.'.$_PAGES['type'].'.php');

						}

						else

							require_once('php/home.php');

					?>

				</div>

		</article>

		<div class="spacer"></div>

	</section>



    <script src="http://code.jquery.com/jquery-latest.min.js"></script>

	<script src="/admin/js/hideshow.js"></script>

	<script src="/admin/js/jquery.tablesorter.min.js"></script>

	<script src="/admin/js/jquery.equalHeight.js"></script>

	<script src="/admin/js/DateTimePicker.js"></script>

	<script>

	    $(document).ready(function () {

	        $(".tablesorter").tablesorter();

	        $(".tab_content").hide();

	        $("ul.tabs li:first").addClass("active").show();

	        $(".tab_content:first").show(); 

	        $("ul.tabs li").click(function () {

	            $("ul.tabs li").removeClass("active");

	            $(this).addClass("active");

	            $(".tab_content").hide();

	            var activeTab = $(this).find("a").attr("href");

	            $(activeTab).fadeIn();

	            return false;

	        });

	    });

	    $(function () {

	        $('.column').equalHeight();

	    });

    </script>

	<?php

	echo('<script>

	$(\'#add_new_type\').click(function(){

			$("<fieldset><label for=\"\">Zakład</label><select name=\"bets[typ][]\">');

			$zaklady = $db->query('SELECT * FROM `bettypes` ORDER BY `id` ASC');

			while($zaklad = $zaklady->fetch())

			{

				echo('<option value=\"'.$zaklad['id'].'\">'.$_LANG[$zaklad['type']].'</option>');

			}echo('</select><p><tt>Kurs 1:</tt> <input type=\"text\" name=\"bets[kurs-1][]\"></p><p><tt>Kurs 2:</tt>  <input type=\"text\" name=\"bets[kurs-2][]\"></p></fieldset>").appendTo(\'#bets\');

			return false;

	});

	</script>');

	?>

</body>

</html>