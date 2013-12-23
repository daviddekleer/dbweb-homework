<?php 
/*
error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

//---------------------------------------- SESSION MANAGEMENT -----------------------------------------\\

if(file_exists("phplib/session_dbconnect.php"))
        require_once("phplib/session_dbconnect.php");
    else 
        exit("<p>Sorry, the session management/database connection functions could not be found.</p>"); 

if(file_exists('phplib/config.php'))
        require('phplib/config.php'); // obtain website url
    else 
        exit("<p>Sorry, the configuration file could not be found.</p>");
        
startSession();

if(isset($_POST["quit"])) // user chose to quit the quiz, unset quiz session variables
    unset($_SESSION["count"], $_SESSION["submitted"], $_SESSION["score"], $_SESSION["store"]);

if(!isset($_SESSION["usr"])) // unknown/logged out person visits personal page: redirect to login
{
    header("Location: " . $url . "login.php");
    echo("<p>You have to login to be able to view this page.</p>"); 
        // if - for whatever reason - someone misleads the header, show info 
    exit;
}
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>Personal M-Choice Page</title>
</head>

<h2>Personal page</h2>
<?php 
$escaped_uname = htmlentities($_SESSION["usr"]); // make username safe to output
?>
<p>Welcome, <?php echo $escaped_uname; ?>.</p>
<form action=quiz.php method=post>
<input type="submit" name="quiz" value="Start/continue quiz!"/>
</form>
<br/>
<form action=login.php method=post>
<input type="submit" name="logout" value="Logout"/>
</form>
<h3>Your top 5</h3>
<?php
$db_handle = setupDBConnection();

$q_handle = $db_handle->prepare("select score, time_taken, time_started from history where" .
                                " u_name = ? order by score desc, time_taken limit 5");
$q_handle->bindParam(1, $_SESSION["usr"]);
$q_handle->execute();
$arr = $q_handle->fetchAll();

if(empty($arr[0]["score"])) // no top 5 for this user
    echo "<p>You don't seem to have any scores in your history. Please take the quiz!</p>";
else 
{
    echo '<table border=1 style="border-collapse:collapse;text-align:center;width:400px;">' .
         '<tr><th>Score</th><th>Time taken (s)</th><th>Quiz started at</th></tr>';
    foreach($arr as $row)
    {
        $date = date_create();
        date_timestamp_set($date, $row["time_started"]);
        echo "<tr><td>" . $row["score"] . "</td><td>" . $row["time_taken"] . "</td><td>" . 
        date_format($date, 'H:i:s Y-m-d') . "</td></tr>";
    }
    echo "</table>";
}
?>


<h3>The Hall of Fame</h3>
<?php
$q_handle = $db_handle->query("select u_name, score, time_taken, time_started from history" . 
                              " order by score desc, time_taken limit 5");
$arr = $q_handle->fetchAll();

if(empty($arr[0]["score"])) // no top 5 for this user
    echo "<p>There are no scores in the top 5 yet!</p>";
else
{
    echo '<table border=1 style="border-collapse:collapse;text-align:center;width:500px;">' .
         '<tr><th>User</th><th>Score</th><th>Time taken (s)</th><th>Quiz started at</th></tr>';
    foreach($arr as $row)
    {
        $date = date_create();
        date_timestamp_set($date, $row["time_started"]);
        echo "<tr><td>" . $row["u_name"] . "</td><td>" . $row["score"] . "</td><td>" . 
        $row["time_taken"] . "</td><td>" . date_format($date, 'H:i:s Y-m-d') . "</td></tr>";
    }
    echo "</table>";
}
?>
</body>

</html>