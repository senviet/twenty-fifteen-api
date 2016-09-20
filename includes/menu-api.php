<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register route for menus
 * @author Tat Thien
 **/
function tfa_register_menus_route() {
  register_rest_route( 'wp/v2', '/menus', array(
    'methods' => 'GET',
    'callback' => 'tfa_get_menus'
  ) );

  register_rest_route( 'wp/v2', '/menus/(?P<location>\w+)', array(
    'methods' => 'GET',
    'callback' => 'tfa_get_menu'
  ) );
}

add_action( 'rest_api_init', 'tfa_register_menus_route' );

/**
 * Get site menus
 *
 * @return array
 * @author Tat Thien
 **/
function tfa_get_menus() {
  $menus = get_registered_nav_menus();

  $rest_menus = array();
  foreach ( $menus as $location => $description ) {
    $rest_menus[$location] = tfa_get_menu( array( 'location' => $location ) );
  }

  return $rest_menus;
}

/**
 * Get a single menu by id
 *
 * @return array
 * @author Tat Thien
 **/
function tfa_get_menu( $request ) {
  $location = $request['location'];
  $locations = get_nav_menu_locations();
  $id = $locations[$location];
	return tfa_get_menu_items($id);
}

function tfa_get_menu_items($id){
	$wp_menu_object = $id ? wp_get_nav_menu_object( $id ) : array();
	$wp_menu_items = $id ? wp_get_nav_menu_items( $id ) : array();

	$rest_menu = array();

	if( $wp_menu_object ) {
		$menu = ( array ) $wp_menu_object;
		$rest_menu['id'] = $menu['term_id'];
		$rest_menu['name'] = $menu['name'];
		$rest_menu['description'] = $menu['description'];
		$rest_menu['count'] = $menu['count'];

		$rest_menu_items = array();
		foreach ( $wp_menu_items as $item ) {
			$rest_menu_items[] = tfa_format_menu_item( $item );
		}

		$rest_menu_items = tfa_nested_menu_items( $rest_menu_items, 0);
		$rest_menu['items'] = $rest_menu_items;
	}

	return $rest_menu;
}

/**
 * Format a menu item for REST API
 *
 * @param object $menu_item
 * @return array
 * @author Tat Thien
 **/
function tfa_format_menu_item( $menu_item ) {
  return array(
    'id' => $menu_item->ID,
    'url' => $menu_item->url,
    'title' => $menu_item->title,
    'target' => $menu_item->target,
    'attr' => $menu_item->attr_title,
    'description' => $menu_item->description,
    'classes' => $menu_item->classes,
    'parent' => $menu_item->menu_item_parent,
  );
}

/**
 * Handle nested menu
 *
 * @param array $menu_items
 * @param int $parent
 * @return array $parents
 * @author Tat Thien
 **/
function tfa_nested_menu_items( &$menu_items, $parent = null ) {
  $parents = array();
  $children = array();

  array_map( function( $item ) use ( $parent, &$parents, &$children ) {
    if( $item['id'] != $parent && $item['parent'] == $parent ) {
      $parents[] = $item;
    } else {
      $children[] = $item;
    }
  }, $menu_items );

  foreach ( $parents as &$parent ) {
    if( tfa_has_child( $children, $parent['id'] ) ) {
      $parent['children'] = tfa_nested_menu_items( $children, $parent['id'] );
    }
  }

  return $parents;
}

/**
 * Check if the collection of menu item contains an item that is the parent id of `parent_id`
 *
 * @param array $menu_items
 * @param int $parent_id
 * @return boolean
 * @author Tat Thien
 **/
function tfa_has_child( $menu_items, $parent_id ) {
  foreach ( $menu_items as $item ) {
    return $item['parent'] == $parent_id;
  }
}
