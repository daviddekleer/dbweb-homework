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
    $db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* useful for debugging */
}
catch (PDOException $e)
{
    echo "Connection failed, something's wrong: " . $e->getMessage();
    exit;
}

$q_handle = $db_handle->query("select count(*) from question");
$arr = $q_handle->fetch(PDO::FETCH_ASSOC);
$QandAlen = $arr["count(*)"];

//// KEEP TRACK OF THE QUESTION NUMBER
if(isset($_POST["count"]))
    // get the value of the hidden "count" input field (if it has been set)
    $count = $_POST["count"];
else
    // IMPORTANT: $count starts at 1 (because it's question 1), not 0!
    $count = 1;

if(isset($_POST["next"]) && $count < $QandAlen) 
    // user pressed next button and there are more questions
    $count += 1;
else if(isset($_POST["prev"]) && $count > 1) 
    // user pressed previous button and there is a previous question
    $count -= 1;
 
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
if($count == 1)              // first question, hide previous button
{
    $next = '"submit"';
    $prev = '"hidden"';
}
else if($count == $QandAlen) // last question, hide next button
{
    $next = '"hidden"';
    $prev = '"submit"';
} 
else                         // somewhere in between, show both buttons
    $next = $prev = '"submit"';
?>

<input type="hidden" name="count" value=<?php echo $count; ?> />
<input type="submit" name="sub" value="Submit"/>
<input type=<?php echo $prev; ?> name="prev" value="Previous question"/>
<input type=<?php echo $next; ?> name="next" value="Next question"/>
</form>

<?php 
//// EVALUATE ANSWER
if(isset($_POST["sub"]) and isset($_POST["question"])) 
    // user pressed the submit button and an answer is set
{
    $q_handle = $db_handle->query("select correct from choice where q_number = $count and c_number = " . $_POST["question"]);
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    if($arr["correct"])
        echo "<br/>That's right!";
    else
        echo "<br/>Sorry, that's the wrong answer.";
}
?>

</body>
</html>