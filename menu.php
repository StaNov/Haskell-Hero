<?php
/*
 * MENU.PHP
 * ========
 * Shows the left menu of the textbook.
 */

require_once "mysqlStuff.php";


/**
 * Shows menu items - names of lessons as links.
 */
function showMenuItems() {
    $con = db_connect();

    $resultSet = $con->query("SELECT *
                                FROM lesson
                                ORDER BY number, lessonid");
    while($lesson = $resultSet->fetch_array()) {
        $number = $lesson["number"];
        $id = $lesson["lessonid"];
        $name = $lesson["name"];
        
        // if this lesson is selected, then show menu unit as selected
        $selected = isset($_GET["lesson"]) && $_GET["lesson"] == $id // lessonid is in GET
                        ||
                        // lessonid is not in GET, but there has been a lesson displayed before
                        ! isset($_GET["lesson"]) && isset ($_SESSION["lastlesson"]) && $_SESSION["lastlesson"] == $id ?
                        " selectedLesson" : "";


        if($lesson["public"]) { // lesson is public
            echo "<div class=\"menuUnit$selected\">
                 <a href=\"index.php?page=lessons&lesson=$id\" class=\"$selected\">
                    $number. $name
                 </a>
              </div>";
        } elseif (isset ($_SESSION["isadmin"]) && $_SESSION["isadmin"] == true) { // lesson isn't public, but user is admin
            echo "<div class=\"menuUnit disabledLesson\">
                 <a href=\"index.php?page=lessons&lesson=$id\">
                    $number. $name
                 </a>
              </div>";
        }
    }

    $resultSet->close();
    $con->close();
}





showMenuItems();

if(isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) {
?>

<div class="menuUnit" style="background-color: lightsalmon; text-align: center">
    <a href="index.php?page=admin&section=lessons" style="color: black; text-decoration: underline">spravovat lekce</a>
</div>

<?php
}
?>