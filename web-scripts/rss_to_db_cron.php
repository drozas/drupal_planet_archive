<?php

/**
 * Drupal Planet Links Archive
 *
 * Script to retrieve periodically the articles from Drupal Planet.
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
$db_username = "xxxxx";
$db_password = "xxxxx";
$db_database = "xxxxx";


// Variables for tables
$tbl_entries = "rssingest";

// Counter
$new_entries = 0;

//Setting timezone
date_default_timezone_set('Europe/Madrid');

// In this version there are no params: url is hardcoded and no need for key
$feed_url = "https://www.drupal.org/planet/rss.xml";


try
	{
	
	$mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	
	// Check connection
	if ($mysqli->connect_errno) {
		printf("Connect failed: %s\n", $mysqli->connect_error);
		exit();
	}
	
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
		$item_title = str_replace("'", "++", $RSSitem->title);
		$item_id 	= md5($item_title);
		$fetch_date = date("Y-m-j G:i:s"); //NOTE: we don't use a DB SQL function so its database independent
		$item_date  = date("Y-m-j G:i:s", strtotime($RSSitem->pubDate));
		$item_url	= $RSSitem->link;

		// Insert the item only if it does not exist already (based on hash key)
		$item_exists_sql = "SELECT item_id FROM " . $tbl_entries . " where item_id = '" . $item_id . "'";
		//echo "DEB: running " . $item_exists_sql. "\n";
		//$item_exists = mysql_num_rows (mysql_query($item_exists_sql, $db));
		//$exist = mysqli_num_rows($mysqli->query($item_exists_sql));
		//echo "DEB: exist " . $exist. "\n";
		if(mysqli_num_rows($mysqli->query($item_exists_sql)) <1)
		{
			echo "# New entry will be added: ". $item_title . "(" . $item_url .") \n.";
			$item_insert_sql = "INSERT INTO " . $tbl_entries . "(item_id, feed_url, item_title, item_date, item_url, fetch_date) VALUES ('" . $item_id . "', '" . $feed_url . "', '" . $item_title . "', '" . $item_date . "', '" . $item_url . "', '" . $fetch_date . "')";
			//echo "DEB: running " . $item_insert_sql . "\n";
			$res = $mysqli->query($item_insert_sql);
			//echo "DEB: res " . $res . "\n";
			$new_entries++;
		}
	}	
	
	echo "---> Drupal Archive cron successfully executed. ". $new_entries . " new entries added in this run \n.";
	$mysqli->close();
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
