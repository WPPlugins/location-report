<?php
/*
Plugin Name: Location Report
Plugin URI:  http://pitufa.at/current_location_and_route_kml
Description: Provides the [locationreport ...] shortcode that lets you update your current location. It generates two kml files. The first has a placemark of your current location and the second one shows the route along all your location reports. Those kml files can be displayed by one of the many available map plugins (e.g., OpenStreetMap or Flexible Map). Note, this plugin itself does NOT provide a map. However, it provides a simple, universal (no GUI interaction necessary, so you can report your position even when posting by email), portable (no db, just kml files) and modular way of position reporting and together with a map plugin you can show your latest position and/or your travel route in a map widget or page. 
Version:     1.0.2
Author:      Christian Feldbauer
Author URI:  http://www.pitufa.at
Text Domain: locationreport
Domain Path: /languages
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

require('libkml.php');
define('LOCATIONREPORT_DIR_PATH', dirname( __FILE__ ));
define('LOCATIONREPORT_TEMPLATES_PATH', path_join(LOCATIONREPORT_DIR_PATH,'templates'));
define('LOCATIONREPORT_SYMLINKS_PATH', path_join(LOCATIONREPORT_DIR_PATH,'symlinks'));

function locationreport_load_plugin_textdomain() {
    load_plugin_textdomain( 'locationreport', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'locationreport_load_plugin_textdomain' );

// enable shortcodes in widgets (e.g. to make your own map widget using a simple text widget...) 
add_filter('widget_text','do_shortcode');

//Plugin Activation
function locationreport_activate()
{
  $upload_dir_arr = wp_upload_dir();
  $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');
  if( !is_dir($kmldir))
  {
    mkdir( $kmldir);
  }
  
  $fn_empty = path_join( LOCATIONREPORT_TEMPLATES_PATH, 'empty.kml');
  
  $fn_route = path_join( $kmldir, 'route.kml');
  $link_route = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'route.kml.txt');
  if( !file_exists($fn_route))
    copy( $fn_empty, $fn_route);
  if( !file_exists($link_route))
    symlink( $fn_route, $link_route);
  
  $fn_latest = path_join( $kmldir, 'latest.kml');
  $link_latest = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'latest.kml.txt');
  if( !file_exists($fn_latest))
    copy( $fn_empty, $fn_latest); 
  if( !file_exists($link_latest))
    symlink( $fn_latest, $link_latest);
  
  $fn_style = path_join( $kmldir, 'style.kml');
  $link_style = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'style.kml.txt');
  if( !file_exists($fn_style))
    copy( $fn_empty, $fn_style); 
  if( !file_exists($link_style))
    symlink( $fn_style, $link_style); 
}
register_activation_hook( __FILE__, 'locationreport_activate' );

// [locationreport lat=... lon=...]
add_shortcode( 'locationreport', 'locationreport_dummy' );
function locationreport_dummy(){}
// shortcode will never be executed because it will be removed from post content before it is saved in db
function locationreport_before_insert( $data , $postarr )
{ 
  if( $data['post_status'] === 'publish' || $data['post_status'] === 'private' )
  {  
    $content = $data['post_content'];
    $pattern = get_shortcode_regex( ); 
    
    preg_match_all('/'.$pattern.'/s', $content, $matches);
    
    $tag_index = array_search( 'locationreport', $matches[2]);
    if ( $tag_index !== false )
    {
      $loc_arr = shortcode_parse_atts( stripslashes($matches[3][$tag_index]));
      if( array_key_exists ( 'lat' ,  $loc_arr) && 
          array_key_exists ( 'lon' ,  $loc_arr) )
      {
         $lat_dec = locationreport_coord_to_dec_deg( $loc_arr['lat']);
         $lon_dec = locationreport_coord_to_dec_deg( $loc_arr['lon']);
         
         $locationreport_str1 = __( 'Position reported on', 'locationreport');
         $locationreport_str2 = __( 'at', 'locationreport');
         $date_fmt_str = 'Y-m-d';
         $time_fmt_str = 'H:i';
         $repl = '<p><em>' . $locationreport_str1 . ' ' . date($date_fmt_str, strtotime($data['post_date_gmt'])) . ' ' . $locationreport_str2 . ' ' .date($time_fmt_str, strtotime($data['post_date_gmt'])) . ' UTC: <br>' . locationreport_lat_dec_deg_to_min_str($lat_dec) . ', ' . locationreport_lon_dec_deg_to_min_str($lon_dec) . '</em></p>';

         // remove shortcode from content and replace with location report box
         $data['post_content'] = str_replace( $matches[0][$tag_index], $repl, $content );
       
         $upload_dir_arr = wp_upload_dir();
         $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');
         $fn_route = path_join( $kmldir, 'route.kml');
         $fn_latest = path_join( $kmldir, 'latest.kml');
         $fn_empty = path_join( $kmldir, 'style.kml'); 
       
         process_kml_files( $fn_route, $fn_latest, $fn_empty, $lat_dec, $lon_dec, apply_filters('the_content', $data['post_content']), $data['post_title']);       
       }
     }
  }  
  return $data;
}
add_filter( 'wp_insert_post_data', 'locationreport_before_insert', '91', 2 );

function process_kml_files( $fn_route, $fn_latest, $fn_empty, $lat, $lon, $content, $title)
{
  $kml_str = file_get_contents($fn_route);
  $kml = KML::createFromText($kml_str);    
  $doc = $kml->getFeature();
  $ftrs = $kml->getAllFeatures();
    
  $points = array();
  foreach( $ftrs as $ftr )
  {
    if( is_a( $ftr, 'libKML\Placemark') )
    { 
      $ftr->setStyleUrl('#locationreportRouteStyle');
      $geom = $ftr->getGeometry();
      if( is_a( $geom, 'libKML\Point') )
      {
        $points[] = $geom->getCoordinates();
      }  
      if( is_a( $geom, 'libKML\LineString') )
      {
        $line = $geom;
      }
    }      
  }
  if( !isset($line) )
  {
    //create LineString if at least 1 old point
    if( !empty( $points))
    {
      $line = new libKML\LineString();
      $line->setCoordinates( $points);
      $line->setTessellate(1);
      $placemark = new libKML\Placemark();
      $placemark->setStyleUrl('#locationreportRouteStyle');
      $placemark->setGeometry($line);
      $doc->addFeature($placemark);
    }  
  }
  //add new point
  $coordinates = new libKML\Coordinates();
  $coordinates->setLongitude( $lon );
  $coordinates->setLatitude( $lat );
  $coordinates->setAltitude( 0 );
    
  if( isset($line) )
    $line->addCoordinate( $coordinates);
    
  $point = new libKML\Point();
  $point->setCoordinates($coordinates);
  $placemark = new libKML\Placemark();
  $placemark->setName($title);
  $placemark->setDescription($content);
  $placemark->setStyleUrl('#locationreportLatestStyle');
  $placemark->setGeometry($point);
  $doc->addFeature($placemark);
  $doc->clearStyleSelectors();
  
  // kml file with single point placemark: latest.kml from style.kml template
  $kml_str = file_get_contents($fn_empty);
  $kml2 = KML::createFromText($kml_str);    
  $doc2 = $kml2->getFeature();
  $styles = $doc2->getAllStyles();
  $doc2->addFeature($placemark);
  
  foreach( $styles as $st )
    $doc->addStyleSelector($st); // copy template styles into route.kml
    
  file_put_contents( $fn_route, $kml->__toString());
  file_put_contents( $fn_latest, $kml2->__toString());
}

function locationreport_coord_to_dec_deg( $val)
{
  if( is_numeric($val) )
    $dec = $val;
  else
  {
    $n = preg_match_all('/(\d+(\.\d+)?)/', $val, $matches);
    $nums = $matches[1];
    $dec = 0;
    if( $n > 0 )
    {
      $dec += $nums[0];
      if( $n > 1 )
        $dec += $nums[1]/60;
    }
    if( strpos( strtoupper($val) , 'S') !== false )
      $dec *= -1;
    if( strpos( strtoupper($val) , 'W') !== false )
      $dec *= -1;
  }
  return $dec;
}

function locationreport_lat_dec_deg_to_min_str($coord)
{
  $isnorth = $coord>=0;
  $coord = abs($coord);
  $deg = floor($coord);
  $min = ($coord-$deg)*60;

  return sprintf("%02d°%05.2f'%s", $deg, $min, $isnorth ? 'N' : 'S');
}

function locationreport_lon_dec_deg_to_min_str($coord)
{
  $iseast = $coord>=0;
  $coord = abs($coord);
  $deg = floor($coord);
  $min = ($coord-$deg)*60;

  return sprintf("%03d°%05.2f'%s", $deg, $min, $iseast ? 'E' : 'W');
}

?>
