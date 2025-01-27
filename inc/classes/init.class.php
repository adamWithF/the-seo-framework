<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Init
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

/**
 * Class The_SEO_Framework\Init
 *
 * Outputs all data in front-end header
 *
 * @since 2.8.0
 */
class Init extends Query {

	/**
	 * @since 2.4.3
	 * @var bool Enable object caching.
	 */
	protected $use_object_cache = true;

	/**
	 * A true legacy. Ran the plugin on the front-end.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Silently deprecated. Displaying legacy roots.
	 * @deprecated
	 * @ignore
	 */
	public function autodescription_run() {
		$this->init_the_seo_framework();
	}

	/**
	 * Initializes the plugin actions and filters.
	 *
	 * @since 2.8.0
	 */
	public function init_the_seo_framework() {

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized.
		 */
		\do_action( 'the_seo_framework_init' );

		$this->init_global_actions();
		$this->init_global_filters();

		if ( $this->is_admin() ) {
			$this->init_admin_actions();
		} else {
			$this->init_front_end_actions();
			$this->init_front_end_filters();
		}

		/**
		 * @since 3.1.0
		 * Runs after the plugin is initialized.
		 * Use this to remove filters and actions.
		 */
		\do_action( 'the_seo_framework_after_init' );
	}

	/**
	 * Initializes the plugin front- and back-end actions.
	 *
	 * @since 2.8.0
	 */
	public function init_global_actions() {

		if ( \wp_doing_cron() )
			$this->init_cron_actions();
	}

	/**
	 * Initializes the plugin front- and back-end filters.
	 *
	 * @since 2.8.0
	 */
	public function init_global_filters() {
		//* Adjust category link to accommodate primary term.
		\add_filter( 'post_link_category', [ $this, '_adjust_post_link_category' ], 10, 3 );
	}

	/**
	 * Initializes cron actions.
	 *
	 * @since 2.8.0
	 */
	public function init_cron_actions() {
		//* Flush post cache.
		$this->init_post_cache_actions();

		//* Ping searchengines.
		if ( $this->get_option( 'ping_use_cron' ) )
			\add_action( 'tsf_sitemap_cron_hook', '\\The_SEO_Framework\\Bridges\\Ping::ping_search_engines' );
	}

	/**
	 * Initializes Admin Menu actions.
	 *
	 * @since 2.7.0
	 */
	public function init_admin_actions() {

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized in the admin screens.
		 */
		\do_action( 'the_seo_framework_admin_init' );

		//= Initialize caching actions.
		$this->init_admin_caching_actions();

		//= Initialize profile fields.
		$this->init_profile_fields();

		//= Initialize term meta filters and actions.
		$this->init_term_meta();

		//= Initialize the SEO Bar tables.
		$this->init_seo_bar_tables();

		//* Save post data.
		\add_action( 'save_post', [ $this, '_update_post_meta' ], 1, 2 );
		\add_action( 'edit_attachment', [ $this, '_update_attachment_meta' ], 1 );
		\add_action( 'save_post', [ $this, '_save_inpost_primary_term' ], 1, 2 );

		//* Enqueues admin scripts.
		\add_action( 'admin_enqueue_scripts', [ $this, '_init_admin_scripts' ], 0, 1 );

		//* Add plugin links to the plugin activation page.
		\add_filter( 'plugin_action_links_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, [ $this, '_add_plugin_action_links' ], 10, 2 );
		\add_filter( 'plugin_row_meta', [ $this, '_add_plugin_row_meta' ], 10, 2 );

		//* Initialize post states.
		\add_action( 'current_screen', [ $this, 'post_state' ] );

		if ( $this->load_options ) :
			//* Loads setting notices.
			\add_action( 'the_seo_framework_setting_notices', [ $this, '_do_settings_page_notices' ] );

			//* Set up site settings and save/reset them
			\add_action( 'admin_init', [ $this, 'register_settings' ], 5 );

			//* Load the SEO admin page content and handlers.
			\add_action( 'admin_init', [ $this, 'settings_init' ], 10 );

			//* Enqueue Inpost meta boxes.
			\add_action( 'add_meta_boxes', [ $this, 'add_inpost_seo_box_init' ], 5 );

			//* Enqueue Taxonomy meta output.
			\add_action( 'current_screen', [ $this, 'add_taxonomy_seo_box_init' ], 10 );

			//* Prepare quick-edit columns -- required as we need a custom column prior adding bulk/quick-edit fields.
			\add_action( 'current_screen', [ $this, '_prepare_quick_edit_columns' ], 10 );

			// Add Post bulk-edit fields.
			\add_action( 'bulk_edit_custom_box', [ $this, '_display_bulk_edit_fields' ], 10, 2 );

			// Add Post/Term quick-edit fields.
			\add_action( 'quick_edit_custom_box', [ $this, '_display_quick_edit_fields' ], 10, 3 );

			// Add menu links and register $this->seo_settings_page_hook
			\add_action( 'admin_menu', [ $this, 'add_menu_link' ] );

			// Set up notices
			\add_action( 'admin_notices', [ $this, 'notices' ] );

			//* Admin AJAX for counter options.
			\add_action( 'wp_ajax_the_seo_framework_update_counter', [ $this, '_wp_ajax_update_counter_type' ] );

			//* Admin AJAX for TSF Cropper
			\add_action( 'wp_ajax_tsf-crop-image', [ $this, '_wp_ajax_crop_image' ] );
		endif;

		/**
		 * @since 2.9.4
		 * Runs after the plugin is initialized in the admin screens.
		 * Use this to remove actions.
		 */
		\do_action( 'the_seo_framework_after_admin_init' );
	}

	/**
	 * Initializes front-end actions.
	 * Disregards other SEO plugins, the meta output does look at detection.
	 *
	 * WARNING: Do not use query functions here.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_actions() {

		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized on the front-end.
		 */
		\do_action( 'the_seo_framework_front_init' );

		//* Remove canonical header tag from WP
		\remove_action( 'wp_head', 'rel_canonical' );

		//* Remove shortlink.
		\remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		//* Remove adjecent rel tags.
		\remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		//* Earlier removal of the generator tag. Doesn't require filter.
		\remove_action( 'wp_head', 'wp_generator' );

		/**
		 * Outputs sitemap or stylesheet on request.
		 *
		 * Adding a higher priority will cause a trailing slash to be added.
		 * We need to be in front of the queue to prevent this from happening.
		 *
		 * This brings other issues we had to fix. @see $this->validate_sitemap_scheme()
		 */
		if ( $this->can_run_sitemap() )
			\add_action( 'template_redirect', [ $this, '_init_sitemap' ], 1 );

		//* Initialize 301 redirects.
		\add_action( 'template_redirect', [ $this, '_init_custom_field_redirect' ] );

		//* Sets robots headers.
		\add_action( 'template_redirect', [ $this, '_init_robots_headers' ] );
		\add_action( 'the_seo_framework_sitemap_header', [ $this, '_output_robots_noindex_headers' ] );

		//* Output meta tags.
		\add_action( 'wp_head', [ $this, 'html_output' ], 1 );

		if ( $this->get_option( 'alter_archive_query' ) )
			$this->init_alter_archive_query();

		if ( $this->get_option( 'alter_search_query' ) )
			$this->init_alter_search_query();

		//* Alter the content feed.
		\add_filter( 'the_content_feed', [ $this, 'the_content_feed' ], 10, 2 );

		//* Only add the feed link to the excerpt if we're only building excerpts.
		if ( $this->rss_uses_excerpt() )
			\add_filter( 'the_excerpt_rss', [ $this, 'the_content_feed' ], 10, 1 );

		/**
		 * @since 2.9.4
		 * Runs before the plugin is initialized on the front-end.
		 * Use this to remove actions.
		 */
		\do_action( 'the_seo_framework_after_front_init' );
	}

	/**
	 * Runs front-end filters.
	 *
	 * @since 2.5.2
	 */
	protected function init_front_end_filters() {

		//* Overwrite the robots.txt file
		\add_filter( 'robots_txt', [ $this, 'robots_txt' ], 10, 2 );

		/**
		 * @since 2.9.3
		 * @param bool $overwrite_titles Whether to enable title overwriting.
		 */
		if ( \apply_filters( 'the_seo_framework_overwrite_titles', true ) ) {
			//* Removes all pre_get_document_title filters.
			\remove_all_filters( 'pre_get_document_title', false );

			//* New WordPress 4.4.0 filter. Hurray! It's also much faster :)
			\add_filter( 'pre_get_document_title', [ $this, 'get_document_title' ], 10 );
			//* Override WooThemes Title TODO move this to wc compat file.
			\add_filter( 'woo_title', [ $this, 'get_document_title' ], 99 );

			/**
			 * @since 2.4.1
			 * @param bool $overwrite_titles Whether to enable legacy title overwriting.
			 */
			if ( \apply_filters( 'the_seo_framework_manipulate_title', true ) ) {
				\remove_all_filters( 'wp_title', false );
				//* Override WordPress Title
				\add_filter( 'wp_title', [ $this, 'get_wp_title' ], 9, 3 );
			}
		}
	}

	/**
	 * Runs header actions.
	 *
	 * @since 3.1.0
	 *
	 * @param string $location Either 'before' or 'after'.
	 * @return string The filter output.
	 */
	public function get_legacy_header_filters_output( $location = 'before' ) {

		$output = '';

		/**
		 * @since 2.2.6
		 * @param array $functions {
		 *    'callback' => string|array The function to call.
		 *    'args'     => scalar|array Arguments. When array, each key is a new argument.
		 * }
		 */
		$functions = (array) \apply_filters( "the_seo_framework_{$location}_output", [] );

		foreach ( $functions as $function ) {
			if ( ! empty( $function['callback'] ) ) {
				$args = isset( $function['args'] ) ? $function['args'] : '';

				$output .= call_user_func_array( $function['callback'], (array) $args );
			}
		}

		return $output;
	}

	/**
	 * Echos the header meta and scripts.
	 *
	 * @since 1.0.0
	 * @since 2.8.0 Cache is busted on each new release.
	 * @since 3.0.0 Now converts timezone if needed.
	 * @since 3.1.0 1. Now no longer outputs anything on preview.
	 *              2. Now no longer outputs anything on blocked post types.
	 * @since 3.3.0 Now no longer outputs anything on Customizer.
	 * @access private
	 */
	public function html_output() {

		if ( $this->is_preview() || $this->is_customize_preview() || ! $this->query_supports_seo() ) return;

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_before_output' );

		/**
		 * Start the timer here. I know it doesn't calculate the initiation of
		 * the plugin, but it will make the code smelly if I were to do so.
		 * A static array cache counter function would make it possible, but meh.
		 * This function presumably takes the most time anyway.
		 */
		$init_start = microtime( true );

		if ( $this->use_object_cache ) {
			$cache_key = $this->get_meta_output_cache_key_by_query();
			$output    = $this->object_cache_get( $cache_key );
		} else {
			$cache_key = '';
			$output    = false;
		}

		if ( false === $output ) :

			$robots = $this->robots();

			/**
			 * @since 2.6.0
			 * @param string $before The content before the SEO output. Stored in object cache.
			 */
			$before = (string) \apply_filters( 'the_seo_framework_pre', '' );

			$before_legacy = $this->get_legacy_header_filters_output( 'before' );

			//* Limit processing and redundant tags on 404 and search.
			if ( $this->is_search() ) :
				$output = $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_url()
						. $this->og_sitename()
						. $this->shortlink()
						. $this->canonical()
						. $this->paged_urls()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			elseif ( $this->is_404() ) :
				$output = $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();
			else :
				$set_timezone = $this->uses_time_in_timestamp_format() && ( $this->output_published_time() || $this->output_modified_time() );
				$set_timezone and $this->set_timezone();

				$output = $this->the_description()
						. $this->og_image()
						. $this->og_locale()
						. $this->og_type()
						. $this->og_title()
						. $this->og_description()
						. $this->og_url()
						. $this->og_sitename()
						. $this->facebook_publisher()
						. $this->facebook_author()
						. $this->facebook_app_id()
						. $this->article_published_time()
						. $this->article_modified_time()
						. $this->twitter_card()
						. $this->twitter_site()
						. $this->twitter_creator()
						. $this->twitter_title()
						. $this->twitter_description()
						. $this->twitter_image()
						. $this->shortlink()
						. $this->canonical()
						. $this->paged_urls()
						. $this->ld_json()
						. $this->google_site_output()
						. $this->bing_site_output()
						. $this->yandex_site_output()
						. $this->pint_site_output();

				$set_timezone and $this->reset_timezone();
			endif;

			$after_legacy = $this->get_legacy_header_filters_output( 'after' );

			/**
			 * @since 2.6.0
			 * @param string $after The content after the SEO output. Stored in object cache.
			 */
			$after = (string) \apply_filters( 'the_seo_framework_pro', '' );

			$output = $robots . $before . $before_legacy . $output . $after_legacy . $after;

			$this->use_object_cache and $this->object_cache_set( $cache_key, $output, DAY_IN_SECONDS );
		endif;

		$output = $this->get_plugin_indicator( 'before' )
				. $output
				. $this->get_plugin_indicator( 'after', $init_start );

		// phpcs:ignore, WordPress.Security.EscapeOutput -- $output is escaped.
		echo PHP_EOL . $output . PHP_EOL;

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_after_output' );
	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.9.0
	 * @since 3.1.0: 1. Now no longer redirects on preview.
	 *               2. Now listens to post type settings.
	 * @since 3.3.0: 1. No longer tries to redirect on "search".
	 *               2. Added term redirect support.
	 *               3. No longer redirects on Customizer.
	 * @access private
	 *
	 * @return void early on non-singular pages.
	 */
	public function _init_custom_field_redirect() {

		if ( $this->is_preview() || $this->is_customize_preview() || ! $this->query_supports_seo() ) return;

		$url = '';

		if ( $this->is_singular() ) {
			$url = $this->get_post_meta_item( 'redirect' ) ?: '';
		} elseif ( $this->is_term_meta_capable() ) {
			$term_meta = $this->get_current_term_meta();

			if ( isset( $term_meta['redirect'] ) )
				$url = $term_meta['redirect'] ?: '';
		}

		$url and $this->do_redirect( $url );
	}

	/**
	 * Redirects vistor to input $url.
	 *
	 * @since 2.9.0
	 *
	 * @param string $url The redirection URL
	 * @return void Early if no URL is supplied.
	 */
	public function do_redirect( $url = '' ) {

		if ( 'template_redirect' !== \current_action() ) {
			$this->_doing_it_wrong( __METHOD__, 'Only use this method on action "template_redirect".', '2.9.0' );
			return;
		}

		//= All WP defined protocols are allowed.
		$url = \esc_url_raw( $url );

		if ( empty( $url ) ) {
			$this->_doing_it_wrong( __METHOD__, 'You need to supply an input URL.', '2.9.0' );
			return;
		}

		/**
		 * @since 2.8.0
		 * @param int <unsigned> $redirect_type
		 */
		$redirect_type = \absint( \apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

		if ( $redirect_type > 399 || $redirect_type < 300 )
			$this->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

		if ( ! $this->allow_external_redirect() ) {
			//= Only HTTP/HTTPS and home URLs are allowed.
			$path = $this->set_url_scheme( $url, 'relative' );
			$url  = \trailingslashit( $this->get_home_host() ) . ltrim( $path, ' /' );

			$scheme = $this->is_ssl() ? 'https' : 'http';

			\wp_safe_redirect( $this->set_url_scheme( $url, $scheme ), $redirect_type );
			exit;
		}

		// phpcs:ignore, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- intended feature. Disable via $this->allow_external_redirect().
		\wp_redirect( $url, $redirect_type );
		exit;
	}

	/**
	 * Prepares sitemap output.
	 *
	 * @since 3.3.0
	 * @access private
	 */
	public function _init_sitemap() {
		Bridges\Sitemap::get_instance()->_init();
	}

	/**
	 * Edits the robots.txt output.
	 * Requires not to have a robots.txt file in the root directory.
	 *
	 * This methods completely hijacks default output, intentionally.
	 * The robots.txt file should be left as default, so to improve SEO.
	 * The Robots Exclusion Protocol encourages you not to use this file for
	 * non-administrative endpoints.
	 *
	 * @since 2.2.9
	 * @since 2.9.3 Casts $public to string for check.
	 * @uses robots_txt filter located at WP core
	 *
	 * @param string $robots_txt The current robots_txt output.
	 * @param string $public The blog_public option value.
	 * @return string Robots.txt output.
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {

		/**
		 * Don't do anything if the blog isn't public.
		 */
		if ( '0' === (string) $public )
			return $robots_txt;

		if ( $this->use_object_cache ) {
			$cache_key = $this->get_robots_txt_cache_key();
			$output    = $this->object_cache_get( $cache_key );
		} else {
			$output = false;
		}

		if ( false === $output ) :
			$output = '';

			if ( $this->is_subdirectory_installation() ) {
				$output .= '# This is an invalid robots.txt location.' . "\r\n";
				$output .= '# Please visit: ' . \esc_url( \trailingslashit( $this->set_preferred_url_scheme( $this->get_home_host() ) ) . 'robots.txt' ) . "\r\n";
				$output .= "\r\n";
			}

			$site_url  = \wp_parse_url( \site_url() );
			$site_path = ( ! empty( $site_url['path'] ) ) ? \esc_attr( $site_url['path'] ) : '';

			/**
			 * @since 2.5.0
			 * @param string $pre The output before this plugin's output.
			 *                    Don't forget to add line breaks ( "\r\n" || PHP_EOL )!
			 */
			$output .= (string) \apply_filters( 'the_seo_framework_robots_txt_pre', '' );

			//* Output defaults
			$output .= "User-agent: *\r\n";
			$output .= "Disallow: $site_path/wp-admin/\r\n";
			$output .= "Allow: $site_path/wp-admin/admin-ajax.php\r\n";

			/**
			 * @since 2.5.0
			 * @param bool $disallow Whether to disallow robots queries.
			 */
			if ( \apply_filters( 'the_seo_framework_robots_disallow_queries', false ) ) {
				$output .= "Disallow: /*?*\r\n";
			}

			/**
			 * @since 2.5.0
			 * @param string $pro The output after this plugin's output.
			 *                    Don't forget to add line breaks ( "\r\n" || PHP_EOL )!
			 */
			$output .= (string) \apply_filters( 'the_seo_framework_robots_txt_pro', '' );

			//* Add extra whitespace and sitemap full URL
			if ( $this->can_do_sitemap_robots( true ) ) {
				$sitemaps = Bridges\Sitemap::get_instance();
				foreach ( $sitemaps->get_sitemap_endpoint_list() as $id => $data ) {
					if ( ! empty( $data['robots'] ) ) {
						$output .= sprintf( "\r\nSitemap: %s", \esc_url( $sitemaps->get_expected_sitemap_endpoint_url( $id ) ) );
					}
				}
				$output .= "\r\n";
			}

			$this->use_object_cache and $this->object_cache_set( $cache_key, $output, 86400 );
		endif;

		// Completely override robots with output.
		$robots_txt = $output;

		return $robots_txt;
	}

	/**
	 * Adjusts the X-Robots tag headers.
	 *
	 * @since 3.3.0
	 * @access private
	 */
	public function _init_robots_headers() {

		if ( $this->is_feed() || $this->is_robots() ) {
			$this->_output_robots_noindex_headers();
		}
	}

	/**
	 * Sets the X-Robots tag headers to 'noindex, follow'.
	 *
	 * @since 3.3.0
	 * @access private
	 */
	public function _output_robots_noindex_headers() {

		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, follow', true );
		}
	}

	/**
	 * Initializes search query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_search_query() {
		switch ( $this->get_option( 'alter_search_query_type' ) ) :
			case 'post_query':
				\add_filter( 'the_posts', [ $this, '_alter_search_query_post' ], 10, 2 );
				break;

			default:
			case 'in_query':
				\add_action( 'pre_get_posts', [ $this, '_alter_search_query_in' ], 9999, 1 );
				break;
		endswitch;
	}

	/**
	 * Initializes archive query adjustments.
	 *
	 * @since 2.9.4
	 */
	public function init_alter_archive_query() {
		switch ( $this->get_option( 'alter_archive_query_type' ) ) :
			case 'post_query':
				\add_filter( 'the_posts', [ $this, '_alter_archive_query_post' ], 10, 2 );
				break;

			default:
			case 'in_query':
				\add_action( 'pre_get_posts', [ $this, '_alter_archive_query_in' ], 9999, 1 );
				break;
		endswitch;
	}

	/**
	 * Alters search query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if no search query is found.
	 */
	public function _alter_search_query_in( $wp_query ) {

		// Don't exclude pages in wp-admin.
		if ( $wp_query->is_search ) {
			//* Only interact with an actual Search Query.
			if ( ! isset( $wp_query->query['s'] ) )
				return;

			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = $this->get_ids_excluded_from_search();

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_merge( (array) $post__not_in, $excluded );
				$excluded = array_unique( $excluded );
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}

	/**
	 * Alters archive query.
	 *
	 * @since 2.9.4
	 * @since 3.0.0 Exchanged meta query for post__not_in query.
	 * @see Twenty Fourteen theme @source \Featured_Content::pre_get_posts()
	 * @access private
	 *
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return void Early if query alteration is useless or blocked.
	 */
	public function _alter_archive_query_in( $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return;

			$excluded = $this->get_ids_excluded_from_archive();

			if ( ! $excluded )
				return;

			$post__not_in = $wp_query->get( 'post__not_in' );

			if ( ! empty( $post__not_in ) ) {
				$excluded = array_merge( (array) $post__not_in, $excluded );
				$excluded = array_unique( $excluded );
			}

			$wp_query->set( 'post__not_in', $excluded );
		}
	}

	/**
	 * Alters search results after database query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_search_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_search ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_post_meta_item( 'exclude_local_search', $post->ID ) ) {
					unset( $posts[ $n ] );
				}
			}
			//= Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Alters archive results after database query.
	 *
	 * @since 2.9.4
	 * @access private
	 *
	 * @param array     $posts    The array of retrieved posts.
	 * @param \WP_Query $wp_query The WP_Query instance.
	 * @return array $posts
	 */
	public function _alter_archive_query_post( $posts, $wp_query ) {

		if ( $wp_query->is_archive || $wp_query->is_home ) {
			if ( $this->is_archive_query_adjustment_blocked( $wp_query ) )
				return $posts;

			foreach ( $posts as $n => $post ) {
				if ( $this->get_post_meta_item( 'exclude_from_archive', $post->ID ) ) {
					unset( $posts[ $n ] );
				}
			}
			//= Reset numeric index.
			$posts = array_values( $posts );
		}

		return $posts;
	}

	/**
	 * Determines whether the archive query adjustment is blocked.
	 *
	 * @since 2.9.4
	 * @since 3.1.0 Now checks for the post type.
	 *
	 * @param \WP_Query $wp_query WP_Query object.
	 * @return bool
	 */
	protected function is_archive_query_adjustment_blocked( $wp_query ) {

		static $has_filter = null;

		$blocked = false;

		if ( null === $has_filter ) {
			$has_filter = \has_filter( 'the_seo_framework_do_adjust_archive_query' );
		}
		if ( $has_filter ) {
			/**
			 * @since 2.9.4
			 * @param bool      $do       True is unblocked (do adjustment), false is blocked (don't do adjustment).
			 * @param \WP_Query $wp_query The current query. Passed by reference.
			 */
			if ( ! \apply_filters_ref_array( 'the_seo_framework_do_adjust_archive_query', [ true, $wp_query ] ) )
				$blocked = true;
		}

		if ( isset( $wp_query->query_vars->post_type ) )
			$blocked = $this->is_post_type_disabled( $wp_query->query_vars->post_type );

		return $blocked;
	}
}
