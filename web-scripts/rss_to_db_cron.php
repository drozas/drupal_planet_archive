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

$db_hostname = "yourdatabaseserverhere";
$db_username = "yourusernamehere";
$db_password = "yourpasswordhere";

//Setting default timezone
date_default_timezone_set('Europe/Madrid');


// Variables for counter
$tbl_name = "executions"; // Table name 

// In this version there are no params: url is hardcoded and no need for key

$feed_url = "https://www.drupal.org/planet/rss.xml";


try
{
	// Query the database
	$db = mysql_connect($db_hostname,$db_username,$db_password);
	if (!$db)
	{
		die("Could not connect: " . mysql_error());
	}
	mysql_select_db("drupalrss", $db);


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
		$fetch_date = date("Y-m-j G:i:s"); //NOTE: we don't use a DB SQL function so its database independant
		$item_title = $RSSitem->title;
		$item_date  = date("Y-m-j G:i:s", strtotime($RSSitem->pubDate));
		$item_url	= $RSSitem->link;

		// Insert the item only if it does not exist already (based on hash key)
		$item_exists_sql = "SELECT item_id FROM rssingest where item_id = '" . $item_id . "'";
		$item_exists = mysql_query($item_exists_sql, $db);
		if(mysql_num_rows($item_exists)<1)
		{
			$item_insert_sql = "INSERT INTO rssingest(item_id, feed_url, item_title, item_date, item_url, fetch_date) VALUES ('" . $item_id . "', '" . $feed_url . "', '" . $item_title . "', '" . $item_date . "', '" . $item_url . "', '" . $fetch_date . "')";
			$insert_item = mysql_query($item_insert_sql, $db);
		}
	}


	//Select counter
	$sql = "SELECT * FROM $tbl_name";
	$result = mysql_query($sql);

	$rows = mysql_fetch_array($result);
	$counter = $rows['counter'];

	// If there is not value in the counter, set it to 1
	if(empty($counter)){
		$counter = 1;
		$sql1 = "INSERT INTO $tbl_name(counter) VALUES('$counter')";
		$result1 = mysql_query($sql1);
	}

	// Increase the counter
	$addcounter = $counter+1;
	$sql2 = "UPDATE $tbl_name SET counter='$addcounter'";
	$result2 = mysql_query($sql2);

} catch (Exception $e)
{
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
