<?php
/**
 * Plugin Name: Adventure Tracks Blog Post Map
 * Plugin URI: http://www.adventure-tracks.com
 * Description: Visualze georeferenced posts on a map.
 * Version: 1.1
 * Author: Tobi Binna
 * Author URI: http://www.adventure-tracks.com
 * License: GPL2
 */

$mygpGeotagsGeoMetatags_key = "mygpGeotagsGeoMetatags";

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}	

/** Define script location */
define( 'AT_BLOGPOSTMAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AT_BLOGPOSTMAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue scripts and styles
 */
function blog_post_map_scripts() {

	global $wp_styles;

	wp_enqueue_style('blog_post_map', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.css');
	wp_enqueue_script('blog_post_map', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.js', array(), false, false );

	// Mapbox style and script
	wp_enqueue_style('mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v1.6.4/mapbox.css', array(), '1.6.4');
	wp_enqueue_script('mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v1.6.4/mapbox.js', array(), '1.6.4', false );

	// Mapbox fullscreen plugin style and script
	wp_enqueue_style('mapbox fullscreen', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/leaflet.fullscreen.css', array(), '0.0.3');
	wp_enqueue_script('mapbox fullscreen', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/Leaflet.fullscreen.min.js', array('mapbox'), '0.0.3', false );

	// Mapbox locate plugin style and script
	wp_enqueue_style('mapbox locate', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.css', array(), '0.24.0');
	wp_enqueue_style('mapbox locate ie', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.21.0/L.Control.Locate.ie.css', array('mapbox locate'), '0.21.0');
	$wp_styles->add_data( 'mapbox locate ie', 'conditional', 'IE 9' );
	wp_enqueue_script('mapbox locate', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.js', array('mapbox'), '0.24.0', false );
}

add_action('wp_enqueue_scripts', 'blog_post_map_scripts');


class Marker {

	var $title;
	var $permalink;
	var $latLon;

	function Marker($title, $permalink, $latLon) {
       $this->title = $title;
       $this->permalink = $permalink;
       $this->latLon = $latLon;
   }
}


/** Create and return map */
function getMap() {

	global $wp_query, $mygpGeotagsGeoMetatags_key;

	$markers = array();
	$posts = get_posts(array('numberposts' => 1000, 'meta_key' => $mygpGeotagsGeoMetatags_key, 'post_status' => 'publish'));

	foreach($posts as $post) {
		$positionData = get_post_meta($post->ID, $mygpGeotagsGeoMetatags_key, true);

		$dataSplitted = "";
		if($positionData['position'] != "") {
			$dataSplitted = array_map('doubleval', explode(";", $positionData[ 'position' ]));
		} else {
			continue; // post seems not to be georeferenced
		}

		array_push($markers, new Marker(get_the_title($post->ID), get_permalink($post->ID), $dataSplitted));
	}

	$mapId = 'map' . $wp_query->post->ID;

	$html = '<div id="' . $mapId . '" style="width:100%; height:600px;"></div>';
	$html .= '<script type="text/javascript">initMap(' . $mapId . ', ' . json_encode($markers) .');</script>';

	return $html;
	
}

/** Add map to post */
function addMap($content) {

	global $wp_query;
	
	$postId = $wp_query->post->ID;
	$shortcode = '[at_blog_post_map]';

	$html = getMap();
	$content = str_replace($shortcode, $html, $content);

    return $content;
}
add_filter('the_content', 'addMap');

?>