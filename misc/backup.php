<?php
/*
 * BACKUP.PHP
 * ==================
 *
 * Creates a backup of database into file 'backup.txt'.
 *
 * Structure of the backup file:
 *
 *       lessonid
 *     == ATRIBUTE ==
 *       number
 *     == ATRIBUTE ==
 *       name
 *     == ATRIBUTE ==
 *       public
 *   == LESSON ==
 *     [more lessons ...]
 * == MAIN ==
 *       paragraphid
 *     == ATRIBUTE ==
 *       lessonid
 *     == ATRIBUTE ==
 *       number
 *     == ATRIBUTE ==
 *       name
 *     == ATRIBUTE ==
 *       text
 *   == PARAGRAPH ==
 *     [more paragraphs ...]
 * == MAIN ==
 *       problemid
 *     == ATRIBUTE ==
 *       lessonid
 *     == ATRIBUTE ==
 *       number
 *     == ATRIBUTE ==
 *       task
 *     == ATRIBUTE ==
 *       answer
 *     == ATRIBUTE ==
 *       xp
 *     == ATRIBUTE ==
 *       public
 *   == PROBLEM ==
 *     [more problems ...]
 */

require_once "../mysqlStuff.php";
require_once "../frames.php";


/**
 * Gets contain of table "lesson" and formats it.
 *
 * Returns string made of information about lessons in database.
 */
function backupLessons() {

    $con = db_connect();

    $resultSet = $con->query("SELECT * FROM lesson");

    while ($row = $resultSet->fetch_array()) {
        if(isset($result)) { // add line, but not at the beginning
            $result .= "== LESSON ==";
        }

        $result .= $row["lessonid"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["number"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["name"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["public"];
    }

    $con->close();

    return $result;
}


/**
 * Gets contain of table "paragraph" and formats it.
 *
 * Returns string made of information about paragraphs in database.
 */
function backupParagraphs() {

    $con = db_connect();

    $resultSet = $con->query("SELECT * FROM paragraph");

    while ($row = $resultSet->fetch_array()) {
        if(isset($result)) { // add line, but not at the beginning
            $result .= "== PARAGRAPH ==";
        }

        $result .= $row["paragraphid"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["lessonid"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["number"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["name"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["text"];
    }

    $con->close();

    return $result;
}


/**
 * Gets contain of table "problem" and formats it.
 *
 * Returns string made of information about problems in database.
 */
function backupProblems() {

    $con = db_connect();

    $resultSet = $con->query("SELECT * FROM problem");

    while ($row = $resultSet->fetch_array()) {
        if(isset($result)) { // add line, but not at the beginning
            $result .= "== PROBLEM ==";
        }

        $result .= $row["problemid"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["lessonid"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["number"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["task"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["answer"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["xp"];
        $result .= "== ATRIBUTE ==";
        $result .= $row["public"];
    }

    $con->close();

    return $result;
}















// main part of script
$backup = backupLessons();
$backup .= "== MAIN ==";
$backup .= backupParagraphs();
$backup .= "== MAIN ==";
$backup .= backupProblems();

$ok = file_put_contents("backup.txt", $backup);



?>
<center>
    <div style="border: <?php echo $ok ? "green" : "red" ?> solid; background-color: <?php echo $ok ? "lightgreen" : "pink" ?>">
        <h1>
            <?php
            if($ok) {
                echo "Ucebnice byla uspesne zalohovana do souboru backup.txt!";
            } else {
                echo "Ulozeni se nepovedlo!";
            }
            ?>
        </h1>
    </div>
</center>