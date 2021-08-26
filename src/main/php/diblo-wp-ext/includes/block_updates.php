<?php
/**
 * Block updates of specific plugins or themes
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_block_theme_updates: (array) empty
 *  diblo_block_plugin_updates: (array) empty
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

// Block update of specific plugins or themes ###
add_filter('site_transient_update_themes', function ($value) {
    foreach ((array) apply_filters('diblo_block_theme_updates', array()) as $theme_name) {
        if (isset($value->response[$theme_name]))
            unset($value->response[$theme_name]);
    }
    return $value;
}, 9999);

add_filter('site_transient_update_plugins', function ($value) {
    foreach ((array) apply_filters('diblo_block_plugin_updates', array()) as $plugin_basename) {
        if (isset($value->response[$plugin_basename]))
            unset($value->response[$plugin_basename]);
    }
    return $value;
}, 9999);

?>
