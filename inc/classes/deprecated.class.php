<?php
/**
 * @package The_SEO_Framework\Classes\Deprecated
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) or die;

/**
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }

	/**
	 * HomePage Metabox General Tab Output.
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_general() {
		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_general_tab()' );
		the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'general' );
	}

	/**
	 * HomePage Metabox Additions Tab Output.
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_additions() {
		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_additions_tab()' );
		the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'additions' );
	}

	/**
	 * HomePage Metabox Robots Tab Output
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_robots() {
		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_robots_tab()' );
		the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'robots' );
	}

	/**
	 * Delete transient for the automatic description for blog on save request.
	 * Returns old option, since that's passed for sanitation within WP Core.
	 *
	 * @since 2.3.3
	 *
	 * @deprecated
	 * @since 2.7.0
	 *
	 * @param string $old_option The previous blog description option.
	 * @return string Previous option.
	 */
	public function delete_auto_description_blog_transient( $old_option ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Transients::delete_auto_description_frontpage_transient()' );

		the_seo_framework()->delete_auto_description_transient( the_seo_framework()->get_the_front_page_ID(), '', 'frontpage' );

		return $old_option;
	}

	/**
	 * Add term meta data into options table of the term.
	 * Adds separated database options for terms, as the terms table doesn't allow for addition.
	 *
	 * Applies filters array the_seo_framework_term_meta_defaults : Array of default term SEO options
	 * Applies filters mixed the_seo_framework_term_meta_{field} : Override filter for specifics.
	 * Applies filters array the_seo_framework_term_meta : Override output for term or taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param object $term     Database row object.
	 * @param string $taxonomy Taxonomy name that $term is part of.
	 * @return object $term Database row object.
	 */
	public function get_term_filter( $term, $taxonomy ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "get_term_meta()"' );

		return false;
	}

	/**
	 * Adds The SEO Framework term meta data to functions that return multiple terms.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param array  $terms    Database row objects.
	 * @param string $taxonomy Taxonomy name that $terms are part of.
	 * @return array $terms Database row objects.
	 */
	public function get_terms_filter( array $terms, $taxonomy ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "get_term_meta()"' );

		return false;
	}

	/**
	 * Save taxonomy meta data.
	 * Fires when a user edits and saves a taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Term Taxonomy ID.
	 * @return void Early on AJAX call.
	 */
	public function taxonomy_seo_save( $term_id, $tt_id ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "update_term_meta()"' );

		return false;
	}

	/**
	 * Delete term meta data.
	 * Fires when a user deletes a term.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Taxonomy Term ID.
	 */
	public function term_meta_delete( $term_id, $tt_id ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "delete_term_meta()"' );

		return false;
	}

	/**
	 * Faster way of doing an in_array search compared to default PHP behavior.
	 * @NOTE only to show improvement with large arrays. Might slow down with small arrays.
	 * @NOTE can't do type checks. Always assume the comparing value is a string.
	 *
	 * @since 2.5.2
	 * @since 2.7.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $needle The needle(s) to search for
	 * @param array $array The single dimensional array to search in.
	 * @return bool true if value is in array.
	 */
	public function in_array( $needle, $array, $strict = true ) {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Core::' . __FUNCTION__, '2.7.0', 'in_array()' );

		$array = array_flip( $array );

		if ( is_string( $needle ) ) {
			if ( isset( $array[ $needle ] ) )
				return true;
		} elseif ( is_array( $needle ) ) {
			foreach ( $needle as $str ) {
				if ( isset( $array[ $str ] ) )
					return true;
			}
		}

		return false;
	}

	/**
	 * Fetches posts with exclude_local_search option on
	 *
	 * @since 2.1.7
	 * @since 2.7.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Excluded Post IDs
	 */
	public function exclude_search_ids() {

		the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Search::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Search::get_excluded_search_ids()' );

		return $this->get_excluded_search_ids();
	}

	/**
	 * Fetches posts with exclude_local_search option on.
	 *
	 * @since 2.1.7
	 * @since 2.7.0 No longer used for performance reasons.
	 * @uses $this->exclude_search_ids()
	 * @deprecated
	 * @since 2.8.0
	 *
	 * @param array $query The possible search query.
	 * @return void Early if no search query is found.
	 */
	public function search_filter( $query ) {

		the_seo_framework()->_deprecated_function( 'the_seo_framework()->search_filter()', '2.8.0' );

		// Don't exclude pages in wp-admin.
		if ( $query->is_search && false === the_seo_framework()->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( false === isset( $q['s'] ) )
				return;

			//* Get excluded IDs.
			$protected_posts = $this->get_excluded_search_ids();
			if ( $protected_posts ) {
				$get = $query->get( 'post__not_in' );

				//* Merge user defined query.
				if ( is_array( $get ) && ! empty( $get ) )
					$protected_posts = array_merge( $protected_posts, $get );

				$query->set( 'post__not_in', $protected_posts );
			}

			// Parse all ID's, even beyond the first page.
			$query->set( 'no_found_rows', false );
		}
	}

	/**
	 * Fetches posts with exclude_local_search option on
	 *
	 * @since 2.7.0
	 * @since 2.7.0 No longer used.
	 * @global int $blog_id
	 * @deprecated
	 *
	 * @return array Excluded Post IDs
	 */
	public function get_excluded_search_ids() {

		the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_excluded_search_ids()', '2.7.0' );

		global $blog_id;

		$cache_key = 'exclude_search_ids_' . $blog_id . '_' . get_locale();

		$post_ids = the_seo_framework()->object_cache_get( $cache_key );
		if ( false === $post_ids ) {
			$post_ids = array();

			$args = array(
				'post_type'        => 'any',
				'numberposts'      => -1,
				'posts_per_page'   => -1,
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'meta_key'         => 'exclude_local_search',
				'meta_value'       => 1,
				'meta_compare'     => '=',
				'cache_results'    => true,
				'suppress_filters' => false,
			);
			$get_posts = new \WP_Query;
			$excluded_posts = $get_posts->query( $args );
			unset( $get_posts );

			if ( $excluded_posts )
				$post_ids = wp_list_pluck( $excluded_posts, 'ID' );

			the_seo_framework()->object_cache_set( $cache_key, $post_ids, 86400 );
		}

		// return an array of exclude post IDs
		return $post_ids;
	}
}
