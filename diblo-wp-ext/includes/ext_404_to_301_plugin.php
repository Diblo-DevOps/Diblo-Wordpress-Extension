<?php
/**
 * Optimize mysql table 404_to_301
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_optimize_404_to_301: (bool) false
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

// Ext 404 to 301 plugin
add_action('optimize_404to301_table', function () {
    global $wpdb;
    $querystr = "
        SELECT id
        FROM (
            SELECT *
            FROM " . $wpdb->prefix . "404_to_301 AS getExludeIDs
            WHERE redirect LIKE ''
            ORDER BY date DESC
        ) AS GroupBY
        GROUP BY url 
    ";
    $res = $wpdb->get_results($querystr);
    if ($res) {
        $ids = array();
        foreach ($res as $row)
            $ids[] = $row->id;

        $wpdb->query("DELETE FROM " . $wpdb->prefix . "404_to_301 WHERE id NOT IN (" . implode(',', $ids) . ")");
    }
});

function diblo_optimize_404_to_301_scheduled_hook()
{
    include_once (ABSPATH . 'wp-admin/includes/plugin.php');

    $enable = (bool) apply_filters('diblo_optimize_404_to_301', false);
    $is_diblo_wp_ext_active = is_plugin_active('diblo-wp-ext/diblo-wp-ext.php');
    $is_404_to_301_active = is_plugin_active('404-to-301/404-to-301.php');
    $schedule = wp_get_schedule('optimize_404to301_table');

    if (current_user_can('activate_plugins') and $enable === true and $is_diblo_wp_ext_active === true and $is_404_to_301_active === true and $schedule === false) {
        $hour_in_seconds = 24 * 60 * 60;
        $timestamp = time();

        wp_schedule_event((($timestamp - ($timestamp % $hour_in_seconds)) + $hour_in_seconds), 'hourly', 'optimize_404to301_table');

        do_action('optimize_404to301_table');

        return;
    }

    if (($enable === false || $is_diblo_wp_ext_active === false || $is_404_to_301_active === false) and $schedule !== false)
        wp_clear_scheduled_hook('optimize_404to301_table');
}

add_action('admin_init', 'diblo_optimize_404_to_301_scheduled_hook');
register_activation_hook(__FILE__, 'diblo_optimize_404_to_301_scheduled_hook');
register_deactivation_hook(__FILE__, 'diblo_optimize_404_to_301_scheduled_hook');

?>
