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

if(isset($_POST["logout"])) // user wants to log out (is referred to this page), kill his/her session
{
    $_SESSION = array();
    session_destroy();
}

if(isset($_SESSION["usr"])) 
{
    // there is still some login session active: redirect to personal page
    header("Location: " . $url . "personalpage.php");
    exit;
}

if(!isset($_SESSION["failattempt"]) || time() - $_SESSION["failattempt"][0] > 300) 
    // failattempt session variable not set or inactivity for 5 minutes
    $_SESSION["failattempt"] = array(time(), 0); // array(current time, failed attempt number)  
    
//--------------------------------------- FUNCTION DEFINITIONS ----------------------------------------\\

function validInput() // check if user input is not empty
{
    /* This may look a bit silly, but more code could be added if 
     * users have to input more than only a username and password! 
     */
    if(isset($_POST["username"], $_POST["password"]))
    {
        if($_POST["username"] != "" && $_POST["password"] != "")
            return array(1, "");
        return array(0,'<p style="color:red"><b>Please enter a username and a password.</b></p>');
    }
}

function validLogin($db_handle) // check if user has entered valid login data
{ 
    $q_handle = $db_handle->prepare("select name, password from user where name = ?");
    $q_handle->bindParam(1, $_POST["username"]);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    
    require_once("phplib/PasswordHash.php"); // PHPass hashing algorithm
    $hasher = new PasswordHash(12, false);
    $correct = $hasher->CheckPassword($_POST["password"], $arr["password"]);
        // compare entered password and hash
    
    if (!empty($arr["name"]) && $correct) // login data valid!
        return 1;
    return 0; 
}

//--------------------------------------------- MAIN PART ---------------------------------------------\\

$validarr = validInput();
$validinput = $validarr[0]; // input valid(1) or not(0)?
$error = $validarr[1];      // error message returned by validInput


if ($_SESSION["failattempt"][1] == 5 && !empty($_POST))
    $error = '<p style="color:red"><b>Sorry, login failed more than 5 times! Please wait a few minutes.</b></p>';
else if ($validinput) 
{
    $db_handle = setupDBConnection();

    if (validLogin($db_handle)) // regenerate session ID and move user to his/her personal page
    {
        session_regenerate_id();
        $_SESSION["usr"] = $_POST["username"];
        header("Location: " . $url . "personalpage.php");
    }
    else
    {
        $error = '<p style="color:red"><b>Username/password incorrect or user does not exist.</b></p>';
        ++$_SESSION["failattempt"][1];
        echo $_SESSION["failattempt"][1];
    }
} 
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>M-Choice Login Page</title>
</head>

<body>
 
<h2>Login</h2>

<form action=login.php method=post>
<p>Please enter your username and password.</p>
<?php echo $error; ?>
<p>Username</p>
<input type="text" name="username" maxlength="15"/>
<br/>
<p>Password</p>
<input type="password" name="password" maxlength="30"/>
<br/><br/>
<input type="submit" name="sub" value="Submit"/>
</form>

</body>
</html>