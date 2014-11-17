<?php

/*
 * MYSQLSTUFF.PHP
 * ==============
 *
 * Login info and function for establishing connection.
 */

if ($_SERVER["SERVER_NAME"] == "localhost") {  // databáze na localhostu
    define("SERVER_NAME", "localhost");
    define("USER_NAME", "stanov");
    define("PASSWORD", "stanov");
    define("DB_NAME", "haskellhero");
} else {                                       // databáze na serveru
    define("SERVER_NAME", "db.fi.muni.cz");
    define("USER_NAME", "xnovak34");
    define("PASSWORD", "demonskyhuste66");
    define("DB_NAME", "dbxnovak34");
}

/**
 * Returns instance of mysqli database connection. Uses login info from
 * definitions above. Returns null if login info is incorrect.
 */
function db_connect() {
    $con = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DB_NAME);
    $con->query("SET NAMES UTF8");
    $con->query("SET CHARACTER SET UTF8");

    return $con;
}

?>
