<?php
/**
 * Plugin Name:       The Events Calendar Extension: Custom Category Filter Groups (v2)
 * Plugin URI:
 * GitHub Plugin URI: https://github.com/mt-support/tec-labs-custom-category-filter-groups
 * Description:
 * Version:           0.9.0
 * Author:            The Events Calendar
 * Author URI:        https://evnt.is/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tec-labs-custom-category-filter
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

/**
 * Define the base file that loaded the plugin for determining plugin path and other variables.
 *
 * @since 1.0.0
 *
 * @var string Base file that loaded the plugin.
 */
define( 'TEC_EXTENSION_CUSTOM_CATEGORY_FILTER_GROUPS_FILE', __FILE__ );

/**
 * Register and load the service provider for loading the extension.
 *
 * @since 1.0.0
 */
function tec_extension_custom_category_filter_groups() {
	// When we don't have autoloader from common we bail.
	if ( ! class_exists( 'Tribe__Autoloader' ) ) {
		return;
	}

	// Register the namespace so we can the plugin on the service provider registration.
	Tribe__Autoloader::instance()->register_prefix(
		'\\TEC\\Extensions\\Custom_Category_Filter_Groups\\',
		__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Tec',
		'custom-category-filter'
	);

	// Deactivates the plugin in case of the main class didn't autoload.
	if ( ! class_exists( '\TEC\Extensions\Custom_Category_Filter_Groups\Plugin' ) ) {
		tribe_transient_notice(
			'custom-category-filter',
			'<p>' . esc_html__( 'Couldn\'t properly load "The Events Calendar Extension: Custom Category Filter Groups" the extension was deactivated.', 'tec-labs-custom-category-filter' ) . '</p>',
			[],
			// 1 second after that make sure the transient is removed.
			1
		);

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		deactivate_plugins( __FILE__, true );
		return;
	}

	tribe_register_provider( '\TEC\Extensions\Custom_Category_Filter_Groups\Plugin' );
}

// Loads after common is already properly loaded.
add_action( 'tribe_common_loaded', 'tec_extension_custom_category_filter_groups' );
