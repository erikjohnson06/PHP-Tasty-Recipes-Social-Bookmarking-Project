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

//Find out which member's profile to display
//(profileID is this member's customer_id)
$profileID = $_GET['profileID'];

$myprofile_array = array();
if ($profileID) {
   $myprofile_array = fetch_profile($profileID, $db);
}
if ((count($myprofile_array) <= 0) && $customer_id) {
   //check for this profile
   $myprofile_array = fetch_profile($customer_id, $db);
   $profileID = $customer_id;
}
if (count($myprofile_array) <= 0) {
   //cannot find profile
   header("Location: index.php");
}  


//Include header
include("../tasty_include/tasty_header.inc");

?>

<div class="left_col">
 <div class="box">
  <div class="top">
	<div class="inside">
	 <div class="title">
	  <div class="left">
	   My Network Bookmarks
	  </div>
	  <div class="right">
	  </div>
	  <div class="clear_both"></div>
	 </div>
	</div>
   </div>
   <div class="bot">
	<div class="inside_custom">
	 
	 <?
           $bookmark_array = fetch_my_network_bookmarks($profileID, $db);
	   $array_count = count($bookmark_array);
	   while (list($key, $this_bookmark) = each($bookmark_array)) {
	      
	        echo "<a href=" . $this_bookmark['url'] . ">";
	     
	        echo $this_bookmark['title'];
	     
	        echo "</a><br>";
	      
	   }
	   if ($array_count <= 0) {
	     echo "<span style='color:#777'>Your network has not posted any bookmarks yet.</span>";
	   }
           
	 ?>
	</div>
   </div>
  </div>		
</div>
		
<div class="right_col">
 <div class="box">
  <div class="top">
   <div class="inside">
	<div class="title">
	 <div class="left">
	   Following
	 </div>
	 <div class="right">
	 </div>	
	 <div class="clear_both"></div>
	</div>
   </div>
  </div>
  <div class="bot">
   <div class="inside_custom">
	
	 <?
	   $network_array = fetch_my_network($profileID, $db);
	   $array_count = count($network_array);
	   while (list($key, $this_member) = each($network_array)) {
	      ?>
	        <a href="profile.php?profileID=<? echo $this_member['customer_id']; ?>">
	      <?
	        echo $this_member['login'] . "</a>" ;
	        
	        if ($this_member['name'] != " ") {
	            echo "<span style='color:#777;'>  [ " . $this_member['name'] . " ]</span>";
	        }
	      ?>
	        <br>
	      <?
	   }
	   if ($array_count <= 0) {
	     echo "<span style='color:#777'>You are not following anybody yet.</span>";
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

echo "Network array: <br>";
echo "<pre>";
print_r($network_array);
echo "</pre>";

echo "Bookmarks array: <br>";
echo "<pre>";
print_r(fetch_my_network_bookmarks($profileID, $db));
echo "</pre>";


echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/
$db->close();
?>