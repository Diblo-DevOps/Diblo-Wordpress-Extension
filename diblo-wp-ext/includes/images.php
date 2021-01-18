<?php
/**
 * Images
 * 
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 * 
 * Filters:
 *  diblo_chroma_sampling: (array) '2x2', '1x1', '1x1'
 *  diblo_image_resolution: (array) 96, 96
 *  diblo_image_depth: (int) 8
 *  diblo_strip_meta: (bool) true
 *  diblo_jpeg_quality: (int) 75
 *  diblo_max_scale_up: (int) 0
 *  diblo_pre_generated_all_image_sizes: (bool) true
 *  diblo_pre_generated_sizes: (array) 'thumbnail' => 1, 'medium' => 1, 'medium_large' => 1, 'large' => 1
 *
 * Constants:
 *  AUTO_GEN_IMAGE_FOLDER: (string) resized
 *
 *
 * Chroma subsampling
 * ==================
 * diblo_chroma_sampling:
 * ┌─────────────────────┐
 * │ Subsampling (J:a:b) │
 * ├─────────────────────┤
 * │ 1x1,1x1,1x1 (4:4:4) │
 * │ 1x2,1x1,1x1 (4:4:0) │
 * │ 1x2,1x2,1x2 (4:4:4) │
 * │ 2x1,1x1,1x1 (4:2:2) │ <- recommended
 * │ 2x2,1x1,1x1 (4:2:0) │
 * │ 2x2,1x2,1x2 (4:2:2) │
 * │ 4x1,1x1,1x1 (4:1:1) │
 * └─────────────────────┘
 * 
 * Image depth
 * ==================
 * diblo_image_depth:
 * 8, 16 or 32
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

defined('AUTO_GEN_IMAGE_FOLDER') || define('AUTO_GEN_IMAGE_FOLDER', 'resized'); // Default auto gen image path

// Reduce Chroma sampling to 4:2:0 colors and Modify image auto gen path
require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';
require_once DIBLO_PLUGIN_DIR_PATH . 'libs/class-wp-image-editor-gmagick.php';

function is_gd($image)
{
    return is_resource($image) && 'gd' === get_resource_type($image) || is_object($image) && $image instanceof GdImage;
}

trait ImageOptions
{

    public function load()
    {
        $status = parent::load();

        if ($status === true) {
            $chroma_sampling = (array) apply_filters('diblo_chroma_sampling', array(
                '2x2',
                '1x1',
                '1x1'
            ));
            $image_resolution = (array) apply_filters('diblo_image_resolution', array(
                96,
                96
            ));
            $image_depth = (int) apply_filters('diblo_image_depth', 8);

            if (is_gd($this->image)) {
                imageresolution($this->image, $image_resolution[0], $image_resolution[1]);
            } elseif ($this->image instanceof Imagick) {
                $this->image->setSamplingFactors($chroma_sampling);
                $this->image->setImageResolution($image_resolution[0], $image_resolution[1]);
                $this->image->setImageDepth($image_depth);
            } elseif ($this->image instanceof Gmagick) {
                $this->image->setsamplingfactors($chroma_sampling);
                $this->image->setimageresolution($image_resolution[0], $image_resolution[1]);
                $this->image->setimagedepth($image_depth);
            }

            if ((bool) apply_filters('diblo_strip_meta', true))
                $this->strip_meta();
        }

        return $status;
    }

    protected function make_image($filename, $function, $arguments)
    {
        $status = parent::make_image($filename, $function, $arguments);

        if ($status === true && filesize($filename) > 10000) {

            if (is_gd($function[0])) {
                imageinterlace($function[0], true);
            } elseif ($function[0] instanceof Imagick) {
                $function[0]->setInterlaceScheme(Imagick::INTERLACE_PLANE);
            } elseif ($function[0] instanceof Gmagick) {
                $function[0]->setimageinterlacescheme(Gmagick::INTERLACE_PLANE);
            } else {
                return $status;
            }

            $status = parent::make_image($filename, $function, $arguments);
        }

        return $status;
    }
}

trait SaveMethods
{

    public function generate_filename($suffix = null, $dest_path = null, $extension = null)
    {
        $file = parent::generate_filename($suffix, $dest_path, $extension);
        return dirname($file) . '/' . AUTO_GEN_IMAGE_FOLDER . '/' . basename($file);
    }

    function multi_resize($sizes)
    {
        $sizes = parent::multi_resize($sizes);

        foreach ($sizes as &$data) {
            $data['file'] = AUTO_GEN_IMAGE_FOLDER . '/' . $data['file'];
        }

        return $sizes;
    }

    function save($destfilename = null, $mime_type = null)
    {
        $saved = parent::save($destfilename, $mime_type);

        $auto_gen_file = AUTO_GEN_IMAGE_FOLDER . '/' . $saved['file'];
        if ($saved['path'] == dirname(dirname($saved['path'])) . '/' . $auto_gen_file) {
            $saved['file'] = $auto_gen_file;
        }

        return $saved;
    }
}

class WP_Image_Editor_GD_Ext extends WP_Image_Editor_GD
{
    use SaveMethods;
}

class WP_Image_Editor_Gmagick_Ext extends WP_Image_Editor_Gmagick
{
    use ImageOptions, SaveMethods;
}

class WP_Image_Editor_Imagick_Ext extends WP_Image_Editor_Imagick
{
    use ImageOptions, SaveMethods;
}

add_filter('wp_image_editors', function ($editors) {
    if (extension_loaded('gd'))
        array_unshift($editors, "WP_Image_Editor_GD_Ext");

    if (extension_loaded('gmagick'))
        array_unshift($editors, "WP_Image_Editor_Gmagick_Ext");

    if (extension_loaded('imagick'))
        array_unshift($editors, "WP_Image_Editor_Imagick_Ext");

    return $editors;
}, 9999);

// Set image quality to 75 if it was higher. (max 85)
add_filter('jpeg_quality', function () {
    return (int) apply_filters('diblo_jpeg_quality', 75);
}, 9999);

$__bySizeName__ = array();
$__bySize__ = array();

// Genrate image on fly
add_filter('image_downsize', function ($out, $id, $size) {
    global $__bySizeName__, $__bySize__;

    // If image size exists let WP serve it like normally
    $imagedata = wp_get_attachment_metadata($id);

    // Image attachment doesn't exist
    if (! is_array($imagedata)) {
        return false;
    }

    // Gather all the different image sizes and size names
    if (empty($__bySizeName__) || empty($__bySize__)) {
        global $_wp_additional_image_sizes;

        $__bySizeName__ = array();
        $__bySize__ = array();
        foreach (get_intermediate_image_sizes() as $sizeName) {
            if (in_array($sizeName, array(
                'thumbnail',
                'medium',
                'medium_large',
                'large'
            ))) {
                // By size name
                $__bySizeName__[$sizeName]['width'] = get_option($sizeName . '_size_w');
                $__bySizeName__[$sizeName]['height'] = get_option($sizeName . '_size_h');
                $__bySizeName__[$sizeName]['crop'] = (bool) get_option($sizeName . '_crop');

                // By size
                $__bySize__[implode('x', $__bySizeName__[$sizeName])][] = $sizeName;
            } elseif (isset($_wp_additional_image_sizes[$sizeName])) {
                // By size name
                $__bySizeName__[$sizeName] = $_wp_additional_image_sizes[$sizeName];

                // By size
                $__bySize__[implode('x', $__bySizeName__[$sizeName])][] = $sizeName;
            }
        }
    }

    // If the size given is a string / a name of a size
    if (is_string($size)) {
        // If WP doesn't know about the image size name, then we can't really do any resizing of our own
        if (empty($__bySizeName__[$size])) {
            return false;
        }

        $crop = $__bySizeName__[$size]['crop'];
        $width = $__bySizeName__[$size]['width'];
        $height = $__bySizeName__[$size]['height'];
        // $size =
        $__bySize__Id = implode('x', $__bySizeName__[$size]);

        // If the size given is a custom array size
    } else if (is_array($size)) {
        $crop = array_key_exists(2, $size) ? $size[2] : true;
        $width = $size[0];
        $height = $size[1];
        $size = $size[0] . 'x' . $size[1];
        $__bySize__Id = $size[0] . 'x' . $size[1] . 'x' . $crop;
    } else {
        return false;
    }

    $att_file = get_attached_file($id);
    $att_url = dirname(wp_get_attachment_url($id));

    // Looking for other files that fit exactly to this size
    if (isset($__bySize__[$__bySize__Id]) && empty($imagedata['sizes'][$size])) {
        foreach ($__bySize__[$__bySize__Id] as $sizeName) {
            // Finds an existing size
            if (empty($imagedata['sizes'][$sizeName])) {
                continue;
            }

            // We will restore the image later if it is missing

            // Update sizes that have exactly the same size
            foreach ($__bySize__[$__bySize__Id] as $_sizeName) {
                $imagedata['sizes'][$_sizeName] = $imagedata['sizes'][$sizeName];
            }

            // Save the sizes in WP so that it can also perform actions on it
            wp_update_attachment_metadata($id, $imagedata);
            break;
        }
    }

    // If the image exists, serve it
    if (! empty($imagedata['sizes'][$size])) {
        $cur_file = $imagedata['sizes'][$size];
        if (file_exists(dirname($att_file) . '/' . $cur_file['file']))
            return array(
                $att_url . '/' . $cur_file['file'],
                $cur_file['width'],
                $cur_file['height'],
                $crop
            );
    }

    // === Create new image ===

    // Calculate ratio
    $w_ratio = $imagedata['width'] / $width;
    $h_ratio = $imagedata['height'] / $height;

    if ($crop) {
        $ratio = $w_ratio > $h_ratio ? $h_ratio : $w_ratio;
    } else {
        $ratio = $w_ratio > $h_ratio ? $w_ratio : $h_ratio;
    }

    // Scale up?
    $min_ratio = 100 / ((int) apply_filters('diblo_max_scale_up', 0) + 100); // 0 = no scale up, 20 = 20% etc
    if ($min_ratio > 1) {
        $min_ratio = 1;
    }
    if ($ratio < $min_ratio) {
        $ratio = $min_ratio;
    }

    // Calculate new image dimensions
    if ($crop) {
        if ($w_ratio > $h_ratio) {
            $height = round($imagedata['height'] / $ratio);
        } else {
            $width = round($imagedata['width'] / $ratio);
        }
    } else {
        $width = round($imagedata['width'] / $ratio);
        $height = round($imagedata['height'] / $ratio);
    }

    // Resize the image
    $image = wp_get_image_editor($att_file);
    if (is_wp_error($image)) {
        return false;
    }
    $image->resize($width, $height, $crop);
    $resized = $image->save();
    if (is_wp_error($resized)) {
        return false;
    }
    unset($resized['path']);

    // Update sizes that have exactly the same size
    if (isset($__bySize__[$__bySize__Id])) {
        foreach ($__bySize__[$__bySize__Id] as $_sizeName) {
            $imagedata['sizes'][$_sizeName] = $resized;
        }
    }

    // Save the new size(s) in WP so that it can also perform actions on it
    $imagedata['sizes'][$size] = $resized;
    wp_update_attachment_metadata($id, $imagedata);

    // Then serve it
    return array(
        $att_url . '/' . $resized['file'],
        $resized['width'],
        $resized['height'],
        $crop
    );
}, 10, 3);

// Filters the image sizes automatically generated when uploading an image
if ((bool) apply_filters('diblo_pre_generated_all_image_sizes', true) === false) {
    add_filter('intermediate_image_sizes_advanced', function ($sizes) {
        $generated_when_uploading = array(
            'thumbnail' => 1,
            'medium' => 1,
            'medium_large' => 1,
            'large' => 1
        );
        $generated_when_uploading = (array) apply_filters('diblo_pre_generated_sizes', $generated_when_uploading);

        return array_diff_key($sizes, array_diff_key($sizes, $generated_when_uploading));
    }, 99999, 1);
}
?>
