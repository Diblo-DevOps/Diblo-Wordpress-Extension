<?php
/**
 * Clean unknownfiles
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_enable_trash_unknownfiles: (bool) false
 *  diblo_unknownfiles_disable_empty_trash: (bool) false
 *
 * Constants:
 *  TRASH_UNKNOWNFILES_DAYS: (int) 90
 *  UNKNOWNFILES_EMPTY_TRASH_DAYS: (int) 90
 */
defined('ABSPATH') || die('No direct script access allowed!');

defined('TRASH_UNKNOWNFILES_DAYS') || define('TRASH_UNKNOWNFILES_DAYS', 90);
defined('UNKNOWNFILES_EMPTY_TRASH_DAYS') || define('UNKNOWNFILES_EMPTY_TRASH_DAYS', 90);

$wp_upload_dir = wp_upload_dir();
$basedir = rtrim($wp_upload_dir['basedir'], '/') . "/";
$baseurl = rtrim($wp_upload_dir['baseurl'], '/') . "/";

function _gen_next_scheduled_time($recurrence, $add_secs = 0)
{
    $timestamp = time();

    switch ($recurrence) {
        case 'hourly':
            $frequency = 60 * 60;
            break;
        case 'twicedaily':
            $frequency = 12 * 60 * 60;
            break;
        default:
            $frequency = 24 * 60 * 60;
            break;
    }

    return ($timestamp - ($timestamp % $frequency)) + $frequency + $add_secs;
}

function manage_schedules($schedule_name, $enable = true, $recurrence = null, $add_secs = 0)
{
    include_once (ABSPATH . 'wp-admin/includes/plugin.php');

    $is_diblo_wp_ext_active = is_plugin_active('diblo-wp-ext/diblo-wp-ext.php');
    $schedule = wp_get_schedule($schedule_name);

    if (current_user_can('activate_plugins') and $enable === true and $is_diblo_wp_ext_active === true and $schedule === false)
        wp_schedule_event(_gen_next_scheduled_time($recurrence, $add_secs), $recurrence, $schedule_name);
    elseif (($enable === false || $is_diblo_wp_ext_active === false) and $schedule !== false)
        wp_clear_scheduled_hook($schedule_name);
}

function add_schedule($function_to_add)
{
    add_action('admin_init', $function_to_add);
    register_activation_hook(__FILE__, $function_to_add);
    register_deactivation_hook(__FILE__, $function_to_add);
}

function _getFiles($cwd, $exludes = array(), $atLeastOld = 0)
{
    global $basedir;

    $cwd = rtrim($cwd, '/') . "/";
    $r_dir = str_replace($basedir, '', $cwd);
    $files = array();
    if ($dh = opendir($cwd)) {
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..' || isset($exludes[$r_dir . $file]))
                continue;

            if (is_dir($cwd . $file))
                $files = array_merge($files, _getFiles($cwd . $file, $exludes, $atLeastOld));
            elseif (filemtime($cwd . $file) <= $atLeastOld)
                $files[] = $r_dir . $file;
        }
        closedir($dh);
    }
    return $files;
}

// diblo_trash_unknownfiles
function getKnownFiles()
{
    global $wpdb, $baseurl;

    $_wp_attachments = $wpdb->get_results("
        SELECT posts.guid, postmeta.meta_value
        FROM " . $wpdb->posts . " posts
        LEFT JOIN " . $wpdb->postmeta . " postmeta ON posts.ID=postmeta.post_id AND postmeta.meta_key =  '_wp_attachment_metadata'
        WHERE (
            posts.post_status = 'inherit'
            OR posts.post_status = 'trash'
        )
        AND posts.post_type = 'attachment'
    ");

    $files = array();
    foreach ($_wp_attachments as $_wp_attachment) {
        $meta_value = array();
        if (! empty($_wp_attachment->meta_value))
            $meta_value = unserialize($_wp_attachment->meta_value);

        if (isset($meta_value['file']))
            $file = $meta_value['file'];
        else
            $file = str_replace($baseurl, '', $_wp_attachment->guid);

        $files[$file] = true;

        if (! isset($meta_value['sizes']))
            continue;

        $r_dir = dirname($file) . "/";
        foreach ($meta_value['sizes'] as $size) {
            if (! empty($size['file']))
                $files[$r_dir . $size['file']] = true;
        }
    }
    return $files;
}

function getWorkDirs($cwd)
{
    $workDirs = array();
    if ($dh = opendir($cwd)) {
        while (($dir = readdir($dh)) !== false) {
            if ($dir == '.' || $dir == '..' || ! is_dir($cwd . $dir) || ! preg_match("/^[0-9]{4}$/", $dir))
                continue;
            $workDirs[] = $cwd . $dir;
        }
        closedir($dh);
    }
    return $workDirs;
}

add_action('diblo_trash_unknownfiles', function () {
    global $basedir;

    $cwd = rtrim($basedir, '/') . "/";
    $knownFiles = getKnownFiles();
    $delete_timestamp = time() - (TRASH_UNKNOWNFILES_DAYS * 86400);

    foreach (getWorkDirs($cwd) as $workDir) {
        foreach (_getFiles($workDir, $knownFiles, $delete_timestamp) as $file) {
            $source_dir = dirname($cwd . $file) . '/';
            $name = basename($file);

            $dest_dir = str_replace($basedir, $basedir . 'diblo-wp-ext/trash/', $source_dir);
            if (! is_dir($dest_dir))
                mkdir($dest_dir, 0755, true);

            copy($source_dir . $name, $dest_dir . $name);
            unlink($source_dir . $name);
        }
    }
});

// diblo_unknownfiles_empty_trash
function removeEmptyDirs($cwd)
{
    $empty = true;
    $cwd = rtrim($cwd, '/') . "/";

    if ($dh = opendir($cwd)) {
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..')
                continue;

            if (is_dir($cwd . $file) && removeEmptyDirs($cwd . $file) === true)
                rmdir($cwd . $file);

            $empty = false;
        }
        closedir($dh);
    }

    return $empty;
}

add_action('diblo_unknownfiles_empty_trash', function () {
    global $basedir;

    $cwd = rtrim($basedir, '/') . "/diblo-wp-ext/trash/";
    $delete_timestamp = time() - (UNKNOWNFILES_EMPTY_TRASH_DAYS * 86400);
    foreach (_getFiles($cwd, array(), $delete_timestamp) as $file) {
        unlink($cwd . $file);
    }
    removeEmptyDirs($cwd);
});

//
add_schedule(function () {
    $enable = (bool) apply_filters('diblo_enable_trash_unknownfiles', false);
    if ($enable) {
        manage_schedules('diblo_trash_unknownfiles', 'hourly', $enable, 0);

        $enable = (bool) apply_filters('diblo_unknownfiles_disable_empty_trash', false) === false;
        manage_schedules('diblo_unknownfiles_empty_trash', 'daily', $enable, 900);
    }
});

?>
