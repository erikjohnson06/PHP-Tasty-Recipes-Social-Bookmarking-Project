<?php

require_once "XML/RSS.php";

//$rss_feed = "http://ejohnson4.userworld.com/tasty/tasty_recipes.rss";
$rss_feed = "http://rss.allrecipes.com/daily.aspx?hubID=80";
$rss = new XML_RSS($rss_feed);
$rss->parse();

$channel = $rss->getChannelInfo();

?>

<html><head><title><? echo $channel['title']; ?></title></head>
<body>
<h1><a href="<? echo $channel['link']; ?>"><?
echo $channel['title']; ?></a></h1>
<h2><? echo $channel['description']; ?></h2>
<ul>

<?


foreach ($rss->getItems() as $item) {

   echo "<li><a href='". $item['link']. "'>" . $item['title']. "</a><br />" . $item['description'] . "</li>\n";
  
}
?>
</ul>
</body>
</html>