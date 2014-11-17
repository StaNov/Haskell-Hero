<?php
/*
 * LOGIN.PHP
 * =========
 * Shows login dialog or confirmation message.
 *
 * input:
 * answer = true //if the page is shown as the answer
 * login
 * password
 * keeplogged
 */
require_once "mysqlStuff.php";
require_once "frames.php";

$con = db_connect();

function logInUser($errorString) {
    global $con;
    $login = $_REQUEST["login"];

    // <editor-fold defaultstate="collapsed" desc="input tests">
    if (empty($login))
        return $errorString . "Pole <b>login</b> musí být vyplněno.";


    $statement = $con->prepare("SELECT userid, login, password, uco, isadmin
                                FROM user
                                WHERE login = ?");

    $statement->bind_param("s", $login);
    $statement->execute();
    $userExists = $statement->bind_result($id, $login, $password, $uco, $isAdmin);

    if (!$statement->fetch())
        return $errorString . "Uživatel <b>$login</b> neexistuje.";


    if ($password != sha1($_REQUEST["password"]))
        return $errorString . "Nesprávné heslo."; // </editor-fold>

    $_SESSION["id"] = $id;
    $_SESSION["login"] = $login;
    $_SESSION["uco"] = $uco;
    $_SESSION["isadmin"] = $isAdmin;

    return $errorString;
}
?>








<center>
    <h1>Přihlásit se</h1>

<?php
if (isset ($_POST["answer"])  &&  $_POST["answer"]) {
    $errorString = "";
    $errorString = logInUser($errorString);
    if(empty($errorString)) {
        showFrame("Uživatel <b>" . $_REQUEST["login"] . "</b> byl úspěšně přihlášen.", true);
    } else {
        showFrame($errorString, false);
    }
}

if    (! isset ($_POST["answer"])  
    || ! $_POST["answer"]  
    || ! empty($errorString)) {
?>

    <form action="index.php" method="post">
        <input type="hidden" name="page" value="login">
        <input type="hidden" name="answer" value="true">
        <table>
            <tr>
                <td>Login:</td>
                <td><input name="login" value=<?php echo isset ($_POST["login"])  ?  $_POST["login"]  :  ""; ?>></td>
            </tr>
            <tr>
                <td>Heslo:</td>
                <td><input name="password" type="password"></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input type="submit" value="Přihlásit"></td>
            </tr>
        </table>
    </form>
    <a href="index.php?page=register" style="font-size: smaller">Registrovat nového uživatele</a>
</center>

<?php
}

$con->close();
?>
