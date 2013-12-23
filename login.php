<?php

error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

//---------------------------------------- SESSION MANAGEMENT -----------------------------------------\\

if(file_exists("phplib/multfunlib.php"))
        require_once("phplib/multfunlib.php"); 
            // contains functions that are used more than once (DB connection, sessions, Captcha's) 
    else 
        exit("<p>Sorry, the function library could not be found.</p>"); 

if(file_exists('phplib/config.php'))
        require('phplib/config.php'); // obtain website URL
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

function failedAttempt($db_handle, $trusted_user) // user failed to provide correct login
{
    // check if a user with current IP has submitted something earlier
    $q_handle = $db_handle->prepare("select user_ip, attempt from login_attempts where user_ip = ?");
    $ip = ip2long($_SERVER["REMOTE_ADDR"]);
    $q_handle->bindParam(1, $ip);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    
    if (empty($arr["user_ip"])) // this IP is unknown, put it into the database
    {
        $q_handle = $db_handle->prepare("insert into login_attempts values(?,?)");
        $q_handle->bindParam(1, $ip);
        
        if ($trusted_user) // user provided right login data, reset attempts
        {
            $q_handle->bindValue(2, 0, PDO::PARAM_INT);
            $q_handle->execute();
            return;
        }   
        
        $q_handle->bindValue(2, 1, PDO::PARAM_INT); // first login attempt
        $q_handle->execute();
    }
    else // we know this IP
    {
        if ($arr["attempt"] == 5 && !$trusted_user) // beyond 5 login attempts: turn Captcha on
            return 1;
        
        // increment the attempt (or reset attempt for trusted users)
        $q_handle = $db_handle->prepare("update login_attempts set attempt = ? where user_ip = ?");
        
        if ($trusted_user) // user provided right correct data, reset attempts
        {
            $q_handle->bindValue(1, 0, PDO::PARAM_INT);
            $q_handle->bindParam(2, $ip);
            $q_handle->execute();
            return;
        } 
        $q_handle->bindValue(1, ++$arr["attempt"], PDO::PARAM_INT);
        $q_handle->bindParam(2, $ip);
        $q_handle->execute();
    }
    return 0; // do not turn Captcha on
}

function validLogin($db_handle) // check if user has entered valid login data
{ 
    if (isset($_POST["recaptcha_response_field"]) && !CaptchaOK())
        // incorrect Captcha entered, applicable if Captcha is set to on
        return array(0, 1, 0); // array(bool: valid login, bool: put Captcha on/off,
                               //       bool: Captcha correct)

    $q_handle = $db_handle->prepare("select name, password from user where name = ?");
    $q_handle->bindParam(1, $_POST["username"]);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    
    require_once("phplib/PasswordHash.php"); // PHPass hashing algorithm
    $hasher = new PasswordHash(12, false);
    $correct = $hasher->CheckPassword($_POST["password"], $arr["password"]);
        // compare entered password and hash
    
    if (!empty($arr["name"]) && $correct) // login data valid!
    {
        failedAttempt($db_handle, 1); // user regains our trust
        return array(1, 0, 1);
    }        
    
    $captcha_on = failedAttempt($db_handle, 0); // users loses our trust
    if ($captcha_on && !isset($_POST["recaptcha_response_field"]))
        // user already at fifth login attempt but Captcha wasn't displayed at previous login
        return array(0, 1, 0); 
    return array(0, $captcha_on, 1);
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
<?php 
//--------------------------------------------- MAIN PART ---------------------------------------------\\

$validarr = validInput();
$validinput = $validarr[0]; // input valid(1) or not(0)?
$error = $validarr[1];      // error message returned by validInput

if ($validinput) 
{
    $db_handle = setupDBConnection();
    $validarr = validLogin($db_handle);
    $validlogin = $validarr[0];
    $captcha_on = $validarr[1];
    $captcha_correct = $validarr[2];

    if ($captcha_on and !$captcha_correct)
        $error = '<p style="color:red"><b>Too many login attempts!' . 
                 ' Please prove that you are a human.</b></p>';
    else if ($validlogin) // regenerate session ID and move user to his/her personal page
    {
        session_regenerate_id();
        $_SESSION["usr"] = $_POST["username"];
        header("Location: " . $url . "personalpage.php");
    }
    else
        $error = '<p style="color:red"><b>Username/password incorrect or user does not exist.</b></p>';
} 

echo $error; 
?>
<p>Username</p>
<input type="text" name="username" maxlength="15"/>
<br/>
<p>Password</p>
<input type="password" name="password" maxlength="30"/>
<br/>
<?php 
if (!empty($captcha_on) and $captcha_on)
    // captcha_on is not empty and we have to insert a Captcha
    echo '<br/>' . Captcha();
?>
<br/>
<input type="submit" name="sub" value="Submit"/>
</form>

</body>
</html>