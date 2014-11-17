<?php
/* 
 * ADMINMENU.PHP
 * =============
 *
 * Shows menu for admins.
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

?>

<div id="adminMenu">
    |
    <a href="index.php?page=admin&section=lessons">
        Spravovat lekce
    </a>
    |
    <a href="index.php?page=admin&section=users">
        Spravovat uživatele
    </a>
    |
    <a href="index.php?page=admin&section=email">
        Napsat hromadný e-mail
    </a>
    |
    <a href="index.php?page=admin&section=discussion">
        Všechny diskuze
    </a>
    |
</div>