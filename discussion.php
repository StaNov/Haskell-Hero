<?php
/*
 * DISCUSSION.PHP
 * ==============
 * Shows discussion of a lesson.
 *
 * input:
 * lesson: int          | id of selected lesson
 * $con: connection     | connection to DB
 * text: string         | text of received discussion post
 *
 * action : rate        | there is new rating of discussion post to be rated
 *          deletepost  | there is new rating of discussion post to be rated
 * points: int          | how many points to add
 * postid: int          | id of rated/deleted post
 * deleteit: ok         | if set, then delete post
 */
require_once "stringChecker.php";

/**
 * Shows discussion posts of selected lesson.
 */
function showPosts() {
    global $con;
    global $lesson;

    if (isset ($_POST["action"])  &&  isset ($_SESSION["isadmin"])  &&  $_SESSION["isadmin"])
        switch ($_POST["action"]) {
            case "rate":
                ratePost();
                break;
            case "deletepost":
                deletePost();
                break;
            case "archivepost":
                archivePost();
                break;
        }

    $result = $con->query("SELECT login, timestamp, text, isadmin, xp, discussionid, userid, archived
                           FROM discussion natural join user
                           WHERE lessonid = $lesson
                           ORDER BY timestamp ASC"); //contain of $lesson is checked at main.php
    if (!$result) {
        showFrame("Nepodařilo se načíst příspěvky z databáze.", false);
        return;
    }

    while ($post = $result->fetch_array()) {
        if(! $post["archived"]  ||  isset($_GET["showarchived"]))
            showPost($post);
    }

    $result->close();
}

/**
 * Shows discussion post in $post.
 */
function showPost($post) {
?>
<div class="discussionPost">
    <table cellpadding="0px" cellspacing="0px" width="100%">
        <tr>
            <td>
                <a href="index.php?page=profile&userid=<?php echo $post["userid"] ?>" title="Profil uživatele <?php echo $post["login"] ?>">
                    <?php echo $post["login"] ?>
                </a>
            </td>
            <td style="font-size: smaller; text-align: right" width="150px"><?php echo $post["timestamp"] ?></td>
        </tr>
    </table>

    <div class="discussionText" <?php if($post["isadmin"]) echo "style=\"font-weight: bold\"" ?>>
        <?php echo $post["text"] ?>
    </div>
    <?php
        if(! $post["isadmin"])
            showPointsDialog($post);

        if(isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) {
            showDeleteDialog($post);
            showArchiveDialog($post);
        }
    ?>
</div>

<?php
}

/**
 * Shows dialog for adding new discussion post.
 */
function showInputDialog() {
    global $lesson;
?>
    <h3 align="right">nový příspěvek</h3>
    <center>
        <form action="index.php?page=lessons&lesson=<?php echo $lesson ?>#discussion" method="post">

            <textarea name="text" cols="70" rows="8">Text příspěvku...</textarea><br>
            <input type="submit" value="Uložit">
        </form>
    </center>
<?php
}

/**
 * Saves new discussion post into database.
 *
 * Shows error message if text of the post is blank.
 */
function saveNewPost() {
    global $con;

    $text = $_POST["text"];
    $text = makePostSafe($text);

    if($text == "" || $text == "Text příspěvku...") {
        showFrame("Příspěvek nesmí být prázdný!", false);
        return;
    }

    $statement = $con->prepare("INSERT INTO discussion(lessonid, userid, text)
                                VALUES (?,?,?)") or die("Vnitřní chyba: ".$con->error);
    $statement->bind_param("iis", $_GET["lesson"], $_SESSION["id"], $text);
    $statement->execute() or die("Vnitřní chyba: ".$con->error);
    $statement->close();

    echo "<center>";
    showFrame("Diskuzní příspěvek byl <b>úspěšně uložen</b>!", true);
    echo "</center>";
}

/**
 * Shows dialog for editing xp points for the post if user is admin.
 *
 * Shows amount of xp points if it is greater than zero and user is not admin.
 */
function showPointsDialog($post) {
    global $lesson;
    
    $xp = $post["xp"];
    if(isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) { //if user is admin, show input dialog
        ?>

        <form method="post" action="index.php?page=lessons&lesson=<?php echo $lesson ?><?php if (isset($_GET["showarchived"])) echo "&showarchived=" ?>#discussion">
            Ohodnotit příspěvek
            <input name="points" value="<?php echo $post["xp"] ?>">
            <input name="action" value="rate" type="hidden">
            <input name="postid" value="<?php echo $post["discussionid"] ?>" type="hidden">
            <input name="timestamp" value="<?php echo $post["timestamp"]?>" type="hidden">
            body.
            <input type="submit" value="Ohodnotit">
        </form>

        <?php

    } elseif ($xp > 0) {
        ?>
            <div class="discussPoints">
                Tento příspěvek byl ohodnocen <b><?php echo $xp ?> xp</b> body.
            </div>
        <?php
    }
}

/**
 * Shows delete dialog of discussion post.
 */
function showDeleteDialog($post) {
    global $lesson;
    
    $id = $post["discussionid"];
    ?>

    <form action="index.php?page=lessons&lesson=<?php echo $lesson ?><?php if (isset($_GET["showarchived"])) echo "&showarchived=" ?>#discussion" method="post">
        Smazat příspěvek?
        <input type="checkbox" name="deleteit" value="ok">
        <input type="hidden" name="action" value="deletepost">
        <input type="hidden" name="postid" value="<?php echo $id ?>">
        <input type="submit" value="Smazat">
    </form>

    <?php
}

/**
 * Shows button for archiving and dearchiving the post.
 */
function showArchiveDialog($post) {
    global $lesson;
    
    ?>
    <form method="post" target="index.php?page=lessons&lesson=<?php echo $lesson ?><?php if (isset($_GET["showarchived"])) echo "&showarchived=" ?>#discussion">
        <input type="hidden" name="action" value="archivepost" />
        <input type="hidden" name="discussionid" value="<?php echo $post["discussionid"]?>"/>
        <input type="hidden" name="archive" value="<?php echo $post["archived"] ? "false" : "true" ?>" />
        <input type="submit" value="<?php echo $post["archived"] ? "Dearchivovat" : "Archivovat" ?>">
    </form>
    <?php
}

/**
 * Deletes discussion post if user is admin. Shows error message otherwise.
 */
function deletePost() {
    global $con;

    if(! $_SESSION["isadmin"]) {
        showFrame("Příspěvky smí mazat pouze administrátoři!", false);
        return;
    }

    if(!isset($_POST["deleteit"])) {
        showFrame("Musí být zaškrtnuto políčko <b>Smazat příspěvek</b>!", false);
        return;
    }

    $statement = $con->prepare("DELETE
                                FROM discussion
                                WHERE discussionid=?")
            or die("Vnitřní chyba: ".$con->error);

    $statement->bind_param("i", $_POST["postid"])
            or die("Vnitřní chyba: ".$con->error);

    $statement->execute()
            or die("Vnitřní chyba: ".$con->error);

    $statement->close();

    showFrame("Příspěvek byl <b>úspěšně smazán</b>!", true);
}

/**
 * Saves post rating if user is admin. Shows error message otherwise.
 */
function ratePost() {
    global $con;

    if(! $_SESSION["isadmin"]) {
        die("Příspěvky smí hodnotit pouze administrátoři!");
    }

    $statement = $con->prepare("UPDATE discussion
                                SET xp=?, timestamp=?
                                WHERE discussionid=?")
            or die("Vnitřní chyba: ".$con->error);

    $statement->bind_param("isi", $_POST["points"], $_POST["timestamp"], $_POST["postid"])
            or die("Vnitřní chyba: ".$con->error);

    $statement->execute()
            or die("Vnitřní chyba: ".$con->error);

    showFrame("Příspěvek byl <b>úspěšně ohodnocen</b>!", true);
}

/**
 * Sets post as archived.
 */
function archivePost() {
    global $con;
    
    $archiveInt = $_POST["archive"] == "true" ? 1 : 0; // 1 = archive it, 0 = dearchive it
    
    $query = "UPDATE discussion
              SET archived=$archiveInt
              WHERE discussionid=" . (int) $_POST["discussionid"];
    
    $ok = $con->query($query);
    
    if($ok) {
        showFrame("Archivace byla <b>úspěšně provedena</b>!", true);
    } else {
        showFrame("Příspěvek se <b>nepodařilo archivovat</b>: " . $con->error, false);
    }
}

/**
 * Shows link that toggles displaying archived posts.
 */
function showViewArchivedLink() {
    global $lesson;
    
    echo "<div class=\"viewarchived\">";
    
    if (isset ($_GET["showarchived"])) {
        ?>
        <a class="viewarchived" href="index.php?page=lessons&lesson=<?php echo $lesson ?>#discussion">Skrýt archivované příspěvky</a>
        <?php
    } else {
        ?>
        <a class="viewarchived" href="index.php?page=lessons&lesson=<?php echo $lesson ?>&showarchived=#discussion">Zobrazit všechny příspěvky, včetně archivovaných</a>
        <?php
    }
    
    echo "</div>";
    
}
?>
















<hr>

<div id="discussion">
    <h3>diskuze</h3>
    <?php
    if(isset($_POST["text"])) saveNewPost();

    showPosts();
    showViewArchivedLink();
    
    if (isset($_SESSION["id"])) {
        echo "<hr>";
        showInputDialog();
    }
    ?>
</div>
