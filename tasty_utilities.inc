<?
//tasty_utilities.inc -- a private library for tasty recipes

//returns a database handle which allows updates to everything except customer_logins
function member_db_connect() {
   
    $host = "localhost";
    $socket = "/users/ejohnson4/mysql-php/data/mysql.sock";      
    $port = 0;
    $user = "tasty_member";
    $pw = "trish123";
    $database = "tasty_recipes";
    
    $db = new mysqli($host,$user,$pw,$database,$port,$socket) 
       or die("Cannot connect to mySQL.");
       
    return $db;
}

/*returns a database handle which allows full updates to customer_logins
and limited updates to customer_info*/
function login_db_connect() {

    $host = "localhost";
    $socket = "/users/ejohnson4/mysql-php/data/mysql.sock";      
    $port = 0;
    $user = "tasty_login";
    $pw = "scott123";
    $database = "tasty_recipes";
    
    $db = new mysqli($host,$user,$pw,$database,$port,$socket) 
       or die("Cannot connect to mySQL.");
       
    return $db;
}

/*Ensure that each email in the database is unique. If not, 
do not allow the user to update his/her email address to that email address */
function check_email($customer_id, $email, $db) {
     
   $command = "SELECT * from customer_info where email = '" . $db->real_escape_string($email) . 
                   "' and customer_id != '" . $db->real_escape_string($customer_id) . "';"; 

   $result = $db->query($command ); 
                        
   if ($result->num_rows > 0) {
       return false;
   }
   else {
       return true;
   }
}

/*This function can be called upon to verify that the password being entered matches 
the user's current password*/
function verify_password($customer_id, $password, $db) {
      
   $command = "SELECT * FROM customer_logins WHERE customer_id = '".$db->real_escape_string($customer_id)."' ". 
              "AND password = password('".$db->real_escape_string($password)."');";
   $result = $db->query($command);
   
   if ($data = $result->fetch_object()) {
      return true;
   }
   else {
      return false;
   }
}

//Verify the username is not already taken when updating login information
function check_username($customer_id, $username, $db) {
     
   $command = "SELECT * from customer_logins where login = '" . $db->real_escape_string($username) . 
                   "' and customer_id != '" . $db->real_escape_string($customer_id) . "';"; 

   $result = $db->query($command); 
                                               
   if ($result->num_rows > 0) {
       return false;
   }
   else {
       return true;
   }
}

function update_profile($first_name, $last_name, $email, $homepage, $member, $db) {
 
       //Update the customer_info table with the data entered in the form
       $command = "UPDATE customer_info SET first_name = '" . $db->real_escape_string($first_name) . 
                   "', last_name = '" . $db->real_escape_string($last_name) . 
                   "', email = '" . $db->real_escape_string($email) . 
                   "', homepage = '" . $db->real_escape_string($homepage) . 
                   "'  where customer_id = '" . $db->real_escape_string($member) . "';"; 
       
       $result = $db->query($command);
       
       if ($result == true) {
         return true;
       }
       else {
         return false;
       }
}

function update_login_info($customer_id, $username, $new_password, $db) {

       //Update password in database, display a success message, and close the connection.
       $command = "UPDATE customer_logins SET password = password('". $db->real_escape_string($new_password) ."'), " .
                  "login = '" . $db->real_escape_string($username) ."' " . 
                  "WHERE customer_id = '" . $db->real_escape_string($customer_id) ."';";
       $result = $db->query($command);
              
       if ($result == true) {
         return true;
       }
       else {
         return false;
       }
}

function fetch_bookmark_tags($profileID, $db, $bookmark_id='') {
  //tag array is a one-dimensional array holding tags
  $tag_array = array();
  
  if($profileID) {
    $command = "SELECT bt.tag_id, tt.tag FROM bookmark_tags bt, tasty_tags tt " . 
               "WHERE bt.tag_id = tt.tag_id " . 
               "AND bt.customer_id = '" . $db->real_escape_string($profileID) . "' " . 
               "AND bt.date_deleted <= 0 ";
               
    if (is_numeric($bookmark_id)) {
       $command .= "AND bt.bookmark_id='" . $db->real_escape_string($bookmark_id) ."'; ";
    }
       
    $result = $db->query($command);
    
    while ($this_tag_array = $result->fetch_assoc()) {
       array_push($tag_array, $this_tag_array['tag']);
    }
  }
  
  return $tag_array;
}

function fetch_all_bookmark_tags($db) {
  //tag array is a one-dimensional array holding tags
  $tag_array = array();

    $command = "SELECT bt.tag_id, tt.tag FROM bookmark_tags bt INNER JOIN tasty_tags tt " . 
               "ON bt.tag_id = tt.tag_id " . 
               "WHERE bt.date_deleted <= 0 ORDER BY tt.tag_id DESC LIMIT 100;";
               
       
    $result = $db->query($command);
    
    while ($this_tag_array = $result->fetch_assoc()) {
       array_push($tag_array, $this_tag_array['tag']);
    }
 
  
  return $tag_array;
}

function fetch_this_tag($tag, $db, $page, $option='') {
  //tag array is a one-dimensional array holding tags
  $tag_array = array();
  if($tag) {
    $command = "SELECT cb.title, bu.url, bt.bookmark_id, bt.tag_id, tt.tag FROM bookmark_tags bt " . 
               "INNER JOIN tasty_tags tt ON bt.tag_id = tt.tag_id " . 
               "LEFT OUTER JOIN customer_bookmarks cb ON cb.bookmark_id = bt.bookmark_id " . 
               "LEFT OUTER JOIN bookmark_urls bu ON bu.bookmark_id = cb.bookmark_id " . 
               "WHERE tt.tag = '" . $db->real_escape_string($tag) . "' " . 
               "AND bt.date_deleted <= 0 GROUP BY bt.bookmark_id ORDER BY cb.title ";
                     
   /*In order to get a count of all the bookmarks, check for the "all" option. If set, then get an array
   of the first 100 bookmarks*/
   if ($option == "all") {
    $command .= " LIMIT 100;";
   }
   else {
     if (!(is_numeric($page)) || $page < 1) {
        $page = 1; 
     }
     $command .= " LIMIT " . (($page - 1) * 5) . ",10;";
   }
                     
                     
    $result = $db->query($command);
       
    while ($this_tag_array = $result->fetch_assoc()) {
       array_push($tag_array, $this_tag_array);
    }
  }
  
  return $tag_array;
}

//This function can be used to retrieve all the tags for one particular bookmark
function fetch_relevant_tags($db, $bookmark_id) {
  //tag array is a one-dimensional array holding tags
  $tag_array = array();
  
  if(is_numeric($bookmark_id)) {
    $command = "SELECT DISTINCT bt.tag_id, tt.tag FROM bookmark_tags bt, tasty_tags tt " . 
               "WHERE bt.tag_id = tt.tag_id " . 
               "AND bt.date_deleted <= 0 " .
               "AND bt.bookmark_id='" . $db->real_escape_string($bookmark_id) ."'; ";
                     
    $result = $db->query($command);
    
    while ($this_tag_array = $result->fetch_assoc()) {
       array_push($tag_array, $this_tag_array['tag']);
    }
  }
  
  return $tag_array;
}


?>