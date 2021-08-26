<?php
/**
 * Analysis
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_google_id: (string) empty
 *  diblo_facebook_pixel_id: (string) empty
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

$google_tracking_id = $facebook_pixel_id = '';

add_action('wp_enqueue_scripts', function () {
    global $google_tracking_id, $facebook_pixel_id;

    if (is_user_logged_in())
        return;

    // https://developers.google.com/analytics/devguides/collection/gtagjs/user-opt-out
    $google_tracking_id = (string) apply_filters('diblo_google_id', '');
    if (! empty($google_tracking_id)) {
        wp_register_script('google-analytics', 'https://www.googletagmanager.com/gtag/js?id=' . $google_tracking_id, array(), DIBLO_WP_EXT_VERSION, true);
        wp_add_inline_script('google-analytics', "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . $google_tracking_id . "');", 'before');
        wp_enqueue_script('google-analytics');
    }

    // https://developers.facebook.com/docs/facebook-pixel/implementation
    $facebook_pixel_id = (string) apply_filters('diblo_facebook_pixel_id', '');
    if (! empty($facebook_pixel_id)) {
        wp_register_script('facebook-pixel', 'https://connect.facebook.net/en_US/fbevents.js', array(), DIBLO_WP_EXT_VERSION, true);
        wp_add_inline_script('facebook-pixel', "!function(e,u){e.fbq||(u=e.fbq=function(){u.callMethod?u.callMethod.apply(u,arguments):u.queue.push(arguments)},e._fbq||(e._fbq=u),u.push=u,u.loaded=!0,u.version='2.0',u.queue=[])}(window);fbq('init', '" . $facebook_pixel_id . "');fbq('track', 'PageView');", 'before');
        wp_enqueue_script('facebook-pixel');

        add_action('wp_footer', function () {
            global $facebook_pixel_id;
            echo '<noscript id="facebook-pixel-noscript"><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' . $facebook_pixel_id . '&ev=PageView&noscript=1"/></noscript>';
        }, 100001);
    }
});
?>
