<?php
/* 
 * LEVELS.PHP
 * ==========
 *
 * Stores info about leveles and provides functions to get this info.
 */

/**
 * Sorted array of levels, where
 *      $levels[i][0] == minimum amount of xp to obtain level i
 *      $levels[i][1] == label of level i
 */
$levels = array(
    array(0, "Registrovaný uživatel učebnice"),
    array(2, "Uživatel Hugsu"),
    array(5, "Kalkulačník (používá Hugs místo kalkulačky)"),
    array(10, "Krabičkář (chápe význam funkcí v krabičkovém modelu)"),
    array(20, "Typař (chápe a zvládá otypování)"),
    array(50, "Vláčkař (zvládá definovat funkce na seznamech)"),
    array(100, "Eta-redukcionista"),
    array(200, "Ečkař (má šanci dostat u zkoušky E)"),
    array(500, "Monad slayer"),
    array(1000, "GODLIKE!"),
);


/**
 * Returns number of level for given $xp value.
 */
function getLevelNum($xp) {
    $level = _getLevel($xp);
    return $level[0];
}


/**
 * Returns name of level for given $xp value.
 */
function getLevelName($xp) {
    $level = _getLevel($xp);
    return $level[1];
}


/**
 * Returns array, where
 *      a[0] == number of level, 1 for first level
 *      a[1] == label of level
 *
 * The array $levels can't be empty.
 *
 * @param $xp amount of XP points, for which the level should be returned
 * @return array which represents a level info
 */
function _getLevel($xp) {
    global $levels;
    
    for($i = 0; $i < count($levels); $i++) {
        if($levels[$i][0] > $xp) {
            return array(
                $i,
                $levels[$i - 1][1]
            ); // too much xp, return the previous one
        }
    }

    return $levels[count($levels) - 1]; // max level reached
}
?>
