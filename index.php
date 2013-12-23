<?php
if(file_exists('phplib/config.php'))
        require('phplib/config.php'); // obtain website url
    else 
        exit("<p>Sorry, the configuration file could not be found.</p>");
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>Welcome to M-Choice!</title>
</head>

<body>
 
<h2>Welcome</h2>

<p>Hi, welcome to M-Choice!</p>
<p>Please click <a href="<?php echo $url . "registration.php"; ?>"><b>here</b></a> to 
register or <a href="<?php echo $url . "login.php"; ?>"><b>here</b></a> to log in!</p> 

</body>
</html>