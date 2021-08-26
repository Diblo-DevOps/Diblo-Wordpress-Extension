<?php
/**
 * Admin menu and bar
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_admin_menu_order: (array) empty
 *  diblo_remove_admin_menu_items: (array) empty
 *  diblo_change_admin_menu_items: (array) empty
 *  diblo_add_admin_menu_items: (array) empty
 *  diblo_add_admin_menu_separators: (int) 0
 *  diblo_remove_admin_bar_items: (array) empty
 *  diblo_add_admin_bar_items: (array) empty
 *  diblo_change_admin_bar_items: (array) empty
 *  diblo_admin_bar_order: (array) empty
 *
 *
 * re-order admin menu
 * ==============================
 * diblo_admin_menu_order:
 * array(
 *     'index.php', // Dashboard
 *     'edit.php?post_type=page', // Pages
 *     'edit.php', // Posts
 *     'upload.php', // Media
 *     'themes.php', // Appearance
 *     'separator1', // --Space--
 *     'edit-comments.php', // Comments
 *     'users.php', // Users
 *     'separator2', // --Space--
 *     'plugins.php', // Plugins
 *     'tools.php', // Tools
 *     'options-general.php', // Settings
 * );
 * 
 * 
 * Add admin menu
 * ==============================
 * diblo_add_admin_menu_items:
 * array (
 *         array (
 *         page_title,
 *         menu_title,
 *         capability,
 *         menu_slug
 *     ),
 * )
 * 
 * See also: https://developer.wordpress.org/reference/functions/add_menu_page/
 * 
 * 
 * Remove admin menu
 * ==============================
 * diblo_remove_admin_menu_items:
 * array(
 *     'edit-comments.php', // Comments
 * );
 *
 *
 * Custom admin menu
 * ==============================
 * diblo_change_admin_menu_items:
 * array (
 *     index.php => array (
 *         Dashboard,
 *         read,
 *         index.php,
 *         null,
 *         menu-top menu-top-first menu-icon-dashboard,
 *         menu-dashboard,
 *         div,
 *     ),
 * );
 *
 * 
 * Add admin bar
 * ==============================
 * diblo_add_admin_bar_items:
 * array (
 *     array ( ... ),
 * );
 * 
 * See also: https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
 *
 *
 * Remove admin bar
 * ==============================
 * diblo_remove_admin_bar_items:
 * array (
 *     id,
 * );
 * 
 * See also: https://developer.wordpress.org/reference/classes/wp_admin_bar/remove_node/
 *
 *
 * Custom admin bar
 * ==============================
 * diblo_change_admin_bar_items:
 * array (
 *     id => array ( ... ),
 * );
 * 
 * See also: https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
 *
 *
 * re-order admin bar
 * ==============================
 * diblo_admin_bar_order:
 * array (
 *     id,
 * )
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

// re-order admin menu
add_filter('custom_menu_order', function ($custom) {
    if (! empty((array) apply_filters('diblo_admin_menu_order', array()))) {
        return true;
    }
    return $custom;
}, 9999);
add_filter('menu_order', function ($menu_order) {
    return (array) apply_filters('diblo_admin_menu_order', array());
}, 9999);

// Resource page: https://www.easywebdesigntutorials.com/reorder-left-admin-menu-and-add-a-custom-user-role/
add_action('admin_menu', function () {
    global $menu;

    // Add
    foreach ((array) apply_filters('diblo_add_admin_menu_items', array()) as &$menu_page) {
        call_user_func_array('add_menu_page', $menu_page);
    }

    // Add separator
    $add_separators = apply_filters('diblo_add_admin_menu_separators', 0);
    if ($add_separators > 0) {

        $separator_index = 0;
        foreach ($menu as &$m) {
            if (isset($m[2]) and strpos($m[2], 'separator') === 0)
                $separator_index ++;
        }

        $i = 0;
        while ($i < $add_separators) {
            $menu[] = array(
                '',
                'read',
                'separator' . ($separator_index + $i),
                '',
                'wp-menu-separator'
            );
            $i ++;
        }
    }

    /*
     * Remove
     *
     * remove_menu_page('edit-comments.php');
     * remove_submenu_page( 'themes.php', 'widgets.php' );
     * unset($submenu['themes.php'][6]); // remove customize links
     */
    foreach ((array) apply_filters('diblo_remove_admin_menu_items', array()) as &$id) {
        $c = count($id);
        if ($c == 1)
            remove_menu_page($id[0]);
        elseif ($c == 2)
            call_user_func_array('remove_submenu_page', $id);
    }

    /*
     * Custom
     *
     * $menu[5][0] = 'New name';
     * $submenu['themes.php'][5][0] = 'New name';
     * Array
     * (
     * [0] => Array
     * (
     * [0] => Dashboard
     * [1] => read
     * [2] => index.php
     * [3] =>
     * [4] => menu-top menu-top-first menu-icon-dashboard
     * [5] => menu-dashboard
     * [6] => div
     * )
     * )
     */
    $change_admin_menu_items = (array) apply_filters('diblo_change_admin_menu_items', array());
    if (! empty($change_admin_menu_items)) {
        foreach ($menu as $key => &$m) {
            if (isset($m[2]) and isset($change_admin_menu_items[$m[2]]))
                $menu[$key] = $change_admin_menu_items[$m[2]] + $m;
        }
    }
}, 9999);

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;

    // Add
    foreach ((array) apply_filters('diblo_add_admin_bar_items', array()) as &$args) {
        $wp_admin_bar->add_node($args);
    }

    // Remove
    foreach ((array) apply_filters('diblo_remove_admin_bar_items', array()) as &$id) {
        $wp_admin_bar->remove_node($id);
    }

    // Get an array of all the toolbar nodes (items) on the current page
    $nodes = $wp_admin_bar->get_nodes();

    // Custom
    foreach ((array) apply_filters('diblo_change_admin_bar_items', array()) as $id => &$args) {
        if (! isset($nodes[$id]))
            continue;

        foreach ($args as $arg => &$val)
            $nodes[$id]->$arg = $val;

        $wp_admin_bar->add_node($nodes[$id]);
    }

    // re-order admin bar
    $admin_bar_order = (array) apply_filters('diblo_admin_bar_order', array());
    if (! empty($admin_bar_order)) {
        foreach ($admin_bar_order as $id) {
            if (! isset($nodes[$id]))
                continue;

            // This will cause the identifier to act as the last menu item
            $wp_admin_bar->remove_node($id);
            $wp_admin_bar->add_node($nodes[$id]);

            // Remove the identifier from the list of nodes
            unset($nodes[$id]);
        }

        // Unknown identifiers will be moved to appear after known identifiers
        foreach ($nodes as $id => &$obj) {
            // There is no need to organize unknown children identifiers (sub items)
            if (! empty($obj->parent))
                continue;

            // This will cause the identifier to act as the last menu item
            $wp_admin_bar->remove_node($id);
            $wp_admin_bar->add_node($obj);
        }
    }
}, 999);

?>
