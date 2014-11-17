<?php

/*
 * ADMINPAGES/DISCUSSION
 * =====================
 * 
 * Page displaying all discussion posts from newest to oldest.
 */

if(!$_SESSION["isadmin"]) die("Na tuto stránku mají přístup pouze administrátoři.");

require_once "mysqlStuff.php";
require_once "frames.php";

$con = db_connect();

$resultSet = $con->query("SELECT lessonid, number, name, text, timestamp, login, userid
                          FROM discussion NATURAL JOIN lesson NATURAL JOIN user
                          ORDER BY timestamp DESC");

if (! $resultSet) showFrame("Nepodařilo se načíst příspěvky z databáze: " . $con->error, false);

?>

<center>
    <h1>Všechny diskuze</h1>
    
    <table border="1" cellspacing="0" cellpadding="5" class="adminTable" width="900">
        <tr bgcolor="#ff9900">
            <th>Lekce</th>
            <th>Text</th>
            <th>Autor</th>
            <th>Čas přidání</th>
        </tr>
        <?php
        
while (($row = $resultSet->fetch_array()) != false) {
    ?>
        <tr>
            <th align="left">
                <a href="index.php?page=lessons&lesson=<?php echo $row["lessonid"] ?>#discussion"><?php echo $row["number"] . ". " . $row["name"] ?></a>
            </th>
            <td align="left">
                <?php echo $row["text"] ?>
            </td>
            <td>
                <a href="index.php?page=profile&userid=<?php echo $row["userid"] ?>">
                    <?php echo $row["login"] ?>
                </a>
            </td>
            <td>
                <?php echo $row["timestamp"] ?>
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