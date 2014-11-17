<?php
/*
 * LESSON.PHP
 * ==========
 *
 * Shows paragraphs of a lesson and exercises.
 *
 * input:
 * id: int       | id of rated paragraph
 * rating: 1,2,3 | new rating of paragraph
 */
//require_once "mysqlStuff.php";
require_once "frames.php";

$con = db_connect();

/**
 * Shows headline of lesson.
 *
 * @global <connection> $con connection to DB
 * @param <resultset row> $lesson lesson information
 * @return <boolean> true if lesson exists, false otherwise
 */
function showHeadline($lesson) {
    global $con;

    if ((int) $lesson <= 0) { // bad number or string
        showFrame("Položka <b>lesson</b> musí obsahovat <b>celé kladné číslo</b>.", false);
        return false;
    }


    $resultSet = $con->query("SELECT name, public
                              FROM lesson
                              WHERE lessonid=$lesson");

    if ($result = $resultSet->fetch_array()) {
        if ($result["public"] || $_SESSION["isadmin"]) { // lesson is public or user is admin
            $headline = $result["name"];
            echo "<h2>$headline</h2>";
            return true;
        } else { // lesson is not public and user is not admin
            showFrame("Lekce <b>$lesson</b> není zveřejněná. Pokud tento stav přetrvá delší dobu, obraťte se na <b>administrátora</b>.", false);
            return false;
        }
    } else { // lesson doesnt exists
        showFrame("Lekce <b>$lesson</b> neexistuje. Vyber lekci z levého menu.", false);
        return false;
    }
}

/**
 * Shows paragraphs of lesson.
 *
 * @global <connection> $con connection to DB
 * @param <resultset row> $lesson lesson information
 */
function showParagraphs($lesson) {
    global $con;

    $resultSet = $con->query("SELECT paragraphid, number, name, text
                              FROM paragraph
                              WHERE lessonId=$lesson
                              ORDER BY number, paragraphid");
    $paragraphNum = 0; // if there are no paragraphs in the lesson

    while ($paragraph = $resultSet->fetch_array()) {
        $id = $paragraph["paragraphid"];
        $headline = $paragraph["name"];
        $text = $paragraph["text"];
        $paragraphNum = $paragraph["number"];

        showParagraph($id, $headline, $text, $paragraphNum);
    }
    if (isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"])
        echo "<a class=\"adminLink\" href=\"index.php?page=admin&section=paragraph&action=add&num=" . ($paragraphNum + 1) . "&lessid=$lesson\">Přidat odstavec</a>";
}

/**
 * Shows paragraph and its rating.
 *
 * @global <connection> $con connection to DB
 * @param <type> $id ID of showing paragraph
 * @param <type> $headline headline text
 * @param <type> $text text of the paragraph
 * @param <int> $number number of paragraph
 */
function showParagraph($id, $headline, $text, $number) {
    global $con;
    global $lesson;

    $allowRating = isset($_SESSION["id"]) && !$_SESSION["isadmin"]; // user can rate paragraps only if he is logged in and isn't admin

    if ($allowRating) {
        $resultSet = $con->query("SELECT rating
                                    FROM paragraphrating
                                    WHERE userid=" . $_SESSION["id"] . " AND paragraphid=$id");
        if (!$resultSet)
            showFrame("Nepodařilo se načíst ohodnocení odstavce.", false);

        $result = $resultSet->fetch_array();
        
        switch ($result["rating"]) {
            case 1:
                $paragraphClass = "paragraphOk";
                break;
            case 2:
                $paragraphClass = "paragraphIDK";
                break;
            case 3:
                $paragraphClass = "paragraphNoOk";
                break;
        }        
    }
?>
    <div class="paragraph<?php echo isset($paragraphClass) ? " $paragraphClass" : ""  ?>" id="<?php echo $id ?>">
        <div style="margin: 0px; padding: 0px;">
            <h3><?php echo $headline; if (isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) echo " ($number)" ?></h3>
            <span class="paragraphText"><?php echo $text ?></span>
        </div>

        <div align="right" style="padding: 0px;">
        <?php
        if ($allowRating)
            showButtons($id);
        if (isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"])
            showAdminStuff($id);
        ?>
    </div>
</div>
<?php
    }

    /**
     * Shows rating buttons.
     *
     * @global <int> $lesson ID of showing lesson
     * @param <int> $id of showing paragraph
     */
    function showButtons($id) {
        global $lesson;
?>
        <a href="index.php?page=lessons&lesson=<?php echo $lesson ?>&id=<?php echo $id ?>&rating=1#<?php echo $id ?>">
            <img src="button_yes.gif" alt="OK" width="30">
        </a>
        <a href="index.php?page=lessons&lesson=<?php echo $lesson ?>&id=<?php echo $id ?>&rating=2#<?php echo $id ?>">
            <img src="button_idk.gif" alt="IDK" width="30">
        </a>
        <a href="index.php?page=lessons&lesson=<?php echo $lesson ?>&id=<?php echo $id ?>&rating=3#<?php echo $id ?>">
            <img src="button_no.gif" alt="NO" width="30">
        </a>
<?php
    }
    
    
    /**
     * Returns number of lesson with $id, false if there is no lesson with $id.
     * No SQL injection protection.
     */
    function getLessonNumber($id) {
        global $con;
        
        $resultSet = $con->query("SELECT number
                                  FROM lesson
                                  WHERE lessonid = $id");
        $row = $resultSet->fetch_array();
        
        if (isset ($row["number"])) {
            return $row["number"];
        } else {
            return false;
        }
        
    }
    
    
    /**
     * Returns array "lessonid" => ID of next/previous leson
     *               "name"     => name of next/previous leson
     * or false if no next/previous leson exists.
     * 
     * No SQL injection protection.
     * 
     * @param $id ID of current lesson
     * @param $next true if the next lesson's info should be returned, false otherwise
     */
    function getLessonInfo($id, $next) {
        global $con;
        
        $number = getLessonNumber($id);
        $relation = $next ? ">" : "<";
        $orderBy = $next ? "ASC" : "DESC";
        // show only public lessons to non-admins
        $onlyPublic = isset($_SESSION["isadmin"]) && $_SESSION["isadmin"] ? "" : "AND public = 1";
        
        $resultSet = $con->query("SELECT lessonid, name
                                  FROM lesson
                                  WHERE number $relation $number $onlyPublic
                                  ORDER BY number $orderBy
                                  LIMIT 1");
        $row = $resultSet->fetch_array();
        
        if (! $row) return false; // no next lesson
        
        return array(
            "lessonid" => $row["lessonid"],
            "name" => $row["name"],
        );
    }
    
    
    /**
     * Shows navigation fields - next lesson, previous lesson of $lesson.
     */
    function showNavigation($lesson) {
        $previous = getLessonInfo($lesson, false);
        $next = getLessonInfo($lesson, true);
?>

<div style="text-align: center;">
<?php
    if ($previous) {
?>        
    <a href="index.php?page=lessons&lesson=<?php echo $previous["lessonid"] ?>">&lt;= <b><?php echo $previous["name"] ?></b> předchozí lekce</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
    }
    
    if ($next) {
?>        
    <a href="index.php?page=lessons&lesson=<?php echo $next["lessonid"] ?>">následující lekce <b><?php echo $next["name"] ?></b> =&gt;</a>
<?php
    }
?>
</div>

<?php
    }

    /**
     * Shows buttons Edit and Delete.
     *
     * @param <int> $id of showing paragraph
     */
    function showAdminStuff($paragraphId) {
?>
        <table>
            <tr>
                <td>
                    <form action="index.php?page=admin&section=paragraph" method="post">
                        <input type="hidden" name="action" value="modify">
                        <input type="hidden" name="paraid" value="<?php echo $paragraphId ?>">
                        <input type="submit" value="Upravit">
                    </form>
                </td>

                <td>
                    <form action="index.php?page=admin&section=paragraph" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="paraid" value="<?php echo $paragraphId ?>">
                        <input type="submit" value="Smazat">
                    </form>
                </td>
            </tr>
        </table>
<?php
    }

    $lesson = isset($_GET["lesson"])  ?  (int) $_GET["lesson"]  :  0;


    // <editor-fold defaultstate="collapsed" desc="save new rating of paragraph">
    if (isset($_REQUEST["rating"])) { // there is a paragraph with new user rating to be saved in database
        $resultSet = $con->query("SELECT *
                                    FROM paragraphrating
                                    WHERE userid=" . $_SESSION["id"] . " && paragraphid=" . $_REQUEST["id"]);

        if ($resultSet->fetch_array()) { //there is already some rating
            $ok = $con->query("UPDATE paragraphrating
                                SET rating=" . $_REQUEST["rating"] . "
                                WHERE userid=" . $_SESSION["id"] . " AND paragraphid=" . $_REQUEST["id"]);
            if (!$ok)
                showFrame("Nepodařilo se uložit ohodnocení odstavce!", false);
        } else { //create new rating
            $ok = $con->query("INSERT INTO paragraphrating (userid, paragraphid, rating)
                           VALUES (" . $_SESSION["id"] . ", " . $_REQUEST["id"] . ", " . $_REQUEST["rating"] . ")");
            if (!$ok)
                showFrame("Nepodařilo se uložit ohodnocení odstavce!", false);
        }
    }// </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="save shown page as last visited lesson">
    if ($lesson == 0 && isset($_SESSION["lastlesson"])) { // if there is last lesson set, set opening it
        $lesson = $_SESSION["lastlesson"];
    }// </editor-fold>



    if ($lesson == 0) { // show lesson
        echo "<h2>Vítej v učebnici!</h2>
          Lekci zobrazíš kliknutím na její název v levém menu.";
    } else {
        $lessonExists = showHeadline($lesson);
        if ($lessonExists) {
            showNavigation($lesson);
            showParagraphs($lesson);
            showNavigation($lesson);
            include "problems.php";
            include "discussion.php";
        }
    }

    $_SESSION["lastlesson"] = $lesson;


    $con->close();
?>
