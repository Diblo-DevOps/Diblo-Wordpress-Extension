<?php
/**
 * Disable comments
 *
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_disable_comments: (bool) false
 */
// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

if ((bool) apply_filters('diblo_disable_comments', false) === true) {
    add_action('init', function () {
        if (is_admin()) {
            add_filter('diblo_remove_admin_menu_items', function ($array) {
                $array[] = 'edit-comments.php';
                return $array;
            });
        } else {
            add_filter('feed_links_show_comments_feed', '__return_false');
            add_action('wp_enqueue_scripts', function () {
                wp_deregister_script('comment-reply');
            });
            remove_action('set_comment_cookies', 'wp_set_comment_cookies');
        }

        add_filter('diblo_remove_admin_bar_items', function ($array) {
            $array[] = 'comments';
            return $array;
        });
    }, 99999);
}
?>