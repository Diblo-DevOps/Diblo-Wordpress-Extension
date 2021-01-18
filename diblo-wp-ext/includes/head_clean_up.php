<?php
/**
 * <head> clean up
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_remove_generator_tags: (bool) false
 *  diblo_remove_wlwmanifest: (bool) false
 *  diblo_remove_rsd_api: (bool) false
 *  diblo_remove_feeds: (bool) false
 *  diblo_remove_rest_api: (bool) false
 *  diblo_remove_shortlinks: (bool) false
 *  diblo_remove_next_previous: (bool) false
 *  diblo_remove_emojis: (bool) false
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

add_action('init', function () {
    if (! is_admin()) {
        // Remove the "generator" meta tag from the document header (we definitely don't
        // need to let the world know that we are using WordPress), also remove the "generator"
        // name from the RSS feeds.
        if ((bool) apply_filters('diblo_remove_generator_tags', false) === true) {
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wpex_theme_meta_generator', 1);
            add_filter('the_generator', '__return_false');
        }

        // Remove the "wlwmanifest" link. wlwmanifest.xml is the resource file needed to
        // enable support for Windows Live Writer. Note to deny access to the file
        // /wp-includes/wlwmanifest.xml your need to use .htaccess (but that's not strictly
        // needed).
        if ((bool) apply_filters('diblo_remove_wlwmanifest', false) === true) {
            remove_action('wp_head', 'wlwmanifest_link');
        }

        // The RSD is an API to edit your blog from external services and clients.
        if ((bool) apply_filters('diblo_remove_rsd_api', false) === true) {
            remove_action('wp_head', 'rsd_link');
        }

        //
        if ((bool) apply_filters('diblo_remove_feeds', false) === true) {
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
        }

        //
        if ((bool) apply_filters('diblo_remove_rest_api', false) === true) {
            // Disable REST API link tag
            remove_action('wp_head', 'rest_output_link_wp_head', 10);

            // Disable oEmbed Discovery Links
            remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

            // Disable REST API link in HTTP headers
            remove_action('template_redirect', 'rest_output_link_header', 11, 0);
        }

        // "wp_shortlink_wp_head" adds a "shortlink" into the document head that will look
        // like http://example.com/?p=ID. - No need.
        if ((bool) apply_filters('diblo_remove_shortlinks', false) === true) {
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
            remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
        }

        // Remove a link to the next and previous post from the document header.
        // This could be theoretically beneficial, but to my experience it introduces
        // more problems than it solves. Please note that this has nothing to deal with
        // the "next/previous" post that you may want to add at the end of each post.
        if ((bool) apply_filters('diblo_remove_next_previous', false) === true) {
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        }
    }

    // Remove WP 4.2 emoji styles and JS. Nasty stuff.
    if ((bool) apply_filters('diblo_remove_emojis', false) === true) {
        remove_action('wp_head', 'print_emoji_detection_script');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('embed_head', 'print_emoji_detection_script');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        // Filter funcion to remove the emoji plugin from TinyMCE.
        add_filter('tiny_mce_plugins', function ($plugins) {
            if (is_array($plugins))
                return array_diff($plugins, array(
                    'wpemoji'
                ));
            return $plugins;
        }, 99999, 1);

        // Removing emoji from DNS prefetching hints.
        add_filter('wp_resource_hints', function ($urls, $relation_type) {
            if ('dns-prefetch' == $relation_type) {
                // Remove all addresses that refer to emoji
                foreach ($urls as $key => $url) {
                    if (strpos($url, 'https://s.w.org/images/core/emoji/') !== false) {
                        unset($urls[$key]);
                    }
                }
            }
            return $urls;
        }, 99999, 2);

        // Do not convert text smileys to graphic images
        add_filter('option_use_smilies', '__return_false', 99999, 3);
    }
}, 99999);
?>
