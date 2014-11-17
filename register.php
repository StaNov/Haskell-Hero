<?php
/*
 * REGISTER.PHP
 * ============
 * Registers new user.
 *
 * input:
 * answer: true //if the page is shown as the answer
 * login
 * password
 * uco
 * email
 */
require_once "mysqlStuff.php";
require_once "frames.php";
require_once "stringChecker.php";

$con = db_connect();

function checkLogin($errorString) {
    /*
     * 1. Cant be blank
     * 2. Cant be too long
     * 3. Cant contain forbidden characters
     * 4. Cant be in the DB already
     */
    global $con;
    
     if(empty($_POST["login"])) { // (1)
         return $errorString."Není vyplněno pole <b>Login</b>.<br>";
     }

     if(strlen($_POST["login"]) > 16) { // (2)
         return $errorString."Položka <b>Login</b> může být dlouhá maximálně <b>16 znaků</b>.<br>";
     }

     if(!isLoginSafe($_POST["login"]) ||
             $_POST["login"] != $con->escape_string($_POST["login"])) { // (3)
         
         return $errorString."Položka <b>Login</b> obsahuje zakázané symboly.";
     }

     $statement = $con->prepare("SELECT * FROM `user` WHERE login=?");
     $res = $statement ->bind_param("s", $_POST["login"]);
     if(!$res) { // SQL injection
         return $errorString."Položka <b>Login</b> obsahuje neplatnou hodnotu.<br>";
     }
     $statement->execute();
     if($statement->fetch()) {
         return $errorString."Uživatel <b>".$_POST["login"]."</b> je už registrován.<br>"; // (4)
     }
     return $errorString;
}

function checkPassword($errorString) {
    /*
     * 1. Cant be too short
     * 2. Cant be too long
     * 3. Both passwords must be equal
     */
     if(strlen($_POST["password"]) < 6 || strlen($_POST["password"]) > 15) { // (1,2)
         return $errorString."Heslo musí být dlouhé alespoň <b>6</b> a maximálně <b>15</b> znaků.<br>";
     }

     if(strcmp($_POST["password"], $_POST["password2"]) != 0) return $errorString."Zadaná hesla se <b>neshodují</b>.<br>"; // (3)
     
     return $errorString;
}

function checkUco($errorString) {
    /*
     * 1. Must be a number
     * 2. Cant be bigger than 10000000
     */
    if(empty($_POST["uco"])) return $errorString; // nothing filled, user is not a student

    if((int)$_POST["uco"] <= 0) return $errorString."V položce <b>UČO</b> musí být kladné celé číslo.<br>"; // (1)

    if($_POST["uco"] > 10000000) return $errorString."<b>UČO</b> je moc velké.<br>"; // (2)

    return $errorString;
}

function checkEmail($errorString) {
    /*
     * 1. Cant be too long
     */
     if(strlen($_POST["email"]) > 64) { // (1)
         return $errorString."Email může být dlouhý maximálně <b>64</b> znaků.<br>";
     }
     return $errorString;
}

function addUserToDb($errorString) {
    global $con;

    $login = $_POST["login"];
    $pass = $_POST["password"];
    $uco = $_POST["uco"];
    $email = $_POST["email"];

    $statement = $con->prepare("INSERT INTO `user`(login, password, uco, email)
                                VALUES            (?    , ?       , ?  , ?    )");
    $pass = sha1($pass); // encrypts password

    $statement->bind_param("ssis", $login, $pass, $uco, $email);
    
    $ok = $statement->execute();

    if(!$ok) return $errorString."Osobu se nepodařilo uložit do databáze. Pokud problém přetrvá, obraťte se na vývojáře.";

    return $errorString;
}
?>

















<center>
    <h1>Registrace nového uživatele</h1>

    <?php
    if (isset ($_POST["answer"])  &&  $_POST["answer"]) { // the page is opened as the answer
        $errorString = ""; //describes errors, global var
        $errorString = checkLogin($errorString); // checks
        $errorString = checkPassword($errorString);
        $errorString = checkUco($errorString); // rychlá poznámka: funkce si vezme string, pokud se test nepodaří, připojí k němu chybové hlášení. pokud je za konci chybový string prázdný, testy uspěly.
        $errorString = checkEmail($errorString);

        if (empty($errorString)) {
            $errorString = addUserToDb($errorString);
            if (empty($errorString)) {
                showFrame("Uživatel <b>".$_POST["login"]."</b> byl úspěšně registrován.", true);
                echo "<a href=\"index.php?page=login\">Přihlásit se</a>"; // gives opportunity to log in
            } else {
                showFrame($errorString, false);
            }
        } else {
            showFrame($errorString, false);
        }
    }




    if (! isset ($_POST["answer"])
            || ! $_POST["answer"]
            || ! empty($errorString)) {
    ?>

        <form method="post" action="index.php">
            <input type="hidden" name="page" value="register">
            <input type="hidden" name="answer" value="true">
            <table style="text-align: right">
                <tr>
                    <td>Login:</td>
                    <td><input name="login" value="<?php echo isset ($_POST["login"])  ?  $_POST["login"]  :  "" ?>"></td>
                </tr>
                <tr>
                    <td>Heslo:</td>
                    <td><input name="password" type="password"></td>
                </tr>
                <tr>
                    <td>Heslo znovu:</td>
                    <td><input name="password2" type="password"></td>
                </tr>
                <tr>
                    <td>UČO:</td>
                    <td><input name="uco" value="<?php echo isset ($_POST["uco"])  ?  $_POST["uco"]  :  "" ?>"></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><input name="email" value="<?php echo isset ($_POST["email"])  ?  $_POST["email"]  :  "" ?>"></td>
                </tr>
                <tr>
                    <td colspan="2" align="center"><input type="submit" value="Zaregistrovat"></td>
                </tr>
            </table>
        </form>

        <div style="width: 50%; text-align: left">
            <hr>

            <p>
                Položky login a heslo jsou povinné.
            </p>

            <p>
                <b>Upozornění:</b> Registrace i přihlášení probíhá přes nezabezpečené spojení.
                Při volbě hesla tedy pro vlastní bezpečnost nepoužívejte například heslo k emailu nebo do ISu.
            </p>
        </div>
    </center>
    
<?php
    }
    $con->close();
?>