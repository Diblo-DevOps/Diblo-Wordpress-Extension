<?php
/**
 * Add title tag
 *
 * Plugin Name: Diblo Wordpress Extension
 * Author: Henrik Ankersø
 * Author URI: https://www.diblo.dk/
 *
 * Filters:
 *  diblo_autogen_title_tags: (bool) false
 *
 */

// die if accessed directly
defined('ABSPATH') || die('No direct script access allowed!');

$diblo_attr_pattern = '/%s[ ]*=[ ]*((?<quote>"|\')(?:(?!\k<quote>).)*\k<quote>|[^ ]*)/i';

if ((bool) apply_filters('diblo_autogen_title_tags', false) === true) {

    function build_url($parts)
    {
        $url = $parts['scheme'] . '://';

        if (! empty($parts['user'])) {
            $url .= $parts['user'];
        }

        if (! empty($parts['pass'])) {
            $url .= ':' . $parts['pass'];
        }

        if (! empty($parts['user'])) {
            $url .= '@';
        }

        $url .= $parts['host'];

        if (! empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }

        if (! empty($parts['path'])) {
            $url .= $parts['path'];
        }

        if (! empty($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        if (! empty($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }

    function absurl($url, $base)
    {
        $base = parse_url($base);
        $_url = parse_url($url);

        if (! empty($_url['scheme'])) {
            // The URL is already absolute
            return $url;
        }

        if (! empty($_url['host'])) {
            // The URL is only missing the scheme
            return $base["scheme"] . ':' . $url;
        }

        if (! empty($_url['path'])) {
            // Combine path and overwrite base url query and fragment
            unset($base["query"]);
            unset($base["fragment"]);

            if (substr($_url['path'], 0, 1) != '/') {
                $array = explode('/', $_url['path']);

                if (! empty($base['path'])) {
                    $_array = explode('/', $base['path']);

                    # Remove the file and/or empty path name(s)
                    $_array = array_slice($_array, 1, - 1);

                    $array = array_merge($_array, $array);
                }

                $path = array();
                foreach ($array as $dir) {
                    if ($dir == '..') {
                        array_pop($path);
                    } elseif ($dir != '.') {
                        $path[] = $dir;
                    }
                }
                $_url['path'] = "/" . implode('/', $path);
            }
        } elseif (! empty($_url['query'])) {
            // Overwrite base url query and fragment
            unset($base["fragment"]);
        }
        // else: Overwrite base url fragment

        return build_url(array_merge($base, $_url));
    }

    function get_attr_value($att, $string)
    {
        global $diblo_attr_pattern;

        preg_match(sprintf($diblo_attr_pattern, $att), $string, $att_value);

        if (count($att_value) <= 0) {
            return null;
        }

        if (array_key_exists('quote', $att_value)) {
            return substr($att_value[1], 1, - 1);
        }

        return $att_value[1];
    }

    function handle_title_attributes($content)
    {
        global $wp, $diblo_attr_pattern;

        if (empty($content)) {
            return $content;
        }

        preg_match_all('/<a([^>]+)>(.*?)<\/a>/i', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $matche) {
            $attributes = $matche[1];
            $link_text = $matche[2];

            // Only handle a non specified or empty title if
            // the href is specified and not empty
            $href = get_attr_value('href', $attributes);
            $title = get_attr_value('title', $attributes);

            if (! empty($href) && empty($title)) {
                // Use the page title for url
                $url = absurl($href, home_url($wp->request));
                $args = array(
                    'echo' => false,
                    'post' => url_to_postid($url)
                );

                $title = the_title_attribute($args);

                // Use the link text if we do not have a page title
                if (empty($title)) {
                    $title = $link_text;

                    // Use the alt tag value if the link contains an image
                    if (strpos('<img ', $link_text) !== false) {
                        $title = get_attr_value('alt', $link_text);
                    }

                    $title = strip_tags($title); // Remove tags
                }

                // Add title attribute if we have a title value
                if (! empty($title)) {
                    $attributes = preg_replace(sprintf($diblo_attr_pattern, 'title'), '', $attributes); // Removes old title attribute if any
                    $attributes .= " title=\"" . ucfirst(esc_html($title)) . "\""; // Add title attribute

                    $content = str_replace($matche[0], "<a" . $attributes . ">" . $link_text . "</a>", $content);
                }
            }
        }

        return $content;
    }

    add_filter('wp_nav_menu', 'handle_title_attributes', 99999);
    add_filter('the_content', 'handle_title_attributes', 99999);
}
