<?php
/**
 * Theme Update
 *
 * @package     Astra
 * @author      Astra
 * @copyright   Copyright (c) 2017, Astra
 * @link        http://wpastra.com/
 * @since       Astra 1.0.0
 */

if ( ! class_exists( 'Astra_PB_Compatibility' ) ) {

	/**
	 * Astra_PB_Compatibility initial setup
	 *
	 * @since 1.0.0
	 */
	class Astra_PB_Compatibility {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {

			// Theme Updates.
			add_action( 'wp', array( $this, 'page_builder_compatibility' ) );
		}

		/**
		 * Update options of older version than 1.0.7.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function page_builder_compatibility() {

			$offset_comp = get_option( '_astra_pb_compatibility_offset', false );
			$comp_time   = get_option( '_astra_pb_compatibility_time', false );

			if ( ! $offset_comp || ! $comp_time ) {
				return;
			}

			// Get current post id.
			$current_post_id = get_the_ID();
			$this->update_meta_values( $current_post_id );

			// get all post types.
			$all_post_type = get_post_types(
				array(
					'public' => true,
				)
			);
			unset( $all_post_type['attachment'] );

			// wp_query array.
			$query = array(
				'post_type'      => $all_post_type,
				'posts_per_page' => '30',
				'no_found_rows'  => true,
				'post_status'    => 'any',
				'offset'         => $offset_comp,
				'date_query' => array(
					array(
						'before'    => $comp_time,
						'inclusive' => true,
					),
				),
				'fields'         => 'ids',
			);

			// exicute wp_query.
			$posts = new WP_Query( $query );

			$continue = false;
			foreach ( $posts->posts as $id ) {
				$this->update_meta_values( $id );
				$continue = true;
			}

			if ( $continue ) {
				$offset_comp += 30;
				update_option( '_astra_pb_compatibility_offset', $offset_comp );
			} else {
				delete_option( '_astra_pb_compatibility_offset' );
				delete_option( '_astra_pb_compatibility_time' );
			}
		}

		/**
		 * Update meta values
		 *
		 * @param  int $id Post id.
		 * @return void
		 */
		public function update_meta_values( $id ) {

			$layout_flag = get_post_meta( $id, '_astra_content_layout_flag', true );

			if ( empty( $layout_flag ) ) {
				$site_content = get_post_meta( $id, 'site-content-layout', true );
				$elementor    = get_post_meta( $id, '_elementor_edit_mode', true );
				$vc           = get_post_meta( $id, '_wpb_vc_js_status', true );
				if ( 'page-builder' === $site_content ) {
					update_post_meta( $id, '_astra_content_layout_flag', 'disabled' );
					update_post_meta( $id, 'site-post-title', 'disabled' );
					update_post_meta( $id, 'ast-title-bar-display', 'disabled' );
					update_post_meta( $id, 'site-sidebar-layout', 'no-sidebar' );
				} elseif ( 'builder' === $elementor || true === $vc || 'true' === $vc ) {
					update_post_meta( $id, '_astra_content_layout_flag', 'disabled' );
				}
			}
		}
	}
}// End if().



/**
 * Kicking this off by calling 'get_instance()' method
 */
Astra_PB_Compatibility::get_instance();
