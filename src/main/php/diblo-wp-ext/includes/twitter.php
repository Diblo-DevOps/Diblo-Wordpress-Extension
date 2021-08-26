<?php
/**
 * Twitter
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_fix_yoast_default_twitter_image: (bool) false
 *
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

// https://wordpress.stackexchange.com/a/372561/131765
if ((bool) apply_filters('diblo_fix_yoast_default_twitter_image', false) === true) {
    add_filter('wpseo_twitter_image', function ($image) {
        if (! $image) {
            global $post;
            if (! $image = get_post_meta($post->ID)["_yoast_wpseo_opengraph-image"][0]) {
                $image = get_option("wpseo_social")["og_default_image"];
            }
        }

        return $image;
    });
} 
