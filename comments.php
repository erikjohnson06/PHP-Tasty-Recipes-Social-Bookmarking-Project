<?php
//comments.php -- interface for adding bookmarks to a user's profile

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = public_db_connect();

//Start the session cookie
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

//Include header
include("../tasty_include/tasty_header.inc");

//Fetch the comments array for this bookmark
$comments_array = fetch_comments($_GET['bookmarkID'], $db);
$array_count = count($comments_array);

/*Just as a safety precaution, make sure the array has at least one element. If so, cycle through all the 
comments and display them, as well as an option to add this bookmark. This link will redirect to the 
bookmark.php page with the url and title pre-filled. */
if ($array_count > 0) {

echo "<h4>Comments for: <a href='" . htmlentities($comments_array[0]['url']) . "'>" . $comments_array[0][title] . "</a></h4>";

    while (list($key, $this_comment) = each($comments_array)) {
      
      echo "<span class='info'>";
      
      echo "posted: " . date("M j, Y, g:i a", $this_comment['date_posted']);
      echo " by <a href=profile.php?profileID=" . $this_comment['customer_id'] . ">" . 
            posted_by($this_comment['customer_id'], $db) . "</a>";
      echo "</span><br>";
           
      echo htmlentities($this_comment['notes']) . "<br/><br/>";
      
    }
     if ($customer_id) {
     echo "<br><a href='bookmark.php?title=" . urlencode($comments_array[0]['title']) . 
          "&url=". urlencode($comments_array[0]['url']) ."'>Add this bookmark my recipes </a>";
          
     }     
}
else {
     echo "No comments could be found.";
}



//Include footer
include("../tasty_include/tasty_footer.inc");

/*
echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "Comments array: <br>";
echo "<pre>";
print_r(fetch_comments($_GET['bookmarkID'], $db));
echo "</pre>";
*/

$db->close();

?>