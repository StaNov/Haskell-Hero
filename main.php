<?php

/*
 * main.php
 * ========
 * File showing main window.
 *
 * ---
 * input:
 * page: news, lessons, about
 * lesson: int
 */

require_once "frames.php";

if (isset($_REQUEST["page"])) {
    $page = $_REQUEST["page"];
} else {
    $page = "news";
}

switch ($page) {
    case "lessons":
        echo "<table border=0 cellspacing=0 cellpadding=0>
                <tr>
                    <td valign=top>
                        <div class=\"leftMenu\">";
        include "menu.php";
        echo "          </div>
                    </td>";

        echo "      <td style=\"padding-left: 5px;\" valign=top>
                        <div class=\"container lessonText\">";
        include "lesson.php";
        echo "          </div>
                    </td>
                </tr>
            </table>";
        break;


    case "about":
        echo "<div class=\"about\">";
        include "about.php";
        echo "</div>";
        break;


    case "news":
    case "login":
    case "register":
    case "logout":
    case "profile":
    case "ladder":
    case "admin":
        echo "<div class=\"container\">";
        include "$page.php";
        echo "</div>";
        break;


    default:
        echo "<div class=\"container\">";
        echo "Stránka <b>$page</b> nebyla nalezena. Pokračujte některým odkazem z vrchního menu.";
        echo "</div>";
}
?>