<?php
//add.php -- interface for adding other members to a user's profile

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = member_db_connect();

//find out who's logged in and start session
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

//Ensure that the user is logged in
if (!$customer_id) {
   
    //If the user is not logged in, redirect them to the home page:
    header("Location: index.php");
}


if (isset($_GET['searchstring'])) {

    /*Set GET variable for the search string here. Use the trim() function to remove any white spaces
    on either end of the input. Also, make the searchstring all upper case to make it easier to find 
    more consistent matches in the database (all of which will also be converted to upper case for 
    the search)*/	   
    $searchstring = trim($_GET['searchstring']);
    $searchstring = strtoupper($searchstring); 

    
    /*Regular expression for search input. We want to at least limit the search to alphanumeric characters
    and common symbols. */
    $valid_search = "^[A-Za-z0-9-'\?\!\.\:\;\,\@\s]+$";

    /*Ensure that the search field is completed upon submission*/
    if ($searchstring == "" || empty($searchstring)) {
        $error_message = "Please enter a username to search for. <br>";
    }
        
    /*Search input should not exceed 50 characters*/
    else if (strlen($searchstring) > 50) {
        $error_message  .=  "Search may not exceed 50 characters.  <br>";
    }
    
    else if (!(valid_input($searchstring, $valid_search))) {
        $error_message .=  "Please enter a valid keyword. <br> ";
    }
    
    else {
              
        //Search the member_info table for matching names
        $command = "SELECT DISTINCT customer_id, login FROM customer_logins " . 
                   "WHERE upper(login) LIKE '%" . $db->real_escape_string($searchstring) ."%'; ";
    
        $result = $db->query($command);
        
        $search_array = array(); 
        
        if ($result->num_rows > 0) {
          
           while ($data_array = $result->fetch_assoc()) {
              array_push($search_array, $data_array);
           }
           
           $search_count = count($search_array);
           
           if ($search_count == 1) {
              $matches_found = $search_count . " match was found. Click on the username " . 
                               "to visit the user's profile, or click \"Follow\" to add them to your network. <br>";
           }
           else {
              $matches_found = $search_count . " matches were found.Click on the username " . 
                               "to visit the user's profile, or click \"Follow\" to add them to your network. <br>";
           }
        }
        
        else {
           $matches_found = "No matches were found.<br>";
        }
    }
}

//Process network connections here
if ($_GET && isset($_GET['add_member'])) {

    /*Set GET variable for member to be added here. Use the trim() function to remove any white spaces
    on either end of the input (not really necessary here, but it can't hurt). Also, use the htmlentities
    and is_numberic functions to prevent any malicious injections. */	   
    $add_member = trim($_GET['add_member']);
    $add_member = htmlentities($add_member); 
    
    //Ensure that the variable is a number
    if (!(is_numeric($add_member))) {
       $error_message = "Whoops! An error has occurred in adding this member to your network."; 
    }
    /*The user logged in should not be able to add themselves to their own network, but
    just in case the user figures out how to manipulate the URL string, prevent this here */
    else if ($add_member == $customer_id) {
       $error_message = "Whoops! An error has occurred in adding this member to your network."; 
    }
   
    else {
         //Check to see whether the user logged in is already actively following the member
         if (check_network($customer_id, $add_member, $db) == true) {
         
         //If the connection already exists, display a message to the user 
         $error_message = "You are already following this member."; 
        }
        
        //Check to see whether the connection exists, but has been deactivated
        else if (check_deactivated($customer_id, $add_member, $db) == true) {
        
              /*If the connection already exists, update the tasty_network table rather than 
              inserting a new entry, and the redirect to the network page. */
              if (reactivate_connection($customer_id, $add_member, $db) == true) {
                 $confirm_message = "Successfully added member to your network.";
                 header("Location: network.php");
                 
              }
              else {
                $confirm_message = "An error was encountered adding this member to your network.";
              }
        }
      
        //If no connection is present, create a new connection in the tasty_network table
        else {
    
          /*Call the function defined to execute the update query with the appropriate information. 
          Display a success/error message, depending on the outcome*/
          if (follow_member($customer_id, $add_member, $db) == true) {

             header("Location: network.php");
          }
          else {
             $error_message = "Whoops! An error was encountered adding this member to your network.";
          }
        }
    }
}

//Include header
include("../tasty_include/tasty_header.inc");

?>

<h4>Follow other members to discover their favorite recipes!</h4>

<div class="full_col">
 <div class="box">
  <div class="top">
	<div class="inside">
	 <div class="title">
	  <div class="left">
	   Search
	  </div>
	  <div class="right">
	     
	  </div>
	  <div class="clear_both"></div>
	 </div>
	</div>
   </div>
   <div class="bot">
	<div class="inside_custom">

        <form method="GET" action="">
        <table>
	
        <tr>
          <td align="right">
             Enter a username:
          </td>
          <td align="left">
            <input type="text" size="25" maxlength="50" name="searchstring" value="<? echo $_GET['searchstring']; ?>">
          </td>

         <td colspan="2" align="center">
           <input type="submit" value="Search">
         </td>
       </tr>

       </table>
       </form>
       
       <span style="color:black;font-size:12px;">
       <?
       
       //Display error / success messages here
       if ($matches_found) {
         echo $matches_found . "<br>";
        }
       ?>
       </span>
       
       <span style="color:red;font-size:12px;">
       <?
       if ($error_message) {
          echo $error_message. "<br><br>";
       }
       ?>
       </span>
	<?
	
	/*Display search results here. Each result provides a link to the member's home page
	as well as an option to follow them */
	if ($search_count > 0) {
	
	echo "<table>";

	    /*Loop through each of the search results. Do not display the option to follow if the member's
	    own username appears */
	    for ($i = 0; $i < $search_count; $i++) {
	          
	          echo "<tr>";
	          echo "<td><a href='profile.php?profileID=" . $search_array[$i]['customer_id'] . "'>";
	          echo $search_array[$i]['login'] . "</a></td>";
	         
	          if ($search_array[$i]['customer_id'] != $customer_id) {
	             echo "<td><a class='follow' href='add.php?searchstring=" . $_GET['searchstring'] . 
	                  "&add_member=" . $search_array[$i]['customer_id'] . "'>follow</a></td>";
	             echo "</tr>";
	          }
	    }
	
	echo "</table>";
	
	}
	?>

	</div>
   </div>
  </div>		
</div>
<div class="clear_both"></div>




<?

//Include footer
include("../tasty_include/tasty_footer.inc");

/*
echo "GET array: <br>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "Search array: <br>";
echo "<pre>";
print_r($search_array);
echo "</pre>";


echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/
$db->close();
?>