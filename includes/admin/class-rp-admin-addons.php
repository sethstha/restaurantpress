<?php
/**
 * Addons Page
 *
 * @author   WPEverest
 * @category Admin
 * @package  RestaurantPress/Admin
 * @version  1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RP_Admin_Addons Class.
 */
class RP_Admin_Addons {

	/**
	 * Get sections for the addons screen
	 *
	 * @return array of objects
	 */
	public static function get_sections() {
		if ( false === ( $sections = get_transient( 'rp_addons_sections' ) ) ) {
			$raw_sections = wp_safe_remote_get( 'https://gist.githubusercontent.com/shivapoudel/c56a5176412b131283dbdc1c35929fcf/raw/4a053559e4599dc84a30ed2e392c482165a39348/addon-sections.json', array( 'user-agent' => 'RestaurantPress Addons Page' ) );
			if ( ! is_wp_error( $raw_sections ) ) {
				$sections = json_decode( wp_remote_retrieve_body( $raw_sections ) );

				if ( $sections ) {
					set_transient( 'rp_addons_sections', $sections, WEEK_IN_SECONDS );
				}
			}
		}

		$addon_sections = array();

		if ( $sections ) {
			foreach ( $sections as $sections_id => $section ) {
				if ( empty( $sections_id ) ) {
					continue;
				}
				$addon_sections[ $sections_id ]           = new stdClass;
				$addon_sections[ $sections_id ]->title    = rp_clean( $section->title );
				$addon_sections[ $sections_id ]->endpoint = rp_clean( $section->endpoint );
			}
		}

		return apply_filters( 'restaurantpress_addons_sections', $addon_sections );
	}

	/**
	 * Get section for the addons screen.
	 *
	 * @param  string $section_id
	 * @return object|bool
	 */
	public static function get_section( $section_id ) {
		$sections = self::get_sections();
		if ( isset( $sections[ $section_id ] ) ) {
			return $sections[ $section_id ];
		}
		return false;
	}

	/**
	 * Get section content for the addons screen.
	 *
	 * @param  string $section_id
	 * @return array
	 */
	public static function get_section_data( $section_id ) {
		$section      = self::get_section( $section_id );
		$section_data = '';

		if ( ! empty( $section->endpoint ) ) {
			if ( false === ( $section_data = get_transient( 'rp_addons_section_' . $section_id ) ) ) {
				$raw_section = wp_safe_remote_get( esc_url_raw( $section->endpoint ), array( 'user-agent' => 'RestaurantPress Addons Page' ) );

				if ( ! is_wp_error( $raw_section ) ) {
					$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

					if ( ! empty( $section_data->products ) ) {
						set_transient( 'rp_addons_section_' . $section_id, $section_data, WEEK_IN_SECONDS );
					}
				}
			}
		}

		return apply_filters( 'restaurantpress_addons_section_data', $section_data->products, $section_id );
	}

	/**
	 * Handles output of the addons page in admin.
	 */
	public static function output() {
		if ( isset( $_GET['section'] ) && 'helper' === $_GET['section'] ) {
			do_action( 'restaurantpress_helper_output' );
			return;
		}

		if ( isset( $_GET['install-addon'] ) && 'restaurantpress-services' === $_GET['install-addon'] ) {
			self::install_restaurantpress_services_addon();
		}

		$sections        = self::get_sections();
		$theme           = wp_get_theme();
		$section_keys    = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : current( $section_keys );
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-addons.php' );
	}
}
