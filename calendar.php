<?php
if( !defined("__LOAD__") )
{
	exit();
	return false;
}
?>

<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-xs-12 col-sm-12 col-md-8">
        <div class="well-sm biale">
        <h1>Kalendarz imprez</h1>


<?php

/* draws a calendar */
function draw_calendar($month,$year,$dates){

	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

	/* table headings */
	$headings = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
	$calendar.= '<tr class="calendar-row">';
	global $_LANG, $_GLOBALS;
	foreach ($headings as $heading_month) {
		$calendar .= '<td class="calendar-day-head">'.$_LANG['calendar'][$heading_month].'</td>';
	}
	
	$calendar.= '</tr>';

	/* days and weeks vars now ... */
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$running_day = ($running_day > 0) ? $running_day-1 : $running_day;
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np"> </td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td class="calendar-day"><div class="calendar-day-content">';
			/* add in the day number */
			$calendar.= '<div class="day-number">'.$list_day.'</div><div style="clear:both;"></div>';

			/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
			//$calendar.= str_repeat('<p> </p>',2);

			foreach ($dates as $key => $date) {
				$time = strtotime($date['dataBegin']);
				$time_date = date('j', $time);
				if($time_date == $list_day)
				{
					//var_dump($date);
					$calendar.= '<div class="calendar-entry">';
					$time_hour = date('H:i', $time);
					$calendar.= '<span class="calendar-time">'.$time_hour.'</span>';
					$url = ($date['url'] != "http://") ? '<a href="'.$date['url'].'">' : '';
					$url2 = ($date['url'] != "http://") ? '</a>' : '';
					$description = ($_GLOBALS[ 'lang' ] == 'pl') ? $date['description-pl'] : $date['description-en'];
					$calendar.= '<span class="calendar-content"><span class="calendar-title">'.$url.$date['name'].$url2.'</span><br /><span class="calendar-text">'.substr($description, 0, 150).'...</span></span>';
					$calendar.= '</div>';
				}
				

			}
			
		$calendar.= '</div></td>';
		if($running_day == 6):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np"> </td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';
	
	/* all done, return result */
	return $calendar;
}

if($_PAGES[ 'type' ] == "date" && !empty($_PAGES['more']))
{
	if(preg_match('/^(\d+)-(\d+)$/', $_PAGES['more'], $matches))
	{
		$month = (int) $matches[1];
		$year = (int) $matches[2];
		if ($month < 1 || $month > 12 || $year < 2000 || $year > 3000 || !is_int($month) || !is_int($year))
		{
			$month = date('n');
			$year = date('Y');
		}
	}
	else
	{
		$month = date('n');
		$year = date('Y');
	}
}
else
{
	$month = date('n');
	$year = date('Y');
}

$months_label = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

$month_begin = date(_SQLDate_,mktime(0,0,0,$month,1,$year));
$month_end = date(_SQLDate_,mktime(23,59,59,$month,date('t',$month_begin),$year));


try
{
	global $db;
	$stmt = $db->prepare('SELECT * FROM `events` WHERE `dataBegin` BETWEEN :first_day AND :last_day');
	$stmt->bindValue(':first_day', $month_begin);
	$stmt->bindValue(':last_day', $month_end);
	$stmt->execute();
	$result = $stmt->fetchAll();
}
catch(Exception $e)
{
	print_r($e);
}

?>

<div id="calendar-wrapper">
	<div id="calendar-meta">
		<div class="calendarform-wrapper">
			<form action="/calendar-redirect.php" method="post">
				<select name="calendar_month" id="calendar_month"><?php 
					foreach ($months_label as $key => $value)
					{
						$selected = ($key == $month - 1) ? ' selected="seleced"' : '';
						echo '<option value="'.($key+1).'"'.$selected.'>'.$_LANG['calendar'][$value].'</option>';
					} 
				?></select>
				<select name="calendar_year" id="calendar_year"><?php 
					for($i=2013; $i <= date('Y') + 2; $i++)
					{
						$selected = ($i == $year) ? ' selected="seleced"' : '';
						echo '<option value="'.($i).'"'.$selected.'>'.$i.'</option>';
					}
				?></select>
				<input type="submit" class="btn btn-primary" value="<?php echo $_LANG['calendar']['monthchange']; ?>">
			</form>
		</div>
	</div>
	<?php echo draw_calendar($month,$year,$result); ?>
</div>
</div>