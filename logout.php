<?php
/*
 * LOGOUT.PHP
 * ==========
 * Logs user out.
 */
require_once "frames.php";

$login = $_SESSION["login"];

if ($login) { // someone is logged in
    foreach ($_SESSION as $key => $value) { // makes array of keys
        $keys[] = $key;
    }

    foreach ($keys as $key) { // deletes user info
        unset($_SESSION[$key]);
    }

    $ok = true;
}




?>
<center>
    <h1>Odhlásit se</h1>
<?php
if ($ok) {
    showFrame("Uživatel <b>$login</b> byl úspěšně odhlášen.", true);
} else {
    showFrame("Žádný uživatel <b>není přihlášen</b>.", false);
}
?>
    <a href="index.php?page=login">Přihlásit se jako jiný uživatel</a>
</center>