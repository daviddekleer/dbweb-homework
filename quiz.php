<?php
/*
error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

//---------------------------------------- SESSION MANAGEMENT -----------------------------------------\\

require_once("phplib/session_dbconnect.php");
startSession();

if(!isset($_SESSION["usr"])) 
    // unknown/logged out person visits personal page: redirect to login
{
    header("Location: https://siegfried.webhosting.rug.nl/~s2229730/dbweb-homework/login.php");
    echo("<p>You have to login to be able to view this page.</p>"); 
        // if - for whatever reason - someone misleads the header, show info 
    exit;
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

//// KEEP TRACK OF THE QUESTION NUMBER AND DO SOME CHEAT PREVENTION
if(!isset($_SESSION["count"], $_SESSION["submitted"], $_SESSION["score"], $_SESSION["store"])) 
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
    $_SESSION["store"] = array(time(), 0); // array(current time, score stored in database?)
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

$db_handle = setupDBConnection();

// how many questions are there?
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

<form action=quiz.php method=post>

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
        echo '<br/><p style="color:green"><b>That\'s right!</b></p>';
        if(!$_SESSION["submitted"]) // increase score if the question hasn't been submitted before
            {
            ++$_SESSION["score"];
            $_SESSION["submitted"] = 1;
            }
    }
    else
        echo '<br/><p style="color:red"><b>Sorry, that\'s the wrong answer.</b></p>';
        
    if ($count == $Qlen) // display score after answering last question
    {
        echo "<p><b>That's it! Your score is " . $_SESSION["score"] . ".</b></p>";
        
        $store = $_SESSION["store"];
        if(!$store[1]) // check if score of this session has been stored earlier
        {
            $time_taken = time() - $store[0];
            $q_handle = $db_handle->prepare("insert into history values(?,?,?,?)");
            $q_handle->bindParam(1, $_SESSION["usr"]);
            $q_handle->bindParam(2, $time_taken);
            $q_handle->bindParam(3, $_SESSION["score"]);
            $q_handle->bindParam(4, $store[0]);
            $q_handle->execute();

            $_SESSION["store"] = array(0, 1); // do not store something again in this session
        }
    }
}
?>

<br/>
<form action=personalpage.php method=post>
<input type="submit" name="quit" value="Quit"/>
</form>

<br/>
<form action=login.php method=post>
<input type="submit" name="logout" value="Logout"/>
</form>

</body>
</html>