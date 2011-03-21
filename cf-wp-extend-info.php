<?php
/*
Plugin Name: CF WP.org Extend Info 
Plugin URI: http://crowdfavorite.com/wordpress/ 
Description: Get plugin and theme information from the wordpress.org API. Includes local caching with filtered cache timeout setting. 
Version: 1.0 
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

load_plugin_textdomain('cf-wp-extend-info');

function cf_plugin_info($slug, $key) {
	return cfwpei_api_info($slug, $key, 'plugin');
}

function cf_theme_info($slug, $key) {
	return cfwpei_api_info($slug, $key, 'theme');
}

function cfwpei_api_info($slug, $key, $type) {
	$data = cfwpei_get_api_data($slug, $type);
	if (isset($data->$key)) {
		return $data->$key;
	}
	return false;
}

function cfwpei_cache_time() {
	return apply_filters('cfwpei_cache_time', 3600); // 1 hour default
}

function cfwpei_get_api_data($slug, $type) {
	// check cache
	if ($data = cfwpei_get_cached_data($slug, $type)) {
		return $data;
	}
	// check remote, cache
	else if ($data = cfwpei_get_remote_data($slug, $type)) {
		cfwpei_cache_data($data, $type);
		return $data;
	}
	// return any cached data we might have - some data better than no data
	else if ($data = get_option('cfwpei_'.$type.'_'.sanitize_title($slug))) {
		return $data['data'];
	}
	return false;
}

function cfwpei_get_cached_data($slug, $type) {
	if ($data = get_option('cfwpei_'.$type.'_'.sanitize_title($slug))) {
		$cache_time = cfwpei_cache_time();
		if (isset($data['timestamp']) && ($data['timestamp'] + $cache_time) > time()) {
			return $data['data'];
		}
	}
	return false;
}

function cfwpei_get_remote_data($slug, $type) {
	switch ($type) {
		case 'plugin':
			require_once(ABSPATH.'wp-admin/includes/plugin-install.php');
			$data = plugins_api('plugin_information', array('slug' => $slug));
			break;
		case 'theme':
			global $wp_version;
			$file = 'theme'.(version_compare('3.1', $wp_version, '>=') ? '-install' : '').'.php';
			require_once(ABSPATH.'wp-admin/includes/'.$file);
			$data = themes_api('theme_information', array('slug' => $slug));
			break;
	}
	if (is_wp_error($data)) {
		return false;
	}
	else {
		return $data;
	}
}

function cfwpei_cache_data($data, $type) {
	$val = array(
		'timestamp' => time(),
		'data' => $data
	);
	update_option('cfwpei_'.$type.'_'.$data->slug, $val);
}

// API 1.0 supports this plugin data format
/*
stdClass Object
(
    [name] => Popularity Contest
    [slug] => popularity-contest
    [version] => 2.0b2
    [author] => <a href="http://crowdfavorite.com">Crowd Favorite</a>
    [author_profile] => http://wordpress.org/extend/plugins/profile/alexkingorg
    [contributors] => Array
        (
            [alexkingorg] => http://wordpress.org/extend/plugins/profile/alexkingorg
        )

    [requires] => 2.3
    [tested] => 2.8
    [rating] => 68.2
    [num_ratings] => 44
    [downloaded] => 134441
    [last_updated] => 2009-06-25
    [homepage] => http://alexking.org/projects/wordpress
    [sections] => Array
        (
            [description] => {HTML}
            [changelog] => <h4>2.0</h4>
<ul>

<li>Pretty major rewrite, lots of things have changed to work better with recent changes in WordPress.</li>
<li>Now compatible with caching plugins.</li>
<li>Support for tags and tag reports.</li>
<li>Support for tracking search engine visitors differently than direct visitors.</li>
<li>Option to ignore page views by site authors (your own actions on your site don't affect your popularity stats).</li>
<li>Additional options so that there is no need to edit constants in the file directly.</li>
</ul>
            [faq] => {HTML}
        )

    [download_link] => http://downloads.wordpress.org/plugin/popularity-contest.2.0b2.zip
    [tags] => Array
        (
            [stats] => stats
            [statistics] => statistics
            [trackback] => trackback
            [comment] => comment
            [view] => view
            [feedback] => feedback
            [popularity] => popularity
            [popular] => popular
        )

)
*/

// API 1.0 supports this theme data format
/*
stdClass Object
(
    [name] => Carrington Text
    [slug] => carrington-text
    [version] => 1.3
    [author] => alexkingorg
    [preview_url] => http://wp-themes.com/carrington-text
    [screenshot_url] => http://wp-themes.com/wp-content/themes/carrington-text/screenshot.png
    [requires] => 
    [tested] => 
    [rating] => 85.4
    [num_ratings] => 11
    [downloaded] => 
    [last_updated] => 2009-08-18
    [homepage] => http://wordpress.org/extend/themes/carrington-text
    [sections] => Array
        (
            [description] => A simple, text-only theme using the Carrington CMS theme framework.
        )

    [description] => 
    [download_link] => http://wordpress.org/extend/themes/download/carrington-text.1.3.zip
    [tags] => Array
        (
            [threaded-comments] => threaded-comments
            [theme-options] => theme-options
            [fixed-width-1] => fixed-width
            [right-sidebar-2] => right-sidebar
            [two-columns-1] => two-columns
            [light] => light
            [white] => white
        )

)
*/

//a:22:{s:11:"plugin_name";s:21:"CF WP.org Extend Info";s:10:"plugin_uri";s:35:"http://crowdfavorite.com/wordpress/";s:18:"plugin_description";s:60:"Get plugin and theme information from the wordpress.org API.";s:14:"plugin_version";s:3:"1.0";s:6:"prefix";s:6:"cfwpei";s:12:"localization";s:17:"cf-wp-extend-info";s:14:"settings_title";N;s:13:"settings_link";N;s:4:"init";b:0;s:7:"install";b:0;s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";b:0;s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";b:0;s:8:"admin_js";b:0;s:15:"request_handler";b:0;s:6:"snoopy";b:0;s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";b:0;}

?>