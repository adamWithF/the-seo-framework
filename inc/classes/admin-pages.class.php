<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Admin_Pages
 * @subpackage The_SEO_Framework\Admin\Settings
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
 * Class The_SEO_Framework\Site_Options
 *
 * Renders admin pages content for this plugin.
 *
 * @since 2.8.0
 */
class Admin_Pages extends Edit {

	/**
	 * @since 2.7.0
	 * @var string $seo_settings_page_hook The page hook_suffix added via WP add_menu_page()
	 */
	public $seo_settings_page_hook;

	/**
	 * @since 2.6.0
	 * @var bool $load_options Determines whether to load the options.
	 */
	public $load_options;

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 *
	 * @return void Early if method is already called.
	 */
	public function add_menu_link() {

		if ( _has_run( __METHOD__ ) ) return;

		$menu = [
			'page_title' => \esc_html__( 'SEO Settings', 'autodescription' ),
			'menu_title' => \esc_html__( 'SEO', 'autodescription' ),
			'capability' => $this->get_settings_capability(),
			'menu_slug'  => $this->seo_settings_page_slug,
			'callback'   => [ $this, '_output_seo_settings_wrap' ],
			'icon'       => 'dashicons-search',
			'position'   => '90.9001',
		];

		$this->seo_settings_page_hook = \add_menu_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position']
		);

		/**
		 * Simply copy the previous, but rename the submenu entry.
		 * The function add_submenu_page() takes care of the duplications.
		 */
		\add_submenu_page(
			$menu['menu_slug'],
			$menu['page_title'],
			$menu['page_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback']
		);

		//* Enqueue scripts
		\add_action( 'admin_print_scripts-' . $this->seo_settings_page_hook, [ $this, '_init_admin_scripts' ], 11 );
	}

	/**
	 * Initialize the settings page.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Handled settings POST initialization.
	 */
	public function settings_init() {

		//* Handle post-update actions. Must be initialized on admin_init and is initalized on options.php.
		if ( 'options.php' === $GLOBALS['pagenow'] )
			$this->handle_update_post();

		//* Output metaboxes.
		\add_action( $this->seo_settings_page_hook . '_settings_page_boxes', [ $this, '_output_seo_settings_columns' ] );
		\add_action( 'load-' . $this->seo_settings_page_hook, [ $this, '_register_seo_settings_metaboxes' ] );
	}

	/**
	 * Outputs SEO Settings page wrap.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function _output_seo_settings_wrap() {
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pre_seo_settings' );
		$this->get_view( 'admin/seo-settings-wrap', get_defined_vars() );
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pro_seo_settings' );
	}

	/**
	 * Outputs SEO Settings columns.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function _output_seo_settings_columns() {
		$this->get_view( 'admin/seo-settings-columns', get_defined_vars() );
	}

	/**
	 * Registers meta boxes on the Site SEO Settings page.
	 *
	 * @since 3.0.0
	 * @access private
	 * @see $this->general_metabox()     Callback for General Settings box.
	 * @see $this->title_metabox()       Callback for Title Settings box.
	 * @see $this->description_metabox() Callback for Description Settings box.
	 * @see $this->robots_metabox()      Callback for Robots Settings box.
	 * @see $this->homepage_metabox()    Callback for Homepage Settings box.
	 * @see $this->social_metabox()      Callback for Social Settings box.
	 * @see $this->schema_metabox()      Callback for Schema Settings box.
	 * @see $this->webmaster_metabox()   Callback for Webmaster Settings box.
	 * @see $this->sitemaps_metabox()    Callback for Sitemap Settings box.
	 * @see $this->feed_metabox()        Callback for Feed Settings box.
	 */
	public function _register_seo_settings_metaboxes() {

		/**
		 * Various metabox filters.
		 * Set any to false if you wish the meta box to be removed.
		 *
		 * @since 2.2.4
		 * @since 2.8.0: Added `the_seo_framework_general_metabox` filter.
		 */
		$general     = (bool) \apply_filters( 'the_seo_framework_general_metabox', true );
		$title       = (bool) \apply_filters( 'the_seo_framework_title_metabox', true );
		$description = (bool) \apply_filters( 'the_seo_framework_description_metabox', true );
		$robots      = (bool) \apply_filters( 'the_seo_framework_robots_metabox', true );
		$home        = (bool) \apply_filters( 'the_seo_framework_home_metabox', true );
		$social      = (bool) \apply_filters( 'the_seo_framework_social_metabox', true );
		$schema      = (bool) \apply_filters( 'the_seo_framework_schema_metabox', true );
		$webmaster   = (bool) \apply_filters( 'the_seo_framework_webmaster_metabox', true );
		$sitemap     = (bool) \apply_filters( 'the_seo_framework_sitemap_metabox', true );
		$feed        = (bool) \apply_filters( 'the_seo_framework_feed_metabox', true );

		//* Title Meta Box
		if ( $general )
			\add_meta_box(
				'autodescription-general-settings',
				\esc_html__( 'General Settings', 'autodescription' ),
				[ $this, 'general_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Title Meta Box
		if ( $title )
			\add_meta_box(
				'autodescription-title-settings',
				\esc_html__( 'Title Settings', 'autodescription' ),
				[ $this, 'title_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Description Meta Box
		if ( $description )
			\add_meta_box(
				'autodescription-description-settings',
				\esc_html__( 'Description Meta Settings', 'autodescription' ),
				[ $this, 'description_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Homepage Meta Box
		if ( $home )
			\add_meta_box(
				'autodescription-homepage-settings',
				\esc_html__( 'Homepage Settings', 'autodescription' ),
				[ $this, 'homepage_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Social Meta Box
		if ( $social )
			\add_meta_box(
				'autodescription-social-settings',
				\esc_html__( 'Social Meta Settings', 'autodescription' ),
				[ $this, 'social_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Title Meta Box
		if ( $schema )
			\add_meta_box(
				'autodescription-schema-settings',
				\esc_html__( 'Schema.org Settings', 'autodescription' ),
				[ $this, 'schema_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Robots Meta Box
		if ( $robots )
			\add_meta_box(
				'autodescription-robots-settings',
				\esc_html__( 'Robots Meta Settings', 'autodescription' ),
				[ $this, 'robots_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Webmaster Meta Box
		if ( $webmaster )
			\add_meta_box(
				'autodescription-webmaster-settings',
				\esc_html__( 'Webmaster Meta Settings', 'autodescription' ),
				[ $this, 'webmaster_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Sitemaps Meta Box
		if ( $sitemap )
			\add_meta_box(
				'autodescription-sitemap-settings',
				\esc_html__( 'Sitemap Settings', 'autodescription' ),
				[ $this, 'sitemaps_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);

		//* Feed Meta Box
		if ( $feed )
			\add_meta_box(
				'autodescription-feed-settings',
				\esc_html__( 'Feed Settings', 'autodescription' ),
				[ $this, 'feed_metabox' ],
				$this->seo_settings_page_hook,
				'main',
				[]
			);
	}

	/**
	 * Initializes and outputs various notices.
	 *
	 * @since 2.2.2
	 * @since 3.1.0 1. Added seo plugin check.
	 *              2. Now marked private.
	 * @access private
	 */
	public function notices() {

		if ( $this->get_static_cache( 'check_seo_plugin_conflicts' ) && \current_user_can( 'activate_plugins' ) ) {
			$this->detect_seo_plugins()
				and $this->do_dismissible_notice(
					\__( 'Multiple SEO tools have been detected. You should only use one.', 'autodescription' ),
					'warning'
				);
			$this->update_static_cache( 'check_seo_plugin_conflicts', 0 );
		}
	}

	/**
	 * Outputs notices on SEO setting changes.
	 *
	 * @since 3.3.0
	 * @access private
	 */
	public function _do_settings_page_notices() {

		$notice = $this->get_static_cache( 'settings_notice' );

		if ( ! $notice ) return;

		$message = '';
		$type    = '';

		switch ( $notice ) {
			case 'updated':
				$message = \__( 'SEO settings are saved, and the caches have been flushed.', 'autodescription' );
				$type    = 'updated';
				break;

			case 'unchanged':
				$message = \__( 'No SEO settings were changed, but the caches have been flushed.', 'autodescription' );
				$type    = 'warning';
				break;

			case 'reset':
				$message = \__( 'SEO settings are reset, and the caches have been flushed.', 'autodescription' );
				$type    = 'warning';
				break;

			case 'error':
				$message = \__( 'An unknown error occurred saving SEO settings.', 'autodescription' );
				$type    = 'error';
				break;
		}

		$this->update_static_cache( 'settings_notice', '' );

		$message and $this->do_dismissible_notice( $message, $type ?: 'updated' );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 2.2.2
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function get_field_name( $name ) {
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 2.2.2
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		echo \esc_attr( $this->get_field_name( $name ) );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 2.2.2
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function get_field_id( $id ) {
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 2.2.2
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id Field id base.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {
		if ( $echo ) {
			echo \esc_attr( $this->get_field_id( $id ) );
		} else {
			return $this->get_field_id( $id );
		}
	}

	/**
	 * Generates dismissible notice.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 The messages are no longer auto-styled to "strong".
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool   $a11y Whether to add an accessibility icon.
	 * @param bool   $escape Whether to escape the whole output.
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {

		if ( empty( $message ) ) return '';

		//* Make sure the scripts are loaded.
		$this->init_admin_scripts();

		\The_SEO_Framework\Builders\Scripts::enqueue();

		if ( 'warning' === $type )
			$type = 'notice-warning';

		$a11y = $a11y ? 'tsf-show-icon' : '';

		return vsprintf(
			'<div class="notice %s tsf-notice %s"><p>%s%s</p></div>',
			[
				\esc_attr( $type ),
				( $a11y ? 'tsf-show-icon' : '' ),
				sprintf(
					'<a class="hide-if-no-tsf-js tsf-dismiss" title="%s" %s></a>',
					\esc_attr__( 'Dismiss', 'autodescription' ),
					''
				),
				( $escape ? \esc_html( $message ) : $message ),
			]
		);
	}

	/**
	 * Echos generated dismissible notice.
	 *
	 * @since 2.7.0
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $type    The notice type : 'updated', 'error', 'warning'. Expected to be escaped.
	 * @param bool   $a11y    Whether to add an accessibility icon.
	 * @param bool   $escape  Whether to escape the whole output.
	 */
	public function do_dismissible_notice( $message = '', $type = 'updated', $a11y = true, $escape = true ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $escape
		echo $this->generate_dismissible_notice( $message, $type, (bool) $a11y, (bool) $escape );
	}

	/**
	 * Generates dismissible notice that stick until the user dismisses it.
	 * Also loads scripts and styles if out of The SEO Framework's context.
	 *
	 * @since 2.9.3
	 * @see $this->do_dismissible_sticky_notice()
	 * @uses THE_SEO_FRAMEWORK_UPDATES_CACHE
	 * @todo make this do something.
	 * @ignore
	 * NOTE: This method is a placeholder.
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array  $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 * @return string The dismissible error notice.
	 */
	public function generate_dismissible_sticky_notice( $message, $key, $args = [] ) {
		return '';
	}

	/**
	 * Echos generated dismissible sticky notice.
	 *
	 * @since 2.9.3
	 * @uses $this->generate_dismissible_sticky_notice()
	 * @ignore
	 *
	 * @param string $message The notice message. Expected to be escaped if $escape is false.
	 * @param string $key     The notice key. Must be unique and tied to the stored updates cache option.
	 * @param array  $args : {
	 *    'type'   => string Optional. The notification type. Default 'updated'.
	 *    'a11y'   => bool   Optional. Whether to enable accessibility. Default true.
	 *    'escape' => bool   Optional. Whether to escape the $message. Default true.
	 *    'color'  => string Optional. If filled in, it will output the selected color. Default ''.
	 *    'icon'   => string Optional. If filled in, it will output the selected icon. Default ''.
	 * }
	 */
	public function do_dismissible_sticky_notice( $message, $key, $args = [] ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput -- use $args['escape']
		echo $this->generate_dismissible_sticky_notice( $message, $key, $args );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return $this->code_wrap_noesc( \esc_html( $content ) );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description( $content, $block = true ) {
		$this->description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 2.7.0
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description_noesc( $content, $block = true ) {
		$output = '<span class="description">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Mark up content in attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the attention wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention( $content, $block = true ) {
		$this->attention_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the attention wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_noesc( $content, $block = true ) {
		$output = '<span class="attention">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Mark up content in a description+attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description( $content, $block = true ) {
		$this->attention_description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description_noesc( $content, $block = true ) {
		$output = '<span class="description attention">' . $content . '</span>';
		// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
		echo $block ? '<p>' . $output . '</p>' : $output;
	}

	/**
	 * Google docs language determinator.
	 *
	 * @since 2.2.2
	 * @staticvar string $language
	 *
	 * @return string language code
	 */
	protected function google_language() {

		static $language = null;

		if ( isset( $language ) ) return $language;

		/* translators: Language shorttag to be used in Google help pages. */
		return $language = \esc_html_x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * This method does NOT escape.
	 *
	 * @since 2.6.0
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool   $echo  Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {

		if ( is_array( $input ) )
			$input = implode( PHP_EOL, $input );

		if ( $echo ) {
			echo '<div class="tsf-fields">' . $input . '</div>'; // phpcs:ignore -- Escape your $input prior!
		} else {
			return '<div class="tsf-fields">' . $input . '</div>';
		}
	}

	/**
	 * Makes simple and JSON-encoded data-* tags for HTML elements.
	 *
	 * @since 3.3.0
	 * @internal
	 *
	 * @param array $data : Expected to be escaped! {
	 *    string $k => array|string $v
	 * }
	 * @return string The HTML data tags.
	 */
	public function get_field_data( array $data ) {

		$ret = '';
		foreach ( $data as $k => $v ) {
			$k = strtolower( preg_replace( '/[A-Z]/', '-$0', $k ) );
			if ( is_array( $v ) ) {
				//* NOTE: Using single quotes.
				$ret .= sprintf( " data-%s='%s'", $k, json_encode( $v, JSON_UNESCAPED_SLASHES ) );
			} else {
				$ret .= sprintf( ' data-%s="%s"', $k, $v );
			}
		}

		return $ret;
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 * @since 3.0.3 Added $disabled parameter. Defaults to false.
	 * @see $this->make_checkbox_array()
	 *
	 * @param string $field_id    The option ID. Must be within the Autodescription settings.
	 * @param string $label       The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool   $escape      Whether to escape the label and description.
	 * @param bool   $disabled    Whether to disable the input.
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true, $disabled = false ) {
		return $this->make_checkbox_array( [
			'id'          => $field_id,
			'index'       => '',
			'label'       => $label,
			'description' => $description,
			'escape'      => $escape,
			'disabled'    => $disabled,
		] );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 3.1.0
	 *
	 * @param array $args : {
	 *    string $id          The option name, used as field ID.
	 *    string $index       The option index, used when the option is an array.
	 *    string $label       The checkbox label description, placed inline of the checkbox.
	 *    string $description The checkbox additional description, placed underneat.
	 *    bool   $escape      Whether to enable escaping of the $label and $description.
	 *    bool   $disabled    Whether to disable the checkbox field.
	 *    bool   $default     Whether to display-as-default. This is autodetermined when no $index is set.
	 *    bool   $warned      Whether to warn the checkbox field value.
	 * }
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox_array( array $args = [] ) {

		$args = array_merge(
			[
				'id'          => '',
				'index'       => '',
				'label'       => '',
				'description' => '',
				'escape'      => true,
				'disabled'    => false,
				'default'     => false,
				'warned'      => false,
			],
			$args
		);

		if ( $args['escape'] ) {
			$args['description'] = \esc_html( $args['description'] );
			$args['label']       = \esc_html( $args['label'] );
		}

		$index = $this->s_field_id( $args['index'] ?: '' );

		$field_id = $field_name = \esc_attr( sprintf(
			'%s%s',
			$this->get_field_id( $args['id'] ),
			$index ? sprintf( '[%s]', $index ) : ''
		) );

		$value = $this->get_option( $args['id'] );
		if ( $index ) {
			$value = isset( $value[ $index ] ) ? $value[ $index ] : '';
		}

		$cb_class = '';
		if ( $args['disabled'] ) {
			$cb_class = 'tsf-disabled';
		} elseif ( ! $args['index'] ) {
			// Can't fetch conditionals in index.
			$cb_class = $this->get_is_conditional_checked( $args['id'], false );
		} else {
			if ( $args['default'] ) {
				$cb_class = 'tsf-default-selected';
			} elseif ( $args['warned'] ) {
				$cb_class = 'tsf-warning-selected';
			}
		}

		$output = sprintf(
			'<span class="tsf-toblock">%s</span>',
			vsprintf(
				'<label for="%s" %s>%s</label>',
				[
					$field_id,
					( $args['disabled'] ? 'class="tsf-disabled"' : '' ),
					vsprintf(
						'<input type=checkbox class="%s" name="%s" id="%s" value="1" %s %s /> %s',
						[
							$cb_class,
							$field_name,
							$field_id,
							\checked( $value, true, false ),
							( $args['disabled'] ? 'disabled' : '' ),
							$args['label'],
						]
					),
				]
			)
		);

		$output .= $args['description'] ? sprintf( '<p class="description tsf-option-spacer">%s</p>', $args['description'] ) : '';

		return $output;
	}

	/**
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 3.3.0
	 *
	 * @param array $args : {
	 *    string     $id       The select field ID.
	 *    string     $class    The div wrapper class.
	 *    string     $name     The option name.
	 *    int|string $default  The current option value.
	 *    array      $options  The select option values : { value => name }
	 *    string     $label    The option label.
	 *    string     $required Whether the field must be required.
	 *    array      $data     The select field data. Sub-items are expected to be escaped if they're not an array.
	 *    array      $info     Extra info field data.
	 * }
	 * @return string The option field.
	 */
	public function make_single_select_form( array $args ) {

		$defaults = [
			'id'       => '',
			'class'    => '',
			'name'     => '',
			'default'  => '',
			'options'  => [],
			'label'    => '',
			'required' => false,
			'data'     => [],
			'info'     => [],
		];

		$args = array_merge( $defaults, $args );

		// The walk below destroys the option array. As such, we assigned a new value.
		$html_options = $args['options'];

		array_walk(
			$html_options,
			/**
			 * @param string $name    The option name. Passed by reference, returned as the HTML option item.
			 * @param mixed  $value
			 * @param mixed  $default
			 */
			function( &$name, $value, $default ) {
				$name = sprintf(
					'<option value="%s"%s>%s</option>',
					\esc_attr( $value ),
					$value == $default ? ' selected' : '', // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
					\esc_html( $name )
				);
			},
			$args['default']
		);

		return vsprintf(
			sprintf( '<div class="%s">%s</div>',
				\esc_attr( $args['class'] ),
				( \is_rtl() ? '%2$s%1$s%3$s' : '%1$s%2$s%3$s' )
			),
			[
				$args['label'] ? sprintf(
					'<label for=%s>%s</label> ', // NOTE: extra space!
					$this->s_field_id( $args['id'] ),
					\esc_html( $args['label'] )
				) : '',
				$args['info'] ? ' ' . $this->make_info(
					$args['info'][0],
					isset( $args['info'][1] ) ? $args['info'][1] : '',
					false
				) : '',
				vsprintf(
					'<select id=%s name=%s %s %s>%s</select>',
					[
						$this->s_field_id( $args['id'] ),
						\esc_attr( $args['name'] ),
						$args['required'] ? 'required' : '',
						$args['data'] ? $this->get_field_data( $args['data'] ) : '',
						implode( $html_options ),
					]
				),
			]
		);
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link        The non-escaped link.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {

		if ( $link ) {
			$output = sprintf(
				'<a href="%1$s" class="tsf-tooltip-item tsf-help" target="_blank" rel="nofollow noreferrer noopener" title="%2$s" data-desc="%2$s">[?]</a>',
				\esc_url( $link, [ 'https', 'http' ] ),
				\esc_attr( $description )
			);
		} else {
			$output = sprintf(
				'<span class="tsf-tooltip-item tsf-help" title="%1$s" data-desc="%1$s">[?]</span>',
				\esc_attr( $description )
			);
		}

		$output = sprintf( '<span class="tsf-tooltip-wrap">%s</span>', $output );

		if ( $echo ) {
			echo $output; // phpcs:ignore -- Output is escaped.
		} else {
			return $output;
		}
	}

	/**
	 * Returns the HTML class wrap for default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @since 2.2.5
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $depr Deprecated
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_default_checked( $key, $depr = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->get_default_settings( $key );

		if ( 1 === $default )
			$class = 'tsf-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $deprecated Deprecated.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_warning_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {

		$class = '';

		$warned = $this->get_warned_settings( $key, $deprecated );

		if ( 1 === $warned )
			$class = 'tsf-warning-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Added the $wrap parameter
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 */
	public function get_is_conditional_checked( $key, $wrap = true ) {
		return $this->is_conditional_checked( $key, '', $wrap, false );
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 *
	 * @param string $key        The option name which returns boolean.
	 * @param string $deprecated Deprecated. Used to be the settings field.
	 * @param bool   $wrap       Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo       Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_conditional_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {

		$class = '';

		$default = $this->is_default_checked( $key, $deprecated, false, false );
		$warned  = $this->is_warning_checked( $key, $deprecated, false, false );

		if ( '' !== $default && '' !== $warned ) {
			$class = $default . ' ' . $warned;
		} elseif ( '' !== $default ) {
			$class = $default;
		} elseif ( '' !== $warned ) {
			$class = $warned;
		}

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the SEO Bar.
	 *
	 * @since 3.3.0
	 * @uses \The_SEO_Framework\Interpreters\SeoBar::generate_bar();
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @return string The generated SEO bar, in HTML.
	 */
	public function get_generated_seo_bar( array $query ) {
		return Interpreters\SeoBar::generate_bar( $query );
	}

	/**
	 * Returns social image uploader form button.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_social_image_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select"
				%s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				\esc_attr_x( 'Select social image', 'Button hover', 'autodescription' ),
				$s_input_id,
				$this->get_field_data( [
					'inputId'   => $s_input_id,
					'inputType' => 'social',
					'width'     => 1200,
					'height'    => 630,
					'minWidth'  => 200,
					'minHeight' => 200,
					'flex'      => true,
				] ),
				\esc_html__( 'Select Image', 'autodescription' ),
			]
		);

		return $content;
	}

	/**
	 * Returns logo uploader form buttons.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_logo_uploader_form( $input_id ) {

		if ( ! $input_id )
			return '';

		$s_input_id = \esc_attr( $input_id );

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select"
				%s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $this->get_the_real_ID() ) ),
				'', // Redundant
				$s_input_id,
				$this->get_field_data( [
					'inputId'   => $s_input_id,
					'inputType' => 'logo',
					'width'     => 512,
					'height'    => 512,
					'minWidth'  => 112,
					'minHeight' => 112,
					'flex'      => true,
				] ),
				\esc_html__( 'Select Logo', 'autodescription' ),
			]
		);

		return $content;
	}

	/**
	 * Outputs floating and reference title HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_title_elements() {
		echo '<span id=tsf-title-reference style=display:none></span>';
		echo '<span id=tsf-title-offset class=hide-if-no-tsf-js></span>';
		echo '<span id=tsf-title-placeholder class=hide-if-no-tsf-js></span>';
		echo '<span id=tsf-title-placeholder-prefix class=hide-if-no-tsf-js></span>';
	}

	/**
	 * Outputs reference description HTML elements for JavaScript.
	 *
	 * @since 3.0.4
	 */
	public function output_js_description_elements() {
		echo '<span id="tsf-description-reference" style="display:none"></span>';
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 : 1. Added an "what if you click" onhover-title.
	 *                2. Removed second parameter's usage. For passing the expected string.
	 *                3. The whole output is now hidden from no-js.
	 *
	 * @param string $for     The input ID it's for.
	 * @param string $depr    The initial value for no-JS. Deprecated.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_character_counter_wrap( $for, $depr = '', $display = true ) {
		vprintf(
			'<div class="tsf-counter-wrap hide-if-no-tsf-js" %s><span class="description tsf-counter" title="%s">%s</span><span class="tsf-ajax"></span></div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				\esc_attr__( 'Click to change the counter type', 'autodescription' ),
				sprintf(
					/* translators: %s = number */
					\esc_html__( 'Characters Used: %s', 'autodescription' ),
					sprintf(
						'<span id="%s_chars">%s</span>',
						\esc_attr( $for ),
						0
					)
				),
			]
		);
	}

	/**
	 * Outputs pixel counter wrap for javascript.
	 *
	 * @since 3.0.0
	 *
	 * @param string $for  The input ID it's for.
	 * @param string $type Whether it's a 'title' or 'description' counter.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_pixel_counter_wrap( $for, $type, $display = true ) {
		vprintf(
			'<div class="tsf-pixel-counter-wrap hide-if-no-tsf-js" %s>%s%s</div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				sprintf(
					'<div id="%s_pixels" class="tsf-tooltip-wrap">%s</div>',
					\esc_attr( $for ),
					'<span class="tsf-pixel-counter-bar tsf-tooltip-item" aria-label="" data-desc=""><span class="tsf-pixel-counter-fluid"></span></span>'
				),
				sprintf(
					'<div class="tsf-pixel-shadow-wrap"><span class="tsf-pixel-counter-shadow tsf-%s-pixel-counter-shadow"></span></div>',
					\esc_attr( $type )
				),
			]
		);
	}
}
