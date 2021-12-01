<?php
/**
 * Plugin Class.
 *
 * @since 1.0.0
 *
 * @package TEC\Extensions\Custom_Category_Filter_Groups
 */

namespace TEC\Extensions\Custom_Category_Filter_Groups;

/**
 * Class Plugin
 *
 * @since 1.0.0
 *
 * @package TEC\Extensions\Custom_Category_Filter_Groups
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
	const FILE = TEC_EXTENSION_CUSTOM_CATEGORY_FILTER_GROUPS_FILE;

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
	 * @var int The number of groups to activate.
	 */
	public $number_of_filter_groups = 1;

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
		$this->container->singleton( 'extension.custom_category_filter_groups', $this );
		$this->container->singleton( 'extension.custom_category_filter_groups.plugin', $this );
		$this->container->register( PUE::class );

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		// Start binds.

		// Make it work in v1.
		add_action( 'tribe_events_filters_create_filters', [ $this, 'tec_labs_create_filter' ] );
		// Make it work in v2.
		add_filter( 'tribe_context_locations', [ $this, 'tec_labs_filter_context_locations' ] );
		add_filter( 'tribe_events_filter_bar_context_to_filter_map', [ $this, 'tec_labs_filter_map' ] );

		// End binds.

		$this->number_of_filter_groups = apply_filters( 'tec_labs_custom_category_filter_groups_number', $this->number_of_filter_groups );

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
		$this->container->singleton( 'extension.custom_category_filter_groups.dependencies', $plugin_register );
	}

	/**
	 * Filters the Context locations to let the Context know how to fetch the value of the filter from a request.
	 *
	 * Here we add the `filterbar_category_custom` as a read-only Context location: we'll not need to write it.
	 *
	 * @param array<string,array> $locations A map of the locations the Context supports and is able to read from and write
	 *                                       to.
	 *
	 * @return array<string,array> The filtered map of Context locations, with the one required from the filter added to it.
	 */
	function tec_labs_filter_context_locations( array $locations ) {
		// Read the filter selected values, if any, from the URL request vars.

		for ( $i = 1; $i <= $this->number_of_filter_groups; $i++ ) {
			$locations[ 'filterbar_category_custom_' . $i ] = [
				'read' => [ \Tribe__Context::REQUEST_VAR => 'tribe_filterbar_category_custom_' . $i ],
			];
		}
		/*
		$locations[ 'filterbar_category_custom_1' ] = [
			'read' => [ \Tribe__Context::REQUEST_VAR => 'tribe_filterbar_category_custom_1' ],
		];
		*/
		return $locations;
	}

	/**
	 * Filters the map of filters available on the front-end to include the custom one(s).
	 *
	 * @param array<string,string> $map A map relating the filter slugs to their respective classes.
	 *
	 * @return array<string,string> The filtered slug to filter class map.
	 */
	function tec_labs_filter_map( array $map ) {

		for ( $i = 1; $i <= $this->number_of_filter_groups; $i++ ) {
			$classname = "Category_Custom_{$i}";
			$class     = 'TEC\Extensions\Custom_Category_Filter_Groups\Filters\\' . $classname;
			$path      = plugin_dir_path( __FILE__ ) . "/Filters/{$classname}.php";
			include_once $path;

			$map[ 'filterbar_category_custom_' . $i  ] = $class;
		}

		/*
		include_once plugin_dir_path( __FILE__ ) . '/Filters/Category_Custom_1.php';

		$map[ 'filterbar_category_custom_1' ] = $class;
		*/
		return $map;
	}

	/**
	 * Includes the custom filter class(es) and creates instance(s).
	 */
	function tec_labs_create_filter() {
		if ( ! class_exists( 'Tribe__Events__Filterbar__Filter' ) ) {
			return;
		}


		for ( $i = 1; $i <= $this->number_of_filter_groups; $i++ ) {
			$classname = "Category_Custom_{$i}";
			$class     = 'TEC\Extensions\Custom_Category_Filter_Groups\Filters\\' . $classname;
			$path      = plugin_dir_path( __FILE__ ) . "/Filters/{$classname}.php";

			include_once $path;

			new $class (
				__( "Custom Category Filter {$i}", 'tribe-events-filter-view' ),
				"filterbar_category_custom_{$i}"
			);

		}

		/*
		include_once plugin_dir_path( __FILE__ ) . '/Filters/Category_Custom_1.php';

		new Filters\Category_Custom_1 (
			__( 'Custom Category Filter 1', 'tribe-events-filter-view' ),
			'filterbar_category_custom_1'
		);
		*/
	}

}
