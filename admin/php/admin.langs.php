<?php
if( !defined("__ADMIN__") || !defined("__LOAD__") )
{
	exit();
}

$sent = false;
$errors = array();

if( isset($_POST['submit']) && $_POST['submit'] == "Wyślij żądanie" )
{
	unset($_POST['submit']);
	foreach($_POST['pl'] as $category => $values)
	{
		foreach($values as $label => $value)
		{
			$db->exec('UPDATE `langs` SET `label-pl` = "'.addslashes($value).'" WHERE `category` = "'.$category.'" AND `label` = "'.$label.'" LIMIT 1');
		}
	}
	foreach($_POST['en'] as $category => $values)
	{
		foreach($values as $label => $value)
		{
			$db->exec('UPDATE `langs` SET `label-en` = "'.addslashes($value).'" WHERE `category` = "'.$category.'" AND `label` = "'.$label.'" LIMIT 1');
		}
	}
	$sent = true;
}


if($sent == true)
{
	echo('<h4 class="alert_success">Operacja zakończyła się sukcesem!</h4>');
}

$sql = $db->query('SELECT `category`, `label`, `label-pl`, `label-en` FROM `langs`');
while($lang = $sql->fetch())
{
	$_LANG['pl'][ trim($lang['category']) ][ trim($lang['label']) ] = trim(stripslashes($lang['label-pl']));
	$_LANG['en'][ trim($lang['category']) ][ trim($lang['label']) ] = trim(stripslashes($lang['label-en']));
}


echo('<h4>Komunikaty językowe</h4>
<p>Lewa kolumna zawiera tekst w języku polskim, natomiast prawa w języku angielskim.</p>
<ul class="tabs">
<li><a href="#tab-general">Ogólne</a></li>
<li><a href="#tab-labels">Etykiety</a></li>
<li><a href="#tab-register">Rejestracja</a></li>
<li><a href="#tab-edit">Edycja profilu</a></li>
<li><a href="#tab-titles">Tytuły</a></li>
<li><a href="#tab-bets">Zakłady</a></li>
<li><a href="#tab-events">Wydarzenia</a></li>
<li><a href="#tab-history">Historia</a></li>
</ul>
<ul class="tabs">
<li><a href="#tab-notices">Powiadomienia</a></li>
<li><a href="#tab-log">Panel logowania</a></li>
<li><a href="#tab-fav">Ulubione drużyny</a></li>
<li><a href="#tab-forgot">Przypomnij hasło</a></li>
<li><a href="#tab-active">Aktywacja konta</a></li>
</ul>

<form action="'.$_ACTION.'" method="post" class="post_message">
<div style="clear: both" class="tab_container">

'.
/////////////////////////////////////////////////////////// GENERAL
'

	<div id="tab-general" class="tab_content">
	<h5>Ogólne</h5>
		'.showInput( "general", "matches", "Nagłówek tabeli, w której wyświetlone są najbliższe spotkania" ) .'
		'.showInput( "general", "partners", "Nagłówek tabeli, w której wyświetlają się partnerzy" ) .'
		'.showInput( "general", "time", "Informacja wyświetlająca aktualny czas serwera" ) .'
	<h5>Stopka</h5>
		'.showInput( "footer", "find-us", "Nagłówek w stopce <b>Znajdź nas</b>" ) .'
		'.showInput( "footer", "about-us", "Nagłówek w stopce <b>O nas</b>" ) .'
		'.showInput( "footer", "about-us-txt", "Informacja o nas" ) .'
	<h5>Tytuły e-mailów</h5>
		'.showInput( "mails", "activate", "Tytuł e-maila z aktywacją konta" ) .'
		'.showInput( "mails", "password", "Tytuł e-maila z nowym hasłem do konta" ) .'
		'.showInput( "mails", "email", "Tytuł e-maila z potwierdzeniem zmiany adresu e-mail" ) .'		
	<h5>Błędy</h5>
		'.showInput( "errors", "404", "Nagłówek o błędzie #404" ) .'
		'.showInput( "errors", "404-info", "Informacja o tym, że podana strona nie została odnaleziona" ) .'
		'.showInput( "errors", "hack", "Informacja o nieprawidłowych danych zawartych w adresie (aktywacja konta, odzyskiwanie hasła, etc)" ) .'	
	</div>
	
'.
/////////////////////////////////////////////////////////// NOTICES
'

	<div id="tab-notices" class="tab_content">
	<h5>Powiadomienia</h5>
		'.showInput( "powiadomienia", "powiadomienia", "Nagłówek po rozwinięicu listy" ) .'
		'.showInput( "powiadomienia", "otworz", "Etykieta; otwórz" ) .'
		'.showInput( "powiadomienia", "close", "Etykieta: zamknij" ) .'
		'.showInput( "notices", "empty", "Informacja o pustej liście powiadomień" ) .'
		'.showInput( "notices", "finish", "Informacja o zakończonym meczu" ) .'
		'.showInput( "notices", "cancel", "Informacja o anulowaniu meczu" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// BETS
'

	<div id="tab-bets" class="tab_content">
	<h5>Zakłady</h5>
		'.showInput( "bets", "empty", "Informacja o tym, że wystąpiły błędy podczas zakładów" ) .'
		'.showInput( "bets", "noMultiply", "Informacja: kupon nie może być stworzony z zakładów z tego samego meczu" ) .'
		'.showInput( "bets", "needLogin", "Informacja: należy się zalogować" ) .'
		'.showInput( "bets", "wrongBets", "Informacja: niewszystkie zakłady są poprawne i zostały usunięte" ) .'
		'.showInput( "bets", "getCredit", "Informacja: wprowadzona ilość kredytów nie jest prawidłowa" ) .'
		'.showInput( "bets", "needCredits", "Informacja: brak wymaganych kredytów" ) .'
		'.showInput( "bets", "added", "Informacja: zakłady zostały postawione" ) .'
		'.showInput( "bets", "live", "Etykieta: aktualne mecze" ) .'
		'.showInput( "bets", "next", "Etykieta: następne mecze" ) .'
		'.showInput( "bets", "finished", "Etykieta: mecze zakończone" ) .'
		'.showInput( "bets", "credits", "Etykieta: kredyty" ) .'
	</div>
	
'.
/////////////////////////////////////////////////////////// TITLES
'
	
	<div id="tab-titles" class="tab_content">
	<h5>Tytuły</h5>
		'.showInput( "titles", "home", "Tytuł podstrony <b>strona główna</b>" ) .'
		'.showInput( "titles", "credits", "Tytuł podstrony <b>kup kredyty</b>" ) .'
		'.showInput( "titles", "events", "Tytuł podstrony <b>wydarzenia</b>" ) .'
		'.showInput( "titles", "bans", "Tytuł podstrony <b>zablokowany dostęp</b>" ) .'
		'.showInput( "titles", "register", "Tytuł podstrony <b>rejestracja</b>" ) .'
		'.showInput( "titles", "forgot", "Tytuł podstrony <b>przypomnij hasło</b>" ) .'
		'.showInput( "titles", "edit-profile", "Tytuł podstrony <b>edycja profilu</b>" ) .'
		'.showInput( "titles", "active", "Tytuł podstrony <b>aktywacja konta</b>" ) .'
		'.showInput( "titles", "email", "Tytuł podstrony <b>potwierdzenie zmiany adresu e-mail</b>" ) .'
		'.showInput( "titles", "bets", "Tytuł podstrony <b>zakłady</b>" ) .'
		'.showInput( "titles", "history", "Tytuł podstrony <b>historia zakładów</b>" ) .'
		'.showInput( "titles", "coupon", "Tytuł podstrony <b>kupony</b>" ) .'
		'.showInput( "titles", "games", "Tytuł podstrony <b>gry</b>" ) .'
		'.showInput( "titles", "teams", "Tytuł podstrony <b>drużyny</b>" ) .'
		'.showInput( "titles", "favourites", "Tytuł podstrony <b>ulubione drużyny</b>" ) .'
	</div>
	
'.
/////////////////////////////////////////////////////////// LABELS
'

	<div id="tab-labels" class="tab_content">
	<h5>Etykiety</h5>
		'.showInput( "labels", "buy-credits", "Etykieta: zakup kredyty" ) .'
		'.showInput( "labels", "admin", "Etykieta: panel administracyjny" ) .'
		'.showInput( "labels", "ban-info", "Komunikat o byciu zablokowanym" ) .'
		'.showInput( "labels", "cancel-match", "Informacja na stronie z meczami o anulowaniu meczu" ) .'
		'.showInput( "labels", "canceled", "Etykieta: anulowano mecz (rubryka z historią)" ) .'
		'.showInput( "labels", "removeFromFav", "Etykieta: usuń drużynę z ulubionych" ) .'
		'.showInput( "labels", "favouritesTeam", "Etykieta: ulubiona drużyna" ) .'
		'.showInput( "labels", "noFavourites", "Informacja: brak ulubionych drużyn" ) .'
		'.showInput( "labels", "addToFav", "Etykieta: dodaj do ulubionych" ) .'
		'.showInput( "labels", "favourites", "Etykieta: ulubione drużyny" ) .'
		'.showInput( "labels", "data-begin", "Etykieta: data rozpoczęcia" ) .'
		'.showInput( "labels", "empty-events", "Komunikat: brak nadchodzących wydarzeń" ) .'
		'.showInput( "labels", "data-end", "Etykieta: data zakończenia" ) .'
		'.showInput( "labels", "place", "Etykieta: miejsce" ) .'
		'.showInput( "labels", "start-hour", "Etykieta: data rozpoczęcia (strona z zakładem)" ) .'
		'.showInput( "labels", "result-bet", "Etykieta: typ zakładu" ) .'
		'.showInput( "labels", "game", "Etykieta: gra" ) .'
		'.showInput( "labels", "www", "Etykieta: www" ) .'
		'.showInput( "labels", "all", "Etykieta: wszystkie" ) .'
		'.showInput( "labels", "others", "Etykieta: pozostałe zakłady" ) .'
		'.showInput( "labels", "all-bets", "Etykieta: wszystkie zakłady" ) .'
		'.showInput( "labels", "notyet", "Etykieta: jeśli kolumna nie posiada żadnej danej, to wpisujemy tę wartość" ) .'
		'.showInput( "labels", "teams", "Etykieta: drużyny" ) .'
		'.showInput( "labels", "release", "Etykieta: utworzony" ) .'
		'.showInput( "labels", "look-www", "Etykieta: przejdź do strony www" ) .'
		'.showInput( "labels", "last-matches", "Etykieta: ostatnie mecze" ) .'
		'.showInput( "labels", "empty-lastMatch", "Komunikat: brak ostatnich meczów" ) .'
		'.showInput( "labels", "noIssetMatch", "Komunikat: brak meczu o podanym ID" ) .'
		'.showInput( "labels", "next-game", "Etykieta: następny mecz" ) .'
		'.showInput( "labels", "watchLive", "Etykieta: oglądaj na żywo" ) .'
		'.showInput( "labels", "more", "Etykieta: szczegóły" ) .'
		'.showInput( "labels", "coupon", "Etykieta: nr kuponu" ) .'
		'.showInput( "labels", "coupon-create", "Etykieta: data utworzenia kuponu" ) .'
		'.showInput( "labels", "win", "Etykieta: wygrana" ) .'
		'.showInput( "labels", "lose", "Etykieta: porażka" ) .'
		'.showInput( "labels", "draw", "Etykieta: remis" ) .'
		'.showInput( "labels", "once", "Etykieta: pojedyńczy" ) .'
		'.showInput( "labels", "multiply", "Etykieta: wielokrotny" ) .'
		'.showInput( "labels", "credits", "Etykieta: posiadane punkty" ) .'
		'.showInput( "labels", "credit", "Etykieta: stawka" ) .'
		'.showInput( "labels", "history", "Etykieta: historia zakładów" ) .'
		'.showInput( "labels", "date-news", "Etykieta: data (news)" ) .'
		'.showInput( "labels", "forgot", "Etykieta: przypomnij hasło" ) .'
		'.showInput( "labels", "avatar", "Etykieta: avatar" ) .'
		'.showInput( "labels", "del-avatar", "Etykieta: usuń avatar" ) .'
		'.showInput( "labels", "mail", "Etykieta: adres e-mail" ) .'
		'.showInput( "labels", "mail-change", "Etykieta: zmień adres e-mail" ) .'
		'.showInput( "labels", "pass-change", "Etykieta: zmień hasło" ) .'
		'.showInput( "labels", "new-mail", "Etykieta: nowy adres e-mail" ) .'
		'.showInput( "labels", "new-pw", "Etykieta: nowe hasło" ) .'
		'.showInput( "labels", "new-repw", "Etykieta: powtórz nowe hasło" ) .'
		'.showInput( "labels", "firstname", "Etykieta: imię" ) .'
		'.showInput( "labels", "surname", "Etykieta: nazwisko" ) .'
		'.showInput( "labels", "date", "Etykieta: data urodzenia" ) .'
		'.showInput( "labels", "country", "Etykieta: kraj" ) .'
		'.showInput( "labels", "city-code", "Etykieta: kod pocztowy" ) .'
		'.showInput( "labels", "street", "Etykieta: ulica" ) .'
		'.showInput( "labels", "register", "Etykieta: zarejestruj" ) .'
		'.showInput( "labels", "rules", "Etykieta: regulamin" ) .'
		'.showInput( "labels", "show-rules", "Etykieta: pokaż regulamin" ) .'
		'.showInput( "labels", "18years", "Etykieta: potwierdzenie ukończenia 18. lat" ) .'
		'.showInput( "labels", "newsletter", "Etykieta: newsletter" ) .'
		'.showInput( "labels", "nick", "Etykieta: użytkownik" ) .'
		'.showInput( "labels", "pw", "Etykieta: hasło" ) .'
		'.showInput( "labels", "repw", "Etykieta: powtórz hasło" ) .'
		'.showInput( "labels", "login", "Etykieta: zaloguj" ) .'
		'.showInput( "labels", "remember", "Etykieta: pamiętaj mnie" ) .'
		'.showInput( "labels", "logout", "Etykieta: wyloguj się" ) .'
		'.showInput( "labels", "change", "Etykieta: zmień hasło" ) .'
		'.showInput( "labels", "send-form", "Etykieta: wyślij formularz" ) .'
		'.showInput( "labels", "refBy", "Etykieta: zaproszony przez" ) .'
		'.showInput( "labels", "refOur", "Etykieta: link do zapraszania" ) .'
		'.showInput( "labels", "refCount", "Etykieta: ilość osób zaproszonych" ) .'
		'.showInput( "labels", "type", "Etykieta: typ" ) .'
		'.showInput( "labels", "1st", "Etykieta: podstawowe" ) .'
		'.showInput( "labels", "2nd", "Etykieta: dodatkowe" ) .'
		'.showInput( "labels", "3rd", "Etykieta: referencje" ) .'
		'.showInput( "labels", "edit-profile", "Etykieta: edytuj profil" ) .'
		'.showInput( "labels", "empty-box", "Komunikat: brak wyników do wyświetlenia" ) .'
		'.showInput( "labels", "empty-gameList", "Komunikat: lista gier jest pustny" ) .'
		'.showInput( "labels", "enemys", "Etykieta: mecz pomiędzy" ) .'
		'.showInput( "labels", "noTeams", "Komunikat: klan nie zarejestrował drużyny" ) .'
		'.showInput( "labels", "winner", "Etykieta: zwycięzca" ) .'
		'.showInput( "labels", "event", "Etykieta: turniej" ) .'
		'.showInput( "labels", "bets-all", "Etykieta: całkowita ilość zakładów" ) .'
		'.showInput( "labels", "noGames", "Komunikat: brak gier do wyświetlenia" ) .'
		'.showInput( "labels", "nextTournaments", "Etykieta: zaplanowane turnieje" ) .'
		'.showInput( "labels", "liveMatches", "Etykieta: aktualne mecze" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// REGISTER
'

	<div id="tab-register" class="tab_content">
	<h5>Rejestracja</h5>
		'.showInput( "register", "errors", "Komunikat o możliwych błędach podczas rejestracji" ) .'
		'.showInput( "register", "register_done", "Komunikat o prawidłowej rejestracji" ) .'
		'.showInput( "register", "register-too", "Informacja, gdy użytkownik zalogowany chce się rejestrować" ) .'
		'.showInput( "register", "no-register", "Informacja o zablokowanej rejestracji" ) .'
		'.showInput( "register", "info", "Informacja o możliwości, jakie daje portal po utworzeniu konta" ) .'
		'.showInput( "register", "empty_nick", "Komunikat: nie wypełniono pola nick" ) .'
		'.showInput( "register", "wrong_nick", "Komunikat: nick zawiera złe znaki" ) .'
		'.showInput( "register", "same_nickPass", "Komunikat: nick i hasło nie mogą być identyczne" ) .'
		'.showInput( "register", "too_short_pw", "Komunikat: hasło jest za krótkie" ) .'
		'.showInput( "register", "not_same_pw", "Komunikat: hasła nie są identyczne" ) .'
		'.showInput( "register", "mail_not_valid", "Komunikat: błędny adres e-mail" ) .'
		'.showInput( "register", "need_18old", "Komunikat: użytkownik nie ma 18. lat" ) .'
		'.showInput( "register", "rules_accept", "Komunikat: nie zaakceptowano zasad" ) .'
		'.showInput( "register", "busy_mail", "Komunikat: adres e-mail jest zajęty" ) .'
		'.showInput( "register", "busy_nick", "Komunikat: nick jest zajęty" ) .'
		'.showInput( "register", "wrong_catpcha", "Komunikat: źle przepisane potwierdzenie" ) .'
		'.showInput( "register", "wrong_date", "Komunikat: wprowadzona data nie jest prawidłowa" ) .'
		'.showInput( "register", "too_old", "Komunikat: użytkownik jest za stary" ) .'
		'.showInput( "register", "too_young", "Komunikat: użytkownik jest za młody" ) .'
		'.showInput( "register", "too_short_firstname", "Komunikat: imię jest za krótkie" ) .'
		'.showInput( "register", "too_short_surname", "Komunikat: nazwisko jest za krótkie" ) .'
		'.showInput( "register", "too_short_city", "Komunikat: miasto jest za krótkie" ) .'
		'.showInput( "register", "too_short_street", "Komunikat: ulica jest za krótka" ) .'
		'.showInput( "register", "max_size", "Komunikat: rozmiary obrazka (avatar)" ) .'
		'.showInput( "register", "access_format", "Komunikat: dozwolone formaty obrazku (avatar)" ) .'
		'.showInput( "register", "max_weight", "Komunikat: dozwolony rozmiar obrazka (avatar)" ) .'
		'.showInput( "register", "wrong_code", "Komunikat: błędny kod pocztowy" ) .'
		'.showInput( "register", "check_mail", "Informacja o tym, że konto zostało utworzone i należy sprawdzić e-mail" ) .'
		'.showInput( "register", "cannot_send_mail", "Informacja o tym, że konto zostało utworzone, ale należy ręcznie napisać do administracji, ponieważ funkcja mail() przestała działać" ) .'		
	</div>

'.
/////////////////////////////////////////////////////////// EVENTS
'
	
	<div id="tab-events" class="tab_content">
	<h5>Wydarzenia</h5>
		'.showInput( "events", "dataBegin", "Etykieta: data rozpoczęcia" ) .'
		'.showInput( "events", "dataEnd", "Etykieta: data zakończenia" ) .'
		'.showInput( "events", "bets", "Tytuł: zakłady" ) .'
		'.showInput( "events", "name", "Etykieta: nazwa turnieju" ) .'
		'.showInput( "events", "game", "Etykieta: nazwa gry" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// HISTORY
'

	<div id="tab-history" class="tab_content">
	<h5>Historia zakładów</h5>
		'.showInput( "coupon", "empty", "Informacja o nieprawidłowym ID kuponu" ) .'
		'.showInput( "history", "empty", "Informacja o pustej historii zakładów" ) .'
		'.showInput( "history", "bid", "Etykieta: ID zakładu" ) .'
		'.showInput( "history", "date", "Etykieta: data zakładu" ) .'
		'.showInput( "history", "enemys", "Etykieta: drużyny, które między sobą rywalizują" ) .'
		'.showInput( "history", "course", "Etykieta: kurs zakładu" ) .'
		'.showInput( "history", "credits", "Etykieta: zadeklarowane kredyty" ) .'
		'.showInput( "history", "type", "Etykieta: typ zakładu" ) .'
		'.showInput( "history", "enemy", "Etykieta: drużyna" ) .'		
		'.showInput( "history", "result", "Etykieta: rezultat zakładu" ) .'
	</div>
	
'.
/////////////////////////////////////////////////////////// FAV
'
	
	<div id="tab-fav" class="tab_content">
	<h5>Ulubione drużyny</h5>
		'.showInput( "history", "delete", "Etykieta: usuń (checkbox)" ) .'
		'.showInput( "history", "deleteSelect", "Etykieta: usuń zaznaczone (submit)" ) .'
		'.showInput( "history", "date-add", "Etykieta: data dodania" ) .'
		'.showInput( "history", "errors", "Komunikat o błędach podczas usuwania błędów" ) .'
		'.showInput( "history", "noSelected", "Informacja o braku zaznaczenia drużyn do usuwania" ) .'
		'.showInput( "history", "deleted", "Informacja o ilości drużyn, które zostały usunięte" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// EDIT
'
	
	<div id="tab-edit" class="tab_content">
	<h5>Edycja profilu</h5>
		'.showInput( "edit-profile", "firstLogin", "Informacja o pierwszym logowaniu" ) .'
		'.showInput( "edit-profile", "info", "Informacja o czerwonej gwiazdce" ) .'
		'.showInput( "edit-profile", "avatar-info", "Informacja o możliwosci wgrania obrazku (avatar)" ) .'
		'.showInput( "edit-profile", "ref-info", "Informacja o możliwości zdobywania dodatkowych punktów" ) .'
		'.showInput( "edit-profile", "changeAcc", "Informacja o możliwości zmiany parametrów swojego konta" ) .'
		'.showInput( "edit-profile", "errors", "Informacja o występowaniu błędów podczas edycji profilu" ) .'
		'.showInput( "edit-profile", "changed", "Informacja o zaktualizowaniu profilu" ) .'
		'.showInput( "edit-profile", "changed-mail", "Informacja o zaktualizowaniu profilu i konieczności potwierdzenia zmiany adresu e-mail" ) .'
		'.showInput( "edit-profile", "changed-Nomail", "Informacja o zaktualizowaniu profilu i konieczności kontaktu z administracją, ponieważ funkcja mail() przestała działać" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// FORGOT
'

	<div id="tab-forgot" class="tab_content">
	<h5>Przypomnij hasło</h5>
		'.showInput( "forgot", "info", "Informacja o możliwości przypomnienia hasła" ) .'
		'.showInput( "forgot", "info2", "Informacja o wprowadzeniu nowego hasła do konta" ) .'
		'.showInput( "forgot", "errors", "Informacja o błędach podczas próby odzyskania hasła" ) .'
		'.showInput( "forgot", "free_mail", "Informacja o adresie e-mail, który nie jest przypisany do żadnego konta" ) .'
		'.showInput( "forgot", "antispam", "Informacja o zbyt wczesnej próbie wysłania kolejnego e-maila z nowym hasłem" ) .'
		'.showInput( "forgot", "check_mail", "Informacja o konieczności odwiedzenia skrzynki pocztowej w celu odzyskaniu hasła" ) .'
		'.showInput( "forgot", "cannot_send_mail", "Informacja o konieczności kontaktu z administracją, ponieważ funkcja mail() przestała działać" ) .'
		'.showInput( "forgot", "key-noActive", "Informacja o nieaktywnym kluczu" ) .'
		'.showInput( "forgot", "changed", "Informacja o pomyślnym zmienieniu hasła" ) .'
		'.showInput( "forgot", "forgot-too", "Informacja kiedy zalogowany użytkownik chce odzyskać hasło" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// ACTIVE
'

	<div id="tab-active" class="tab_content">
	<h5>Aktywacja konta</h5>
		'.showInput( "active", "active", "Informacja o aktywowaniu konta" ) .'
		'.showInput( "active", "noActive", "Konto nie zostało aktytowane" ) .'
		'.showInput( "active", "not-keys", "Złe parametry do aktywacji konta" ) .'
		'.showInput( "active", "active-too", "Próba aktywacji konta będąc zalogowanym" ) .'
		'.showInput( "active", "changed", "Prawidłowa zmiana adresu e-mail" ) .'
		'.showInput( "active", "link_noActive", "Link, który został użyty do zmiany e-maila stracił ważność" ) .'
		'.showInput( "active", "busy_mail", "Próba zmiany adresu e-mail na adres, który został wykorzystany do stworzenia konta chwilę przed próbą zmiany na innym koncie" ) .'
	</div>

'.
/////////////////////////////////////////////////////////// LOG
'

	<div id="tab-log" class="tab_content">
	<h5>Panel logowania</h5>
		'.showInput( "panels", "login-in", "Nagłówek panelu logowania kiedy jesteśmy zalogowani" ) .'
		'.showInput( "panels", "login-out", "Nagłowek panelu logowania kiedy nikt nie jest zalogowany" ) .'
		'.showInput( "auth", "only_admin", "Informacja o dostępie do strony tylko dla adminów" ) .'
		'.showInput( "auth", "need_login", "Informacja o dostępie do strony tylko dla zalogowanych" ) .'
		'.showInput( "auth", "wrong_post", "Nieprawidłowe dane logowania" ) .'
		'.showInput( "auth", "notActive", "Konto nie jest aktywne" ) .'
		'.showInput( "auth", "too_unactive", "Brak aktywności przez X minut, wylogowanie" ) .'
		'.showInput( "auth", "system_off", "Wylogowanie z powodu wyłączenia systemu" ).'
		'.showInput( "auth", "session_failes", "Komunikat pojawiający się przy `tajemniczym` wylogowaniu" ));
		$authes = $db->query('SELECT * FROM `langs` WHERE `category` = "custom_auth"');
		while($auth = $authes->fetch())
		{
			echo(showInput("custom_auth", $auth['label'], "Własny link dodany z PMA (wyświetlany w menu dla zalogowanego)"));
		}
	echo('</div>

</div>
<input type="submit" value="Wyślij żądanie" class="alt_btn" name="submit">
</form>');

?>