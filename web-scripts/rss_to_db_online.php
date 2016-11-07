<?php

/**
 * Drupal Planet Links Archive
 *
 * Script to retrieve manually the articles from Drupal Planet.
 * Based on RSSIngest (http://code.google.com/p/rssingest/) by Daniel Iversen.
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Credentials
$db_hostname = "localhost";
$db_username = "XXXX";
$db_password = "XXXX";
$db_database = "XXXX";
$private_access_key="XXXX";

// Variables for tables
$tbl_name="executions"; // Table name
$tbl_entries = "rssingest";

//Setting timezone
date_default_timezone_set('Europe/Madrid');



// Check get parameters
if(isset($_GET['feed_url'])) {
	$feed_url = $_GET['feed_url'];
}else{
	die("Need to pass the (consistent) 'feed url'");
}


if(isset($_GET['access_key'])) {
	if($_GET['access_key']==$private_access_key) {
		echo "Access key correct, proceeding...<br/><br/>";
	}else{
		die("wrong access key");
	}
}else{
	die("Need to pass the 'access_key' URL parameter");
}

try
	{
	
	$mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	
	// Check connection
	if ($mysqli->connect_errno) {
		printf("Connect failed: %s\n", $mysqli->connect_error);
		exit();
	}
	
	echo "Starting to work with feed URL '" . $feed_url . "'";
	
	libxml_use_internal_errors(true);
	$RSS_DOC = simpleXML_load_file($feed_url);
	if (!$RSS_DOC) {
		echo "Failed loading XML\n";
		foreach(libxml_get_errors() as $error) {
			echo "\t", $error->message;
		}
	}
	
	// Get title, link, managing editor, and copyright from the document
	$rss_title = $RSS_DOC->channel->title;
	$rss_link = $RSS_DOC->channel->link;
	$rss_editor = $RSS_DOC->channel->managingEditor;
	$rss_copyright = $RSS_DOC->channel->copyright;
	$rss_date = $RSS_DOC->channel->pubDate;
	
	// Loop through each item in the RSS document
	foreach($RSS_DOC->channel->item as $RSSitem)
	{
	
		$item_id 	= md5($RSSitem->title);
		$fetch_date = date("Y-m-j G:i:s"); //NOTE: we don't use a DB SQL function so its database independent
		$item_title = $RSSitem->title;
		$item_date  = date("Y-m-j G:i:s", strtotime($RSSitem->pubDate));
		$item_url	= $RSSitem->link;
	
		echo "Processing item '" , $item_id , "' on " , $fetch_date 	, "<br/>";
		echo $item_title, " - ";
		echo $item_date, "<br/>";
		echo $item_url, "<br/>";
	
		// Insert the item only if it does not exist already (based on hash key)
		$item_exists_sql = "SELECT item_id FROM " . $tbl_entries . " where item_id = '" . $item_id . "'";
		echo "Exec " . $item_exists_sql ."<br/>";
		//$item_exists = mysql_num_rows (mysql_query($item_exists_sql, $db));
		if(mysqli_num_rows($mysqli->query($item_exists_sql)) >=1)
		{
			echo "<font color=blue>Not inserting existing item..</font><br/>";
		}else{
			echo "<font color=green>Inserting new item..</font><br/>";
			$item_insert_sql = "INSERT INTO " . $tbl_entries . " (item_id, feed_url, item_title, item_date, item_url, fetch_date) VALUES ('" . $item_id . "', '" . $feed_url . "', '" . $item_title . "', '" . $item_date . "', '" . $item_url . "', '" . $fetch_date . "')";
			echo "Exec " . $item_insert_sql ."<br/>";
			$res = $mysqli->query($item_insert_sql);
			echo "Exec " . $res ."<br/>";
		}
		echo "<br/>";
	}	
	
	$mysqli->close();
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
