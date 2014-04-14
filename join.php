<?
//join.php -- interface for joining the tasty recipe site

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");
require_once "HTML/Form.php";

//Start the session cookie
session_start();
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);


//Process form input once submitted by the user
if (count($_POST) > 0) {
 
   $db = login_db_connect();
 
   $email = $_POST['email'];
   $login = $_POST['login'];
   $password = $_POST['password'];
   $verify_password = $_POST['password2'];
 
   /* Regex formats for emails and passwords. Password must include 6-12 characters, 
    must have both alphanumberic characters AND numbers, and may allow a few special characters
    as well. */
   $good_login = "[a-zA-Z0-9_]{6,12}";
   $good_password = "^(?=.{6,12})(?=.*[A-Za-z])(?=.*\d)[a-zA-Z0-9_!]+$";
   
   //First, make sure all fields were submitted
   if (!($email && $login && $password && $verify_password)) {
       $error_message = "Please complete all fields.";
   }
   
   //Check for proper formats for all inputs
   else if (strlen($login) > 12 || strlen($login) < 3 || !(valid_input($login, $good_login))) {
       $error_message = "Make sure that your login is between 6 and 12 characters, " .  
                        "and that it only contains letters, numbers, or an underscore (_).  ";
   }
   
   else if (strlen($email) > 50 || !(filter_var($email, FILTER_VALIDATE_EMAIL))) {
       $error_message = "Make sure you enter a valid email address, " .
                        "which is less than 50 characters.  ";
   }
   
   else if (strlen($password) > 12 || strlen($password) < 6 || !(valid_input($password, $good_password))) {
      $error_message =  "Please make sure your password is between 6 and 12 characters, and is a combination of 
                         contains letters and numbers. Special characters allowed include underscores (_) and exclamation marks (!).  ";
   }
   
   //Make sure that both passwords match
   else if (!($password == $verify_password)) {
       $error_message = "Passwords do not match.";
   }
   else {
       
       //Check the database for an existing login
       $command = "SELECT customer_id FROM customer_info WHERE email = '". $db->real_escape_string($email) ."';";
       
       $result = $db->query($command);
       
       if ($data = $result->fetch_object()) {
          $error_message = "We have found an existing member with that email address.  
                            Please contact us if you have forgotten your password.";
       }
       
       else {
          //Check the database for an existing login
          $command = "SELECT customer_id FROM customer_logins WHERE login = '". $db->real_escape_string($login) ."';";
          
          $result = $db->query($command);
          
          if ($data = $result->fetch_object()) {
             $error_message = "We have found an existing member with that login. Please contact us if you " .
                              "have forgotten your password, or choose another login.";
          }
          else {
            //Create the new member since all checks have passed
            $success = true;  //Flag for determining the success of a transaction
            $customer_id = ''; //This will be determined by auto_increment
            
            //Start transaction
            $command = "SET AUTOCOMMIT = 0";
            $result = $db->query($command);
            $command = "BEGIN";
            $result = $db->query($command);
            
            //First, customer logins
            $command = "INSERT INTO customer_logins (customer_id, login, password) " . 
                       "VALUES ('', '".$db->real_escape_string($login)."', password('".$db->real_escape_string($password)."'));";
            $result = $db->query($command);     
            
            if (($result = false) || ($db->affected_rows == 0)) {
               $success = false;
            }  
            
            else {
               //Now, customer info
               $customer_id = $db->insert_id;
               $command = "INSERT INTO customer_info (customer_id, email, date_enrolled) " . 
                          "VALUES ('".$db->real_escape_string($customer_id)."', '".$db->real_escape_string($email)."', now());";
               
               $result = $db->query($command);
               
               if (($result = false) || ($db->affected_rows == 0)) {
               $success = false;
               } 
            }  
            
            if (!$success) {
               $command = "ROLLBACK";
               $result = $db->query($command);
               $error_message = "We're sorry, there has been an error on our end. " . 
                                "Please contact us to report this bug.";
            }  
            else {
               $command = "COMMIT";
               $result = $db->query($command);
               
               //Set session variable
               $_SESSION['customer_id'] = $customer_id;
               $_SESSION['customer_login'] = $login;
               
            }
            $command = "SET AUTOCOMMIT=1";
            $result = $db->query($command);
          }
       }
       
   }
   $db->close();
}

//Include header
include("../tasty_include/tasty_header.inc");

if ($_SESSION['customer_id']) {
   ?>
   <h4>Welcome <? echo $_SESSION['customer_login']; ?>!
   <a href="profile.php">Click here</a> to go your bookmarks,
   or <a href="logout.php">Click here</a> to log out</h4>
   <?
}
else {

?>
<h4>Join now and start bookmarking. Its easy and free!</h4>
<span style="color:red;font-size:12px;">
<?
if ($error_message) {
  echo $error_message;
}
?>
</span>

<?

$form = new HTML_Form('join.php', 'post');

$form->addText("email", "Your Email Address: ", $email, 25, 50);
$form->addText("login", "Choose a Login: ", $login, 12, 12);
$form->addPasswordOne("password", "Choose a Password: ", $password, 12, 12);
$form->addPasswordOne("password2", "Please retype your Password: ", $verify_password, 12, 12);
$form->addSubmit("submit", "Submit");
$form->addPlainText("Already a member?", "<a href='login.php'>Click here</a> to log in!");

$form->display();

}

//Include footer
include("../tasty_include/tasty_footer.inc");

?>