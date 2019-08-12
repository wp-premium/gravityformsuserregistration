<?php

defined( 'ABSPATH' ) || die();

class GF_Page_Choice_Walker extends Walker_Page {

	/**
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 * @param array $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) { }

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 * @param array $args
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) { }

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page. Used for padding.
	 * @param int $current_page Page ID.
	 * @param array $args
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $current_page = 0 ) {

		if ( $depth ) {
			$indent = str_repeat( "&nbsp;&nbsp;&nbsp;", $depth );
		} else {
			$indent = '';
		}

		if ( '' === $page->post_title ) {
			$page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );
		}

		if ( empty( $output ) ){
			$output = array();
		}
		$output[] = array(
			'label' => $indent . $page->post_title,
			'value' => $page->ID
		);

	}

	/**
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 * @param array $args
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) { }

}