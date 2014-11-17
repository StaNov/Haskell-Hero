<?php
/*
 * ADMINPAGES/LESSONS.PHP
 * ======================
 * Manages lessons.
 *
 * input:
 * action: add      | adds new lesson with default name
 *         modify   | shows form for modifying lesson
 *         modifyR  | performs modification and shows report
 *         delete   | asks for permission to delete lesson
 *         deleteR  | deletes lesson and shows report
 * lesson: int
 * name: string
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

require_once "./mysqlStuff.php";
require_once "./frames.php";

$con = db_connect();


/**
 * Performs action according to value in $_GET["action"] variable.
 */
function performAction() {
    switch ($_GET["action"]) {
        case "add":
            actionAdd();
            break;
        case "delete":
            actionDelete();
            break;
        case "deleteR":
            actionDeleteR();
            break;
        case "modify":
            actionModify();
            break;

        case "modifyR": //TODO dodělat testy na políčka
            actionModifyR();
            break;
    }
}

/*
 * Adds lesson with default name into database and shows report.
 */
function actionAdd() {
    
    global $con;

    $ok = $con->query("INSERT
                        INTO lesson(number, name)
                        VALUES (" . $_REQUEST["lesson"] . ", \"Nová lekce\")");
    
    if ($ok) {
        showFrame("Lekce byla <b>úspěšně vložena</b>.", $ok);
    } else {
        showFrame("Vložení lekce se <b>nezdařilo</b>.", $ok);
    }
}

/*
 * Asks for permission to delete lesson from database.
 */
function actionDelete() {
    
    echo "<a href=\"index.php?page=admin&section=lessons&action=deleteR&lesson=" . $_REQUEST["lesson"] . "&name=" . $_REQUEST["name"] . "\">
                        Opravdu smazat lekci <b>" . $_REQUEST["name"] . "</b>!
                  </a>
                  <br><br>";
}

/**
 * Deletes lesson from database.
 */
function actionDeleteR() {
    /*
     * 1. Deletes lesson from database.
     * 2. Deletes all ratings of paragraphs from database.
     * 3. Deletes all paragraphs of the lesson from database.
     * 4. Shows report.
     */
    global $con;

    $ok = $con->query("DELETE
                       FROM lesson
                       WHERE lessonid=" . $_REQUEST["lesson"]); // (1)

    if(!$ok) {
        showFrame("Lekci se <b>nepodařilo smazat</b>.", $ok);
        return;
    }

    // (2 start)
    $paragraphs = $con->query("SELECT paragraphid
                               FROM paragraph
                               WHERE lessonId=" . $_REQUEST["lesson"]);

    if(!$paragraphs) {
        showFrame("Nepodařilo se načíst odstavce.", false);
        return;
    }

    while($paragraph = $paragraphs->fetch_array()) {
        $ok = $con->query("DELETE
                           FROM paragraphrating
                           WHERE paragraphid=".$paragraph["paragraphid"]);
        
        if(!ok) {
            showFrame("Nepodařilo se smazat údaje o hodnocení odstavce.<br>".mysql_error, $ok);
        }
    }
    // (2 end)

    $ok = $con->query("DELETE
                       FROM paragraph
                       WHERE lessonId=" . $_REQUEST["lesson"]);
    
    if ($ok) { // (4)
        showFrame("Lekce <b>" . $_REQUEST["name"] . "</b> byla <b>úspěšně smazána</b>.", $ok);
    } else {
        showFrame("Lekci se <b>nepodařilo smazat</b>.", $ok);
    }
}

/**
 * Gets infromation about lesson from database and shows input form.
 */
function actionModify() {
    
    global $con;

    $resultset = $con->query("SELECT *
                              FROM lesson
                              WHERE lessonid=" . $_REQUEST["lesson"]); // (1)
    if (!$resultset) {
        showFrame("Lekci se nepodařilo načíst.", false);
        return;
    }

    $lesson = $resultset->fetch_array();
?>
    <form action="index.php" method="post">
        <input type="hidden" name="lesson" value="<?php echo $_REQUEST["lesson"] ?>">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="section" value="lessons">
        <input type="hidden" name="action" value="modifyR">

        <table class="adminTable" cellspacing="0" border="1">
            <thead class="adminTable">
                <tr bgcolor="#ff9900">
                    <td colspan="2">Úprava lekce</td>
                </tr>
            </thead>
            <tr>
                <td>Číslo:</td>
                <td><input name="number" value="<?php echo $lesson["number"] ?>" size="1"></td>
            </tr>
            <tr>
                <td>Název:</td>
                <td><input name="name" value="<?php echo $lesson["name"] ?>" size="50"></td>
            </tr>
            <tr>
                <td>Zveřejněná:</td>
                <td>
                    <input type="checkbox" name="public" value="true" <?php echo ($lesson["public"] ? "CHECKED" : "") ?>>
                </td>
            </tr>
        </table>
        <input type="submit" value="Provést změny">
    </form>
<?php // (2)
}

/**
 * Updates lesson in database and shows report.
 */
function actionModifyR() {
    
    global $con;

    $ok = $con->query("UPDATE lesson
                       SET number=" . $_REQUEST["number"] . ",
                           name=\"" . $_REQUEST["name"] . "\",
                           public=" . (isset($_REQUEST["public"]) ? "1" : "0") . "
                       WHERE lessonid=" . $_REQUEST["lesson"]);
    if ($ok) {
        showFrame("Lekce <b>" . $_REQUEST["name"] . "</b> byla <b>aktualizována</b>.", $ok);
    } else {
        showFrame("Lekci <b>" . $_REQUEST["name"] . "</b> se <b>nepodařilo</b> aktualizovat.", $ok);
    }
}
?>









<center>
    <h1>Správa lekcí</h1>
    <?php
    if (isset($_REQUEST["action"]))
        performAction();
    ?>

    <table class="adminTable" border="1px" cellspacing="0" cellpadding="3">
        <thead class="adminTable">
            <tr bgcolor="#ff9900">
                <td>číslo</td>
                <td>název</td>
                <td>zveřejněná?</td>
                <td>&nbsp;</td>
            </tr>
        </thead>

        <tbody>
            <?php
            $resultSet = $con->query("SELECT *
                                      FROM lesson
                                      ORDER BY number, lessonid");

            while($row = $resultSet->fetch_array()) {
            ?>
                <tr>
                    <td>
                    <?php echo $row["number"];
                    $lastNumber = $row["number"] ?>
                </td>
                <td>
                    <a href="index.php?page=lessons&lesson=<?php echo $row["lessonid"] ?>">
                        <?php echo $row["name"] ?>
                    </a>
                </td>
                <?php
                    $color = $row["public"] ? "#99ff99" : "#ff9999";
                ?>
                    <td bgcolor="<?php echo $color ?>">
                    <?php
                    if ($row["public"]) {
                        echo "ANO";
                    } else {
                        echo "NE";
                    }
                    ?>
                </td>
                <td>
                    &nbsp;
                    <a class="adminLink" href="index.php?page=admin&section=lessons&action=modify&lesson=<?php echo $row["lessonid"] ?>&name=<?php echo $row["name"] ?>">
                        upravit</a>
                    
                    <a class="adminLink" href="index.php?page=admin&section=lessons&action=delete&lesson=<?php echo $row["lessonid"] ?>&name=<?php echo $row["name"] ?>">
                        smazat</a>
                    &nbsp;
                </td>
            </tr>
            <?php
                }
            ?>
                <tr>
                    <td colspan="4">
                        <a href="index.php?page=admin&section=lessons&action=add&lesson=<?php echo ($lastNumber + 1) ?>">přidat lekci</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </center>

<?php
                $con->close();
?>