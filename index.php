<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>Untitled</title>
</head>

<body>

<h2>Some multiple choice questions</h2>

<?php
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

if($_POST["count"])
	// get the value of the hidden "count" input field (if it has been set)
	$count = $_POST["count"];
else
	$count = 0;

if($_POST["next"] && $count < count($QandA) - 1) 
	// user pressed next button and there are more questions
	$count += 1;
else if($_POST["prev"] && $count > 0) 
	// user pressed previous button and there is a previous question
	$count -= 1;
?>

<p><?php echo $QandA[$count][0]; ?></p>
<form action=index.php method=post>
<input type="radio" name="question" value="A"/><?php echo $QandA[$count][1]; ?><br/>
<input type="radio" name="question" value="B"/><?php echo $QandA[$count][2]; ?><br/>
<input type="radio" name="question" value="C"/><?php echo $QandA[$count][3]; ?><br/>
<input type="radio" name="question" value="D"/><?php echo $QandA[$count][4]; ?><br/>
<input type="radio" name="question" value="E"/><?php echo $QandA[$count][5]; ?><br/><br/>
<input type="hidden" name="count" value=<?php echo $count; ?> />
<input type="submit" name="sub" value="Submit"/>
<input type="submit" name="next" value="Next question"/>
<input type="submit" name="prev" value="Previous question"/>
</form>

<?php 
if($_POST["sub"])          // user pressed the submit button
{
    if($_POST["question"]) // an answer is set
	{
		if($_POST["question"] == $answers[$count])
    		echo "<br/>That's right!";
		else
			echo "<br/>Sorry, that's the wrong answer.";
	}
}
?>

</body>
</html>
