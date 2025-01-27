/**
 * This file holds The SEO Framework plugin's JS code for forwards compatibility with Gutenberg.
 *
 * This is an intermediate step where I hijack old code to support the new WordPress editor.
 * The current code will be rewritten later. Do not rely on this file. However, the JQ triggers
 * will sustain support until further notice.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

'use strict';

/**
 * Holds tsfGBC (tsf Gutenberg Compat) values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.2.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfGBC = function( $ ) {

	/**
	 * Data property injected by our Scripts l10n handler.
	 *
	 * @since 3.3.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfGBCL10n && tsfGBCL10n;

	// const { addFilter } = wp.hooks;
	// const { addQueryArgs } = wp.url;
	// const apiFetch = wp.apiFetch;

	const editor   = wp.data.select( 'core/editor' );
	const editPost = wp.data.select( 'core/edit-post' );

	const { debounce } = lodash;

	/**
	 * Post data holder.
	 *
	 * @since 3.2.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} post data
	 */
	let postData;

	/**
	 * Retrieves post attribute.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @param {String} attribute
	 * @return {mixed|null}
	 */
	function getPostAttribute( attribute ) {
		return editor.getEditedPostAttribute( attribute );
	}

	/**
	 * Sets editor data.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Now sets post visibility.
	 * @access private
	 *
	 * @function
	 */
	function setData() {
		postData = {
			title:      getPostAttribute( 'title' ),
			link:       editor.getPermalink(),
			content:    getPostAttribute( 'content' ),
			excerpt:    getPostAttribute( 'excerpt' ),
			visibility: editor.getEditedPostVisibility(),
		};
	}

	/**
	 * Returns known editor data.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @param {String} type
	 * @return {mixed|null}
	 */
	function getData( type ) {
		return postData[ type ] || null;
	}

	/**
	 * Assesses the editor data, and dispatches the data when changed.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Now dispatches visibility changes.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function assessData() {
		let oldData = postData;
		setData();
		if ( oldData.title !== postData.title ) {
			triggerUpdate( 'title' );
		}
		if ( oldData.link !== postData.link ) {
			triggerUpdate( 'link' );
		}
		if ( oldData.content !== postData.content ) {
			triggerUpdate( 'content' );
		}
		if ( oldData.excerpt !== postData.excerpt ) {
			triggerUpdate( 'excerpt' );
		}
		if ( oldData.visibility !== postData.visibility ) {
			triggerUpdate( 'visibility' );
		}
	}

	/**
	 * Dispatches an event of a data type, also sends data of set type.
	 *
	 * @since 3.2.0
	 * @access public
	 *
	 * @function
	 * @param {String} type
	 * @return {undefined}
	 */
	const triggerUpdate = ( type ) => {
		$( document ).trigger( 'tsf-updated-gutenberg-' + type, [ getData( type ) ] );
	}

	/**
	 * Maintains asynchronous save states.
	 * @since 3.2.0
	 * @access private
	 * @type {boolean} saved
	 */
	let saved = false;
	/**
	 * Checks if the document is saved successfully, and then dispatches an event if so.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Now waits for 7 seconds for the saveDispatcher to resolve before canceling the process.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function saveDispatcher() {
		if ( ! saved ) {
			if ( editor.isSavingPost() || editor.isAutosavingPost() ) {
				saved = true;
			}
		} else {
			if ( editor.didPostSaveRequestSucceed() ) {
				dispatchSaveEventDebouncer();
				revertSaveStateDebouncer() && revertSaveStateDebouncer().cancel();
				revertSaveState();
			} else {
				revertSaveStateDebouncer();
			}
		}
	}

	const revertSaveStateDebouncer = debounce( revertSaveState, 7000 );
	/**
	 * Reverts save state.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function revertSaveState() {
		saved = false;
	}

	const dispatchSaveEventDebouncer = debounce( dispatchSavedEvent, 500 );
	/**
	 * Maintains retry states.
	 * @since 3.3.0
	 * @access private
	 * @type {number} retryDispatch
	 */
	let retryDispatch = 0;
	/**
	 * Dispatches save event.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function dispatchSavedEvent() {
		if ( editor.isPostSavingLocked() ) {
			// Retry again
			if ( ++retryDispatch < 3 ) {
				dispatchSaveEventDebouncer();
			} else {
				dispatchSaveEventDebouncer() && dispatchSaveEventDebouncer().cancel();
				retryDispatch = 0;
			}
		} else {
			retryDispatch = 0;
			$( document ).trigger( 'tsf-gutenberg-saved-document' );
		}
	}

	/**
	 * Maintains the sidebar opening/closing states.
	 *
	 * @since 3.3.0
	 * @access private
	 * @type {Object<string, *>}
	 */
	let lastSidebarState = {
		opened: false,
	};
	/**
	 * Checks if user changed the sidebar layout.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function sidebarDispatcher() {
		if ( editPost.isEditorSidebarOpened() ) {
			if ( ! lastSidebarState.opened ) {
				lastSidebarState.opened = true;
				$( document ).trigger( 'tsf-gutenberg-sidebar-opened' );
			}
		} else {
			if ( lastSidebarState.opened ) {
				lastSidebarState.opened = false;
				$( document ).trigger( 'tsf-gutenberg-sidebar-closed' );
			}
		}
	}

	/**
	 * Initializes Gutenberg's compatibility and dispatches event hooks.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Now adds tooltip boundaries (moved from tt.js)
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initCompat = () => {

		wp.data.subscribe( debounce( sidebarDispatcher, 500 ) );
		wp.data.subscribe( debounce( assessData, 300 ) );
		wp.data.subscribe( saveDispatcher );

		// Set all values prior debouncing.
		setTimeout( () => {
			setData();

			triggerUpdate( 'title' );
			triggerUpdate( 'link' );
			triggerUpdate( 'content' );
			triggerUpdate( 'excerpt' );
			triggerUpdate( 'visibility' );

			$( document ).trigger( 'tsf-subscribed-to-gutenberg' );
		} );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.3.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			$( document.body ).on( 'tsf-onload', _initCompat );
		},
	}, {
		triggerUpdate,
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfGBC.load );
