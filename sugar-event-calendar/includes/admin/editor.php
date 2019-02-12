<?php

/**
 * Events Editor
 *
 * @package Plugins/Site/Events/Admin/Editor
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Custom metaboxes above th editor
 *
 * @since 2.0.0
 *
 * @global string $post_type
 * @global array $post
 */
function sugar_calendar_editor_above() {
	global $post_type, $post;

	// Description title
	if ( ! in_array( $post_type, sugar_calendar_allowed_post_types(), true ) ) {
		return;
	}

	// Above editor
	do_meta_boxes( $post_type, 'above_event_editor', $post );
}

/**
 * Custom metaboxes above the editor
 *
 * @since 2.0.0
 *
 * @global string $post_type
 */
function sugar_calendar_editor_below() {
	global $post_type, $post;

	// Description title
	if ( ! in_array( $post_type, sugar_calendar_allowed_post_types(), true ) ) {
		return;
	}

	// Below editor
	do_meta_boxes( $post_type, 'below_event_editor', $post );
}

/**
 * Remove media buttons for custom post types
 *
 * @since 2.0.0
 *
 * @param array $settings
 */
function sugar_calendar_editor_settings( $settings = array() ) {
	$post_type = get_post_type();

	// No buttons on custom post types
	if ( in_array( $post_type, sugar_calendar_allowed_post_types(), true ) ) {
		$settings['media_buttons'] = false;
		$settings['dfw']           = false;
		$settings['teeny']         = true;
		$settings['tinymce']       = false;
		$settings['quicktags']     = false;
	}

	return $settings;
}

/**
 * Maybe remove expanding editor for our post types
 *
 * @since 2.0.0
 *
 * @param boolean $expand
 * @param string  $post_type
 *
 * @return boolean
 */
function sugar_calendar_editor_expand( $expand = true, $post_type = '' ) {

	// No expanding for our post types
	if ( ( true === $expand ) && in_array( $post_type, sugar_calendar_allowed_post_types(), true ) ) {
		$expand = false;
	}

	return $expand;
}
