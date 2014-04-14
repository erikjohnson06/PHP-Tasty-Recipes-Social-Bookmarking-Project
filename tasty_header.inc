<?php
//find out navigation
session_start();
$navigation_flag = $_SESSION['navigation'];
$customer_id = $_SESSION['customer_id'];
$customer_login = $_SESSION['customer_login'];

/* This function will test the parameters to see which which page is currently active. 
The active page will be detected by the server variable PHP_SELF, and then set as a session
variable. The session variable will then become the navigation flag, which will be tested
against the navigation title. Because the titles have unique names, a temporary variable will
be created called action_title, which is the name of the current page (same as the navigation
flag). If the action title and navigation flag are the same, they will print out in a bolded
text style, otherwise they will be links to those pages. */
function print_navigation($nav_flag, $nav_title, $nav_url, $last = false) {

  /*Find out the active navigation title. Because popular and recent are already
  equivalent to their navigation flags, there is no need to assigned them this
  temporary variable. */
  if ($nav_title == "add to network") {
      $action_title = "add.php";
  }
  else if ($nav_title == "view network") {
      $action_title = "network.php";
  }
  else if ($nav_title == "my recipes") {
     
     //User is on their own profile page
     if (!$_GET['profileID']) {
           $action_title = "profile.php";
     }
     //User is on another member's profile page
     else if ($_GET['profileID'] && $_GET['profileID']!= $_SESSION['customer_id']){
          $action_title = "";
     }
     else {
          $action_title = "profile.php";
     }
  } 
  else if ($nav_title == "post recipe bookmark") {
      $action_title = "bookmark.php";
  } 
  
  else if ($nav_title == "edit profile") {
      $action_title = "edit.php";
  } 
  else if ($nav_title == "show as cloud") {
      $action_title = "index.php";
  } 
  else if ($nav_title == "show as list") {
      $action_title = "index.php";
  } 
  


  if (filter_var("http://" . $nav_url, FILTER_VALIDATE_URL)) {
    $class_name = "nav";
    if ($last) {
       $class_name = "last";
    }
    
    /*Test whether the navigation flag is equivalent to the navigation
    title (or the temporary action title). If so, print bolded text. 
    Otherwise, print a link to those pages.*/
    if ($nav_flag == $nav_title || $nav_flag == $action_title) {
       echo "<b class='" . $class_name . "'>"; 
       echo htmlentities($nav_title); 
       echo "</b>";
    }
    else {
       echo "<a class='" . $class_name. "' href='" . $nav_url . "'>";
       echo htmlentities($nav_title);
       echo "</a>";
    }
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>t.as.ty recipes: social cook-bookmarking</title>

<link type="text/css" rel="stylesheet" rev="stylesheet" href="css/styles.css" />

</head>

<body>

<div id="page">
    
	<div id="tasty_header">
		<div class="top">
			<div class="inside">
				<table style="width:100%;"><tr>
					<td rowspan=2 style="padding-right:10px;width:60px;">
					<img src="images/pie.jpg" height="60" alt="t.as.ty recipes" />
				</td>
				<td style="align:left;">
					<a href="index.php">t.as.ty recipes</a>
				</td>

				<td align="right" valign="top" style="padding-right:8px;">
				   
                                   <?
                                   if ($customer_id) {
				     ?>
				     <span class="nav">logged in as <b>
				     <? echo htmlentities($customer_login); ?>
				     </b></span>
				     <a class="last" href="logout.php">logout</a>	
				     <?
				   }
				   else {
				      ?>
				      <a class="nav" href="login.php">login</a>
				      <a class="last" href="join.php">join</a>	
				      <?
				   }
				  ?>
				</td>
				</tr>
				<tr><td style="align:left;font-size:20px;">
					social cook-bookmarking
				</td>
				<td style="align:right;">
			        <span style="float:right;margin-right:1px;"><a class="nav" href="tasty_recipes_rss.php">  subscribe
				<img style="vertical-align:text-bottom;" src="images/rss.jpg" height="15" width="15" alt="t.as.ty recipes" />
				  </a>  </span> 
				</td>
				
				</tr></table>
				
				
			</div>
		</div>
		<div class="bot">
			<div class="inside">
				<table style="width:100%;"><tr>
				<td style="font-weight:bold;align:left;">
				
				    <?
				    /*If the user is signed in, display the navigation bar. Otherwise, 
				    give them links to join */
                                   if ($customer_id) {
                                     print_navigation($navigation_flag, "my recipes", "profile.php");
                                     print_navigation($navigation_flag, "post recipe bookmark", "bookmark.php"); 
                                     print_navigation($navigation_flag, "view network", "network.php"); 
                                     print_navigation($navigation_flag, "add to network", "add.php"); 
                                     print_navigation($navigation_flag, "edit profile", "edit.php", true); 
                                    
				   }
				   else {
				      ?>  
                                        <a href="join.php">store</a> your recipe bookmarks...
                                        <a href="join.php">browse</a> other recipes...
                                        <a href="join.php">join</a> today, its easy!
				      <?
				   }
				  ?>
                                </td>
                                <td align=right valign=top>
                                   <? print_navigation($navigation_flag, "popular", "index.php?mode=popular"); ?>
                                   <? print_navigation($navigation_flag, "recent", "index.php?mode=recent", true); ?>
				</td>
				</tr></table>
			</div>
		</div>
	</div><!-- /header -->
        
    
	<div id="tasty_content">
	
