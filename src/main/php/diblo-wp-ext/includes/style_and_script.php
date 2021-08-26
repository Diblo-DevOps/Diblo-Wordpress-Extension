<?php
/**
 * Style and script
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_global_initialization_style: (string) empty
 *  diblo_remove_styles: (array) empty
 *  diblo_add_styles: (array) empty
 *  diblo_remove_scripts: (array) empty
 *  diblo_change_scripts: (array) empty
 *  diblo_add_scripts: (array) empty
 *  diblo_inline_footer_before: (string) empty
 *  diblo_inline_footer_after: (string) empty
 *  diblo_enable_non_block_style: (bool) false
 *  diblo_inline_style_before: (string) empty
 *  diblo_inline_style_after: (string) empty
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

/*
 * With this filter you can add inline style which will
 * be loaded before everything else. It is typically used
 * for pages that load the style at the end of the page
 * (non block style)
 */
add_action('wp_head', function () {
    $style = (string) apply_filters('diblo_global_initialization_style', '');
    if (! empty($style))
        echo '<style type="text/css">' . $style . '</style>';
}, 1);

// Manage styles or scripts
add_action('wp_enqueue_scripts', function () {
    global $wp_scripts;

    // --------------- Style --------------- //
    // remove style
    foreach ((array) apply_filters('diblo_remove_styles', array()) as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    // Add style
    foreach ((array) apply_filters('diblo_add_styles', array()) as $handle => $src) {
        wp_enqueue_style($handle, $src, null, DIBLO_WP_EXT_VERSION);
    }
    // --------------- Script --------------- //

    // remove script
    foreach ((array) apply_filters('diblo_remove_scripts', array()) as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }

    // Perform script changes
    foreach ((array) apply_filters('diblo_change_scripts', array()) as $handle => $src) {
        $queue = false;
        if (in_array($handle, $wp_scripts->queue))
            $queue = true;

        if ($queue)
            wp_dequeue_script($handle);
        wp_deregister_script($handle);
        wp_register_script($handle, $src, array(), DIBLO_WP_EXT_VERSION, true);
        if ($queue)
            wp_enqueue_script($handle);
    }

    // Add scripts
    foreach ((array) apply_filters('diblo_add_scripts', array()) as $handle => $src) {
        wp_enqueue_script($handle, $src, false, DIBLO_WP_EXT_VERSION, false);
    }
}, 998);

// Merge inline scripts
$saved_scripts = array(
    'inline_before' => '',
    'links' => '',
    'inline_after' => ''
);

add_filter('script_loader_tag', function ($tag, $handle, $src) {
    global $saved_scripts;

    if (is_admin() || '<!--[if' == substr($tag, 0, 7))
        return $tag;

    $array = explode('</script>', $tag);
    $link_index = 0;
    while (true) {
        if (false !== strpos($array[$link_index], 'src='))
            break;

        if ($link_index >= 1)
            return $tag;

        $link_index ++;
    }

    if (substr(trim($array[0]), 0, 7) == '<script')
        $saved_scripts['inline_before'] .= preg_replace('#^<[^>]+>#', '', trim($array[0]));

    if (preg_match("#\s(?:async\s*=|defer\s*=|async\s|defer\s)#", $array[$link_index]))
        $array[$link_index] = preg_replace('#\s(?:async\s*=|defer\s*=|async\s|defer\s)(?:\s*(?:"|\')\s*(?:async|defer)\s*(?:"|\')\s)?#', ' ', $array[$link_index]);

    $saved_scripts['links'] .= str_replace('<script', '<script defer', trim($array[$link_index])) . "</script>\n";

    if (! empty($array[$link_index + 1]) and substr(trim($array[$link_index + 1]), 0, 7) == '<script')
        $saved_scripts['inline_after'] .= preg_replace('#^<[^>]+>#', '', trim($array[$link_index + 1]));

    return '';
}, 9999, 3);
add_action('wp_footer', function () {
    global $saved_scripts;

    $saved_scripts['inline_before'] = (string) apply_filters('diblo_inline_footer_before', $saved_scripts['inline_before']);
    if (! empty($saved_scripts['inline_before']))
        echo sprintf('<script type="text/javascript">%s</script>', $saved_scripts['inline_before']) . "\n";

    if (! empty($saved_scripts['links']))
        echo $saved_scripts['links'];

    $saved_scripts['inline_after'] = (string) apply_filters('diblo_inline_footer_after', $saved_scripts['inline_after']);
    if (! empty($saved_scripts['inline_after']))
        echo sprintf('<script type="text/javascript">%s</script>', $saved_scripts['inline_after']) . "\n";
}, 100000);

// Move scripts to the footer
add_action('wp_enqueue_scripts', function () {
    global $wp_scripts;

    foreach ($wp_scripts->queue as $handle) {
        $wp_scripts->registered[$handle]->extra['group'] = 1;
    }
}, 999);

// Move style to the footer (non block style)
if (apply_filters('diblo_enable_non_block_style', false)) {
    $saved_style_handles = array();

    add_filter('wp_enqueue_scripts', function () {
        global $saved_style_handles, $wp_styles;

        if (is_admin())
            return;

        $saved_style_handles = $wp_styles->queue;
        foreach ($saved_style_handles as $handler) {
            wp_dequeue_style($handler);
        }
    }, 99999);

    add_action('wp_footer', function () {
        global $saved_style_handles, $wp_styles;
        include_once (ABSPATH . 'wp-admin/includes/plugin.php');

        $inline_before = (string) apply_filters('diblo_inline_style_before', '');
        if (! empty($inline_before))
            echo sprintf('<style type="text/css">%s</style>', $inline_before) . "\n";

        if (! empty($saved_style_handles)) {
            // echo '<noscript id="deferred-styles">';
            if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
                echo '<!-- W3TC-include-css -->';
            }
            foreach ($saved_style_handles as $handler) {
                wp_enqueue_style($handler);
            }
            $wp_styles->do_items($saved_style_handles);
            // echo '</noscript>';
        }

        $inline_after = (string) apply_filters('diblo_inline_style_after', '');
        if (! empty($inline_after))
            echo sprintf('<style type="text/css">%s</style>', $inline_after) . "\n";
    }, 99999);
}

// Fix refresh of css and js files
include_once (ABSPATH . 'wp-admin/includes/plugin.php');

if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
    add_filter('w3tc_minify_urls_for_minification_to_minify_filename', function ($minify_filename, $files, $type) {
        $pminify_filename_parts = pathinfo($minify_filename);
        $root = rtrim(get_home_path(), "/");
        $time = 0;

        foreach ($files as $file) {
            $time += filemtime($root . "/" . $file);
        }

        return $pminify_filename_parts['filename'] . '_' . substr(md5($time), 0, 5) . '.' . $pminify_filename_parts['extension'];
    }, 10, 3);
}
?>
