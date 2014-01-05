<div class="wybor-gry">
	<span class="wybierz-gre">wybierz grę</span>
    <ul class="gry-linki">		
<?php
	// Game lists
	$games = $db->query( 'SELECT * FROM `games` WHERE `menuDisplay` = "true" ORDER BY `lp` ASC' );
	while( $game = $games->fetch() ){
		echo('<li><a href="games/'. $game[ 'short' ] .'">'. $game[ 'name' ] .'</a></li>');
	}
?>
	</ul>
</div>