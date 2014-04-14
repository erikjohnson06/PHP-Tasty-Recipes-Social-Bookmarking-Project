<?php
//tasty_public.inc -- a public library for tasty recipes

//fetch the bookmarks in the database and make them sortable by popularity or post date
function fetch_bookmarks($flag, $db, $page, $option, $customer_id ='') {
   //bookmark array is a two-dimensional array
   //it holds arrays of bookmark information
   //page should be an integer
   
   $bookmark_array = array();
   $ip_address = getenv("REMOTE_ADDR");
  /* 
    Original command
   $command = "SELECT cb.bookmark_id, cb.title, UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
              "bu.url, cb.notes, count(cb.customer_id) AS popularity FROM " . 
              "customer_bookmarks cb, bookmark_urls bu WHERE cb.bookmark_id = bu.bookmark_id AND " . 
              "cb.date_deleted <= 0 GROUP BY cb.bookmark_id"; 
     
   */
   
   //Old command, for reference
   /*
   if ($flag == "popular") {

   $command = "SELECT cb.bookmark_id, cb.title, UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
              "bu.url, cb.notes, count(cb.customer_id) AS popularity, cb.customer_id, " .
              "count(fb.bookmark_id) AS flag_count FROM " . 
              "customer_bookmarks cb INNER JOIN bookmark_urls bu " .
              "ON cb.bookmark_id = bu.bookmark_id " . 
              "LEFT OUTER JOIN flagged_bookmarks fb ON bu.bookmark_id=fb.bookmark_id ".
              "WHERE (fb.bookmark_id IS NULL OR " . 
              " !(fb.customer_id='". $db->real_escape_string($customer_id) . "' "  .
              "OR fb.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              "AND cb.bookmark_id IN (select MIN(bookmark_id) from customer_bookmarks group by bookmark_id) " .
              "AND cb.date_deleted <= 0 " .
              "GROUP BY cb.bookmark_id " . 
              "ORDER BY popularity DESC, cb.date_posted DESC "; 
   }
   */
   //Command to include login names, using popular flag
   if ($flag == "popular") {

   $command = "SELECT cb.bookmark_id, cb.title, UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
              "bu.url, cb.notes, count(cb.customer_id) AS popularity, cb.customer_id, " .
              "count(fm.flagged_member) AS flag_member_count, " .
              "count(fb.bookmark_id) AS flag_count FROM " . 
              "customer_bookmarks cb INNER JOIN bookmark_urls bu " .
              "ON cb.bookmark_id = bu.bookmark_id " . 
              "LEFT OUTER JOIN flagged_bookmarks fb ON bu.bookmark_id=fb.bookmark_id ".
              "LEFT OUTER JOIN flagged_members fm ON cb.customer_id = fm.flagged_member ".
              
              "WHERE (fb.bookmark_id IS NULL OR " . 
              " !(fb.customer_id='". $db->real_escape_string($customer_id) . "' "  .
              "OR fb.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              

              "AND (fm.flagged_member IS NULL OR " .
              " !(fm.flagging_member ='". $db->real_escape_string($customer_id) . "' "  .
              "OR fm.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              
              "AND cb.bookmark_id IN (select MIN(bookmark_id) from customer_bookmarks group by bookmark_id) " .
              "AND cb.date_deleted <= 0 " .
              "GROUP BY cb.bookmark_id " . 
              "ORDER BY popularity DESC, cb.date_posted DESC "; 
   }

   //Old command, for reference
   /*
   else if ($flag == "recent") {

   $command = "SELECT cb.bookmark_id, cb.title, UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
              "bu.url, cb.notes, cb.customer_id, " .
              "count(fb.bookmark_id) AS flag_count FROM " . 
              "customer_bookmarks cb INNER JOIN bookmark_urls bu " .
              "ON cb.bookmark_id = bu.bookmark_id " . 
              "LEFT OUTER JOIN flagged_bookmarks fb ON bu.bookmark_id=fb.bookmark_id ".
              "WHERE (fb.bookmark_id IS NULL OR " . 
              " !(fb.customer_id='". $db->real_escape_string($customer_id) . "' "  .
              "OR fb.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              "AND date_posted = (select MAX(date_posted) from customer_bookmarks cbs WHERE cb.bookmark_id = cbs.bookmark_id) " .
              "AND cb.date_deleted <= 0 " .
              "GROUP BY cb.bookmark_id " . 
              "ORDER BY date_posted DESC "; 
    }
    */
    
   //Command to include login names, using recent flag
   else if ($flag == "recent") {

   $command = "SELECT cb.bookmark_id, cb.title, UNIX_TIMESTAMP(MIN(cb.date_posted)) AS date_posted, " . 
              "bu.url, cb.notes, cb.customer_id, " .
              "count(fm.flagged_member) AS flag_member_count, " .
              "count(fb.bookmark_id) AS flag_count FROM " . 
              "customer_bookmarks cb INNER JOIN bookmark_urls bu " .
              "ON cb.bookmark_id = bu.bookmark_id " . 
              "LEFT OUTER JOIN flagged_bookmarks fb ON bu.bookmark_id = fb.bookmark_id ".
              "LEFT OUTER JOIN flagged_members fm ON cb.customer_id = fm.flagged_member ".
              
              "WHERE (fb.bookmark_id IS NULL OR " . 
              " !(fb.customer_id='". $db->real_escape_string($customer_id) . "' "  .
              "OR fb.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              
              "AND (fm.flagged_member IS NULL OR " .
              " !(fm.flagging_member ='". $db->real_escape_string($customer_id) . "' "  .
              "OR fm.ip_address='" . $db->real_escape_string($ip_address) . "')) " .
              
              "AND date_posted = (select MAX(date_posted) from customer_bookmarks cbs WHERE cb.bookmark_id = cbs.bookmark_id) " .
              "AND cb.date_deleted <= 0 " .
              "GROUP BY cb.bookmark_id " . 
              "ORDER BY date_posted DESC "; 
    }

   /*In order to get a count of all the bookmarks, check for the "all" option. If set, then get an array
   of the first 100 bookmarks*/
   if ($option == "all") {
    $command .= " LIMIT 100;";
   }
   else {
     if (!(is_numeric($page)) || $page < 1) {
        $page = 1; 
     }
     $command .= " LIMIT " . (($page - 1) * 5) . ",5;";
   }
  
  //echo "Command: " . $command . "<br><br>";
   
   $result = $db->query($command);
   
   /*Build the array here, but only add those bookmarks where they have been flagged less than
   10 times, and where the member who posted it has been flagged less than 10 times */ 
   while ($this_bookmark_array = $result->fetch_assoc()) {
      if ($this_bookmark_array['flag_count'] < 10 && $this_bookmark_array['flag_member_count'] < 10) {
         array_push($bookmark_array, $this_bookmark_array);
       }
   }
   return $bookmark_array;
}

//function used to flag a bookmark if deemed inappropriate
function flag_bookmark($bookmark_id, $db, $customer_id = '') {
   $ip_address = getenv("REMOTE_ADDR");
   
   if (is_numeric($bookmark_id)) {
       $command = "SELECT bookmark_id FROM flagged_bookmarks WHERE bookmark_id = '". $bookmark_id ."' ".
                  "AND (ip_address='". $db->real_escape_string($ip_address) ."'";
      if (intval($customer_id) > 0) {
         $command .= " OR customer_id='". $customer_id ."'";
      }
      $command .= ");";
      
      $result = $db->query($command);
            
      if ($result->num_rows <= 0) {
        //add new entry
        $command = "INSERT INTO flagged_bookmarks (bookmark_id, customer_id, ip_address, date_flagged) " . 
                   "VALUES ('". $bookmark_id ."',  '". 
                    $db->real_escape_string($customer_id) ."', '" . 
                    $db->real_escape_string($ip_address) ."', now());";
                    
        $result = $db->query($command);
        
      }
   }
}

//function used to flag a member if their posts are deemed inappropriate
function flag_member($flagged_member, $db, $flagging_member = '') {
   $ip_address = getenv("REMOTE_ADDR");
   
   if (is_numeric($flagged_member)) {
       $command = "SELECT flagged_member FROM flagged_members WHERE flagged_member = '". $flagged_member ."' ".
                  "AND (ip_address='". $db->real_escape_string($ip_address) ."'";
      if (intval($flagging_member) > 0) {
         $command .= " OR flagging_member='". $flagging_member ."'";
      }
      $command .= ");";
      
      $result = $db->query($command);
            
      if ($result->num_rows <= 0) {
        //add new entry
        $command = "INSERT INTO flagged_members (flagged_member, flagging_member, ip_address, date_flagged) " . 
                   "VALUES ('". $flagged_member ."',  '". 
                    $db->real_escape_string($flagging_member) ."', '" . 
                    $db->real_escape_string($ip_address) ."', now());";
                    
        $result = $db->query($command);
        
      }
   }
}


//This function will take the respective bookmark_id as a parameter, and display all the comments
function fetch_comments($bookmark_id, $db) {

   $comments_array = array();

   if (is_numeric($bookmark_id)) {
      
      $command = "SELECT cb.customer_id, cb.bookmark_id, cb.title, cb.notes, UNIX_TIMESTAMP(cb.date_posted) as date_posted, bu.url " . 
                 "FROM customer_bookmarks cb, bookmark_urls bu WHERE cb.bookmark_id = bu.bookmark_id " . 
                 "AND cb.bookmark_id = '". $db->real_escape_string($bookmark_id)  ."' AND cb.date_deleted <= 0 " . 
                 "ORDER BY cb.date_posted DESC ;";
                 
      $result = $db->query($command);
          
      if ($result->num_rows > 0) {
      
        while ($this_comments_array = $result->fetch_assoc()) {
            array_push($comments_array, $this_comments_array);
        }
      }      
   }
      return $comments_array;
}

function posted_by($customer_id, $db) {

  $command = "SELECT customer_id, login FROM customer_logins WHERE customer_id = '". $db->real_escape_string($customer_id) ."';";
  $result = $db->query($command);
  
  if ($data = $result->fetch_object()) {
         $posted_by = $data->login;
  }
  return $posted_by;
}

//returns a database handle which allows ONLY SELECT on customer_bookmarks and bookmark_urls
function public_db_connect() {


    $host = "localhost";
    $socket = "/users/ejohnson4/mysql-php/data/mysql.sock";      
    $port = 0;
    $user = "tasty_public";
    $pw = "public123";
    $database = "tasty_recipes";
    
    $db = new mysqli($host,$user,$pw,$database,$port,$socket) 
       or die("Cannot connect to mySQL.");
       
    return $db;
}

//a function to check inputs against a regular expression representing the proper input format.
function valid_input($myinput, $good_input) {
   
   if (preg_match("/$good_input/", $myinput, $regs)) {
       return true;
   }
   else {
       return false;
   }
}

//Retrieve the profile for a member based on their profile ID
function fetch_profile($profileID, $db) {
    $profile_array = array();
    if (is_numeric($profileID)) {
      
      //make sure that the profile exists
      $command = "SELECT ci.email, ci.first_name, ci.last_name, ci.homepage, UNIX_TIMESTAMP(ci.date_enrolled) as date_enrolled, cl.login " . 
                 "FROM customer_info ci, customer_logins cl WHERE ci.customer_id = cl.customer_id " . 
                 "AND cl.date_deactivated <= 0 AND ci.customer_id = '". $db->real_escape_string($profileID) ."';";
      $result = $db->query($command);
           
      if ($result && $result->num_rows > 0) {
         $profile_array = $result->fetch_assoc();
         
      }
    }
    return $profile_array;
}

/*Retrieve the bookmarks posted by a member. Accept parameters for the customer's profile ID, 
database connection, the page they are viewing, and the option (which is used to count the 
total number of pages to be viewed) */
function fetch_customer_bookmarks($profileID, $db, $page, $option, $bookmark_id = '') {
   //bookmark array is a two-dimensional array
   //it hold arrays of bookmark information
   $bookmark_array = array();
   
   if (is_numeric($profileID)) {
     $command = "SELECT cb.bookmark_id, cb.title, cb.notes, UNIX_TIMESTAMP(cb.date_posted) as date_posted, bu.url " . 
                "FROM customer_bookmarks cb, bookmark_urls bu WHERE cb.bookmark_id = bu.bookmark_id " . 
                "AND cb.customer_id = '". $db->real_escape_string($profileID) ."' " . 
                "AND cb.date_deleted <= 0 ";

   if (is_numeric($bookmark_id)) {
      $command .= " AND cb.bookmark_id = '".$bookmark_id."';";
   }
   else {
   
     /*In order to get a count of all the bookmarks, check for the "all" option. If set, then get an array
     of the first 100 bookmarks*/
     if ($option == "all") {
       $command .= " ORDER BY date_posted DESC LIMIT 100;";
     }
     else {
       if (!(is_numeric($page)) || $page < 1) {
         $page = 1; 
       }
       $command .= " ORDER BY date_posted DESC LIMIT " . (($page - 1) * 5) . ",5;";
       }
   }
     
     $result = $db->query($command);
         
     if ($result->num_rows > 0){
       while ($this_bookmark_array = $result->fetch_assoc()) {
         array_push($bookmark_array, $this_bookmark_array);
       }
     }
   }
   return $bookmark_array;
}

//This function will ensure that URLs are in the proper format
function validURL($url) {
   return filter_var($url, FILTER_VALIDATE_URL);
}

//This function checks whether an active connection already exists between members
function check_network($req_customer, $follow_customer, $db) {

    $command = "SELECT connect_id FROM tasty_network " . 
               "WHERE req_customer = '". $db->real_escape_string($req_customer) ."' " . 
               "AND follow_customer = '". $db->real_escape_string($follow_customer) ."' " .
               "AND date_deactivated <= 0 ;";
                                                    
   $result = $db->query($command);
   
   if ($result->num_rows > 0){
      return true; 
   }
   else {
      return false;
   }
}

//this function checks whether an inactive connection exists in the tasty_network table
function check_deactivated($req_customer, $follow_customer, $db) {

    $command = "SELECT connect_id FROM tasty_network " . 
               "WHERE req_customer = '". $db->real_escape_string($req_customer) ."' " . 
               "AND follow_customer = '". $db->real_escape_string($follow_customer) ."';";
               "AND date_deactivated > 0 ;";
                                                           
   $result = $db->query($command);
   
   if ($result->num_rows > 0){
      return true; 
   }
   else {
      return false;
   }
}

//This function creates a new connection between two members
function follow_member($req_customer, $follow_customer, $db) {
    
    $command = "INSERT INTO tasty_network (connect_id, req_customer, follow_customer, connect_date) " . 
               "VALUES ('', '". $db->real_escape_string($req_customer) ."', " . 
               "'" . $db->real_escape_string($follow_customer) ."', " .
               "now());";
                             
   $result = $db->query($command);
   
   if ($result == true) {
      return true; 
   }
   else {
      return false;
   }
}

//this function reactivates an inactive connection between two members
function reactivate_connection($req_customer, $follow_customer, $db) {
    
    $command = "UPDATE tasty_network SET date_deactivated = 0, connect_date = now() " . 
               "WHERE req_customer = '". $db->real_escape_string($req_customer) ."' " . 
               "AND follow_customer = '". $db->real_escape_string($follow_customer) ."';"; 
               
               
   $result = $db->query($command);
   
   if ($result == true) {
      return true; 
   }
   else {
      return false;
   }
}

//This function will retrieve all the members in a user's network
function fetch_my_network($profileID, $db) {
    //network array is a two dimensional array
    //it holds arrays of each following member's information
    $network_array = array();
    
    if ($profileID) {
      $command = "SELECT ci.customer_id, ci.name, cl.login FROM customer_info ci, customer_logins cl " . 
                 "WHERE ci.customer_id = cl.customer_id AND ci.customer_id IN " .  
                 "(SELECT follow_customer FROM tasty_network WHERE req_customer ='". $db->real_escape_string($profileID)."' " . 
                 "AND date_deactivated <= 0) " .
                 "AND ci.customer_id !='". $db->real_escape_string($profileID)."' " . 
                 "GROUP BY ci.customer_id;";
                 
      $result = $db->query($command);
      
      echo "Command: " . $command;
      
      if ($result->num_rows > 0) {
                 
         while ($thismember_array = $result->fetch_assoc()) {
            array_push($network_array, $thismember_array);
         }
      }
     }
    return $network_array;
  }

//This function will retrieve all of the bookmarks in a person's network, and will sort by most recent date
function fetch_my_network_bookmarks($profileID, $db) {
   //bookmark array is a two-dimensional array
   //it hold arrays of bookmark information
   $bookmark_array = array();
   
   if (is_numeric($profileID)) {
     $command = "SELECT cb.customer_id, cb.bookmark_id, cb.title, cb.notes, UNIX_TIMESTAMP(cb.date_posted) as date_posted, bu.url " . 
                "FROM customer_bookmarks cb, bookmark_urls bu WHERE cb.bookmark_id = bu.bookmark_id " . 
                "AND cb.customer_id = '". $db->real_escape_string($profileID) ."' " . 
                "OR cb.customer_id IN (SELECT follow_customer FROM tasty_network " . 
                "WHERE req_customer = '". $db->real_escape_string($profileID) ."' AND date_deactivated <= 0) " .
                "AND cb.date_deleted <= 0 " . 
                "GROUP BY cb.bookmark_id " . 
                "ORDER BY date_posted DESC;";
                
           
     $result = $db->query($command);
         
     if ($result->num_rows > 0){
       while ($this_bookmark_array = $result->fetch_assoc()) {
         array_push($bookmark_array, $this_bookmark_array);
       }
     }
   }
   return $bookmark_array;
}

function remove_bookmark($customer_id, $db, $remove_id) {

   if (is_numeric($remove_id)) {
   
      $command = "UPDATE customer_bookmarks SET date_deleted = now() WHERE " . 
                 "bookmark_id = '". $db->real_escape_string($remove_id) ."' " . 
                 "AND customer_id = '". $db->real_escape_string($customer_id) ."';";
                 
      $result = $db->query($command);           
   }
}


?>