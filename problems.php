<?php
/* 
 * PROBLEMS.PHP
 * ============
 * Shows problems to be solved to user and their administration for admin.
 *
 * input:
 * problemId : int
 * answer    : String
 * lesson    : int    | ID of actually shown lesson
 */

require_once "frames.php";


/**
 * Handles incoming answer and shows message about correctness.
 *
 * Does nothing if there is no answer.
 * Shows warning if no user is logged in.
 */
function handleAnswer() {
    global $con;

    if(!isset($_POST["problemId"])) { // no answer
        return;
    }

    if(! isset($_SESSION["id"])) { // no user logged in
        showFrame("Nepřihlášení uživatelé <b>nemohou odpovídat na příklady</b>!", false);
        return;
    }

    $problemId = $_POST["problemId"];
    $answer = $_POST["answer"];
    $userId = $_SESSION["id"];

    $statement = $con->prepare("SELECT problemid
                                FROM problem
                                WHERE problemid=? AND answer=?"); // if the answer is correct, there will be one line in result set
    $res = $statement->bind_param("is", $problemId, $answer);
    if(!$res) { // SQL injection
        showFrame("Odpověď <b>$answer</b> obsahuje neplatnou hodnotu!", false);
        return;
    }
    $statement->execute();
    if(! $statement->fetch()) { // bad answer
        showFrame("Zadaná odpověď <b>není správná</b>!", false);
        return;
    }
    $statement->close();

    // correct answer, save it into DB!
    $saveStatement = $con->prepare("INSERT
                                    INTO `problemsolve` (userid, problemid)
                                    VALUES (?,?)");
    $saveStatement->bind_param("ii", $userId, $problemId);
    $saveStatement->execute();

    showFrame("Zadaná odpověď <b>je správná</b>!", true);
}

/**
 * Shows all problems of current lesson.
 */
function showProblems() {
    
    global $con;
    global $lesson; // i know that $lesson exists from lesson.php

    $resultSet = $con->query("SELECT problemid, number, task, answer, xp, public
                              FROM problem
                              WHERE lessonId=$lesson
                              ORDER BY number, problemid"); // $lesson has been checked at 'lesson.php'

    while ($problem = $resultSet->fetch_array()) {
        $id = $problem["problemid"];
        $number = $problem["number"];
        $task = $problem["task"];
        $answer = $problem["answer"];
        $xp = $problem["xp"];
        $public = $problem["public"];

        showProblem($id, $number, $task, $answer, $xp, $public);
    }

    if(isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) {
        ?>
            <form method="post" action="index.php?page=lessons&lesson=<?php echo $lesson; ?>#problems">
                <input type="hidden" name="adminaction" value="addproblem">
                <input type="hidden" name="lastproblemnumber" value="<?php echo $number; ?>">
                <input type="submit" value="Přidat příklad">
            </form>
        <?php
    }
}

/**
 * Shows a problem determined by arguments of this function.
 *
 * @global <type> $con    connection to DB
 * @param <type> $id      ID of problem
 * @param <type> $number  number of problem
 * @param <type> $task    task of problem
 * @param <type> $answer  answer of problem
 * @param <type> $xp      xp amount of problem
 * @param <type> $public  is the problem public?
 */
function showProblem($id, $number, $task, $answer, $xp, $public) {
    global $con;

    if (isset($_SESSION["isadmin"])  &&  $_SESSION["isadmin"]) { // user is admin
        showAdminProblem($id, $number, $task, $answer, $xp, $public);
        return;
    }

    if (! $public) { // problem is not public and user is not admin - show nothing
        return;
    }

    if (! isset($_SESSION["id"])) { // user is not logged id, show problem as unsolved
        showUnsolvedProblem($id, $number, $task, $xp);
        return;
    }

    // user is logged in and is not admin
    $resultSet = $con->query("SELECT userid
                              FROM problemsolve
                              WHERE userid=".$_SESSION["id"]." and problemid=$id"); // is there a record about solving the problem id DB?
    
    if ($resultSet->fetch_array()) { // problem has been already solved by user
        showSolvedProblem($id, $number, $task, $answer, $xp);
    } else { // not solved yet
        showUnsolvedProblem($id, $number, $task, $xp);
    }

    $resultSet->close();
}

/**
 * Shows an unsolved problem.
 *
 * Shows answer form if the user is logged in.
 */
function showUnsolvedProblem($id, $number, $task, $xp) {
    global $lesson;

    ?>

    <div class="unsolvedProblem">
        <div class="problemHeader">
            <b>Příklad <?php echo $number ?></b> (<?php echo $xp ?> xp):
        </div>
        <div class="peopleSolved">
            Příklad už vyřešilo <b><?php echo getSolversCount($id); ?></b> lidí.
        </div>

        <div class="task">
            <?php echo $task ?>
        </div>
        <?php

        if (isset ($_SESSION["id"])) { // user is logged in, show answer form!
        ?>
            <div class="answerForm">
                <form method="post" action="index.php?page=lessons&lesson=<?php echo $lesson ?>#problems">
                    Odpověď:
                    <input name="answer">
                    <input type="hidden" name="problemId" value="<?php echo $id ?>">
                    <input type="submit" value="Odeslat">
                </form>
            </div>
        <?php
        }
        ?>
    </div>

<?php
}

/**
 * Shows solved problem.
 */
function showSolvedProblem($id, $number, $task, $answer, $xp) {
    ?>
    <div class="solvedProblem">
        <div class="problemHeader">
            <b>Příklad <?php echo $number; ?></b> (<?php echo $xp; ?> xp):
        </div>
        <div class="peopleSolved">
            Příklad už vyřešilo <b><?php echo getSolversCount($id); ?></b> lidí.
        </div>

        <div class="task">
            <?php echo $task; ?>
        </div>

        <div class="answerForm">
            Odpověď: <b><?php echo $answer; ?></b>
        </div>
    </div>
<?php
}

/**
 * Shows the administration interface for the problem.
 */
function showAdminProblem($id, $number, $task, $answer, $xp, $public) {
    global $lesson;

    ?>
    <div class="adminProblem">
        <form method="post" action="index.php?page=lessons&lesson=<?php echo $lesson; ?>#problems">
            <div class="problemHeader">
                <b>Příklad <input name="problemnumber" value="<?php echo $number; ?>" size="1"></b>
                (<input name="problemxp" value="<?php echo $xp; ?>" size="1"> xp):
            </div>
            <div class="peopleSolved">
                Příklad už vyřešilo <b><?php echo getSolversCount($id); ?></b> lidí.
            </div>

            <div class="task">
                <?php echo $task; ?><br>
                <textarea rows="5" cols="50" name="problemtask"><?php echo $task; ?></textarea>
            </div>

            <div class="answerForm">
                Zveřejnit příklad? <input type="checkbox" name="problempublic" value="true"<?php if ($public) echo " checked"?>>
                Odpověď: <input name="problemanswer" value="<?php echo $answer; ?>" size="40">
            </div>
            <input type="hidden" name="problemid" value="<?php echo $id; ?>">
            <input type="hidden" name="adminaction" value="saveproblem">

            <hr>

            <center>
                <input type="submit" value="Uložit příklad">
            </center>
        </form>

        <form method="post" action="index.php?page=lessons&lesson=<?php echo $lesson; ?>#problems">
            <input type="hidden" name="problemid" value="<?php echo $id; ?>">
            <input type="hidden" name="adminaction" value="deleteproblem">
            <center>
                <label>Smazat?</label><input type="checkbox" name="deletecheck" value="true">
                <input type="submit" value="Smazat příklad">
            </center>
        </form>
    </div>
<?php
}

/**
 * Returns count of people, who solved the problem.
 *
 * @param <type> $problemId ID of problem
 * @return <type> count of users, who already solved the problem
 */
function getSolversCount($problemId) {
    global $con;

    $resultSet = $con->query("SELECT count(*) as count
                              FROM `problemsolve`
                              WHERE problemid=$problemId");
    $row = $resultSet->fetch_array();

    return $row["count"];
}

/**
 * Adds, updates or deletes a problem - in case of $_POST["adminaction"] item.
 *
 * Does nothing, if $_POST["adminaction"] is not set.
 * Shows warning if user is not admin.
 * Shows warning if the value of $_POST["adminaction"] is other than
 * "addproblem", "saveproblem" or "deleteproblem".
 * Shows warning if the "Smazat" checkbox has not been checked.
 */
function handleAdministration() {
    if (! isset ($_POST["adminaction"])) { // no administration needed
        return;
    }

    if (! $_SESSION["isadmin"]) { // user is not admin
        showFrame("Příklady může upravovat pouze administrátor!", false);
        return;
    }

    switch ($_POST["adminaction"]) {
        case "addproblem":
            addProblem();
            break;

        case "saveproblem":
            saveProblem();
            break;

        case "deleteproblem":
            if (! isset ($_POST["deletecheck"])) { // misclick protection
                showFrame("Při mazání musí být zaškrtnuto políčko <b>Smazat</b>.", false);
                return;
            }
            deleteProblem();
            break;

        default:
            showFrame("Neplatná hodnota položky <b>adminaction</b>", false);
            return;
    }
}

/**
 * Adds new problem to currently shown lesson filled with default values.
 *
 * @global <type> $lesson  ID of currently shown lesson
 */
function addProblem() {
    global $con;
    global $lesson;

    $problemNumber = isset ($_POST["lastproblemnumber"]) ?
                                        $_POST["lastproblemnumber"] + 1
                                        : 1;

    $ok = $con->query("INSERT INTO problem(lessonid, number, task, answer, xp)
                       VALUES ($lesson, $problemNumber, 'Kolik je 2 + 2?', '4', 0)");
    if($ok) {
        showFrame("Příklad byl <b>úspěšně přidán</b>!", true);
    } else {
        showFrame("<b>Nepodařilo se</b> přidat příklad: " . $con->error, false);
    }
}

/**
 * Updates problem in DB.
 */
function saveProblem() {
    global $con;

    $problemNumber = $_POST["problemnumber"];
    $problemXp = $_POST["problemxp"];
    $problemPublic = isset ($_POST["problempublic"]) ? 1 : 0;
    $problemTask = $_POST["problemtask"];
    $problemAnswer = $_POST["problemanswer"];
    $problemId = $_POST["problemid"];

    $statement = $con -> prepare("UPDATE problem
                                  SET number = ?,
                                      xp = ?,
                                      public = ?,
                                      task = ?,
                                      answer = ?
                                  WHERE problemid = ?");
    $ok = $statement -> bind_param("iiissi", $problemNumber, $problemXp, $problemPublic, $problemTask, $problemAnswer, $problemId);
    if(!$ok) {
        showFrame("Nepodařilo se uložit příklad!", false);
        $statement->close();
        return;
    }

    $ok = $statement->execute();
    if(!$ok) {
        showFrame("Nepodařilo se uložit příklad!", false);
    } else {
        showFrame("Příklad byl <b>úspěšně uložen</b>!", true);
    }

    $statement->close();
}

/**
 * Deletes from DB problem from table "problem" and all records from table
 * "problemsolve".
 */
function deleteProblem() {
    /*
     * 1. Delete problem from table problem
     * 2. Delete all records about problem from table problemsolve
     */
    global $con;

    // (1 start)
    $statement = $con -> prepare("DELETE FROM problem
                           WHERE problemid=?");
    if(!$statement) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }

    $ok = $statement -> bind_param("i", $_POST["problemid"]);
    if(!$ok) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }
    
    $ok = $statement -> execute();
    if(!$ok) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }
    // (1 end)

    // (2 start)
    $statement = $con -> prepare("DELETE FROM problemsolve
                                  WHERE problemid=?");
    if(!$statement) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }

    $ok = $statement -> bind_param("i", $_POST["problemid"]);
    if(!$ok) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }

    $ok = $statement -> execute();
    if(!$ok) {
        showFrame("Příklad se <b>nepodařilo odstranit</b>: " . $con->error, false);
        return;
    }
    // (2 end)

    showFrame("Příklad byl <b>úspěšně odstraněn</b>!", true);
}
?>










<hr>

<div id="problems">
    <h3>příklady</h3>
    <?php
        handleAnswer();
        handleAdministration();
        showProblems();
    ?>
</div>