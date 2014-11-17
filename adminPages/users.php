<?php
/* 
 * USERS.PHP
 * =========
 *
 * Page that allows admins to delete users and change their administrational
 * status.
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

require_once "./mysqlStuff.php";
require_once "./frames.php";


/**
 * Handles request to delete or modify admin rights if there is one.
 */
function handleRequest() {
    
    if (isset ($_POST["action"]))
        switch ($_POST["action"]) {
            case "makeAdmin":
                makeAdmin();
                break;

            case "removeAdmin":
                removeAdmin();
                break;

            case "deleteUser":
                deleteUser();
                break;
        }
}

/**
 * Assigns admin rights to user if the corresponding checkbox is checked.
 * Shows error message otherwise.
 */
function makeAdmin() {
    if($_POST["changeAdmin"] != $_POST["userid"]) {
        showFrame("Pro přiřazení admin. práv musí být zaškrtnuto políčko nalevo od tlačítka.", false);
        return;
    }

    $con = db_connect();

    $ok = $con->query("UPDATE user
                       SET isAdmin=1
                       WHERE userid=".(int) $_POST["userid"]);
    if($ok) {
        showFrame("Uživatel byl <b>úspěšně prohlášen za administrátora</b>!", true);
    } else {
        showFrame("Nastala chyba: ".$con->error, false);
    }

    $con->close();
}

/**
 * Removes admin rights of user if the corresponding checkbox is checked.
 * Shows error message otherwise.
 */
function removeAdmin() {
    if($_POST["changeAdmin"] != $_POST["userid"]) {
        showFrame("Pro odebrání admin. práv musí být zaškrtnuto políčko nalevo od tlačítka.", false);
        return;
    }

    $con = db_connect();

    $ok = $con->query("UPDATE user
                       SET isAdmin=0
                       WHERE userid=".(int) $_POST["userid"]);
    if($ok) {
        showFrame("Uživatel byl <b>úspěšně zbaven administrátorských práv</b>!", true);
    } else {
        showFrame("Nastala chyba: ".$con->error, false);
    }

    $con->close();
}

/**
 * Deletes all records about user.
 * Shows error message if the corresponding checkbox is not checked.
 */
function deleteUser() {
    /*
     * Delete user from table:
     *    1. user
     *    2. problemsolve
     *    3. paragraphrating
     *    4. discussion
     */
    if($_POST["deleteUser"] != $_POST["userid"]) {
        showFrame("Pro smazání uživatele musí být zaškrtnuto políčko nalevo od tlačítka.", false);
        return;
    }

    $con = db_connect();

    $ok = $con->query("DELETE FROM user, problemsolve, paragraphrating, discussion
                       WHERE userid=".(int) $_POST["userid"]);
    if($ok) {
        showFrame("Uživatel byl <b>úspěšně smazán</b>!", true);
    } else {
        showFrame("Nastala chyba: ".$con->error, false);
    }
}











?>
<center>
    <h1>Správa uživatelů</h1>
<?php

handleRequest();

$con = db_connect();

$resultSet = $con->query("SELECT userid, login, isAdmin
                          FROM user
                          ORDER BY isAdmin DESC, login ASC");




?>
    <table class="adminTable" cellspacing="0" border="1">
        <tr>
            <th>login</th>
            <th>volby</th>
        </tr>
<?php
while($result = $resultSet->fetch_array()) {
?>
        <tr>
            <td>
                <a href="index.php?page=profile&userid=<?php echo $result["userid"] ?>">
                    <?php echo $result["login"] ?>
                </a>
            </td>
            <td>
                <table>
                    <tr>
                        <td>
                            <form method="post" action="index.php?page=admin&section=users">
                                <input type="hidden" name="userid" value="<?php echo $result["userid"] ?>">
                                <input type="checkbox" name="changeAdmin" value="<?php echo $result["userid"] ?>">
                                <?php
                                if($result["isAdmin"]) { ?>
                                    <input type="submit" value="Odebrat administrátora">
                                    <input type="hidden" name="action" value="removeAdmin">
                                <?php } else { ?>
                                    <input type="submit" value="Přidat administrátora">
                                    <input type="hidden" name="action" value="makeAdmin">
                                <?php } ?>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="index.php?page=admin&section=users">
                                <input type="hidden" name="userid" value="<?php echo $result["userid"] ?>">
                                <input type="hidden" name="action" value="deleteUser">
                                <input type="checkbox" name="deleteUser" value="<?php echo $result["userid"] ?>">
                                <input type="submit" value="Smazat uživatele">
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
<?php
}
?>
    </table>
</center>



<?php
$con->close();
?>