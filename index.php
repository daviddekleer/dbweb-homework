<?php
//// KEEP TRACK OF THE QUESTION NUMBER
if(!isset($_COOKIE["count"]) && !isset($_COOKIE["vst"])) 
{
    // cookie doesn't exist yet: give $count initial value
    $count = 1;
    /* sessvst keeps track of visits to prevent users from loading 
       the same question again, submitting it and increasing score 
       (even beyond the amount of questions). */
    $sessvst = 0;
}
else
{
    // user returns or submits an answer: don't change $count
    $count = $_COOKIE["count"];
    $sessvst = $_COOKIE["vst"] + 1;
}
    
if(isset($_POST["sub"]))
{
    // something has been submitted
    switch($_POST["sub"])
        // look for a click to the next/previous question
    {
    case "Previous question":
        $sessvst = 0;
        setcookie("score", 0); // reset score if a user goes back (no cheating)
        $count = $_COOKIE["count"]-1;
        break;
    case "Next question":
        $sessvst = 0;
        $count = $_COOKIE["count"]+1;
        break;
    }
}
setcookie("vst", $sessvst);
setcookie("count", $count);
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>Multiple Choice Questions</title>
</head>

<body>
 
<h2>Some multiple choice questions</h2>

<?php
/*
error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

/* All questions (except the first) come from http://www.pubquizarea.com/
 * view_question_and_answer_quizzes.php?cat_title=general-knowledge&
 * type_title=multiple-choice&cat=32&type=1&&id=6272 
 */

// CONNECT TO THE DATABASE
require_once("db-config.php");
try
{ 
    $db_handle = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);
    /*$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* useful for debugging */
}
catch (PDOException $e)
{
    echo "Connection failed, something's wrong: " . $e->getMessage();
    exit;
}

$q_handle = $db_handle->query("select count(*) from question");
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
$Qlen = $arr["count(*)"];
 
//// FETCH A QUESTION FROM THE DATABASE
$q_handle = $db_handle->query("select q_text from question where q_number = $count");
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
echo "<p>" . $arr["q_text"] . "</p>";
?>

<form action=index2.php method=post>

<?php
//// FETCH ANSWERS FROM THE DATABASE
$q_handle = $db_handle->query("select count(*) from choice where q_number = $count");
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
$answer_amount = $arr["count(*)"]; // questions with a variable amount of answers are possible
$q_handle = $db_handle->query("select c_text from choice where q_number = $count");
$arr = $q_handle->fetchAll();
for($i = 1; $i < $answer_amount+1; ++$i) // echo answers to the current question
{
    echo '<input type="radio" name="question" value="' . $i . '"/>' . $arr[$i-1]["c_text"] . '<br/>';
}
echo '<br/>';

//// SHOW/HIDE THE PREVIOUS/NEXT BUTTONS
/* Note: the type="hidden" trick as in index.php doesn't work if $count is set by
   cookies. The problem is that if the script arrives at the last question and
   you press the "Previous question" button, it advances to the next(!) (non-existing)
   question. That's why I decided to handle it with display:none (then the right 
   button will be picked).   
*/
$next = $prev = "";
if($count == 1)              // first question, hide previous button
    $prev = 'style = "display:none"';
else if($count == $Qlen) // last question, hide next button
    $next = 'style = "display:none"';
?>

<input type="submit" name="sub" value="Previous question" <?php echo $prev; ?>/>
<input type="submit" name="sub" value="Next question" <?php echo $next; ?>/>
<input type="submit" name="sub" value="Submit"/>

</form>

<?php 
//// EVALUATE ANSWER
if(isset($_POST["sub"]) && $_POST["sub"] == "Submit" && isset($_POST["question"])) 
    // user pressed the submit button and an answer is set
{
    $q_handle = $db_handle->query("select correct from choice where q_number = $count and c_number = " . $_POST["question"]);
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    if($arr["correct"])
    {
        $score = 1;
        echo "<br/>That's right!";
    }
    else
    {
        $score = 0;
        echo "<br/>Sorry, that's the wrong answer.";
    }
    
    //// KEEP TRACK OF THE SCORE
    if(isset($_COOKIE["score"])) //add a the value of the score cookie to $score 
    {
        if($sessvst < 2) // increase score if the session hasn't been visited multiple times
            $score += $_COOKIE["score"];
        else             // otherwise, the score is equal to the already stored score
            $core = $_COOKIE["score"];
    }  
    if ($count == $Qlen) // display score after answering last question
            echo "<br/><br/>That's it! Your score is " . $score . ".";
    
    /* Without sessions or hidden input, it's impossible to create the score cookie 
       at the start of the script (because answers to questions are determined
       the end of this script, not already at the beginning!). Setting a cookie 
       later (like this) doesn't produce any errors on siegfried. */
    setcookie("score", $score); 
}
?>

</body>
</html>