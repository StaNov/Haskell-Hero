<?php
/*
#!/packages/run/php/bin/php
=== Uncomment if needed ===
*/

/*
 * index.php
 * =========
 * Main page. Includes header, main part of the page and footer.
 *
 * ---
 * input:
 * page: news, lessons, about
 * lesson: int
 * _session[id, login, uco, isadmin] data about logged user
 */


session_start();

// Nyan start
if (! isset ($_SESSION["redirected"])) {
    $_SESSION["redirected"] = TRUE;
    header("Location: http://nyanit.com/www.fi.muni.cz/~xnovak34/haskellhero/");
}
// Nyan end

header('Content-Type: text/html; charset=UTF-8');

//session_start(); // původní pozice
?>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="styles.css">
        <title>Haskell Hero</title>
    </head>
    <body>
        <center>
            <div class="mainContainer" align="left">
                <div class="topContainer">
                    <?php
                    include "header.php";
                    ?>
                </div>
                <div class="bottomContainer">
                    <?php
                    include "main.php";
                    ?>
                </div>
                <div class="footer" align="right">
                    <?php
                    include "footer.php";
                    ?>
                </div>
            </div>
        </center>
    </body>
</html>
