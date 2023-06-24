<?php
/**
 * Handles the Extension plugin dependency manifest registration.
 *
 * @since 1.0.0
 *
 * @package TEC\Extensions\Custom_Category_Filter_Groups
 */

namespace TEC\Extensions\Custom_Category_Filter_Groups;

use Tribe__Abstract_Plugin_Register as Abstract_Plugin_Register;

/**
 * Class Plugin_Register.
 *
 * @since 1.0.0
 *
 * @package TEC\Extensions\Custom_Category_Filter_Groups
 *
 * @see Tribe__Abstract_Plugin_Register For the plugin dependency manifest registration.
 */
class Plugin_Register extends Abstract_Plugin_Register {
	protected $base_dir     = Plugin::FILE;
	protected $version      = Plugin::VERSION;
	protected $main_class   = Plugin::class;
    // We need both The Events Calendar and Filter Bar.
    // Filter Bar itself requires The Events Calendar, so we don't need to add that here.
	protected $dependencies = [
		'parent-dependencies' => [
			'Tribe__Events__Filterbar__View' => '5.5.0',
		],
	];
}
