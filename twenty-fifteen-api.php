<?php

/*
Plugin Name: Twenty Fifteen API
Plugin URI: http://magazine.enginethemes.com
Description: API for frontend
Version: 1.0
Author: nguyenvanduocit
Author URI: http://senviet.org
License: GPL2
*/

/**
 * Register route
 */
function tfa_register_route(){
	register_rest_route( 'wp/v2', '/settings', array(
		'methods' => 'GET',
		'callback' => 'tfa_get_setting',
	) );
}
add_action( 'rest_api_init', 'tfa_register_route');

/**
 * Get site setting
 *
 * @return array
 */
function tfa_get_setting(){
	return [
		'title'=>get_bloginfo('name'),
		'tagline'=>get_bloginfo('description')
	];
}
