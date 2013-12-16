<?php
/* Add a large session timeout. Looks quite dangerous in terms of
 * session ID hijacking (if someone steals your session ID somewhere
 * in the meantime, he/she can continue where you left off).
 */
//session_set_cookie_params(7*24*3600, "", "", 1); 
//session_start();
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>M-Choice Registration Page</title>
</head>

<body>
 
<h2>Registration</h2>

<?php

error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

function printForm() // prints the registration form
{
    require('captcha-config.php'); // obtain the public Captcha key
    $secure = 1;
    echo  '<form action=registration.php method=post>
           <p>Please enter your desired username/password.</p>
           <p>Username</p>
           <input type="text" name="username" maxlength="15"/>
           <br/>
           <p>Password</p>
           <input type="password" name="password" maxlength="30"/>
           <p>Are you human? Prove it :-)</p>'
           . recaptcha_get_html($publickey, null, $secure) .
           '<br/>
           <input type="submit" name="sub" value="Submit"/>
           </form>';
}

function CaptchaOK() // check if the Captcha value is correct
{
    require('captcha-config.php'); // obtain the private Captcha key
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

function validInput() // check if user input is not empty
{
    /* This may look a bit silly, but more code could be added if 
     * users have to input more than only a username and password! 
     */
    if(isset($_POST["username"], $_POST["password"]))
    {
        if($_POST["username"] != "" && $_POST["password"] != "")
            return 1;
        echo '<p style="color:red"><b>Please enter a username and a password.</b></p>';
        return 0;
    }
}

function userExists($db_handle) // check if user exists in database
{ 
    $q_handle = $db_handle->prepare("select name from user where name = ?");
    $q_handle->bindParam(1, $_POST["username"]);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    if ($arr["name"] == "") // user doesn't exist
        return 0;
    return 1; 
}

function createUser($db_handle) // put a new user/password hash in the database
{
    //hash = password_hash($_POST["password"], PASSWORD_DEFAULT); // for future PHP versions
    require_once("PasswordHash.php"); // PHPass hashing algorithm
    $hasher = new PasswordHash(12, false);
    $hash = $hasher->HashPassword($_POST["password"]);

    $q_handle = $db_handle->prepare("insert into user values(?,?)");
    $q_handle->bindParam(1, $_POST["username"]);
    $q_handle->bindParam(2, $hash);
    $q_handle->execute();
}
    
require_once('recaptchalib.php');

// CHECK CAPTCHA & INPUT, TRY TO SUBMIT DATA IF VALID
if(CaptchaOK() and validInput())
    // valid Captcha and text entered in input fields, this user is not a spammer!
{   
    //// CONNECT TO THE DATABASE
    require_once("db-config.php");
    try
    { 
        $db_handle = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);
        /*$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* useful for debugging */
    }
    catch (PDOException $e)
    {
        echo "Connection failed, something's wrong: " . $e->getMessage();
        exit;
    }
    
    if(userExists($db_handle))
    {
        echo '<p style="color:red"><b>This user already exists! Please try another name...</b></p>';
        printForm(); // give user another opportunity to enter something else
    }
    else 
    {
        createUser($db_handle);
        echo '<p style="color:green"><b>Success! Welcome to M-Choice!</b><p>
              <p>Please click <a href="login.php">here</a> to log in.</p>';
    }
}
else // invalid input or first view of registration page
   printForm();

?>

</body>
</html>