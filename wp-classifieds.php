<?php
/*
Plugin Name:
Plugin URI:
Description:
Author: Bogdan Dobrica
Version: 0.1
Author URI: http://ublo.ro/
*/

function wp_classifieds () {
	}

function wp_classifieds_cookie () {
	}

/* user actions */

function wp_classifieds_list ($text) {
	return $text;
	}

function wp_classifieds_show ($text) {
	return $text;
	}

function wp_classifieds_new ($text) {
	return $text;
	}

function wp_classifieds_ads ($text) {
	return $text;
	}

function wp_classifieds_profile ($text) {
	return $text;
	}

function wp_classifieds_inbox ($text) {
	return $text;
	}

/* user filter */

function wp_classifieds_filter ($text) {
	$actions = array ('new');
	foreach ($actions as $action)
		if (
			(strpos('[wp_classifieds_'.$action.']', $text) !== FALSE) &&
			function_exists('wp_classifieds_' . $action)
			)
			return call_user_func('wp_classifieds_' . $action, $text);
	return $text;
	}

/* constructor and destructor */

function wp_classifieds_firstrun () {
	$objects = array (new WP_CLS_Ad (), new WP_CLS_Attachment (), new WP_CLS_Group (), new WP_CLS_User ());
	foreach ($objects as $object) $object->init ();

	$pages = array (
		array (
			'name' => 'List Ads',
			'slug' => '[wp_classifieds_list]',
			),
		array (
			'name' => 'Show Ad',
			'slug' => '[wp_classifieds_show]',
			),
		array (
			'name' => 'New Ad',
			'slug' => '[wp_classifieds_new]',
			),
		array (
			'name' => 'My Ads',
			'slug' => '[wp_classifieds_ads]',
			),
		array (
			'name' => 'My Profile',
			'slug' => '[wp_classifieds_profile]'
			),
		array (
			'name' => 'My Inbox',
			'slug' => '[wp_classifieds_inbox]',
			),
		);


	$menu_order = 1;
	$ids = array ();
	foreach ($pages as $page) {
		$ids[] = wp_insert_post ( array (
			'post_title' => $page['name'],
			'menu_order' => $menu_order++,
			'comment_status' => 'closed',
			'post_content' => $page['slug'],
			'post_date' => date('Y-m-d H:i:s'),
			'post_status' => 'publish',
			'post_type' => 'page',
			));
		}
	/* remember the created pages in a non-auto-loadable option -> last 'no' means no-auto-load */
	add_option ('wp_classifieds_pages', $ids, '', 'no');
	}

function wp_classifieds_lastrun () {
	/* cleanup: first objects */
	$objects = array (new WP_CLS_Ad (), new WP_CLS_Attachment (), new WP_CLS_Group (), new WP_CLS_User ());
	foreach ($objects as $object) $object->init (FALSE);

	/* cleanup: delete pages */
	$pages = get_option ('wp_classifieds_pages');
	foreach ($pages as $page) wp_delete_post ($page, true);
	/* cleanup: delete custom option */
	delete_option ('wp_classifieds_pages');
	}

/* admin functions */

function wp_classifieds_admin () {
	add_menu_page ('WP Classifieds', 'WP Classifieds', 'publish_posts', 'wp_classifieds', 'wp_classifieds');
	}

function wp_classifieds_scripts () {
	wp_enqueue_script ('wp-classifieds', WP_CRM_URL . '/scripts/wp-classifieds.js', array('jquery'), '0.1');
	wp_enqueue_style  ('wp-classifieds', WP_CRM_URL . '/style/wp-classifieds.css', '0.1');
	}

/* hooks, actions, filter */

register_activation_hook (__FILE__, 'wp_classifieds_firstrun');
register_deactivation_hook (__FILE__, 'wp_classifieds_lastrun');

add_action ('get_header', 'wp_classifieds_cookie');

add_action ('admin_enqueue_scripts', 'wp_classifieds_scripts');
add_action ('admin_menu', 'wp_classifieds_admin');

add_filter ('the_content', 'wp_classifieds_filter');
?>
