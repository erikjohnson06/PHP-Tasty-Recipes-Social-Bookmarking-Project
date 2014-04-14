<?php
//index.php

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = public_db_connect();

$bookmark_flag = $_GET['mode'];
if (!($bookmark_flag)) {
    $bookmark_flag = "popular";
}

//Start session
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = $bookmark_flag;

$page = 1;
if (is_numeric($_GET['page'])) {
   $page = intval($_GET['page']);
}

if (is_numeric($_GET['spam_id'])) {
   flag_bookmark($_GET['spam_id'], $db, $_SESSION['customer_id']);
   $message = "You have successfully flagged a bookmark as inappropriate. " .
              "You will no longer see the bookmark in this list."; 
}

if (is_numeric($_GET['block_member_id'])) {
   flag_member($_GET['block_member_id'], $db, $_SESSION['customer_id']);
   $message = "You have blocked all posts by this member. " .
              "You will no longer see their bookmarks in this list."; 
}



//Use option as an arugment for the fetch_bookmarks function to retrieve all bookmarks
$option = "all";

//Include header
include("../tasty_include/tasty_header.inc");

?>

<div>

<h2><? echo htmlentities($bookmark_flag); ?> recipe bookmarks</h2>
<span style="color:red;font-size:12px;">
<?
if ($message) {
   echo $message;
}
?>
</span>

  <?
    $bookmark_array = fetch_bookmarks($bookmark_flag, $db, $page, null, $customer_id);
    $array_count = count($bookmark_array);
    
    $all_bookmarks_array = fetch_bookmarks($bookmark_flag, $db, $page, $option, $customer_id);
    $array_count_all = count($all_bookmarks_array);
    
    /*Find the total number of pages by dividing the total count of the array by 5 and rounding up. 
    If a sneaky user tries to change the page number in the URL, redirect back to the default page. */
    $page_count = ceil($array_count_all / 5);
    if ($page > $page_count) {
       $page = 1;
       header("Location: index.php?mode=" . $bookmark_flag);
    }
     
    
    if ($page <= 1) {
      ?>&lt;&lt; back |<?
    }
    else {
    ?><a href="index.php?mode=<? echo $bookmark_flag; ?>&page=<? echo ($page - 1); ?>">&lt;&lt; back</a> |<?
    }
    if ($array_count < 5 || $page == $page_count) {
      ?> next &gt;&gt;<?
    }
    else {
    ?><a href="index.php?mode=<? echo $bookmark_flag; ?>&page=<? echo ($page + 1); ?>"> next &gt;&gt;</a><?
    }
    
       
    echo "<span class='info'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; page " . $page . " out of " .  $page_count . "</span>";
    
    echo "<br><ul style='list-style-type:none;'>";
    
    while (list($key, $this_bookmark) = each($bookmark_array)) {
      
      echo "<br><li><a href='" . htmlentities($this_bookmark['url']) . "'>";
      echo htmlentities($this_bookmark['title']) . "</a><br/>";
      
      echo "<span class='info'>";
      
      if ($bookmark_flag == "popular") {
      
         echo "# of posts: " . $this_bookmark['popularity'];
         
         echo " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=comments.php?bookmarkID=" . $this_bookmark['bookmark_id'] . ">" . 
              "comments</a>";
              
         echo " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; first posted by: " . 
              "<a href=profile.php?profileID=" . $this_bookmark['customer_id'] . ">" . 
              posted_by($this_bookmark['customer_id'], $db)  . "</a>";
              
      }
      else {
         echo "posted: " . date("M j, Y, g:i a", $this_bookmark['date_posted']);
         echo " by <a href=profile.php?profileID=" . $this_bookmark['customer_id'] . ">" .
         posted_by($this_bookmark['customer_id'], $db) . "</a>";

      }
      echo "</span><br>";
      
      echo htmlentities($this_bookmark['notes']) . "<br>";
      
      echo "<span class='action'><a class='next' href='bookmark.php?title=" . 
            urlencode($this_bookmark['title']) . "&url=". 
            urlencode($this_bookmark['url']) . "'>save</a>";
           
      echo "<a class='next' href='index.php?spam_id=" . 
           $this_bookmark['bookmark_id'] . "'>flag as inappropriate</a>";
           
      echo "<a class='last' href='index.php?block_member_id=" . 
           $this_bookmark['customer_id'] . "'>block posts by this member</a></span><br>";
      
      
      echo "<hr style='margin-top:10px;margin-bottom:10px;'>";
      
    }
    if ($array_count <= 0) {
       echo "No bookmarks yets";
    }

  ?>
</ul>
</div>
<div class="clear_both"></div>

<div class='tag_toggle'>
<?
   if ($_GET['displaytags'] == 'list') {
   
      echo "<span class='tag_toggle'>";
      print_navigation($navigation_flag, "show as cloud", "index.php?mode=" .$bookmark_flag . "&displaytags=cloud");
      echo "</span>";
   }
   else {
      echo "<span class='tag_toggle'>";
      print_navigation($navigation_flag, "show as list", "index.php?mode=" .$bookmark_flag . "&displaytags=list");
      echo "</span>";
   }
?>
</div>
<div class="clear_both"></div>

<div class="tags">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="css/tag_styles.css" />

<?
include("../tasty_include/tag_cloud.inc");
$tag_array = fetch_all_bookmark_tags($db);
$cloud = new wordCloud($tag_array);

if ($_GET['displaytags'] == 'list') {
   echo $cloud->showList();
}
else {
   echo $cloud->showCloud();
}







?>

</div>
<div class="clear_both"></div>


<?

//Include footer
include("../tasty_include/tasty_footer.inc");

/*
echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

var_dump(filter_var("index.php?mode=popular", FILTER_VALIDATE_URL));

var_dump(filter_var("http://index.php?mode=popular", FILTER_VALIDATE_URL));

echo "Bookmarks array: <br>";
echo "<pre>";
print_r($bookmark_array);
echo "</pre>";

echo "Bookmarks array: <br>";
echo "<pre>";
print_r($all_bookmarks_array);
echo "</pre>";


echo "Tag array: <br>";
echo "<pre>";
print_r(fetch_all_bookmark_tags($db));
echo "</pre>";
*/

$db->close();
?>