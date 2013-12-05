<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>Multiple Choice Questions</title>
</head>

<body>
 
<h2>Some multiple choice questions</h2>

<?php

/* Debugging: uncomment when needed */

error_reporting(-1);
ini_set("display_errors", 1);


/* All questions (except the first) come from http://www.pubquizarea.com/
 * view_question_and_answer_quizzes.php?cat_title=general-knowledge&
 * type_title=multiple-choice&cat=32&type=1&&id=6272 
 */
    
$QandA = array(array("What is the name of the first cheese mentioned in".
         " the <i>Cheese Shop</i> sketch of Monty Python?", "Gouda",
         "Red Leicester", "Mozzarella", "Edam", "Cheshire"),
         array("What colour is the pigment chlorophyll?", "Blue",
         "Red", "Green", "Purple", "Orange"),
         array("What type of creature is a gecko?", "Bird", "Fish",
         "Monkey", "Insect", "Lizard"),
         array("Which US president once claimed to have been". 
         " 'misunderestimated'?", "George W. Bush", "Jimmy Carter",
         "Barack Obama", "Gerald Ford", "Ronald Reagan"),
         array("In which country is the Harz mountain range?", "Austria",
         "Switzerland", "Spain", "Germany", "Belgium"));
$answers = array("B", "C", "E", "A", "D");
$QandAlen = count($QandA);

if(isset($_POST["count"]))
    // get the value of the hidden "count" input field (if it has been set)
    $count = $_POST["count"];
else
    $count = 0;

if(isset($_POST["next"]) && $count < $QandAlen - 1) 
    // user pressed next button and there are more questions
    $count += 1;
else if(isset($_POST["prev"]) && $count > 0) 
    // user pressed previous button and there is a previous question
    $count -= 1;
?>

<p><?php echo $QandA[$count][0]; ?></p>

<form action=index.php method=post>

<?php
$answer_values = array('"A"', '"B"', '"C"', '"D"', '"E"');
for($i = 0; $i < $QandAlen; ++$i) // echo answers to the current question
    echo '<input type="radio" name="question" value=' . $answer_values[$i] . '/>' . $QandA[$count][$i+1] . '<br/>';
echo '<br/>';
?>

<input type="hidden" name="count" value=<?php echo $count; ?> />
<input type="submit" name="sub" value="Submit"/>

<?php
if($count == 0)                  // first question, hide previous button
{
    $next = '"submit"';
    $prev = '"hidden"';
}
else if($count == $QandAlen - 1) // last question, hide next button
{
    $next = '"hidden"';
    $prev = '"submit"';
} 
else                             // somewhere in between, show both buttons
    $next = $prev = '"submit"';
?>

<input type=<?php echo $prev; ?> name="prev" value="Previous question"/>
<input type=<?php echo $next; ?> name="next" value="Next question"/>
</form>

<?php 
if(isset($_POST["sub"]) and isset($_POST["question"])) 
    // user pressed the submit button and an answer is set
{
    if($_POST["question"] == $answers[$count])
        echo "<br/>That's right!";
    else
        echo "<br/>Sorry, that's the wrong answer.";
}
?>

</body>
</html>