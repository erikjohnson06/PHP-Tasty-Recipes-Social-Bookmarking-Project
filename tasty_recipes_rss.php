<?php

header("Content-Type: application/rss+xml; charset=ISO-8859-1");

include("tasty_public.inc");

$db = public_db_connect();

     
$rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
$rssfeed .= '<rss version="2.0">';
$rssfeed .= '<channel>';
$rssfeed .= '<title>Tasty Recipes RSS feed</title>';
$rssfeed .= '<link>http://ejohnson4.userworld.com/tasty/index.php</link>';
$rssfeed .= '<description>store your recipe bookmarks...browse other recipes...
             join today, its easy!</description>';

//For some reason, the ORDER BY clause will not work properly on this command. Using 
//"ASC" here builds the array just fine, but when changed to "DESC", only one row is retrieved.
$command = "SELECT cb.bookmark_id, cb.title, " . 
           "UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
           "bu.url, cb.notes " . 
           "FROM customer_bookmarks cb, bookmark_urls bu WHERE cb.bookmark_id = bu.bookmark_id AND " . 
           "cb.date_deleted <= 0 GROUP BY cb.bookmark_id ORDER BY date_posted ASC LIMIT 15;";
              
        
             
      $result = $db->query($command);
       
      //$bookmark_flag = "recent";
      //$bookmark_array = fetch_bookmarks($bookmark_flag, $db, 1, null, $customer_id);
      
      $bookmark_array = array();
      while ($this_bookmark_array = $result->fetch_assoc()) {
           array_push($bookmark_array, $this_bookmark_array);
      }
      
      
      while (list($key, $this_bookmark) = each($bookmark_array)) {
     
      //while($row = mysql_fetch_array($result)) {
      //  extract($row);
      
      $rssfeed .= "<item>";
      $rssfeed .="<title>" . $this_bookmark['title'] . "</title>";
      $rssfeed .="<link>" . $this_bookmark['url'] . "</link>";
      $rssfeed .="<pubDate>" . date('D, d M Y H:i:s O', $this_bookmark['date_posted']) . "</pubDate>";
      $rssfeed .="<description>" . $this_bookmark['notes'] . "</description>";
      $rssfeed .="</item>";
      
      }

    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';
 
    echo $rssfeed;

?>