<?php
/**
 * Prefetch
 *
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_enable_prefetch_extension: (bool) false
 *  diblo_prefetch_domain_list: (array) empty
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

if ((bool) apply_filters('diblo_enable_prefetch_extension', false) === true) {
    add_action('wp_head', function () {
        $diblo_prefetch_domain_list = (array) apply_filters('diblo_prefetch_domain_list', array());
        if (empty($diblo_prefetch_domain_list)) {
            remove_action('wp_head', 'wp_resource_hints', 2);
            echo '<meta http-equiv="x-dns-prefetch-control" content="off">' . "\n";
        } else {
            echo '<meta http-equiv="x-dns-prefetch-control" content="on">' . "\n";
            foreach ($diblo_prefetch_domain_list as &$domain) {
                if (substr($domain, 0, 2) !== '//' and substr($domain, 0, 4) !== "http") {
                    $domain = '//' . $domain;
                }
                echo '<link rel="dns-prefetch" href="' . $domain . '">' . "\n";
            }
        }
    }, 0);
        
    remove_action('wp_head', 'wp_resource_hints', 2);
}
?>