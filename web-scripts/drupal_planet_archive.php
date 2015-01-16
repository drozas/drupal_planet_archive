<html>
 <head>
  <title>Drupal Planet: links archive (29/10/2013 - ...)</title>
 </head>
 <body>

<h1>Drupal Planet: links archive (29/10/2013 - ...)</h1>

Notes: 
 <ul>
  <li>This archive has been designed for researching purposes for the <a href="http://www.surrey.ac.uk/sociology/people/phd/david_rozas/index.htm">PhD thesis: "Drupal as a Commons-Based Peer Production community: an ethnographic perspective"</a>. SOME OF THE POSTS WHICH MIGHT HAVE BEEN PUBLISHED IN DRUPAL PLANET DURING THIS PERIOD MIGHT NOT APPEAR IN THIS ARCHIVE.</li>
  <li>Two different strategies are employed:</li>
 <ul>
  <li>A PHP script which periodically includes the new posts from Drupal Planet. This script was first run on 30/12/2014. From that date (including the previous 30 posts), the list should be exhaustive as far as no errors might have provoked the server to go down.</li>
  <li>A Python script to recover the blog posts fetched via the RSS reader of Thunderbird. The source were a set of .eml files parsed and included into the database. These came from several machines and were merged. However, some of the blog posts might not have been gathered (e.g.: if the e-mail client was not run for a while). Therefore, the list regarding the previous period might not be so exhaustive and some of the posts might have gotten lost. More links (up to March 2013) will be added soon.</li>
</ul> 
  <li>The source code <a href="https://github.com/drozas/drupal_planet_archive">can be found in GitHub</a> under a GPLv3 license.</li>

  <li>If you have any comments/suggestions/feedback, please do not hesitate to contact me at: drozas (at) surrey (dot) ac (dot) uk</li>
</ul> 


<?php

/**
 * Drupal Planet Links Archive
 *
 * Script to print out the contents of the archive of Drupal Planet Links.
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
$db_name = "yourdatabasenamehere";


try
{
// Create connection
$conn = new mysqli($db_hostname, $db_username, $db_password, $db_name);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT item_id, item_title, item_url, item_date, fetch_date FROM rssingest ORDER BY item_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	echo '<h2>Total number of posts: ' . $result->num_rows .'</h2>';
    echo "<table border=1><tr><th>ID</th><th>Post title</th><th>URL</th><th>Post date</th><th>Fetched on</th></tr>";
    // output data of each row
    while($row = $result->fetch_assoc()) {
		$link = '<a href="' . $row["item_url"] . '" target="_blank">' . $row["item_url"] . '</a>';
        echo "<tr><td>".$row["item_id"]."</td><td>".utf8_decode($row["item_title"])."</td><td>".$link."</td><td>".$row["item_date"]."</td><td>".$row["fetch_date"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "There are not any articles.";
}
$conn->close();


} catch (Exception $e)
{
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>

        <!-- Piwik -->
        <script type="text/javascript"> 
          var _paq = _paq || [];
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          (function() {
            var u=(("https:" == document.location.protocol) ? "https" : "http") + "://davidrozas.com/piwik//";
            _paq.push(['setTrackerUrl', u+'piwik.php']);
            _paq.push(['setSiteId', 1]);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
            g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
          })();
        
        </script>
        <noscript><p><img src="http://davidrozas.com/piwik/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
        <!-- End Piwik Code -->	

 </body>
</html>
