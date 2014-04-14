<?php
//tag_bookmarks.php -- lists bookmarks by tag name

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = member_db_connect();

//Start session and find out who's logged in
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

//Default page number is 1, unless there is a "page" variable in the URL
$page = 1;
if (is_numeric($_GET['page'])) {
   $page = intval($_GET['page']);
}

//Use option as an arugment for the fetch_this_tag function to retrieve all bookmarks
$option = "all";
       
//Include header and stylesheet for tags
include("../tasty_include/tasty_header.inc");

?>

<link type="text/css" rel="stylesheet" rev="stylesheet" href="css/tag_styles.css" />

<div>
<h2>recipes for <? echo $_GET['tag']; ?> </h2>

<?
/*Use the fetch_this_tag function to retreive all bookmarks by the tag name and display them in pages of 10 each*/
$tag_array = fetch_this_tag($_GET['tag'], $db, $page);
$tag_array_all = fetch_this_tag($_GET['tag'], $db, $page, $option);

$array_count = count($tag_array);
$array_count_all = count($tag_array_all);

  /*Find the total number of pages by dividing the total count of the array by 5 and rounding up. 
  If a sneaky user tries to change the page number in the URL, reset the page number back to 1. */
  $page_count = ceil($array_count_all / 10);
  if ($page > $page_count) {
    $page = 1;
  }

  if ($array_count <= 0) {
    echo "No bookmarks found for " . $_GET['tag'];
  }
  else {
  
    if ($page <= 1) {
      ?>&lt;&lt; back |<?
    }
    else {
    echo "<a href=tag_bookmarks.php?tag=". $_GET['tag'] . "&page=" .  ($page - 1) . ">&lt;&lt; back</a> |";
    }
    if ($array_count < 10 || $page == $page_count) {
      ?> next &gt;&gt;<?
    }
    else {
    echo "<a href=tag_bookmarks.php?tag=". $_GET['tag'] . "&page=" .  ($page + 1) . "> next &gt;&gt;</a>";
    }
 
    echo "<span class='info'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; page " . $page . " out of " .  $page_count . "</span>";
    
    echo "<br/><ul style='list-style-type:none;'>";
    
    //Cycle through the array and list out the bookmark, as well as an option to save the bookmark
    while (list($key, $this_tag) = each($tag_array)) {
        echo "<br><li><a href='" . $this_tag['url'] . "'>";
        echo $this_tag['title'] . "</a>";
        
        echo "<span class='action'><a class='next' href='bookmark.php?title=" . 
             urlencode($this_tag['title']) . "&url=". 
             urlencode($this_tag['url']) . "'>save</a></span><br><br>";
             
        echo "<span class='tags' style='float:left;color:#aaa;'>tags: </span>";     
             
        $relevant_tags = fetch_relevant_tags($db, $this_tag['bookmark_id']);   
        while (list($key, $value) = each($relevant_tags)) {
         
           echo "  <span class='tags' style='float:left;'><a class='follow' href='tag_bookmarks.php?tag=" . 
                        $value . "'> ". 
                        $value." </a></span> ";
         
        }
         
         
        echo "<br><hr>"; 
     }
}

?>
</ul>
</div>

<?
//Include footer
include("../tasty_include/tasty_footer.inc");
 
/*

echo "Tag array: <br>";
echo "<pre>";
print_r(fetch_this_tag($_GET['tag'], $db, $page));
echo "</pre>";

echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

*/




 
$db->close();
 
?>