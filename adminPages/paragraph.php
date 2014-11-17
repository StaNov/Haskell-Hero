<?php
/*
 * ADMINPAGES/PARAGRAPH.PHP
 * =============
 * Admin page for editing paragraphs.
 *
 * input:
 * action: add, modify, modifyR, delete, deleteR   | what to do with the paragraph
 * lessid: int                                     | id of lesson of adding paragraph
 * paraid: int                                     | id of selected paragraph
 * num: int                                        | number of newly adding / modifying paragraph
 * name: string                                    | name of modifying paragraph
 * text: string                                    | text of modifying paragraph
 * bigchange:                                      | is set if change of paragraph is significant and ratings have to be cleared
 */

if (!$_SESSION["isadmin"])
    die("Na tuto stránku mají přístup pouze administrátoři.");

require_once "./mysqlStuff.php";
require_once "./frames.php";

$con = db_connect();

/**
 * Adds paragraph and shows dialog for it's editing.
 */
function addPrgph() {
    global $con;
    $lessid = $_REQUEST["lessid"];
    $num = $_REQUEST["num"];

    $ok = $con->query("INSERT
                       INTO paragraph(lessonId, number, name, text)
                       VALUES ($lessid, $num, \"Nový odstavec\", \"<p>Text nového odstavce</p>\")");
    if (!$ok) {
        showFrame("Nepodařilo se vložit nový odstavec", false);
        return;
    }

    showFrame("Nový odstavec byl <b>úspěšně přidán.</b>", true);

    $paraId = getIdOfLastPrgph();

    modifyPrgph($paraId);
}

/**
 * Shows dialog for editing paragraph of given ID in argument $paraid.
 */
function modifyPrgph($paraid) {
    global $con;

    if (!isset($_REQUEST["name"])) { // editing of paragraph is shown for the first time, it has to be loaded from database
        $resultSet = $con->query("SELECT number, name, text
                                    FROM paragraph
                                    WHERE paragraphid=$paraid");
        if (!$resultSet) {
            showFrame("Nepodařilo se načíst odstavec z databáze.<br>" . $con->error, false);
            return;
        }

        $paragraph = $resultSet->fetch_array();
        $number = $paragraph["number"];
        $name = $paragraph["name"];
        $text = $paragraph["text"];

        $resultSet->close();
    } else { // paragraph has been already edited
        $number = $_REQUEST["num"];
        $name = $_REQUEST["name"];
        $text = $_REQUEST["text"];
    }
?>
    <h1>Úprava odstavce</h1>

    <form action="index.php?page=admin&section=paragraph&action=modify#paragraph" method="post">
        <input type="hidden" name="paraid" value="<?php echo $paraid ?>">
        <table>
            <tr>
                <td align="right">Číslo odstavce:</td>
                <td><input name="num" value="<?php echo $number ?>"></td>
            </tr>
            <tr>
                <td align="right">Název odstavce:</td>
                <td><input name="name" value="<?php echo $name ?>"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <textarea name="text" cols="80" rows="30"><?php echo $text ?></textarea>
                </td>
            </tr>
        </table>

        <input type="submit" value="Náhled / provést změny">

    </form>

    <div class="paragraph" align="left" style="width: 600px;" id="paragraph">
        <h2><?php echo $name ?></h2>

    <?php echo $text ?>
</div>

<form action="index.php?page=admin&section=paragraph&action=modifyR" method="post">
    <input type="hidden" name="paraid" value="<?php echo $paraid ?>">
    <input type="hidden" name="num" value="<?php echo $number ?>">
    <input type="hidden" name="name" value="<?php echo $name ?>">
    <input type="checkbox" name="bigchange"> Velká změna<br><br>
    <input type="submit" value="Uložit změny">
    <br>
    <textarea name="text" style="visibility: hidden" rows="0" cols="0" style="width: 0px; height: 0px"><?php echo $text ?></textarea>


</form>
<?php
}

/**
 * Updates paragraph in database. If there has been big changes made, deletes
 * paragraph ratings.
 */
function modifyPrgphR() {
    /*
     * 1. Updates paragraph
     * 2. If big change has been made, deletes all paragraph ratings
     */
    global $con;

    $paraid = $_REQUEST["paraid"];
    $number = $_REQUEST["num"];
    $name = $_REQUEST["name"];
    $text = $_REQUEST["text"];

    $statement = $con->prepare("UPDATE paragraph
                                SET number=?, name=?, text=?
                                WHERE paragraphid=?");
    $statement->bind_param("issi", $number, $name, $text, $paraid);
    $ok = $statement->execute(); // (1)

    if (!$ok) {
        showFrame("Nepodařilo se změnit obsah odstavce.<br>" . mysql_error, false);
        return;
    }

    if (isset($_REQUEST["bigchange"])) { // (2)
        $ok = $con->query("DELETE
                       FROM paragraphrating
                       WHERE paragraphid=$paraid");
        if (!$ok) {
            showFrame("Nepodařilo se odstranit hodnocení odstavce z databáze.<br>" . $con->error, false);
            return;
        }
    }

    showFrame("Odstavec <b>$name</b> byl úspěšně změněn.", true);
}

/**
 * Shows confirmation dialog for deleting paragraph.
 */
function deletePrgph() {
    $paraid = $_REQUEST["paraid"];
    echo "<h1>Smazat odstavec</h1>
            <a href=\"index.php?page=admin&section=paragraph&action=deleteR&paraid=$paraid\">Opravdu smazat odstavec!</a>";
}

/**
 * Deletes paragraph.
 */
function deletePrgphR() {
    /*
     * 1. Delete paragraph
     * 2. Delete all paragraph ratings
     */
    global $con;
    $paraid = $_REQUEST["paraid"];

    echo "<h1>Smazat odstavec</h1>";

    $ok = $con->query("DELETE
                       FROM paragraph
                       WHERE paragraphid=$paraid"); // (1)
    if (!$ok) {
        showFrame("Nepodařilo se odstranit odstavec.", false);
        return;
    }


    $ok = $con->query("DELETE
                       FROM paragraphrating
                       WHERE paragraphid=$paraid"); // (2)

    if (!$ok) {
        showFrame("Nepodařilo se odstranit hodnocení odstavce.", false);
        return;
    }

    showFrame("Odstavec byl úspěšně odstraněn.", true);
}

/**
 * Returns ID of last paragraph in database.
 *
 * Used in function addParagraph().
 */
function getIdOfLastPrgph() {
    global $con;

    $resultSet = $con->query("SELECT paragraphid
                                FROM paragraph
                                ORDER BY paragraphid DESC LIMIT 1");
    if (!$resultSet) {
        showFrame("Nepodařilo se zjistit id posledního odstavce.", false);
        return;
    }

    $paragraph = $resultSet->fetch_array();

    return $paragraph["paragraphid"];
}
?>











<center>
    <?php
    switch ($_REQUEST["action"]) {
        case "add":
            addPrgph();
            break;
        case "modify":
            modifyPrgph($_REQUEST["paraid"]);
            break;
        case "modifyR":
            modifyPrgphR();
            break;
        case "delete":
            deletePrgph();
            break;
        case "deleteR":
            deletePrgphR();
            break;
        default:
            showFrame("Akce " . $_REQUEST["action"] . " nebyla nalezena.", false);
            break;
    }
    ?>
</center>

<?php
    $con->close();
?>