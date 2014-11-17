<?php
/*
 * PROFILE.PHP
 * ===========
 *
 * Page showing info about user.
 *
 * input:
 * userid : int | user's id whose profile should be displayed
 */

require_once "frames.php";
require_once "mysqlStuff.php";
require_once "levels.php";


/**
 * Gets data from DB of user with given ID.
 *
 * @param $userId ID of user whose data should be fetched
 * @return array of data in shape:
 *      ["login","uco","isAdmin","isSubscripted","problemsXp","discussionXp"]
 *      or null if person with $userId doesn't exist
 */
function getDataFromDb($userId) {
    $data = getPersonalInfo($userId);
    if($data == null)
        return null; // user not found in DB
    $data["problemsXp"] = getProblemsXp($userId);
    $data["discussionXp"] = getDiscussionXp($userId);
    return $data;
}


/**
 * Gets personal info about person.
 *
 * @param $userId ID of person whose info should be fetched
 * @return array of data in shape:
 *      ["login","uco","isAdmin","isSubscripted"]
 *      or null if person with $userId doesn't exist
 */
function getPersonalInfo($userId) {
    $con = db_connect();

    $statement = $con->prepare("SELECT login, uco, isAdmin, isSubscribed
                                FROM user
                                WHERE userid=?");
    $statement->bind_param("i", $userId);
    $statement->execute();

    $statement->bind_result($data["login"], $data["uco"], $data["isAdmin"], $data["isSubscribed"]);

    $userExists = $statement->fetch();

    if(! $userExists)
        return null;

    $statement->close();
    $con->close();

    return $data;
}


/**
 * Gets total amount of XP points that user has obtained for solving problems.
 * Assumes that the user exists and $userId is integer.
 *
 * @param $userId ID of user
 */
function getProblemsXp($userId) {

    $con = db_connect();

    $resultSet = $con->query("SELECT SUM(xp) AS xp
                              FROM problemsolve NATURAL JOIN problem
                              WHERE userid=$userId");
    $result = $resultSet->fetch_array();

    $resultSet->close();
    $con->close();
    
    return (int) $result["xp"];

}




/**
 * Gets total amount of XP points that user has obtained for adding posts
 * to discussion. Assumes that the user exists and $userId is integer.
 *
 * @param $userId ID of user
 */
function getDiscussionXp($userId) {

    $con = db_connect();

    $resultSet = $con->query("SELECT SUM(xp) AS xp
                              FROM discussion
                              WHERE userid=$userId");
    $result = $resultSet->fetch_array();

    $resultSet->close();
    $con->close();

    return (int) $result["xp"];

}


/**
 * Shows public information about user with given ID.
 *
 * @param $data personal data of user to be shown
 */
function showProfile($data) {

?>
    <h1>Profil uživatele <?php echo $data["login"] ?></h1>

<?php
if($data["isAdmin"]) {
    echo "<h2>Administrátor</h2>";
} else {
    
    $xpTotal = $data["problemsXp"] + $data["discussionXp"];

?>
    <h2><?php echo "Level ".getLevelNum($xpTotal).": ".getLevelName($xpTotal) ?></h2>

    <table border="1" cellspacing="0" cellpadding="3px">
        <tr>
            <td>Vyřešené příklady</td>
            <td><?php echo $data["problemsXp"] ?> xp</td>
        </tr>
        <tr>
            <td>Příspěvky v diskuzi</td>
            <td><?php echo $data["discussionXp"] ?> xp</td>
        </tr>
        <tr>
            <th>Celkem</th>
            <th><?php echo $xpTotal ?> xp</th>
        </tr>
    </table>
<?php
}


if($data["uco"] != 0) {
?>
    <p>
        <a href="https://is.muni.cz/auth/osoba/<?php echo $data["uco"] ?>">
            Osobní stránka uživatele <?php echo $data["login"] ?> v ISu
        </a> (autorizovaný vstup)
    </p>
<?php
}

}


/**
 * Shows private options of user with given ID.
 *
 * @param $data personal data of user to be shown
 */
function showOptions($data) {

?>
    <h2>Odběr novinek e-mailem</h2>

    <form action="index.php?page=profile&userid=<?php echo $_GET["userid"] ?>" method="post">
        <input type="hidden" name="action" value="subscribe">
        <label>
            <input type="checkbox" name="subscribe" value="true"<?php if($data["isSubscribed"]) echo " checked" ?>>
            Odebírat novinky e-mailem
        </label>
        <input type="submit" value="Uložit">
    </form>
<?php
}


/**
 * Saves in DB information about (un)subscribing user to email newsletter.
 * Assumes that $userId is integer.
 *
 * Can be enhanced with other options. (changing password etc.)
 *
 * @param $usedId ID of user
 */
function saveOptions($userId) {

    if($userId != $_SESSION["id"]) {
        showFrame("Odběr novinek může přihlášený uživatel nastavit pouze pro sebe!", false);
        return;
    }

    $subscribe = (int) isset($_POST["subscribe"]);

    $con = db_connect();

    $ok = $con->query("UPDATE user
                       SET isSubscribed=$subscribe
                       WHERE userid=$userId");
    
    if($ok) {
        if($subscribe) {
            showFrame("Úspěšně bylo nastaveno <b>přihlášení</b> odběru novinek!", true);
        } else {
            showFrame("Úspěšně bylo nastaveno <b>odhlášení</b> odběru novinek!", true);
        }
    } else {
        showFrame("<b>Nepodařilo se</b> uložit nastavení: ".$con->error, false);
    }

    $con->close();
}





?>
<center>
<?php

if (!isset($_GET["userid"]) || $_GET["userid"] <= 0) {
    showFrame("Nenalezeno ID uživatele v adrese!", false);
} else {
    $userId = (int) $_GET["userid"];
    if(isset($_POST["action"])) saveOptions($userId);
    $data = getDataFromDb($userId);
    if($data == null) {
        showFrame("Uživatel neexistuje!", false);
    } else {
        showProfile($data);
        if($userId == $_SESSION["id"]) {
            showOptions($data);
        }
    }
}
?>
</center>