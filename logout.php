<?php

//logout.php -- removes session variable 'customer_id'

session_start();
$_SESSION['customer_id'] = '';
$_SESSION['customer_login'] = '';
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

//Include header
include("../tasty_include/tasty_header.inc");

?>

<h4>You have successfully logged out. <a href="index.php">Click here</a>
to go back to t.as.ty recipes.</h4>

<?

//Include footer
include("../tasty_include/tasty_footer.inc");

?>