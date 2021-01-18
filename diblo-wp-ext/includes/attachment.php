<?php
/**
 * Redirect attachment
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_redirect_all_attachment: (bool) false
 *  diblo_redirect_attachment_with_no_parent: (bool) false
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

add_action('template_redirect', function () {
    global $post;

    if (! is_attachment())
        return;

    $redirect = (bool) apply_filters('diblo_redirect_all_attachment', false);
    $redirect_with_no_parent = (bool) apply_filters('diblo_redirect_attachment_with_no_parent', false);

    if ($redirect && $post->post_parent) {
        wp_safe_redirect(esc_url_raw(get_permalink($post->post_parent)), 301);
        exit();
    } elseif ($redirect || $redirect_with_no_parent) {
        wp_safe_redirect(esc_url_raw(get_home_url()), 301);
        exit();
    }
}, 0);

?>
