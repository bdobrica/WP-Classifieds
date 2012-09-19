<?php
/*
Plugin Name: WP Classifieds
Plugin URI: http://grozavesti.info/
Description: WP Classifieds Plugin
Author: Bogdan Dobrica
Version: 0.1
Author URI: http://ublo.ro/
*/

include (dirname(__FILE__).'/libs/class.cls.php');
load_plugin_textdomain (WP_CLS_NAME, false, dirname(plugin_basename( __FILE__ )).'/languages/');

function wp_classifieds () {
	}

function wp_classifieds_cookie () {
	}

/* user filter */

function wp_classifieds_filter ($text) {
	return $text;
	}

/* constructor and destructor */

function wp_classifieds_firstrun () {
	$objects = array (new WP_CLS_Ad (), new WP_CLS_Attachment (), new WP_CLS_Group (), new WP_CLS_User ());
	foreach ($objects as $object) $object->init ();

	add_role ('wp_classified', 'WP Classified', array (
                'read' => true,
                'add_classifieds' => true,
                ));
	}

function wp_classifieds_lastrun () {
	/* cleanup: first objects */
	$objects = array (new WP_CLS_Ad (), new WP_CLS_Attachment (), new WP_CLS_Group (), new WP_CLS_User ());
	foreach ($objects as $object) $object->init (FALSE);

	remove_role ('wp_classified');
	}

/* admin functions */

function wp_classifieds_admin () {
	add_menu_page ('WP Classifieds', 'WP Classifieds', 'publish_posts', 'wp_classifieds', 'wp_classifieds');
	}

function wp_classifieds_scripts () {
	wp_enqueue_script ('fileprogress', WP_CLS_URL . '/scripts/fileprogress.js', array('jquery', 'swfupload', 'swfupload-queue'), '0.1');
	wp_enqueue_script ('underscore', WP_CLS_URL . '/scripts/underscore-min.js', array(), '1.3.3');
	wp_enqueue_script ('wp-classifieds-handlers', WP_CLS_URL . '/scripts/wp-classifieds-handlers.js', array(), '0.1');
	wp_enqueue_script ('wp-classifieds', WP_CLS_URL . '/scripts/wp-classifieds.js', array(), '0.1');
	wp_enqueue_style  ('wp-classifieds', WP_CLS_URL . '/style/wp-classifieds.css', '0.1');
	}

/* hooks, actions, filter */

register_activation_hook (__FILE__, 'wp_classifieds_firstrun');
register_deactivation_hook (__FILE__, 'wp_classifieds_lastrun');

add_action ('get_header', 'wp_classifieds_cookie');

add_action ('wp_enqueue_scripts', 'wp_classifieds_scripts');
add_action ('admin_menu', 'wp_classifieds_admin');

add_filter ('the_content', 'wp_classifieds_filter');
?>
