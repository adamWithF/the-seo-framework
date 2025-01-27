<?php
/**
 * @package The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_locale', 4 );
/**
 * Plugin locale 'autodescription'
 * Files located in plugin folder `../autodescription/language/`
 *
 * @since 2.8.0
 */
function _init_locale() {
	/**
	 * @since 1.0.0
	 */
	\load_plugin_textdomain(
		'autodescription',
		false,
		THE_SEO_FRAMEWORK_DIR_PATH . 'language'
	);
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\_init_tsf', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @since 3.1.0
 * @access private
 * @see function the_seo_framework().
 * @staticvar object $tsf
 * @factory
 *
 * @return object|null The SEO Framework Facade class object. Null on failure.
 */
function _init_tsf() {

	//* Cache the class. Do not run constructors more than once.
	static $tsf = null;

	if ( $tsf )
		return $tsf;

	// @TODO use autoloader instead?
	_load_trait( 'core/overload' );

	/**
	 * @package The_SEO_Framework
	 */
	if ( \The_SEO_Framework\_can_load() ) {
		if ( \is_admin() ) {
			//! TODO: admin-only loader.
			$tsf         = new \The_SEO_Framework\Load();
			$tsf->loaded = true;

			$tsf->_load_early_compat_files();

			/**
			 * @since 3.1.0
			 * Runs after TSF is loaded in the admin.
			 */
			\do_action( 'the_seo_framework_admin_loaded' );
		} else {
			$tsf         = new \The_SEO_Framework\Load();
			$tsf->loaded = true;

			$tsf->_load_early_compat_files();
		}

		/**
		 * @since 3.1.0
		 * Runs after TSF is loaded.
		 */
		\do_action( 'the_seo_framework_loaded' );
	} else {
		$tsf         = new \The_SEO_Framework\Silencer();
		$tsf->loaded = false;
	}

	// did_action() checks for current action too.
	if ( ! \did_action( 'plugins_loaded' ) )
		$tsf->_doing_it_wrong( 'the_seo_framework() or ' . __FUNCTION__, 'Use <code>the_seo_framework()</code> after action <code>plugins_loaded</code> priority 5.', '3.1' );

	return $tsf;
}

spl_autoload_register( __NAMESPACE__ . '\\_autoload_classes', true, true );
/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 2.8.0
 * @since 3.1.0 : 1. No longer maintains cache.
 *                2. Now always returns void.
 * @since 3.3.0 : 1. Streamlined folder lookup by more effectively using the namespace.
 *                2. Added timing functionality
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 * @access private
 *
 * @NOTE 'The_SEO_Framework\' is a reserved namespace. Using it outside of this
 *       plugin's scope coul result in an error.
 *
 * @param string $class The class name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	if ( 0 !== strpos( $class, 'The_SEO_Framework\\', 0 ) ) return;

	$_bootstrap_timer = microtime( true );

	$_chunks       = explode( '\\', strtolower( $class ) );
	$_chunck_count = count( $_chunks );

	if ( $_chunck_count > 2 ) {
		//? directory position = $_chunck_count - ( 2 = $offset (1) + $class name (1) )
		$rel_dir = implode( DIRECTORY_SEPARATOR, array_splice( $_chunks, 1, $_chunck_count - 2 ) ) . DIRECTORY_SEPARATOR;
	} else {
		$rel_dir = '';
	}

	$class = str_replace( '_', '-', end( $_chunks ) );

	if ( strpos( $class, '-interface' ) ) {
		$file = str_replace( '-interface', '', $class ) . '.interface.php';
		$path = THE_SEO_FRAMEWORK_DIR_PATH_INTERFACE;
	} else {
		$file = $class . '.class.php';
		$path = THE_SEO_FRAMEWORK_DIR_PATH_CLASS;
	}

	require $path . $rel_dir . $file;

	_bootstrap_timer( microtime( true ) - $_bootstrap_timer );
}

\add_action( 'activate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_activation' );
/**
 * Performs plugin activation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_activation() {
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'activation.php';
}

\add_action( 'deactivate_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, __NAMESPACE__ . '\\_do_plugin_deactivation' );
/**
 * Performs plugin deactivation actions.
 *
 * @since 2.8.0
 * @access private
 */
function _do_plugin_deactivation() {
	require THE_SEO_FRAMEWORK_BOOTSTRAP_PATH . 'deactivation.php';
}
