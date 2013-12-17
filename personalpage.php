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

if(isset($_POST["logout"])) // user wants to log out, kill his/her session
{
    $_SESSION = array();
    session_destroy();
}

if(!isset($_SESSION["usr"])) 
    // unknown/logged out person visits personal page: redirect to login
{
    header("Location: https://siegfried.webhosting.rug.nl/~s2229730/dbweb-homework/login.php");
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
<input type="submit" name="quiz" value="Take quiz!"/>
</form>
<br/>
<form action=personalpage.php method=post>
<input type="submit" name="logout" value="Logout"/>
</form>
<h3>Your history</h3>
<h3>The Hall of Fame</h3>

</body>

</html>