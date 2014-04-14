<?php
//login.php -- interface for signing in to tasty recipes

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = login_db_connect();

//Start the session cookie
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

$login = $_POST['login'];
$password = $_POST['password'];

//Only check if someone has submitted a username or a password
if ($login || $password) {
   
   /*Regex formats for emails and passwords. Because these formats were used when the
   username and password was created, the same format should also be used when entering
   these values into the fields. This will add an additional step in preventing 
   malicious users from performing an SQL injection*/
   $good_login = "[a-zA-Z0-9_]{6,12}";
   $good_password = "^(?=.{6,12})(?=.*[A-Za-z])(?=.*\d)[a-zA-Z0-9_!]+$";
   
   //Make sure both exist and the email is valid 
   if (!($login )) {
       $error_message = "Please enter your username.";
   }
   
   else if (!($password)) {
       $error_message = "Please enter your password.";
   }
   
   /* Check for proper formats for all inputs. */
   else if (!(valid_input($login, $good_login)) || !(valid_input($password, $good_password))) {
       $error_message = "Please enter a valid username or password.";
   }
   
   else {
      //Now, check the database for a correct login
      $command = "SELECT customer_id, login FROM customer_logins WHERE login = '".$db->real_escape_string($login)."' ". 
                 "AND password = password('".$db->real_escape_string($password)."');";
      $result = $db->query($command);
      
      if ($data = $result->fetch_object()) {
      
         //Correct login. Set session variables
         $_SESSION['customer_id'] = $data->customer_id;
         $_SESSION['customer_login'] = $data->login;

         header("Location: login.php");
      }
      
      else {
         //Incorrect username or password.
         $error_message = "Sorry, your login was incorrect.  Please contact us if you've forgotten your password.";
     
      }
   }
}

//Include header
include("../tasty_include/tasty_header.inc");

//If the user is already logged in, display links to profile.php and logout.php
if ($_SESSION['customer_id']) {
   ?>
   <h4>Welcome <? echo $_SESSION['customer_login']; ?>!
   <a href="profile.php">Click here</a> to go your bookmarks,
   or <a href="logout.php">Click here</a> to log out</h4>
   <?
}

//If not logged in already, display a form requesting username and password
else {
   ?>
    <h4>Sign in</h4>
    <span style="color:red;font-size:12px;">
     <?
     if ($error_message) {
        echo $error_message;
     }
     ?>
   
</span>

<form method="POST" action="">
  <table>
    <tr>
      <td align="right">
         Username:
      </td>
      <td align="left">
         <input type="text" size="24" maxlength="12" name="login" value="<? echo $_POST['login']; ?>">
      </td>
   </tr>

   <tr>
     <td align="right">
        Password:
     </td>
     <td align="left">
        <input type="password" size="24" maxlength="12" name="password" value="">
     </td>
   </tr>

   <tr>
     <td colspn="2">&nbsp;</td>
   </tr>

   <tr>
     <td>&nbsp;</td>
     <td align="center">
       <input type="submit" value="Login">
    </td>
   </tr>
</table>
<br>
Not a member?  <a href="join.php">Click here</a> to join!
</form>
 
<br>
<br>


<?
}

//Include footer
include("../tasty_include/tasty_footer.inc");

/*
echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/

$db->close();

?>