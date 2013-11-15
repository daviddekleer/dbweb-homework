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

<form action=index.php method=post>
<input type="radio" name="cheese" value="A"/>Gouda<br/>
<input type="radio" name="cheese" value="B"/>Red Leicester<br/>
<input type="radio" name="cheese" value="C"/>Mozzarella<br/>
<input type="radio" name="cheese" value="D"/>Edam<br/>
<input type="radio" name="cheese" value="E"/>Cheshire<br/><br/>
<input type="submit" value="Submit"/>
</form>

<?php 
if($_POST["cheese"]) 
{
	if($_POST["cheese"] == "B")
    		echo "<br/>That's right!";
	else
		echo "<br/>Sorry, that's the wrong answer.";
}
?>

</body>
</html>
