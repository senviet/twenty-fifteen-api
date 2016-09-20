<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register route for widgets
 **/
function tfa_register_widget_route() {
	register_rest_route( 'wp/v2', '/widgets', array(
		'methods' => 'GET',
		'callback' => 'tfa_get_widgets'
	) );
}

add_action( 'rest_api_init', 'tfa_register_widget_route' );

function tfa_get_widgets(){
	$sidebarSettings = get_option('sidebars_widgets');
	unset($sidebarSettings['wp_inactive_widgets'], $sidebarSettings['array_version']);
	$sidebarOptions = [];
	foreach ($sidebarSettings as $sidebarId => $sidebar){
		$sidebarOptions[$sidebarId] = [];
		foreach ($sidebar as $widgetId){
			$lastSplitCharPost = strrpos($widgetId, '-');
			$widgetIndex = substr($widgetId, $lastSplitCharPost+1, strlen($widgetId)  - $lastSplitCharPost);
			$widgetKey = substr($widgetId, 0, $lastSplitCharPost);
			$widgetOption =  get_option('widget_'.$widgetKey);
			$widgetType = str_replace('_', '-', $widgetKey).'-widget';
			$widgetOption[$widgetIndex]['type'] = $widgetType;

			switch ($widgetType){
				case "recent-posts-widget":
					$widgetOption[$widgetIndex]['data'] = tfa_get_recent_posts_widget_data($widgetOption[$widgetIndex]);
					break;
				case "nav-menu-widget":
					$widgetOption[$widgetIndex]['data'] = tfa_get_nav_widget_data($widgetOption[$widgetIndex]);
					break;
			}
			$sidebarOptions[$sidebarId][$widgetId] = $widgetOption[$widgetIndex];
		}
	}
	return $sidebarOptions;
}

function tfa_get_nav_widget_data($args){
	return tfa_get_menu_items($args['nav_menu']);
}

/**
 * @param $options
 *
 * @return array
 */
function tfa_get_recent_posts_widget_data($options){
	$posts = get_posts( apply_filters( 'widget_posts_args', array(
		'posts_per_page'      => $options['number'],
		'no_found_rows'       => true,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true
	)));
	$formatedPosts = [];
	foreach ($posts as $index => $post){
		$formatedPosts[] = fta_prepare_post_for_response($post);
	}
	return $formatedPosts;
}

/**
 *
 * @param $post
 *
 * @return array
 */
function fta_prepare_post_for_response($post){
	$preparedPost = [
		'id'           => $post->ID,
		'guid'         => array(
			/** This filter is documented in wp-includes/post-template.php */
			'rendered' => apply_filters( 'get_the_guid', $post->guid ),
			'raw'      => $post->guid,
		),
		'date'         => fta_prepare_date_response( $post->post_date_gmt, $post->post_date ),
		'date_gmt'     => fta_prepare_date_response( $post->post_date_gmt ),
		'slug'         => $post->post_name,
		'status'       => $post->post_status,
		'type'         => $post->post_type,
		'link'         => get_permalink( $post->ID ),
		'title' =>  [
			'raw'      => $post->post_title,
			'rendered' => get_the_title( $post->ID ),
		]
	];
	return $preparedPost;
}

function fta_prepare_date_response( $date_gmt, $date = null ) {
	// Use the date if passed.
	if ( isset( $date ) ) {
		return mysql_to_rfc3339( $date );
	}

	// Return null if $date_gmt is empty/zeros.
	if ( '0000-00-00 00:00:00' === $date_gmt ) {
		return null;
	}

	// Return the formatted datetime.
	return mysql_to_rfc3339( $date_gmt );
}
