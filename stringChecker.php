<?php
/* 
 * STRINGCHECKER.PHP
 * =================
 * Checks string inputs from user.
 */

/**
 * Replaces potentially dangerous characters with entities.
 */
function makePostSafe($post) {
    $post = str_replace("<", "&lt;", $post);
    $post = str_replace(">", "&gt;", $post);
    $post = str_replace("\n", "<br>", $post);

    return $post;
}

/**
 * Returns true if $login can be used as login of user. False otherwise.
 */
function isLoginSafe($login) {
    $isNotOk = strpos($login, "<") ||
                strpos($login, ">");

    return !$isNotOk;
}

?>
