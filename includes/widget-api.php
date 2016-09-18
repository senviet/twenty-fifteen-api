<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register route for menus
 * @author Tat Thien
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
	unset($sidebarSettings['wp_inactive_widgets']);
	unset($sidebarSettings['array_version']);
	$sidebarOptions = [];
	foreach ($sidebarSettings as $sidebarId => $sidebar){
		$sidebarOptions[$sidebarId] = [];
		foreach ($sidebar as $widgetId){
			$lastSplitCharPost = strrpos($widgetId, '-');
			$widgetIndex = substr($widgetId, $lastSplitCharPost+1, strlen($widgetId)  - $lastSplitCharPost);
			$widgetKey = substr($widgetId, 0, $lastSplitCharPost);
			$widgetOption =  get_option('widget_'.$widgetKey);
			$widgetOption[$widgetIndex]['type'] = $widgetKey;
			$sidebarOptions[$sidebarId][$widgetId] = $widgetOption[$widgetIndex];
		}
	}
	return $sidebarOptions;
}
