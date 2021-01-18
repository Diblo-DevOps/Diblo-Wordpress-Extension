<?php
/**
 * Fix mail info
 *
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_enable_enhance_email_info: (bool) false
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

if ((bool) apply_filters('diblo_enable_enhance_email_info', false) === true) {
    // Function to change email address
    add_filter('wp_mail_from', function ($original_email_address) {
        $email_address = get_site_option('admin_email');
        if (! empty($email_address))
            return $email_address;
            
            return $original_email_address;
    });
        
    // Function to change sender name
    add_filter('wp_mail_from_name', function ($original_sender_name) {
        $sender_name = get_site_option('blogname');
        if (! empty($sender_name))
            return $sender_name;
            
            return $original_sender_name;
    });
}
?>
