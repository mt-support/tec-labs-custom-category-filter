<?php
/**
 * Abstract Custom Category Filter
 *
 * @since 1.0.0
 */

 namespace TEC\Extensions\Custom_Category_Filter_Groups\Filters;

use Tribe\Events\Filterbar\Views\V2\Filters\Context_Filter;
use Tribe__Events__Main as Main;

/**
 * Class Abstract_Category_Custom
 *
 * @since 1.0.0
 */
abstract class Abstract_Category_Custom extends \Tribe__Events__Filterbar__Filter {
	// Use the trait required for filters to correctly work with Views V2 code.
	use Context_Filter;

	public $type = 'select';

	public $alias = 'custom_category';

	public $wanted_categories = [];

    public $sources = [];

	public function __construct( $name, $slug ) {
		parent::__construct( $name, $slug );
	}

	/**
	 * Return the filter settings form.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_admin_form() {
		$title = $this->get_title_field();
		$type  = $this->get_multichoice_type_field();

		return $title . $type;
	}

	/**
	 * Return a list of possible values for this filter. This should be an array of arrays,
	 * with each inner array structured as follows:
	 *
	 *     [ 'name'  => 'some_name'
	 *       'value' => 'actual_value' ]
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_values() {
		$terms = [];
        $sources = get_terms( Main::TAXONOMY, [ 'orderby' => 'name', 'order' => 'ASC' ] );

		foreach ( $sources as $source ) {
			$terms[] = [
				'name'  => $source->name,
				'slug'  => $source->slug,
				'value' => $source->term_id,
			];
		}

		/**
		 * Filters the categories that should show up in the filter on the front end.
		 *
		 * @var array $wanted_categories
		 */
		$this->wanted_categories = apply_filters( "tec_labs_{$this->alias}_filter_wanted_categories",  $this->wanted_categories );

		if ( empty( $this->wanted_categories ) ) {
			return $terms;
		}

		// Preprocess the terms
		foreach ( $terms as $index => $term ) {
			// If the term is not in the wanted array, then skip it.
			if ( ! in_array( $term['slug'], $this->wanted_categories ) ) {
				unset( $terms[ $index ] );
			}
		}

		return $terms;
	}

	/**
	 * Sets up the filter JOIN clause.
	 *
	 * This will be added to the running events query to add (JOIN) the tables the filter requires.
	 */
	protected function setup_join_clause() {
		add_filter( 'posts_join', [ 'Tribe__Events__Query', 'posts_join' ], 10, 2 );
		global $wpdb;
		$values = $this->currentValue;


		if ( empty( $values ) ) {
			return;
		}

		// object_id, term_taxonomy_id
		$this->joinClause .= " INNER JOIN {$wpdb->term_relationships} as {$this->alias} ON ({$wpdb->posts}.ID = {$this->alias}.object_id)";
	}


	/**
	 * Sets up the filter WHERE clause(s).
	 *
	 * This will be added to the running events query to apply the matching criteria handled by the
	 * custom filter.
	 *
	 * @throws Exception
	 */
	protected function setup_where_clause() {
		global $wpdb;
		$values  = implode( ',', $this->currentValue );
		if ( empty( $values ) ) {
			return;
		}

		$clause = $wpdb->prepare(
			"{$this->alias}.term_taxonomy_id IN (%s)",
			$values
		);

		$this->whereClause .= " AND ( {$clause} )";
	}
}
