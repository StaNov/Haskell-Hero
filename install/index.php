<?php
/*
#!/packages/run/php/bin/php
=== Uncomment if needed ===
*/

/* 
 * INSTALL/INDEX.PHP
 * ==================
 *
 * Installation homepage. User gets here from address /install/ .
 */

require_once "../mysqlStuff.php";
require_once "../frames.php";

?>

<html>
    <head>
        <title>Instalace Haskell Hero</title>
    </head>

    <body>
        <h1>Instalace učebnice Haskell Hero</h1>

        <p>
            Pro úspěšnou instalaci proveď následující kroky:
        </p>

        <ol>
            <li>V souboru <code>mysqlStuff.php</code> vyplň přihlašovací údaje k databázi. Dá se použít jak databáze na localhostu, tak i databáze na jiném stroji.

                <pre>
if ($_SERVER["SERVER_NAME"] == "localhost") {  // databáze na localhostu
    define("SERVER_NAME", "localhost");
    define("USER_NAME", "LOGIN");
    define("PASSWORD", "HESLO");
    define("DB_NAME", "JMENO_DATABAZE");
} else {                                       // databáze na serveru
    define("SERVER_NAME", "NAZEV_DB_SERVERU");
    define("USER_NAME", "LOGIN");
    define("PASSWORD", "HESLO");
    define("DB_NAME", "NAZEV_DATABAZE");
}
                </pre>

            <li>V následujícím formuláři vyplň přihlašovací informace o prvním administrátorovi (pravděpodobně o sobě). Login ani heslo se nedají později upravit. Zvol tedy takové údaje, které nebudeš muset později měnit. Heslo v poli Heslo není skrývané hvězdičkami, proto dej pozor, aby jej někdo neviděl. Další administrátory budeš mít možnost vytvořit z běžných uživatelů přímo v učebnici.

            <li>Klikni na Instalovat.

            <li>Po úspěšné instalaci smaž složku <code>install</code>. Tento krok bude ještě připomenut na stránce s výsledkem.
        </ol>

        <form action="install.php" method="post">
            <table>
                <tr>
                    <th>Login:</th>
                    <td>
                        <input name="login">
                    </td>
                </tr>

                <tr>
                    <th>Heslo:</th>
                    <td>
                        <input name="password">
                    </td>
                </tr>

                <tr>
                    <th>UČO:</th>
                    <td>
                        <input name="uco">
                    </td>
                </tr>

                <tr>
                    <th>E-mail:</th>
                    <td>
                        <input name="email">
                    </td>
                </tr>

                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Instalovat!">
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>