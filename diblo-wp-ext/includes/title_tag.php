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

if ((bool) apply_filters('diblo_autogen_title_tags', false) === true) {

    # https://stackoverflow.com/a/25778430/678611
    function rel2abs($rel, $base)
    {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '')
            return ($rel);

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return ($base . $rel);

        /*
         * parse base URL and convert to local variables:
         * $scheme, $host, $path
         */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/')
            $path = '';

        /* dirty absolute URL */
        $abs = '';

        /* do we have a user in our URL? */
        if (isset($user)) {
            $abs .= $user;

            /* password too? */
            if (isset($pass))
                $abs .= ':' . $pass;

            $abs .= '@';
        }

        $abs .= $host;

        /* did somebody sneak in a port? */
        if (isset($port))
            $abs .= ':' . $port;

        $abs .= $path . '/' . $rel;

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array(
            '#(/\.?/)#',
            '#/(?!\.\.)[^/]+/\.\./#'
        );
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, - 1, $n)) {}

        /* absolute URL is ready! */
        return ($scheme . '://' . $abs);
    }

    add_filter('the_content', function ($content) {
        if (empty($content)) {
            return $content;
        }

        preg_match_all('/<a ([^>]+)>(.+?)<\/a>/i', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $matche) {
            if (strpos(strtolower($matche[1]), ' title') === false) {
                $title = '';

                // Use the page title for url if we have a title
                if (preg_match('/href=("[^"]+"|\'[^\']+\'|[^ ]+)/i', $matche[1], $url_matche) !== false) {
                    $url = str_replace(array(
                        '"',
                        "'"
                    ), "", $url_matche[1]);
                    $url = rel2abs($url, home_url($wp->request));

                    $title = the_title_attribute(array(
                        'post' => url_to_postid($url)
                    ));
                }

                // Use the link text if we do not have a page title
                if (! empty($title)) {
                    $title = $matche[2]; // link text

                    // Use the alt tag text if we have an image link
                    if (preg_match('/<img[^>]+alt=("[^"]+"|\'[^\']+\'|[^ ]+)/i', $title, $alt_matche) !== false) {
                        $title = str_replace(array(
                            '"',
                            "'"
                        ), "", $alt_matche[1]); // tag text
                    }

                    $title = strip_tags($title); // Remove tags
                }

                // Adds title if we have a title
                if (! empty($title)) {
                    $replace = "<a " . $matche[1] . " title=\"" . $title . "\">" . $matche[2] . "</a>";

                    $content = str_replace($matche[0], $replace, $content);
                }
            }
        }

        return $content;
    });
}