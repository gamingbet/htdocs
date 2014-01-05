<?php

	if (isset($_POST['calendar_month']) && isset($_POST['calendar_year']) && $_POST['calendar_month'] >= 1 && $_POST['calendar_month'] <= 12 && $_POST['calendar_year'] >= 2013)
	{
		if($_POST['calendar_year'] == 2013 && $_POST['calendar_month'] < 9) //if(FALSE)
		{
			header('Location: /calendar');
		}
		else
		{
			header('Location: /calendar/date/'.$_POST['calendar_month'].'-'.$_POST['calendar_year']);
		}
	}
	else
	{
		header('Location: /calendar');
	}
?>