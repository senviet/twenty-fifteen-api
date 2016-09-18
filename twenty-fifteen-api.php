<?php

/*
Plugin Name: Twenty Fifteen API
Plugin URI: http://magazine.enginethemes.com
Description: API for frontend
Version: 1.0
Author: nguyentatthien, nguyenvanduocit
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('TFA_DIR_PATH', plugin_dir_path(__FILE__));

require_once TFA_DIR_PATH.'/includes/tfa-api-menus.php';

/**
 * Register route.
 */
function tfa_register_route()
{
    register_rest_route('wp/v2', '/settings', array(
        'methods' => 'GET',
        'callback' => 'tfa_get_setting',
    ));
}
add_action('rest_api_init', 'tfa_register_route');

/**
 * Get site setting.
 *
 * @return array
 */
function tfa_get_setting()
{
    return [
        'title' => get_bloginfo('name'),
        'tagline' => get_bloginfo('description'),
        'date_format' => get_option('date_format'),
    ];
}
