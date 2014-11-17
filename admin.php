<?php
/* 
 * ADMIN.PHP
 * =========
 *
 * Main page for admin interface.
 *
 * Shows admin menu and requested page.
 *
 * This page cannot be displayed by non-admin users.
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

include "adminPages/adminMenu.php";

if(! isset($_REQUEST["section"])) {
    ?>
    <center>
        <h2>Vítej, ó velký administrátore!</h2>
        <p>Vyber položku v horním menu.</p>
    </center>
    <?php

} else {
    $pageName = "adminPages/".$_REQUEST["section"].".php";

    if(file_exists($pageName)) {
        include $pageName;
    } else {
        echo "Stránka <b>$pageName</b> neexistuje.";
    }
}

?>
