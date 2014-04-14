<?php
//bookmark.php -- interface for adding bookmarks to a user's profile

//Include utility files and public API
require("../tasty_include/tasty_utilities.inc");
include("tasty_public.inc");

$db = member_db_connect();

//find out who's logged in and start session
session_start();
$customer_id = $_SESSION['customer_id'];
$_SESSION['navigation'] = basename($_SERVER['PHP_SELF']);

if (!(is_numeric($customer_id))) {
  //Nothing to display. Redirect to home page
  header("Location: index.php");
}

//Proces form inputs if they are submitted by they user
if (count($_POST) > 0 ) {
   
   $url = $_POST['url'];
   $title = $_POST['title'];
   $notes = $_POST['notes'];
   $tags = explode(" ", $_POST['tags']);
   for ($i = 0; $i < count($tags); $i++) {
      $tags[$i] = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $tags[$i]));
   }
   
   
   //Ensure that all fields were completed
   if (!($url && $title)) {
      $error_message = "Please make sure you've filled in all the form fields.";
   }
   //Check for proper formats for all the inputs
   else if (strlen($url) > 250 || !(validURL($url))) {
      $error_message = "Please make sure your bookmark is a valid URL.";
   }
   
   else if (strlen($title) > 250) {
      $error_message = "Please make sure your bookmark title is 250 characters or less.";
   }
   
   else {
      $success = true; //flag to determine the success of transaction
      
      //start transaction
      $command = "SET AUTOCOMMIT = 0";
      $result = $db->query($command);
      $command = "BEGIN";
      $result = $db->query($command);
      
      //Check the database for an existing bookmark with this URL
      $command = "SELECT bookmark_id FROM bookmark_urls WHERE url = '" . $db->real_escape_string($url) . "';";
      $result = $db->query($command);
      
      if ($data = $result->fetch_object()) {
         //Add an entry in the customer bookmarks using this bookmark id
         $bookmark_id = $data->bookmark_id;
      }
      else {
         //We need to create a bookmark url entry
         $command = "INSERT INTO bookmark_urls (bookmark_id, url) VALUES " . 
                    "('', '". $db->real_escape_string($url) ."');";
         $result = $db->query($command);
         
         if (($result == false) || $db->affected_rows == 0) {
            $success = false;
         }
         else {
            $bookmark_id = $db->insert_id;
         }
      }
      
      if (is_numeric($_POST['bookmark_id']) && !($_POST['bookmark_id'] == $bookmark_id)) {
           $old_bookmark_id = $_POST['bookmark_id'];
           $command = "UPDATE customer_bookmarks SET date_deleted=now() " . 
                      "WHERE customer_id = '". $db->real_escape_string($customer_id) ."' " . 
                      "AND bookmark_id = '".$db->real_escape_string($old_bookmark_id) ."';";
         $result = $db->query($command);
      
      }
      
      if ($success && is_numeric($bookmark_id)) {
          //must also check the database for an existing customer bookmark with this bookmark_id
          $command = "SELECT customer_id FROM customer_bookmarks " . 
                     "WHERE customer_id = '". $db->real_escape_string($customer_id) ."' " . 
                     "AND bookmark_id =  '". $db->real_escape_string($bookmark_id) ."';";
          $result = $db->query($command);
          
          if ($data = $result->fetch_object()) {
             //the bookmark already exists for the customer, so simply update the title, notes 
             //make sure it's not set as "deleted"
             $command = "UPDATE customer_bookmarks SET title = '". $db->real_escape_string($title) ."', ". 
                        "notes = '". $db->real_escape_string($notes) ."', ". 
                        "date_deleted = '' WHERE customer_id = '". $db->real_escape_string($customer_id) ."' "  . 
                        "AND bookmark_id = '". $db->real_escape_string($bookmark_id) ."';";
                        
             $result = $db->query($command);
             
             
             if ($result == false) {
                 $success = false;
             }
          }
          
          else {
             //insert into customer_bookmarks
             $command = "INSERT INTO customer_bookmarks (customer_id, bookmark_id, title, notes, date_posted) " . 
                        "VALUES ('". $db->real_escape_string($customer_id) ."', '". $db->real_escape_string($bookmark_id) ."', " .
                        "'". $db->real_escape_string($title) ."', '". $db->real_escape_string($notes) ."', now());";
                        
             $result = $db->query($command);
             
             if (($result == false) || $db->affected_rows == 0) {
                  $success = false;
             }
          }
      }
      
      //first delete all previous tags
      $command = "UPDATE bookmark_tags SET date_deleted=now() " . 
                 "WHERE customer_id='". $db->real_escape_string($customer_id) ."' " . 
                 "AND bookmark_id='". $db->real_escape_string($bookmark_id) ."' " . 
                 "AND date_deleted <=0;";
      $result = $db->query($command);
      
      //now, go through the tag array to add or update
      
      for ($j = 0; $j < count($tags); $j++) {
         if ($success && $tags[$j]) {
            
            //check the database for an existing tag_id for this tag
            $command = "SELECT tag_id FROM tasty_tags WHERE tag='". $db->real_escape_string($tags[$j]) ."';";
            $result = $db->query($command);
            
            if ($data = $result->fetch_object()) {
               //we will simply add an entry in bookmark_tags using this tag_id
               $tag_id = $data->tag_id;
            }
            else {
               //we need to create a tasty_tags entry
               $command = "INSERT INTO tasty_tags (tag_id, tag) VALUES " . 
                          "('', '". $db->real_escape_string($tags[$j]) ."');";
               $result = $db->query($command);
               
               if (($result == false) || ($db->affected_rows == 0)) {
                  $success = false;
                  break;
               }
               else {
                  $tag_id = $db->insert_id;
               }
             }
             
             if ($success && is_numeric($tag_id)) {
               //must also check the database for an existing tag with this tag_id
               
               $command = "SELECT tag_id FROM bookmark_tags WHERE " . 
                          "customer_id='". $db->real_escape_string($customer_id) ."' " . 
                          "AND bookmark_id='". $db->real_escape_string($bookmark_id) ."' " . 
                          "AND tag_id='". $db->real_escape_string($tag_id) ."';";
                          
               $result = $db->query($command);
               
               if ($data = $result->fetch_object()) {
                  $command = "UPDATE bookmark_tags SET date_deleted=0 " . 
                             "WHERE customer_id='". $db->real_escape_string($customer_id) ."' " . 
                             "AND bookmark_id='". $db->real_escape_string($bookmark_id) ."' " . 
                             "AND tag_id='". $db->real_escape_string($tag_id) ."';";
                             
                  $result = $db->query($command);
                  
                  if ($result == false) {
                      $success = false;
                  }
               }
               
               else {
               $command = "INSERT INTO bookmark_tags (tag_id, customer_id, bookmark_id) VALUES " . 
                          "('". $db->real_escape_string($tag_id) ."', '". $db->real_escape_string($customer_id) ."', " .
                          "'". $db->real_escape_string($bookmark_id) ."');";
               $result = $db->query($command);
               
                  if (($result == false) || ($db->affected_rows == 0)) {
                    $success = false;
                  }
               }
             }
         }
      }        
      
      
      if (!$success) {
         $command = "ROLLBACK";
         $result = $db->query($command);
         $error_message = "We're sorry, there has been an error on our end.  
                                Please contact us to report this bug.  ";
      }
      else {
         $command = "COMMIT";
         $result = $db->query($command);
      }
      //return to autocommit mode
      $command = "SET AUTOCOMMIT = 1";
      $result = $db->query($command);
      
      if ($success) {
          header("Location: profile.php");
      }
      
   }
}

else if (is_numeric($_GET['bookmark_id'])) {
    $bookmark_array = fetch_customer_bookmarks($customer_id, $db, null, null, $_GET['bookmark_id']);
    list($this_bookmark) = $bookmark_array;
    list($bookmark_id, $title, $notes, $date_posted, $url) = array_values($this_bookmark);
    
    $tag_array = fetch_bookmark_tags($customer_id, $db, $_GET['bookmark_id']);
    $tag_string = implode(" ", $tag_array);
}

//Include header
include("../tasty_include/tasty_header.inc");

if ($bookmark_id) {
echo "<h4>Edit your bookmark:</h4>";
}
else {
echo "<h4>Post a new bookmark:</h4>";
}

?>

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
       url:
      </td>
      <td align="left">
        <input type="text" size="50" maxlength="250" name="url" value="<? 
        if ($_GET['url']) {
          echo urldecode($_GET['url']); 
          } else  {
          echo htmlentities($url);
          }
          ?>">
      </td>
    </tr>

   <tr>
     <td align="right">
       title:
     </td><td align="left">
      <input type="text" size="50" maxlength="250" name="title" value="<? 
         if ($_GET['title']) {
          echo urldecode($_GET['title']); 
          } else {  
          echo htmlentities($title); }
          ?>">
     </td>
   </tr>

   <tr>
     <td align="right" valign="top">
       notes:
     </td>
     <td align="left">
        <textarea rows="3" cols="49" maxlength="500" name="notes"><? echo htmlentities($notes); ?></textarea>
     </td>
   </tr>

   <tr>
    <td align="right">
      tags:
    </td>
    <td align="left">
      <input type="text" size="50" maxlength="250" name="tags" value="<? echo htmlentities($tag_string); ?>" />
      *<i class="last">separate tags with spaces</i>
     </td>
    </tr>

  <tr>
    <td colspan="2" align="center">
      <input type="hidden" name="bookmark_id" value="<? echo htmlentities($bookmark_id); ?>">
      <input type="submit" value="Submit">
    </td>
  </tr>

</table><br/>
</form>


<?

/*Add section to list suggested bookmarks from allrecipes.com rss feed. 
Only display if the user is posting a new bookmark, not editing an existing one.*/
if (!$bookmark_id) {
echo "<hr>";
echo "<div>";
echo "<h4>Suggested bookmarks from Allrecipes.com:</h4>";

require_once "XML/RSS.php";

//RSS feed for main daily dishes
$rss_feed = "http://rss.allrecipes.com/daily.aspx?hubID=80";
$main_dishes = new XML_RSS($rss_feed);
$main_dishes->parse();

$channel = $main_dishes->getChannelInfo();

echo "<h4><a href=" . $channel['link'] . ">" .  $channel['title'] . "</a></h4>";
echo "<ul style='list-style-type:none;'>";

foreach ($main_dishes->getItems() as $item) {
  echo "<li><a href='". $item['link']. "'>" . $item['title']. "</a><br /></li>\n";
}

echo "</ul>";


//RSS feed for healthy dishes
$rss_feed = "http://rss.allrecipes.com/daily.aspx?hubID=84";
$healthy_dishes = new XML_RSS($rss_feed);
$healthy_dishes->parse();

$channel = $healthy_dishes->getChannelInfo();

echo "<h4><a href=" . $channel['link'] . ">" .  $channel['title'] . "</a></h4>";
echo "<ul style='list-style-type:none;'>";

foreach ($healthy_dishes->getItems() as $item) {
  echo "<li><a href='". $item['link']. "'>" . $item['title']. "</a><br /></li>\n";
}

echo "</ul>";

//RSS feed for dessert dishes
$rss_feed = "http://rss.allrecipes.com/daily.aspx?hubID=79";
$dessert_dishes = new XML_RSS($rss_feed);
$dessert_dishes->parse();

$channel = $dessert_dishes->getChannelInfo();

echo "<h4><a href=" . $channel['link'] . ">" .  $channel['title'] . "</a></h4>";
echo "<ul style='list-style-type:none;'>";

foreach ($dessert_dishes->getItems() as $item) {
  echo "<li><a href='". $item['link']. "'>" . $item['title']. "</a><br /></li>\n";
}

echo "</ul>";


//RSS feed for entertaining dishes
$rss_feed = "http://rss.allrecipes.com/daily.aspx?hubID=85";
$entertaining_dishes = new XML_RSS($rss_feed);
$entertaining_dishes->parse();

$channel = $entertaining_dishes->getChannelInfo();

echo "<h4><a href=" . $channel['link'] . ">" .  $channel['title'] . "</a></h4>";
echo "<ul style='list-style-type:none;'>";

foreach ($entertaining_dishes->getItems() as $item) {
  echo "<li><a href='". $item['link']. "'>" . $item['title']. "</a><br /></li>\n";
}

echo "</ul>";

echo "</div>";

}

//Include footer
include("../tasty_include/tasty_footer.inc");

/*
echo "Session array: <br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";


echo "Tag array: <br>";
echo "<pre>";
print_r(fetch_bookmark_tags($customer_id, $db, $_GET['bookmark_id']));
echo "</pre>";
*/

?>