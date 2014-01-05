<?php
session_start();
unset($_SESSION['auth']);
setcookie("auth[nick]", NULL, 1);
setcookie("auth[pass]", NULL, 1);
setcookie("lang", NULL, 1);
header('Location: index.php');
exit();
?>