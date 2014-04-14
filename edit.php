<?php
//edit.php -- edit member profile and login information

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = member_db_connect();

//Start session and find out who's logged in
session_start();
$customer_id = $_SESSION['customer_id'];
$customer_login = $_SESSION['customer_login'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);


//Retrieve the member's profile
$myprofile_array = fetch_profile($customer_id, $db);


/*If the form has been submitted, proceed with checking the input for errors*/
if ($_POST['update_profile']) {

    /*Set post variables here. Use the trim() function to remove any white spaces
    on either end of the input.*/	   
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $homepage = trim($_POST['homepage']);
    $member = $_POST['customer_id'];
    
    /*Regular expressions for all text input. Names must contain dashes and apostrophes, 
    but must contain at least some letters. */
    $valid_name = "^(?=.*[A-Za-z])[A-Za-z][A-Za-z-']+$";
    
    /*Ensure that all fields are completed upon submission*/
    if (!($first_name && $last_name && $email && $homepage)) {
        $error_message = "Please make sure you've filled in all the form fields. <br>";
    }
    
    /*Check all input for length and validity based on the regular expressions above*/
    if (strlen($first_name) > 25 || strlen($last_name) > 25) {
        $error_message  = $error_message. "Please make sure both your first and last names are fewer than 25 characters each.  <br>";
    }
    
    if (!(valid_input($first_name, $valid_name)) || !(valid_input($last_name, $valid_name))) {
        $error_message = $error_message.  "Please enter a valid name, which contains letters, hyphens or apostrophes only. <br> ";
    }
    
    if (strlen($email) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message  = $error_message. "Please enter a valid email address, which is less than 50 characters.  <br>";
    }
    
    if (strlen($homepage) > 100 || !filter_var($homepage, FILTER_VALIDATE_URL)) {
        $error_message  = $error_message. "Please enter a valid URL address, as in http://www.example.com  <br>";
    }

    
/*If any error messages occurred as a result of not passing validation, display them as messages 
to the user and do not proceed further. If the data passed the validation, perform database constraint
checks, and then insert the data into the database. */
if (!$error_message) {
   
   /*Check the email address and name to make sure it doesn't already exist and remains unique in the database. */
   if (check_email($customer_id, $email, $db) != true) {
       $error_message  = $error_message. "Email address already exists! <br>"; 
   }

   /*If the database validation has passed, begin a transaction to update the member_info and member_login tables. */
   else {
            
       if ((update_profile($first_name, $last_name, $email, $homepage, $customer_id, $db)) == true) {
              $success_message = "<br>Profile was updated successfully!<br>";         
       }
       else {
              $error_message  = "<br>We're sorry. An error occured in updating your profile.<br>"; 
       }

   }
 } 
}


/*If the form to update login information has been submitted, proceed with the validation checks */
if ($_POST['update_login']) {
	   
    $username =  trim($_POST['username']);
    $current_password = trim($_POST['current_password']);
    $new_password1 = trim($_POST['new_password1']);
    $new_password2 = trim($_POST['new_password2']);
    $current_login = $_POST['current_login'];
    
    
    /*Regular expressions for new password. Password must include 6-12 characters, 
    must have both alphanumberic characters AND numbers, and may allow a few special characters
    as well. */
    $good_login = "[a-zA-Z0-9_]{6,12}";
    $good_password = "^(?=.{6,12})(?=.*[A-Za-z])(?=.*\d)[a-zA-Z0-9_!]+$"; 
    
    /*Ensure that all field are completed and meet te complexity requirements */
    if (!($username && $current_password && $new_password1 && $new_password2)) {
        $error_message = "Please complete all fields. ";
    }
        
    else if (strlen($username) > 12 || strlen($username) < 6 || !(valid_input($username, $good_login))) {
       $error_message = "Make sure that your login is between 6 and 12 characters, " .  
                        "and that it only contains letters, numbers, or an underscore (_).  ";
    }   
        
    else if (strlen($new_password1) > 12 || strlen($new_password1) < 6 || !(valid_input($new_password1, $good_password))) {
       $error_message = "Please make sure your new password is between 6 and 12 characters, and is a combination of 
                         contains letters and numbers. Special characters allowed include underscores (_) and exclamation marks (!).  ";
    }
    
    /*Makes sure the both passwords match each other*/
    else if (!($new_password1 == $new_password2)) {
       $error_message = "Passwords do not match.";
    }
    
    else {
       /*Now, check the database to make sure the current password is correct*/
       
       //Connect using the login credentials
       $db = login_db_connect();
       
          /*Check the username to name to make sure it doesn't already exist and remains unique in the database. */
       if (check_username($customer_id, $username, $db) != true) {
           $error_message = "Username already exists! <br>"; 
       }
       //Ensure that the current password is correct before proceeding
       else if (verify_password($customer_id, $current_password, $db) != true) {
           $error_message = "Invalid current password. Please make sure you are entering your password correctly.";
       }
 
       else  {
          
          //Update the login database and display a message 
          if ((update_login_info($customer_id, $username, $new_password1, $db)) == true) {
               $success_message = "Your login information has been successfully updated.";     
               //Close the connection   
          }
          else {
              $error_message  = "<br>We're sorry. An error occured in updating your login information.<br>"; 
          }
          
       }
   }
}


//Include header
include("../tasty_include/tasty_header.inc");

?>


<h2>edit your profile</h2>

<span style="color:red;font-size:12px;">
<?
//Display error / success messages here
if ($error_message) {
   echo $error_message . "<br>";
}
?>
</span>

<span style="color:blue;font-size:12px;">
<?
if ($success_message) {
   echo $success_message . "<br>";
}
?>
</span>

<form method="POST" action="">
 <fieldset>
 <legend>contact information:</legend>
 <table>

  <tr>
   <td align="right">
    First Name:
   </td>
   <td align="left">
     <input type="text" size="25" maxlength="25" name="first_name" value="<? if ($_POST) {echo $_POST['first_name'];} else {echo $myprofile_array['first_name'];} ?>">
   </td>
</tr>

<tr>
  <td align="right">
    Last Name:
  </td>
  <td align="left">
    <input type="text" size="25" maxlength="25" name="last_name" value="<? if ($_POST) {echo $_POST['last_name'];} else {echo $myprofile_array['last_name'];}?>">
  </td>
</tr>

<tr>
  <td align="right">
  Email address:
  </td>
  <td align="left">
    <input type="text" size="25" maxlength="50" name="email" value="<? if ($_POST) {echo $_POST['email'];} else {echo $myprofile_array['email'];} ?>">
  </td>
</tr>

<tr>
  <td align="right">
  Homepage:
  </td>
  <td align="left">
    <input type="text" size="25" maxlength="50" name="homepage" value="<? if ($_POST) {echo $_POST['homepage'];} else {echo $myprofile_array['homepage'];} ?>">
  </td>
</tr>




<tr>
  <td colspan="2" align="center">
   &nbsp;
  </td>
</tr>

<tr>
  <td colspan="2">
   <input type="hidden" name="customer_id" value="<? echo $customer_id ?>">
   <input style="margin-left:150px;" type="submit" name="update_profile" value="Update">
  </td>
</tr>

</table>
</fieldset>
</form>

<br>
<br>

<form method="POST" action="">
 <fieldset>
 <legend>login information:</legend>
<table>

<tr>
  <td align="right">
  username:
  </td><td align="left">
  <input type="text" size="24" maxlength="12" name="username" value="<? if ($_POST) {echo $_POST['username'];} else {echo $customer_login;} ?>">
  </td>

<tr>
  <td align="right">
  current password:
  </td><td align="left">
  <input type="password" size="24" maxlength="12" name="current_password" value="<? echo $_POST['current_password']; ?>">
  </td>
</tr>

<tr>
  <td align="right">
  new password:
  </td><td align="left">
  <input type="password" size="24" maxlength="12" name="new_password1" value="<? echo $_POST['new_password1']; ?>">
  </td>
</tr>

<tr>
  <td align="right">
  confirm new password:
  </td><td align="left">
  <input type="password" size="24" maxlength="12" name="new_password2" value="<? echo $_POST['new_password2']; ?>">
  </td>
</tr>

<tr>
<td colspan="2">
<input type="hidden" name="customer_id" value="<? echo $customer_id ?>">
<input type="hidden" name="customer_login" value="<? echo $customer_login ?>">
<input style="margin-left:150px;" type="submit" name="update_login" value="Update">
</td></tr>
</table><br>
</fieldset>
</form>

<br>
<br>
<?

//Include footer
include("../tasty_include/tasty_footer.inc");
 
/*

echo "Profile array: <br>";
echo "<pre>";
print_r(fetch_profile($customer_id, $db));
echo "</pre>";

echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "Post array: <br>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */


$db->close();
 
?>
