<?php
/**
if( !(__LOAD__) )

{

	exit();

	return false;

}



if( $_GLOBALS[ 'login' ][ 'login' ] == true )

{

	echo('<div class="panel-logged"><ul class="user-menu" '. ( (!empty($_USER['avatar'])) ? ' style="background: url(\'files/images/avatars/'.$_USER['avatar'].'\') no-repeat right top;"' : '').'>

    <li>'.$_LANG[ 'labels' ][ 'credits' ].' <b>'.$_USER['credits'].'</b></li>');

    if( $_GLOBALS[ 'login' ][ 'access' ] != "user" )

    {

        echo('<li><a href="admin/">'.$_LANG[ 'labels' ][ 'admin' ].'</a></li>');

    }

	echo('<li><a href="edit-profile">'.$_LANG[ 'labels' ][ 'edit-profile' ].'</a></li>

	<li><a href="history">'.$_LANG[ 'labels' ][ 'history' ].'</a></li>

	<li><a href="favourites">'.$_LANG[ 'labels' ][ 'favourites' ].'</a></li>

	<li><a href="credits">'.$_LANG[ 'labels' ][ 'buy-credists' ].'</a></li>



	<li><a href="logout.php">'. sprintf( $_LANG[ 'labels' ][ 'logout' ], $_USER[ 'nick' ] ) .'</a></li>

	</ul></div>');

}

else

{

	echo('<form action="/" method="post">');

	if( isset( $_GLOBALS[ 'login' ][ 'errors' ] ) && !empty( $_GLOBALS[ 'login' ][ 'errors' ] ) )

	{

		echo('<ul class="errors">');

		foreach( $_GLOBALS[ 'login' ][ 'errors' ] as $error )

		{

			echo('<li>'. $error. '</li>');

		}

		echo('</ul>');

	}

	echo('<fieldset id="login">

	<dl>

		

		<dt><label for="auth_01">'. $_LANG[ 'labels' ][ 'nick' ] .'</label></dt>

		<dd><input type="text" class="login" name="auth_nick" id="auth_01" value="'. @$auth_nick .'" required></dd>

		

		<dt><label for="auth_02">'. $_LANG[ 'labels' ][ 'pw' ] .'</label></dt>

		<dd><input type="password" class="pass" name="auth_pass" id="auth_02" required></dd>

		');

		/*
		echo '

		<dd>

			<input type="checkbox" name="auth_remember" value="true" id="auth_03">

			<label for="auth_03">'. $_LANG[ 'labels' ][ 'remember' ] .'</label>

		</dd>

		';
		

		echo('

		<dd class="left-align">

			<input type="submit" class="submit" name="auth_submit" value="'. $_LANG[ 'labels' ][ 'login' ] .'">

			<a href="register" class="button">'. $_LANG[ 'labels' ][ 'register' ] .'</a>

		</dd>



		



	</dl>

	</fieldset>');

	echo('</form>');

}

		<dd class="left-align null-margin">

			<a href="forgot">'. $_LANG[ 'labels' ][ 'forgot' ] .'</a>

		</dd> 
**/
?>