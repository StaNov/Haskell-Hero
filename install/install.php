<?php
/*
#!/packages/run/php/bin/php
=== Uncomment if needed ===
*/

/* 
 * INSTALL/INSTALL.PHP
 * ===================
 *
 * Installs textbook in database. Assumes input from page "/install/index.php".
 * There has to be file "backup.txt" in the "install" folder.
 *
 * - checks input fields
 * - checks database connection
 * - creates tables
 * - fills tables with contain
 * - creates first administrator
 */

require_once "../frames.php";
require_once "../mysqlStuff.php";


/**
 * Checks values given in installation form.
 *
 * Returns true if all of them are correct, false otherwise.
 */
function checkValues() {
    if(empty($_POST["login"])) {
        showFrame("Není vyplněno pole <b>Login</b>.", false);
        return false;
    }

    if(strlen($_POST["login"]) > 16) {
        showFrame("Položka <b>Login</b> může být dlouhá maximálně <b>16 znaků</b>", false);
        return false;
    }

    if(strlen($_POST["password"]) < 6 || strlen($_REQUEST["password"]) > 15) {
        showFrame("Heslo musí být dlouhé alespoň <b>6</b> a maximálně <b>15</b> znaků.", false);
        return false;
    }

    if((int)$_POST["uco"] <= 0 && ! empty($_POST["uco"])) {
        showFrame("V položce <b>UČO</b> musí být kladné celé číslo.", false);
        return false;
    }

    if($_POST["uco"] > 10000000) {
        showFrame("<b>UČO</b> je moc velké.", false);
        return false;
    }

    if(strlen($_POST["email"]) > 64) {
        showFrame("Email může být dlouhý maximálně <b>64</b> znaků.", false);
        return false;
    }

    showFrame("Kontrola údajů proběhla úspěšně!", true);
    return true;
}

/**
 * Tries to connect to database.
 *
 * Returns true on success, false otherwise.
 */
function tryDbConnection() {
    $con = db_connect();

    if($con->connect_errno) {
        showFrame("Nepodařilo se připojit k databázi: ".$con->error, false);
        return false;
    }

    $con->close();

    showFrame("Test připojení k databázi uspěl!", true);
    return true;
}

/**
 * Creates tables in database.
 *
 * Returns true on success, false otherwise.
 */
function createTables() {
    $con = db_connect();

    $ok = $con->query("CREATE TABLE IF NOT EXISTS  `discussion` (
                          `discussionid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                          `lessonid` INT NOT NULL ,
                          `userid` INT NOT NULL ,
                          `timestamp` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                          `text` VARCHAR( 2048 ) NOT NULL ,
                          `xp` INT NOT NULL
                      )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `lesson` (
                                `lessonid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `number` INT NOT NULL DEFAULT  '0',
                                `name` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL ,
                                `public` BOOL NOT NULL DEFAULT  '0'
                               )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `paragraph` (
                                `paragraphid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `lessonid` INT NOT NULL ,
                                `number` INT NOT NULL DEFAULT  '0',
                                `name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL ,
                                `text` VARCHAR( 8192 ) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL DEFAULT  ''
                               )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `paragraphrating` (
                                `userid` INT NOT NULL ,
                                `paragraphid` INT NOT NULL ,
                                `rating` INT NOT NULL ,
                                PRIMARY KEY (  `userid` ,  `paragraphid` )
                               )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `problem` (
                                `problemid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `lessonid` INT NOT NULL ,
                                `number` INT NOT NULL ,
                                `task` VARCHAR( 1024 ) NOT NULL DEFAULT  '',
                                `answer` VARCHAR( 256 ) NOT NULL DEFAULT  '',
                                `xp` INT NOT NULL DEFAULT  '1',
                                `public` BOOL NOT NULL DEFAULT  '0'
                               )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `problemsolve` (
                                `userid` INT NOT NULL ,
                                `problemid` INT NOT NULL ,
                                PRIMARY KEY (  `userid` ,  `problemid` )
                               )");

    if($ok) $ok = $con->query ("CREATE TABLE IF NOT EXISTS  `user` (
                                `userid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `password` VARCHAR( 40 ) NOT NULL ,
                                `login` VARCHAR( 16 ) NOT NULL ,
                                `uco` INT NOT NULL DEFAULT  '0',
                                `email` VARCHAR( 64 ) NOT NULL ,
                                `isAdmin` BOOL NOT NULL DEFAULT  '0',
                                `isSubscribed` BOOL NOT NULL DEFAULT  '1'
                               )");
    $con->close();

    if($ok) {
        showFrame("Úspěšně byly přidány tabulky do databáze!", true);
        return true;
    } else {
        showFrame("Při přidávání tabulek do databáze se vyskytla chyba.", false);
    }
}

/**
 * Fills database with contain from file "backup.txt" in folder "install".
 *
 * Returns true on success, false otherwise.
 */
function insertContain() {
    $backup = file_get_contents("backup.txt");

    if(! $backup) {
        showFrame("Soubor <code>backup.txt</code> nebyl nalezen!", false);
        return false;
    }

    $exploded = explode("== MAIN ==", $backup);

    $ok = insertLessons($exploded[0]);
    if($ok) $ok = insertParagraphs($exploded[1]);
    if($ok) $ok = insertProblems($exploded[2]);

    showFrame("Tabulky byly úspěšně naplněny obsahem!", true);

    return true;
}

/**
 * Adds first administrator into the table "user".
 *
 * Returns true on success, false otherwise.
 */
function addAdminToDb() {
    $con = db_connect();

    $login = $_REQUEST["login"];
    $pass = $_REQUEST["password"];
    $uco = $_REQUEST["uco"];
    $email = $_REQUEST["email"];

    $statement = $con->prepare("INSERT INTO `user`(login, password, uco, email, isadmin)
                                VALUES            (?    , ?       , ?  , ?    , 1      )");
    $pass = sha1($pass); // encrypts password

    $statement->bind_param("ssis", $login, $pass, $uco, $email);

    $ok = $statement->execute();

    if(!$ok) {
        showFrame("Osobu se nepodařilo uložit do databáze: ".$con->error, false);
    } else {
        showFrame("Administrátor byl uložen do databáze!", true);
    }

    $con->close();
    return $ok;
}

/**
 * Inserts lessons from string $lessonsStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertLessons($lessonsStr) {

    $lessons = explode("== LESSON ==", $lessonsStr);

    foreach ($lessons as $lesson) {
        $ok = insertLesson($lesson);

        if(! $ok) return false;
    }

    return true;
}

/**
 * Inserts paragraphs from string $paragraphsStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertParagraphs($paragraphsStr) {

    $paragraphs = explode("== PARAGRAPH ==", $paragraphsStr);

    foreach ($paragraphs as $paragraph) {
        $ok = insertParagraph($paragraph);

        if(! $ok) return false;
    }

    return true;
}
/**
 * Inserts problems from string $problemsStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertProblems($problemsStr) {

    $problems = explode("== PROBLEM ==", $problemsStr);

    foreach ($problems as $problem) {
        $ok = insertProblem($problem);

        if(! $ok) return false;
    }

    return true;
}

/**
 * Inserts lesson from string $lessonStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertLesson($lessonStr) {

    $lesson = explode("== ATRIBUTE ==", $lessonStr);

    $con = db_connect();

    $statement = $con->prepare("INSERT INTO lesson (lessonid, number, name, public)
                                VALUES             (?       , ?     , ?   , ?     )");
    $statement->bind_param("iiss", $lesson[0], $lesson[1], $lesson[2], $lesson[3]);
    $ok = $statement->execute();

    $con->close();

    return $ok;
}

/**
 * Inserts paragraph from string $paragraphStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertParagraph($paragraphStr) {

    $paragraph = explode("== ATRIBUTE ==", $paragraphStr);

    $con = db_connect();

    $statement = $con->prepare("INSERT INTO paragraph (paragraphid, lessonid, number, name, text)
                                VALUES                (?          , ?       , ?     , ?   , ?   )");
    $statement->bind_param("iiiss", $paragraph[0], $paragraph[1], $paragraph[2], $paragraph[3], $paragraph[4]);
    $ok = $statement->execute();

    $con->close();

    return $ok;
}

/**
 * Inserts problem from string $problemStr into database.
 *
 * Returns true on success, false otherwise.
 */
function insertProblem($problemStr) {

    $problem = explode("== ATRIBUTE ==", $problemStr);

    $con = db_connect();

    $statement = $con->prepare("INSERT INTO problem (problemid, lessonid, number, task, answer, xp, public)
                                VALUES              (?        , ?       , ?     , ?   , ?     , ? , ?     )");
    $statement->bind_param("iiissii", $problem[0], $problem[1], $problem[2], $problem[3], $problem[4], $problem[5], $problem[6]);
    $ok = $statement->execute();

    $con->close();

    return $ok;
}














?>
<html>
    <head>
        <style type="text/css">
.okframe, .notokframe {
    margin: 10px;
    padding: 5px;
}

.okframe {
    border: green solid;
    background-color: lightgreen;
}

.notokframe {
    border: red solid;
    background-color: pink;
}
        </style>
    </head>

    <body>
        <?php
                 $ok = checkValues();
        if ($ok) $ok = tryDbConnection();
        if ($ok) $ok = createTables();
        if ($ok) $ok = insertContain();
        if ($ok) $ok = addAdminToDb();

        echo "<hr>";

        if ($ok) {
            showFrame("Instalace proběhla úspěšně! Nyní smaž složku <code>install</code>.<br><br>Příjemnou práci s učebnicí přeje StaNov!", true);
        } else {
            showFrame("Během instalace se vyskytla chyba. <a href=\"javascript: history.go(-1)\">Zpět na předchozí stránku</a>", false);
        }
        ?>
    </body>
</html>