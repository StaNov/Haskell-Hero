<?php
/* 
 * TEXMAKER.PHP
 * ============
 *
 * If you take source-code of this page, you can use it as
 * LaTeX input. Not perfect, does only basic job.
 */


?>
\documentclass{article}
\usepackage[english,czech]{babel} % package for multilingual support
\usepackage[utf8]{inputenc} % Windows OS encoding
\usepackage[T1]{fontenc}
\usepackage{lmodern}
\usepackage{graphicx}

\begin{document}

<?php

require_once "mysqlStuff.php";

function replace($old, $new) {
    global $text;

    $text = str_replace($old, $new, $text);
}




$con = db_connect();

$resultSet = $con->query("SELECT lesson.name as lessonname,
                                 paragraph.name as paragraphname,
                                 text
                          FROM lesson join paragraph
                          WHERE lesson.lessonid = paragraph.lessonid and public
                          ORDER BY lesson.number, paragraph.number");

$text = ""; // there will be the text stored

while ($row = $resultSet->fetch_array()) {
    if ($prevLessonName != $row["lessonname"]) {
        $text .= "\n\\section{".$row["lessonname"]."}\n\n";
        $prevLessonName = $row["lessonname"];
    }

        $text .= "\\subsection{".$row[paragraphname]."}\n\n";

        $text .= $row["text"];
}
$con->close();


replace("<ul>", "\\begin{itemize}");
replace("</ul>", "\\end{itemize}");
replace("<li>", "\\item ");
replace("<b>", "{\\bf ");
replace("</b>", "}");
replace("<i>", "{\\itshape ");
replace("</i>", "}");
replace(array("<p>", "</p>"), "\n\n");
replace("<pre>", "\\begin{verbatim}");
replace("</pre>", "\\end{verbatim}\n\n");
replace("<code>", "\\verb@");
replace("</code>", "@");
replace("<br>", "\\\\");
replace("<center>", "\\begin{centering}");
replace("</center>", "\\end{centering}");
replace("−", "--");
replace("<div class=\"quote\">", "\\begin{quotation}");
replace("</div>", "\\end{quotation}");
replace("<img class=\"lessonImg\" src=\"", "\\begin{figure}[h!]\n\\includegraphics[width=0.25\\textwidth]{");
replace("\">", "}\n\\end{figure} ");
replace("\\end{figure} \\\\", "\\end{figure}");
replace("gif", "png");


echo $text;
?>

\end{document}