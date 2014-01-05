	<div class="top-bar">
    	<div class="logo">
        	<h1><a href="">gamingbet<br><small>this is sport, this is e-sport</small></a></h1>
        </div>
        <div class="menu-icon">
        	<ul>
            	<?php 
					// Menu before game-lists, poostałości, nie umiem inaczej ustawić tego :P
					$menu[ 'before' ] = $db->query( 'SELECT * FROM `menu` WHERE `position` = "before" ORDER BY `lp` ASC' );
					while( $menu[ 'left' ] = $menu[ 'before' ]->fetch() )
					{
						echo('<li><a class="news" href="'. stripslashes($menu[ 'left' ][ 'link' ]) .'">'. $_LANG[ 'menu' ][ $menu[ 'left' ][ 'name' ] ] .'</a></li>');
					}
                	// Menu after game-lists
					$menu[ 'after' ] = $db->query( 'SELECT * FROM `menu` WHERE `position` = "after" ORDER BY `lp` ASC' );
					while( $menu[ 'right' ] = $menu[ 'after' ]->fetch() )
					{
						echo('<li><a href="'. stripslashes($menu[ 'right' ][ 'link' ]) .'">'. $_LANG[ 'menu' ][ $menu[ 'right' ][ 'name' ] ] .'</a></li>');
			
					}
					if( $_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'general' ][ 'change-lang' ] == "true" )
					echo('
					<li><a class="news" href="'.$_PAGES[ 'lang' ].'/pl"><img src="files/images/pl.png" alt="Polska wersja"></a></li>
					<li><a class="news" href="'.$_PAGES[ 'lang' ].'/en"><img src="files/images/en.png" alt="English version"></a></li>
					');
				?>
            </ul>
        </div>
     
        <button class="menu-right-a" id="showRightPush"></button>
        
        <div class="szukaj">
        	<input type="text" class="search" placeholder="wpisz czego szukaj i naciśnij enter">
        </div>
        
    </div>
           
<div class="menu-glowne">
<?php
	if( $_GLOBALS[ 'login' ][ 'login' ] == true ){        
      echo('
	  	<div class="user-panel">   
			<div class="avatar"><img src="files/images/avatars/'.$_USER['avatar'].'" alt="avatar"></div>       
			<div class="imie-nazwisko">'.$_USER['firstName'].' '.$_USER['surname'].'</div>
			<div class="stan-konta">'.$_LANG[ 'labels' ][ 'credits' ].' '.$_USER['credits'].'</div>
    	 	<div class="dodaj-znajomego"><a href=""><img src="icon/add-user.png"></a></div>
            <div class="wiadomosci"><a href=""><img src="icon/wiadomosci.png"></a></div>
            <div class="powiadomienia"><a href=""><img src="icon/powiadomienia.png"></a></div>
		</div>
		<ul class="user-menu">');
    if( $_GLOBALS[ 'login' ][ 'access' ] != "user" ){
        echo('<li><a href="admin/">'.$_LANG[ 'labels' ][ 'admin' ].'</a></li>');
    }
	echo('
		<li><a class="moje-konto" href="edit-profile">'.$_LANG[ 'labels' ][ 'edit-profile' ].'</a></li>
		<li><a class="historia-zakladow" href="history">'.$_LANG[ 'labels' ][ 'history' ].'</a></li>
		<li><a href="favourites">'.$_LANG[ 'labels' ][ 'favourites' ].'</a></li>
		<li><a class="wykup-kredyty" href="credits">Doładuj kredyty</a></li>
		<li><a href="logout.php">'. sprintf( $_LANG[ 'labels' ][ 'logout' ], $_USER[ 'nick' ] ) .'</a></li>
		</ul>
	');

}

else
{
	echo('
		<h2>Zaloguj się</h2>
		<form action="/" method="post">
		');
	if( isset( $_GLOBALS[ 'login' ][ 'errors' ] ) && !empty( $_GLOBALS[ 'login' ][ 'errors' ] ) )
	{
		echo('<ul class="errors">');
		foreach( $_GLOBALS[ 'login' ][ 'errors' ] as $error )
		{
			echo('<li>'. $error. '</li>');
		}
		echo('</ul>');
	}
	echo('<fieldset>
	
		<input type="text" class="login" name="auth_nick" id="auth_01" placeholder="'. $_LANG[ 'labels' ][ 'nick' ] .'" value="'. @$auth_nick .'" required>	
		<input type="password" class="login" name="auth_pass"  placeholder="'. $_LANG[ 'labels' ][ 'pw' ] .'" id="auth_02" required>
		');
		echo('
			<div class="center-button">
				<input class="przycisk-login" type="submit" class="submit" name="auth_submit" value="'. $_LANG[ 'labels' ][ 'login' ] .'">
				<a href="register" class="zaloz-konto">'. $_LANG[ 'labels' ][ 'register' ] .'</a>
				<div class="links"><a href="forgot" >'. $_LANG[ 'labels' ][ 'forgot' ] .'</a></div>
			</div>
	</fieldset>');
	echo('</form>');
}
?>    

   	<div class="twoje-typy">
<?php
  		require_once('panels/bets.php');
?>
	</div>
    
    <?php
		require_once( 'modules.php' );
	?>

	<div class="stopka">
<?php
if(!empty($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_footer' ]))
	echo(stripslashes($_SETTINGS[ $_GLOBALS[ 'lang' ] ][ 'ads' ][ 'ads_footer' ]));
?>
<br />								   
<?php
			// Menu before game-lists
			$menu[ 'footer' ] = $db->query( 'SELECT * FROM `menu` WHERE `position` = "footer" ORDER BY `lp` ASC' );
			$menu[' footer' ][ 'count' ] = $menu[ 'footer' ]->rowCount();
			while( $menu[ 'bottom' ] = $menu[ 'footer' ]->fetch() )
			{
				echo('<a href="'. stripslashes($menu[ 'bottom' ][ 'link' ]) .'">'. $_LANG[ 'footer' ][ $menu[ 'bottom' ][ 'name' ] ] .'</a>');
				if($menu[' footer' ][ 'count' ] != 1)
				{
					echo ' |';
				}
				$menu[' footer' ][ 'count' ]--;
			}
		?>
        <br />		
        <?php echo($_LANG[ 'footer' ][ 'about-us-txt' ]); ?>	
    </div>
</div>



<div class="center">