<?php
/* 
 * LADDER.PHP
 * ==========
 *
 * Shows ladder of the best users.
 */

require_once "mysqlStuff.php";

/**
 * Gets info from DB about users and their xp points.
 *
 * @return array, where each item consists of array:
 *           [0] = login of user
 *           [1] = user's ID
 *           [2] = amount of xp
 *         sorted descendent by xp
 */
function getUsersAndXp() {

    $con = db_connect();

    // pom je pomocný sloupec, který dovoluje více stejně ohodnocených příkladů
    // a disk. příspěvků, aby prošly UNIONem; musí v něm být pokaždé jiná hodnota
    $query = "SELECT login, userid, xp
              FROM
                  (SELECT userid, SUM(xp) as xp
                   FROM
                       (SELECT userid, xp, 1223*discussionid AS pom
                        FROM discussion

                       UNION

                        SELECT userid, xp, 1229*problemid AS pom
                        FROM problemsolve NATURAL JOIN problem
                       ) AS BTABLE

                   GROUP BY userid
                  ) AS CTABLE

                  NATURAL JOIN user
              WHERE xp > 0
              ORDER BY xp DESC, login ASC"; // je to prasárna, já vim, ale funguje to.

    $resultSet = $con->query($query);

    while ($row = $resultSet->fetch_array()) {
        $result[] = array($row["login"], $row["userid"], $row["xp"]);
    }

    $con->close();

    return $result;
}



?>

<center>
    <h1>Žebříček uživatelů</h1>
<?php

$usersAndXp = getUsersAndXp();

if(! $usersAndXp) {
    showFrame("V žebříčku se zatím nikdo neumístil.", false);
} else {

?>
        <table border="1" cellspacing="0" cellpadding="3">
            <tr>
                <th>Místo</th>
                <th>Uživatel</th>
                <th>Počet xp</th>
            </tr>
    <?php
    $place = 0; // current place in ladder
    $currentXp = 10000000; // currently highest xp, max possible at the beginning


    foreach ($usersAndXp as $item) {

        if($item[2] != $currentXp) { //next user has lower place in ladder
            $place++;
            $currentXp = $item[2];
        }

        ?>
            <tr>
                <td><?php echo $place ?>.</td>
                <td><a href="index.php?page=profile&userid=<?php echo $item[1] ?>"><?php echo $item[0] ?></a></td>
                <td><?php echo $item[2] ?></td>
            </tr>
        <?php
    }

    ?>
        </table>

<?php
}
?>
</center>