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

echo "Now we're getting personal! :-)" 
?>