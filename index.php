<?php
session_set_cookie_params(7*24*3600, "", "", 1); 
session_start();

if (!isset($_SESSION["expire"]))
    $_SESSION["expire"] = time() + 7*24*3600; // when will the session expire?
else
{
    if (time() > $_SESSION["expire"]) // time has expired: destroy session
    {
        $_SESSION = array();
        session_destroy();
    }
} 
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

//// KEEP TRACK OF THE QUESTION NUMBER AND DO SOME CHEAT PREVENTION
if(!isset($_SESSION["count"], $_SESSION["submitted"], $_SESSION["score"])) 
{
    // session variable do not exist yet: initialize them
    $_SESSION["count"] = 1;
    /* $_SESSION["submitted"] is a flag that will be set to 1 if a 
     * question has been submitted somewhere in the past. This way, 
     * it prevents cheating (otherwise, it would be possible to keep 
     * increasing your score, even beyond the amount of questions!) 
     */
    $_SESSION["submitted"] = 0;
    $_SESSION["score"] = 0;
}
    
if(isset($_POST["sub"]))
{
    // something has been submitted
    switch($_POST["sub"]) // look for a click to the next/previous question
    {
    case "Previous question":
        $_SESSION["submitted"] = 0;
        $_SESSION["score"] = 0; // reset score if a user goes back (no cheating)
        --$_SESSION["count"];
        break;
    case "Next question":
        $_SESSION["submitted"] = 0;
        ++$_SESSION["count"];
        break;
    }
}

$count = $_SESSION["count"];

//// CONNECT TO THE DATABASE
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
/* All questions (except the first) come from http://www.pubquizarea.com/
 * view_question_and_answer_quizzes.php?cat_title=general-knowledge&
 * type_title=multiple-choice&cat=32&type=1&&id=6272 
 */
$q_handle = $db_handle->prepare("select q_text from question where q_number = ?");
$q_handle->bindParam(1, $count);
$q_handle->execute();
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
echo "<p>" . $arr["q_text"] . "</p>";
?>

<form action=index.php method=post>

<?php
//// FETCH ANSWERS FROM THE DATABASE
$q_handle = $db_handle->prepare("select count(*) from choice where q_number = ?");
$q_handle->bindParam(1, $count);
$q_handle->execute();
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
$answer_amount = $arr["count(*)"]; // questions with a variable amount of answers are possible
$q_handle = $db_handle->prepare("select c_text from choice where q_number = ?");
$q_handle->bindParam(1, $count);
$q_handle->execute();
$arr = $q_handle->fetchAll();
for($i = 1; $i < $answer_amount+1; ++$i) // echo answers to the current question
{
    echo '<input type="radio" name="answer" value="' . $i . '"/>' . $arr[$i-1]["c_text"] . '<br/>';
}
echo '<br/>';

//// SHOW/HIDE THE PREVIOUS/NEXT BUTTONS
/* Note: the type="hidden" trick as in the first version of index.php doesn't work 
 * if $count is set by cookies or sessions. The problem is that if the script arrives
 * at the last question and you press the "Previous question" button, it advances to 
 * the next(!) (not existing) question. That's why I decided to handle it with 
 * display:none (that way, the right button will be picked).   
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
//// EVALUATE ANSWER AND KEEP TRACK OF SCORE
if(isset($_POST["sub"], $_POST["answer"]) && $_POST["sub"] == "Submit") 
    // user pressed the submit button and an answer is set
{
    $q_handle = $db_handle->prepare("select correct from choice where q_number = ? and c_number = ?");
    $q_handle->bindParam(1, $count);
    $q_handle->bindParam(2, $_POST["answer"]);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    if($arr["correct"])
    {   
        echo "<br/>That's right!";
        if(!$_SESSION["submitted"]) // increase score if the question hasn't been submitted before
            {
            ++$_SESSION["score"];
            $_SESSION["submitted"] = 1;
            }
    }
    else
        echo "<br/>Sorry, that's the wrong answer.";
        
    if ($count == $Qlen) // display score after answering last question
            echo "<br/><br/>That's it! Your score is " . $_SESSION["score"] . ".";
}
?>

</body>
</html>