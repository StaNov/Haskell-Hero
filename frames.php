<?php

/*
 * FRAMES.PHP
 * ==========
 * Functions showing ok or notOk frames.
 */


/**
 * Shows frame filled with string $message.
 *
 * Class of the frame is 'okframe' if $ok is true, 'notokframe' otherwise.
 */
function showFrame($message, $ok) {
    echo "<div class=\"" . ($ok ? "" : "not") . "okframe\">
            $message
          </div>";
}

?>
