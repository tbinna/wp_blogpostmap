<?php
/**
 * Plugin Name: Adventure Tracks Blog Post Map
 * Plugin URI: http://www.adventure-tracks.com
 * Description: Visualze georeferenced posts on a map.
 * Version: 1.0
 * Author: Tobi Binna
 * Author URI: http://www.adventure-tracks.com
 * License: GPL2
 */

$mygpGeotagsGeoMetatags_key = "mygpGeotagsGeoMetatags";


/** Define script location */
if ( !defined('WP_CONTENT_URL') ) {
	define('AT_BLOGPOSTMAP_PLUGINPATH',get_option('siteurl').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('AT_BLOGPOSTMAP_PLUGINDIR', ABSPATH.'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
} else {
	define('AT_BLOGPOSTMAP_PLUGINPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('AT_BLOGPOSTMAP_PLUGINDIR', WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
}

/** Add CSS to html head */
function addCustomHeaderTags(){	
  echo '<link rel="stylesheet" type="text/css" href="' . AT_BLOGPOSTMAP_PLUGINPATH . 'blog_post_map.css" />';
  echo '<link rel="stylesheet" type="text/css" href="https://api.tiles.mapbox.com/mapbox.js/v1.6.4/mapbox.css" />';
  echo '<script type="text/javascript" src="https://api.tiles.mapbox.com/mapbox.js/v1.6.4/mapbox.js"></script>';

  // add the fullscreen plugin
  echo '<script src="https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/Leaflet.fullscreen.min.js"></script>';
  echo '<link rel="stylesheet" href="https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/leaflet.fullscreen.css" />';

  // add the locate plugin
  echo '<script src="https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.js"></script>';
  echo '<link href=https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.css rel="stylesheet" />';
  echo '<!--[if lt IE 9]>
  		<link href="https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.21.0/L.Control.Locate.ie.css" rel="stylesheet" />
		<![endif]-->';
} 
add_action('admin_head', 'addCustomHeaderTags');
add_action('wp_head', 'addCustomHeaderTags');



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
	$posts = get_posts();

	foreach($posts as $post) {
		$positionData = get_post_meta($post->ID, $mygpGeotagsGeoMetatags_key, true);

		$dataSplitted = "";
		if($positionData['position'] != "") {
			$dataSplitted = array_map('doubleval', explode(";", $positionData[ 'position' ]));
		} else {
			continue; // post seems to have no position set
		}

		array_push($markers, new Marker(get_the_title($post->ID), get_permalink($post->ID), $dataSplitted));
	}

	$postId = $wp_query->post->ID;
	$mapId = 'map' . $postId;

	$html = '<div id="' . $mapId . '" style="width:100%; height:600px;"></div>';

	$html .= '
	<script>
	var map = L.mapbox.map("' . $mapId . '", "tbinna.i80746eh")
	    .setView([47.529, 8.54], 9);

	// center map to feature on click
	map.featureLayer.on("click", function(e) {
        map.panTo(e.layer.getLatLng());
    });

	// hide the feature layer on load
	map.featureLayer.setFilter(function() { return false; });

	L.control.fullscreen().addTo(map);
	L.control.locate().addTo(map);

	map.on("zoomend", function() {
	    if (map.getZoom() >=13) {
	        map.featureLayer.setFilter(function() { return true; });
	    } else {
	        map.featureLayer.setFilter(function() { return false; });
	    }
	});';

	// add blog post markers
	foreach ($markers as $marker) {
		$html .= 'L.marker(' . json_encode($marker->latLon) . ', {
			icon: L.mapbox.marker.icon({
				"marker-symbol": "star",
				"marker-size": "large"
			})
		})
		.bindPopup("<b>' . $marker->title . '</b><br><a href=\"'. $marker->permalink .'\">' . $marker->permalink . '</a>")
		.addTo(map);';
	}

	$html .= '</script>';

	return $html;
	
}

/** Add map to post */
function addMap($content) {

	global $wp_query;
	
	$postId = $wp_query->post->ID;
	$shortcode = '[at_blog_post_map]';

	$html = getMap();
	if ($html == '') {
		return str_replace($shortcode, "", $content);
	}

	$content = str_replace($shortcode, $html, $content);

    return $content;
}
add_filter('the_content', 'addMap');

?>