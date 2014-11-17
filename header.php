<?php
/*
 * HEADER.PHP
 * ==========
 * 
 * File containing the header stuff.
 * 
 * ---
 * variables:
 * $page: news, lessons, about
 * $lesson: int
 */

/**
 * Prints a header-menu link.
 *
 * @param $pageName name of linking page
 * @param $atributes additional atributes to add to the link
 * @param $label label to be shown
 */
function showLink($pageName, $atributes, $label) {
    if($atributes != null) // add '&' if there are atributes
        $atributes = "&".$atributes;
    echo "<a href=\"index.php?page=".$pageName.$atributes."\">";
    $bold = @$_GET["page"] == $pageName || (!isset($_GET["page"]) && $pageName == "news"); //should i make it bold?; name of page == name of link or page is unset and link == news
    if ($bold)
        echo "<b>";
    echo $label;
    if ($bold)
        echo "</b>";
    echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;";
}









if (isset($_SESSION["id"]))
    echo "Přihlášen <b>" . $_SESSION["login"] . "</b>";
?>


<br>
<span class="headerFont">
    <?php
    showLink("news", null, "novinky");
    showLink("lessons", null, "učebnice");
    showLink("ladder", null, "žebříček");
    showLink("about", null, "o projektu");

    $page = isset($_GET["page"]) ? $_GET["page"] : "news";

    if ($page != "login" && $page != "logout" && $page != "register") { // nezobrazovat, pokud by to mohlo způsobit špatné odkazy
        if (isset($_SESSION["id"])) {
            if($_SESSION["isadmin"])
                showLink("admin", null, "administrace");
            showLink("profile", "userid=".$_SESSION["id"], "profil");
            showLink("logout", null, "odhlásit se");
        } else {
            showLink("login", null, "přihlásit se");
            showLink("register", null, "registrace");
        }
    }
    ?>
</span>