<?php
// Copyright 2011, Clifton H. Griffin II
// This script is DESTRUCTIVE.  It will delete all images in your database! 
// Run at your own risk. It is provided with NO WARRANTY WHATSOEVER. 
// Please read carefully and do a full backup before using!
// Test with Shopp 1.0.14

//EDIT THIS LINE (this directory must be writeable)
$path = "/var/www/vhosts/domain.com/httpdocs/wp-content/uploads/shopp";

//Load WP data objects
require 'wp-load.php';

//Get list of rows from shopp
$sql    = "SELECT id FROM wp_shopp_asset";
$results = $wpdb->get_results($sql, "ARRAY_A");

//Loop through them, grabbing images
foreach ($results as $row)
{
	$id = $row['id'];
	$therow = $wpdb->get_row("SELECT data FROM wp_shopp_asset WHERE id = $id", "ARRAY_A");
	$image = $therow["data"];
	$name = "image" . "_" . $id .".jpg";
	
	echo "File name: ".$path."$name <br />";
	$file = fopen($path."$name","w");
	fwrite($file, $image);
	fclose($file);
	
	//Clear image from DB
	$wpdb->query("UPDATE wp_shopp_asset SET data = null WHERE id = $id");
}
//Optimize the table to clear unused space
$wpdb->query("OPTIMIZE TABLE wp_shopp_asset");
?>