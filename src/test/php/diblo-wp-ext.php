<?php
/**
 * Plugin Name: Customize Wordpress
 * Description: This plugin handles custom actions and filters. Read if necessary the WordPress paradigm about custom actions and filters https://wordpress.org/support/article/must-use-plugins/
 * Version: 0.0.1
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
 *  diblo_chroma_sampling: (array) array('2x2', '1x1', '1x1')
 *  diblo_image_resolution: (array) array(96, 96)
 *  diblo_image_depth: (int) 16
 *  diblo_strip_meta: (bool) true
 *  diblo_jpeg_quality: (int) 75
 *  diblo_max_scale_up: (int) 0
 *  diblo_pre_generated_all_image_sizes: (bool) true
 *  diblo_pre_generated_sizes: (array) array('thumbnail' => 1, 'medium' => 1, 'medium_large' => 1, 'large' => 1)
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
 *  diblo_enable_prefetch_extension: (bool) false
 *  diblo_prefetch_domain_list: (array) empty
 *  diblo_disable_comments: (bool) false
 *  diblo_google_id: (string) empty
 *  diblo_facebook_pixel_id: (string) empty
 *  diblo_optimize_404_to_301: (bool) false
 *  diblo_block_theme_updates: (array) empty
 *  diblo_block_plugin_updates: (array) empty
 *  diblo_admin_menu_order: (array) empty
 *  diblo_remove_admin_menu_items: (array) empty
 *  diblo_change_admin_menu_items: (array) empty
 *  diblo_add_admin_menu_items: (array) empty
 *  diblo_add_admin_menu_separators: (int) 0
 *  diblo_remove_admin_bar_items: (array) empty
 *  diblo_add_admin_bar_items: (array) empty
 *  diblo_change_admin_bar_items: (array) empty
 *  diblo_admin_bar_order: (array) empty
 *  diblo_redirect_all_attachment: (bool) false
 *  diblo_redirect_attachment_with_no_parent: (bool) false
 *  diblo_enable_trash_unknownfiles: (bool) false
 *  diblo_unknownfiles_disable_empty_trash: (bool) true
 *  diblo_enable_enhance_email_info: (bool) false
 *
 * Constants:
 *  AUTO_GEN_IMAGE_FOLDER: (string) resized
 *  CLEAN_UNKNOWNFILES_DAYS: (int) 90
 *  UNKNOWNFILES_EMPTY_TRASH_DAYS: (int) 90
 *
 */

