<?php 
/* 
 * This is a library of PHP functions that are used more than once on the site:
 * - Connecting to the database
 * - Starting a session and keeping track of session expiration
 * - Adding and checking Captcha's
 * I thought of the DRY principle here: Don't Repeat Yourself.
 */
 
//// DATABASE CONNECTION

function setupDBConnection()
{
    if(file_exists('phplib/config.php')) // change the location to from where session_dbconnect.php is included!
        require('phplib/config.php');    // obtain database configuration
    else 
        exit("<p>Sorry, the configuration file could not be found.</p>");

    try
    { 
        $db_handle = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);
        /*$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* useful for debugging */
        return $db_handle;
    }
    catch (PDOException $e)
    {
        echo "Connection failed, something's wrong: " . $e->getMessage();
        exit;
    }
}

//// SESSION MANAGEMENT

function startSession() // start session and keep track of session expiration
{
    $secure = 1;
    $httponly = 1;
    session_set_cookie_params(7*24*3600, "", "", $secure, $httponly); 
    session_start();

    if (!isset($_SESSION["expire"]) || (time() > $_SESSION["expire"])) 
    {
        // new visit or old session has expired: destroy old session...
        $_SESSION = array();
        session_destroy(); 
        // ...and start a new one/create a new session ID
        session_start();
    }
    
    $_SESSION["expire"] = time() + 7*24*3600; // set (idle) timeout time
}

//// ADDING AND CHECKING CAPTCHA'S 

if(file_exists("phplib/recaptchalib.php"))
    require("phplib/recaptchalib.php");
else 
    exit("<p>Sorry, the Captcha library could not be found.</p>");

function Captcha() // a very simple Captcha wrapper which returns Captcha HTML
{   
    if(file_exists("phplib/config.php"))
        require("phplib/config.php"); // obtain the public Captcha key
    else 
        exit("<p>Sorry, the configuration file could not be found.</p>");
        
    $secure = 1;
    return recaptcha_get_html($publickey, null, $secure);
}

function CaptchaOK() // check if the Captcha value is correct
{
    if(file_exists("phplib/config.php"))
        require("phplib/config.php"); // obtain the private Captcha key
    else 
        exit("<p>Sorry, the configuration file could not be found.</p>");
        
    if(isset($_POST["recaptcha_response_field"]))
    {
        $resp = recaptcha_check_answer($privatekey,
                                       $_SERVER["REMOTE_ADDR"],
                                       $_POST["recaptcha_challenge_field"],
                                       $_POST["recaptcha_response_field"]);
        if($resp->is_valid) // Captcha correct!
            return 1;
        echo '<p style="color:red"><b>Sorry, but your Captcha value is incorrect.</b></p>';
        return 0;
    } 
}