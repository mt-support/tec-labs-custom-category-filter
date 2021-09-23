<?php
/**
 * Plugin Class.
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\Custom_Category_Filter
 */

namespace Tribe\Extensions\Custom_Category_Filter;

/**
 * Class Plugin
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\Custom_Category_Filter
 */
class Plugin extends \tad_DI52_ServiceProvider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'custom-category-filter';

	/**
	 * Stores the base slug for the extension.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILE = TRIBE_EXTENSION_CUSTOM_CATEGORY_FILTER_FILE;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * @since 1.0.0
	 *
	 * @var Settings
	 *
	 * TODO: Remove if not using settings
	 */
	private $settings;

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.custom_category_filter', $this );
		$this->container->singleton( 'extension.custom_category_filter.plugin', $this );
		$this->container->register( PUE::class );

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		// Do the settings.
		// TODO: Remove if not using settings
		$this->get_settings();

		// Start binds.

		// Make it work in v1.
		add_action( 'tribe_events_filters_create_filters', [ $this, 'tec_kb_create_filter' ] );
		// Make it work in v2.
		add_filter( 'tribe_context_locations', [ $this, 'tec_kb_filter_context_locations' ] );
		add_filter( 'tribe_events_filter_bar_context_to_filter_map', [ $this, 'tec_kb_filter_map' ] );

		// End binds.

		$this->container->register( Hooks::class );
		$this->container->register( Assets::class );
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		return tribe_check_plugin( static::class );
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since 1.0.0
	 */
	protected function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'extension.custom_category_filter', $plugin_register );
	}

	/**
	 * Get this plugin's options prefix.
	 *
	 * Settings_Helper will append a trailing underscore before each option.
	 *
	 * @return string
     *
	 * @see \Tribe\Extensions\Custom_Category_Filter\Settings::set_options_prefix()
	 *
	 * TODO: Remove if not using settings
	 */
	private function get_options_prefix() {
		return (string) str_replace( '-', '_', 'tec-labs-custom-category-filter' );
	}

	/**
	 * Get Settings instance.
	 *
	 * @return Settings
	 *
	 * TODO: Remove if not using settings
	 */
	private function get_settings() {
		if ( empty( $this->settings ) ) {
			$this->settings = new Settings( $this->get_options_prefix() );
		}

		return $this->settings;
	}

	/**
	 * Get all of this extension's options.
	 *
	 * @return array
	 *
	 * TODO: Remove if not using settings
	 */
	public function get_all_options() {
		$settings = $this->get_settings();

		return $settings->get_all_options();
	}

	/**
	 * Get a specific extension option.
	 *
	 * @param $option
	 * @param string $default
	 *
	 * @return array
	 *
	 * TODO: Remove if not using settings
	 */
	public function get_option( $option, $default = '' ) {
		$settings = $this->get_settings();

		return $settings->get_option( $option, $default );
	}

	/**
	 * Filters the Context locations to let the Context know how to fetch the value of the filter from a request.
	 *
	 * Here we add the `timeofdaycustom` as a read-only Context location: we'll not need to write it.
	 *
	 * @param array<string,array> $locations A map of the locations the Context supports and is able to read from and write
	 *                                       to.
	 *
	 * @return array<string,array> The filtered map of Context locations, with the one required from the filter added to it.
	 */
	function tec_kb_filter_context_locations( array $locations ) {
		// Read the filter selected values, if any, from the URL request vars.
		$locations['filterbar_category_custom'] = [ 'read' => [ \Tribe__Context::REQUEST_VAR => 'filterbar_category_custom' ], ];
		//$locations['filterbar_category_custom_two'] = [ 'read' => [ \Tribe__Context::REQUEST_VAR => 'filterbar_category_custom_two' ], ];

		return $locations;
	}

	/**
	 * Filters the map of filters available on the front-end to include the custom one.
	 *
	 * @param array<string,string> $map A map relating the filter slugs to their respective classes.
	 *
	 * @return array<string,string> The filtered slug to filter class map.
	 */
	function tec_kb_filter_map( array $map ) {
		if ( ! class_exists( 'Tribe__Events__Filterbar__Filter' ) ) {
			// This would not make much sense, but let's be cautious.
			return $map;
		}

		include_once plugin_dir_path( __FILE__ ) . '/filters/Category_Custom.php';
		//include_once __DIR__ . '/src/Category_Custom_Two.php';

		$map['filterbar_category_custom'] = 'Category_Custom';
		//$map['filterbar_category_custom'] = 'Category_Custom_Two';

		return $map;
	}

	/**
	 * Includes the custom filter class and creates an instance of it.
	 */
	function tec_kb_create_filter() {
		if ( ! class_exists( 'Tribe__Events__Filterbar__Filter' ) ) {
			return;
		}

		include_once plugin_dir_path( __FILE__ ) . '/filters/Category_Custom.php';
		//include_once __DIR__ . '/src/Category_Custom_Two.php';

		new \Category_Custom(
			__( 'Custom Category Filter', 'tribe-events-filter-view' ),
			'filterbar_category_custom'
		);

		/*new \Category_Custom_Two(
			__( 'Custom Category Filter 2', 'tribe-events-filter-view' ),
			'filterbar_category_custom_two'
		);*/
	}

}
