<?php
if( !(__LOAD__) )
{
	exit();
	return false;
}

/* Short and sweet */
require('wordpress/wp-blog-header.php');





	//echo($_LANG[ 'general' ][ 'upcoming' ]);
    //require_once( 'files/php/slider.php' );
	//require_once( 'files/php/match-box.php' ); 
?>

<?php 
	// Navigation menu section
	require_once('files/template/menu.php');
?>
<div class="right-menu">
<?php
$games = $db->prepare('SELECT *, DATE_FORMAT(`release`,"%d.%m.%Y") AS `release` FROM `games`');
$games ->execute();
$games_result = $games->fetchAll();
foreach ($games_result as $games_value) {
    $game = $db->prepare('SELECT *, DATE_FORMAT(`release`,"%d.%m.%Y") AS `release` FROM `games` WHERE `short` = :short LIMIT 1');
    $game->bindValue(':short', $games_value['short'], PDO::PARAM_STR);
    $game->execute();
    $result = $game->fetch();
    if( $result != false ){
        $showAll = false;
    }
    $_GAMES['count'] = 0;
    $matches = $db->prepare('SELECT *, DATE_FORMAT(`start`,"%d.%m.%Y %H:%i") AS `date-start` FROM `matches`
    JOIN `bets` ON `bets`.`matchId` = `matches`.`id` AND `bets`.`typeId` = 1 AND `bets`.`active` = "true" AND `matches`.`finish` <> "true"
    WHERE `gameId` = :gId AND `finish` <> "true" AND `start` > NOW() ORDER BY `start` ASC LIMIT 5');
    $matches->bindValue(':gId', (int) $result['id'], PDO::PARAM_INT);
    $matches->execute();
    if( $matches->rowCount() > 0 ){
        //echo('<h3 class="subtitle2">'.$_LANG['labels']['others'].'</h3>
        echo('<div class="right-menu-naglowek">'.$result['name'].'<img src="img/'.$result['logo'].'" style="height: 22px"></div>');
?>

	<table>
	<?php
		for($i=1;  $i<= 5; $i++){
			while( $match = $matches->fetch() ){
				++$_GAMES['count'];
				$enemys = array(
					getGaming($match['teamId-1']),
					getGaming($match['teamId-2'])
				);
				$bets = $db->prepare('SELECT * FROM `bets` WHERE `matchId` = :mId AND `typeId` = 1 LIMIT 1');
				$bets->bindValue(':mId', (int) $match['0'], PDO::PARAM_INT);
				$bets->execute();
				$bet = $bets->fetch();
				$game = getGame($match['gameId']);
				if($match['stream'] == 'http://') {
					$match['stream'] = '#';
				}
				echo '
					<tr>
					<td class="team"><a href="teams/'.$enemys[0]['tag'].'">'.$enemys[0]['fullname'].'</a><br />'; 
					echo (($match['teamWinId'] == '0')?'<a class="zaklad-1" href="'.$_PAGES['lang'].'/'.$bet['id'].'-1">':'').getScore( $bet[ 'score-1' ] )
					.(($match['teamWinId'] == '0')?'</a>':'</td>');
					echo'
					<td class="myslnik"><a style="color:#c69b09;" href="/bets/'.$match['0'].'">vs</a></td>
					<td class="team"><a href=""><a href="teams/'.$enemys[1]['tag'].'">'.$enemys[1]['fullname'].'</a><br />';
					echo (($match['teamWinId'] == '0')?'<a class="zaklad-2" href="'.$_PAGES['lang'].'/'.$bet['id'].'-2">':'').getScore( $bet[ 'score-2' ] )
					. (($match['teamWinId'] == '0')?'</a>':'</td>');
					echo '</tr>';
			}
	
		}
	}?>
</table>

<?php  
}
//'.date('d.m.y H:i',strtotime($match['start'])).'
?>  
</div>
<div class="center-content">
<?php 
$i = 0;
if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php $post_image_id = get_post_thumbnail_id($post_to_use->ID);
		if ($post_image_id) {
			$thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
			if ($thumbnail) (string)$thumbnail = $thumbnail[0];
		} ?>
<?php if ($i==0){?>
<div class="center-news-80" style="background-image:url(<?php echo $thumbnail; ?>);">
<?php $i++; } elseif ($i==1){?>
<div class="center-news-20" style="background-image:url(<?php echo $thumbnail; ?>);">
<?php $i++; }else{ ?>
<div class="center-news-33" style="background-image:url(<?php echo $thumbnail; ?>);">
<?php }?>
	<div class="overlay"></div>
    <div class="corner-overlay-content"><h2><?php the_title() ;?></h2></div>
                <div class="jaka-gra"><?php the_category(', '); ?></div>
                <div class="overlay-content">
                	<?php the_title('<h3><a href="' . get_permalink() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark">', '</a></h3>'); ?>
                	<p><?php the_content(); ?></p>
                </div>
</div>


<?php endwhile; else: ?>

	<p>Sorry, this post does not exist</p>

<?php endif; ?>

</div>
