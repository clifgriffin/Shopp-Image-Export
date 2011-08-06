<?php
// Copyright 2011, Clifton H. Griffin II
// This script is DESTRUCTIVE.  It will delete all images in your database! 
// Run at your own risk. It is provided with NO WARRANTY WHATSOEVER. 
// Please read carefully and do a full backup before using!
// Tested with Shopp 1.1.8

//EDIT THIS LINE (this directory must be writeable)
$path = "/var/www/vhosts/domain.com/httpdocs/wp-content/uploads/shopp/";

//Load WP data objects
require 'wp-load.php';

$image_metas = get_image_metas();

echo "<html><head></head><body><pre>";

foreach ($image_metas as $image_meta) {
  $meta_value = unserialize($image_meta['value']);

  if(is_object($meta_value)) {
    if($meta_value->storage == 'DBStorage') {
      output('Processing file: ', $meta_value->filename);

      $asset_id = $meta_value->uri;
      $asset = get_asset($asset_id);

      $output_file = $path . $meta_value->filename;

      if(!file_exists($output_file)) {
        output('Saving file: ', $output_file);
        $file = fopen($output_file, 'w');
        fwrite($file, $asset['data']);
        fclose($file);
      } else {
        output('File exists, not overvriting: ', $output_file);
      }

      $meta_value->storage = 'FSStorage';
      $meta_value->uri     = $meta_value->filename;

      update_meta($image_meta['id'], $meta_value);
      delete_asset($asset_id);

      output();
    }
  }
}

echo "</pre></body></html>";

//Optimize the tables to clear unused space
$wpdb->query("OPTIMIZE TABLE wp_shopp_meta");
$wpdb->query("OPTIMIZE TABLE wp_shopp_asset");

function get_image_metas() {
  global $wpdb;
  return $wpdb->get_results(
    "SELECT * FROM wp_shopp_meta WHERE type = 'image'",
    "ARRAY_A"
  );
}

function get_asset($asset_id) {
  global $wpdb;
  return $wpdb->get_row(
    'SELECT data FROM wp_shopp_asset WHERE id = ' . $asset_id,
    "ARRAY_A"
  );

}

function update_meta($meta_id, $meta_value) {
  global $wpdb;
  $wpdb->query("UPDATE wp_shopp_meta SET value = '" . mysql_real_escape_string(serialize($meta_value)) . "' WHERE id = " . $meta_id);
  output('Updated meta value for file: ', $meta_value->filename);
}


function delete_asset($asset_id) {
  global $wpdb;
  $wpdb->query('DELETE from wp_shopp_asset WHERE id = ' . $asset_id);
  output('Deleted DB asset data with id: ', $asset_id);
}

function output($message = '', $data = '') {
  echo(str_pad($message, 32, ' ') . $data . "\n"); flush();
}