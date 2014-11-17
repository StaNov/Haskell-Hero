<?php
/* 
 * ADMINPAGES/EMAIL.PHP
 * ====================
 *
 * Sends email to all subscribed users and show input dialog.
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

require_once "./mysqlStuff.php";
require_once "./frames.php";


/**
 * Gets email info from the $_POST variable and sends mail to all subscribed
 * users.
 */
function sendEmail() {
    $con = db_connect();

    $resultSet = $con->query("SELECT email
                              FROM user
                              WHERE email <> '' AND isSubscribed=1");
    while($result = $resultSet->fetch_array()) {
        $ok = mail($result["email"], $_POST["subject"], $_POST["message"]);

        if(!$ok) {
            showFrame ("Email se <b>nepodařilo odeslat!</b>", false);
            return;
        }
    }

    showFrame("Hromadný email byl <b>úspěšně odeslán</b>!", true);

    $resultSet->close();
    $con->close();
}





?>
<center>
    <h1>Poslat hromadný e-mail</h1>
<?php


if(isset ($_POST["subject"])) {
    if($_POST["subject"] == "" || $_POST["message"] == "") {
        showFrame("Nebyl vyplněn předmět nebo tělo zprávy!", false);
    } else {
        sendEmail();
    }
}



?>
    <form method="post" action="index.php?page=admin&section=email">
        Předmět: <input name="subject" value="Předmět zprávy" ><br>
        <textarea name="message" cols="80" rows="25">Text zprávy</textarea><br>
        <input type="submit" value="Odeslat e-mail">
    </form>
</center>