<?php
/**
 * Plugin Name: Diblo Wordpress Extension
 * Description: Optimize, fix and add features to WordPress
 * Version: 0.1.1
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

if (! defined('DIBLO_WP_EXT_VERSION')) {
    define('DIBLO_WP_EXT_VERSION', '0.1.1');
}

if (! defined('DIBLO_WP_EXT_ROOT')) {
    define('DIBLO_WP_EXT_ROOT', plugin_dir_path(__FILE__));
}

// Appends additional links
if (is_admin()) {
    add_filter('plugin_row_meta', function ($links_array, $plugin_file_name) {
        if (strpos($plugin_file_name, basename(__FILE__))) {
            $plugin_url = plugin_dir_url(__FILE__);
            $links_array[] = '<a href="' . $plugin_url . 'about.html" target="_blank">About</a>';
            $links_array[] = '<a href="' . $plugin_url . 'about.html#guide" target="_blank">Guide</a>';
            $links_array[] = '<a href="' . $plugin_url . 'about.html#filters" target="_blank">Filters</a>';
        }

        return $links_array;
    }, 10, 2);
}

/**
 * <head> clean up
 *
 * Filters:
 * diblo_remove_generator_tags: (bool) false
 * diblo_remove_wlwmanifest: (bool) false
 * diblo_remove_rsd_api: (bool) false
 * diblo_remove_feeds: (bool) false
 * diblo_remove_rest_api: (bool) false
 * diblo_remove_shortlinks: (bool) false
 * diblo_remove_next_previous: (bool) false
 * diblo_remove_emojis: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/head_clean_up.php';

/**
 * Images
 *
 * Filters:
 * diblo_chroma_sampling: (array) '2x2', '1x1', '1x1'
 * diblo_image_resolution: (array) 96, 96
 * diblo_image_depth: (int) 8
 * diblo_strip_meta: (bool) true
 * diblo_jpeg_quality: (int) 75
 * diblo_max_scale_up: (int) 0
 * diblo_pre_generated_all_image_sizes: (bool) true
 * diblo_pre_generated_sizes: (array) 'thumbnail' => 1, 'medium' => 1, 'medium_large' => 1, 'large' => 1
 *
 * Constants:
 * AUTO_GEN_IMAGE_FOLDER: (string) resized
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/images.php';

/**
 * Style and script
 *
 * Filters:
 * diblo_global_initialization_style: (string) empty
 * diblo_remove_styles: (array) empty
 * diblo_add_styles: (array) empty
 * diblo_remove_scripts: (array) empty
 * diblo_change_scripts: (array) empty
 * diblo_add_scripts: (array) empty
 * diblo_inline_footer_before: (string) empty
 * diblo_inline_footer_after: (string) empty
 * diblo_enable_non_block_style: (bool) false
 * diblo_inline_style_before: (string) empty
 * diblo_inline_style_after: (string) empty
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/style_and_script.php';

/**
 * Prefetch
 *
 * Filters:
 * diblo_enable_prefetch_extension: (bool) false
 * diblo_prefetch_domain_list: (array) empty
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/prefetch.php';

/**
 * Disable comments
 *
 * Filters:
 * diblo_disable_comments: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/comments.php';

/**
 * Analysis
 *
 * Filters:
 * diblo_google_id: (string) empty
 * diblo_facebook_pixel_id: (string) empty
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/analysis.php';

/**
 * Optimize mysql table 404_to_301
 *
 * Filters:
 * diblo_optimize_404_to_301: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/ext_404_to_301_plugin.php';

/**
 * Block updates of specific plugins or themes
 *
 * Filters:
 * diblo_block_theme_updates: (array) empty
 * diblo_block_plugin_updates: (array) empty
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/block_updates.php';

/**
 * Admin menu and bar
 *
 * Filters:
 * diblo_admin_menu_order: (array) empty
 * diblo_remove_admin_menu_items: (array) empty
 * diblo_change_admin_menu_items: (array) empty
 * diblo_add_admin_menu_items: (array) empty
 * diblo_add_admin_menu_separators: (int) 0
 * diblo_remove_admin_bar_items: (array) empty
 * diblo_add_admin_bar_items: (array) empty
 * diblo_change_admin_bar_items: (array) empty
 * diblo_admin_bar_order: (array) empty
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/admin_menu_and_bar.php';

/**
 * Redirect attachment
 *
 * Filters:
 * diblo_redirect_all_attachment: (bool) false
 * diblo_redirect_attachment_with_no_parent: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/attachment.php';

/**
 * Clean unknownfiles
 *
 * Filters:
 * diblo_enable_trash_unknownfiles: (bool) false
 * diblo_unknownfiles_disable_empty_trash: (bool) false
 * Constants:
 * TRASH_UNKNOWNFILES_DAYS: (int) 90
 * UNKNOWNFILES_EMPTY_TRASH_DAYS: (int) 90
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/clean_unknownfiles.php';

/**
 * Fix mail info
 *
 * Filters:
 * diblo_enable_enhance_email_info: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/mails.php';

/**
 * Twitter
 *
 * Filters:
 * diblo_fix_yoast_default_twitter_image: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/twitter.php';

/**
 * Add title tag
 *
 * Filters:
 * diblo_autogen_title_tags: (bool) false
 */
require_once DIBLO_WP_EXT_ROOT . 'includes/title_tag.php';
