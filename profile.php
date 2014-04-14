<?php
//profile.php -- lists member bookmarks

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = member_db_connect();

//Start session and find out who's logged in
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

//Find out which member's profile to display
//(profileID is this member's customer_id)
$profileID = $_GET['profileID'];

if (!(is_numeric($customer_id) || is_numeric($profileID))) {
   //Nothing to display. Redirect to the home page
   header("Location: index.php");
}

      
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
       
$page = 1;
if (is_numeric($_GET['page'])) {
   $page = intval($_GET['page']);
}

//Use option as an arugment for the fetch_bookmarks function to retrieve all bookmarks
$option = "all";
      
       
if ($_GET['remove_bookmark_id']) {

   $remove_id = $_GET['remove_bookmark_id'];

   remove_bookmark($customer_id, $db, $remove_id);
   
}
       
       
       
//Include header
include("../tasty_include/tasty_header.inc");

?>

<div>
<h2>recipes for 
 <? 
   echo $myprofile_array['login'];
   if ($myprofile_array['name']) {
      echo " -- " . $myprofile_array['name'];
   }
 ?>
</h2>


<?

  //Fetch all of the customer's bookmarks and place them in an array
  $bookmark_array_all = fetch_customer_bookmarks($profileID, $db, $page, $option);
  $array_count_all = count($bookmark_array_all);
  
  
  /*Find the total number of pages by dividing the total count of the array by 5 and rounding up. 
  If a sneaky user tries to change the page number in the URL, reset the page number back to 1. */
  $page_count = ceil($array_count_all / 5);
  if ($page > $page_count) {
    $page = 1;
    //header("Location: profile.php?profileID=" . $_GET['profileID'] . "&page=" . $page);
  }
  
  //Fetch the customer's bookmarks in groups of 5 to keep the content "above the fold"
  $bookmark_array = fetch_customer_bookmarks($profileID, $db, $page, null);
  $array_count = count($bookmark_array);
  
  
  if ($array_count <= 0) {
    echo "No bookmarks yet.";
  }
  else {
  
    if ($page <= 1) {
      ?>&lt;&lt; back |<?
    }
    else {
    echo "<a href=profile.php?profileID=". $_GET['profileID'] . "&page=" .  ($page - 1) . ">&lt;&lt; back</a> |";
    }
    if ($array_count < 5 || $page == $page_count) {
      ?> next &gt;&gt;<?
    }
    else {
    echo "<a href=profile.php?profileID=". $_GET['profileID'] . "&page=" .  ($page + 1) . "> next &gt;&gt;</a>";
    }
 
    echo "<span class='info'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; page " . $page . " out of " .  $page_count . "</span>";
    
    echo "<br/><ul style='list-style-type:none;'>";
    
    while (list($key, $this_bookmark) = each($bookmark_array)) {
        echo "<br><li><a href='" . $this_bookmark['url'] . "'>";
        echo $this_bookmark['title'] . "</a><br>";
        echo "<span class='info'>posted: " . date("M j, Y, g:i a", $this_bookmark['date_posted']) . "</span><br/>";
        echo $this_bookmark['notes'] . "<br>";
        
       if ($customer_id == $profileID) {
       
         echo "<span class='action'><a class='next' href='profile.php?remove_bookmark_id=" . 
                $this_bookmark['bookmark_id'] . "'>remove</a>";
       
         echo "<a class='last' href='bookmark.php?bookmark_id=" . 
                $this_bookmark['bookmark_id'] . "'>edit</a></span><br>";
       }
       
       if ($customer_id && $customer_id != $profileID) {
            echo "<span class='action'><a class='next' href='bookmark.php?title=" . 
            urlencode($this_bookmark['title']) . "&url=". 
            urlencode($this_bookmark['url']) . "'>save</a>";
           
            echo "<a class='last' href='index.php?spam_id=" . 
            $this_bookmark['bookmark_id'] . "'>flag as inappropriate</a></span>";
        }   
       echo "<br><hr>"; 
     }
}


?>
</ul>
</div>
<div class="clear_both"></div>


<div class="tags">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="css/tag_styles.css" />

<?

/*Display a word cloud for other users based the number of tags they've placed on recipes. 
Make sure that the count is at least 1 (or else, an error will occur). */
include("../tasty_include/tag_cloud.inc");
$bookmark_array = fetch_bookmark_tags($customer_id, $db);
if (count($bookmark_array)) {
    $cloud = new wordCloud($bookmark_array);
    echo $cloud->showCloud();
}

?>

   </div>

<div class="clear_both"></div>

<?
//Include footer
include("../tasty_include/tasty_footer.inc");
 
/*
echo "myprofile array: <br>";
echo "<pre>";
print_r($myprofile_array);
echo "</pre>";

echo "Profile array: <br>";
echo "<pre>";
print_r(fetch_profile($customer_id, $db));
echo "</pre>";

echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "Bookmarks array: <br>";
echo "<pre>";
print_r(fetch_customer_bookmarks($profileID, $db, $page, null));
echo "</pre>";

echo "Bookmarks array: <br>";
echo "<pre>";
print_r(fetch_customer_bookmarks($profileID, $db, $page, "all"));
echo "</pre>";

echo "tag array: <br>";
echo "<pre>";
print_r($bookmark_array);
echo "</pre>";
 */


$db->close();
 
?>