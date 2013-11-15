<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8"/>
<title>Untitled</title>
</head>

<body>

<h2>A silly multiple choice question</h2>
<p>What is the name of the first cheese mentioned in the 
<i>Cheese Shop</i> sketch of Monty Python?</p>

<form action=mchoice.php method=post>
<input type="radio" name="cheese"/>Gouda<br/>
<input type="radio" name="cheese" value="red"/>Red Leicester<br/>
<input type="radio" name="cheese"/>Mozzarella<br/>
<input type="radio" name="cheese"/>Edam<br/>
<input type="radio" name="cheese"/>Cheshire<br/><br/>
<input type="submit" value="Submit"/>
</form>

<?php 
if($_POST["cheese"]) 
{
	if($_POST["cheese"] == "red")
    		echo "<br/>That's right!";
	else
		echo "<br/>Sorry, that's the wrong answer.";
}
?>

</body>
</html>
