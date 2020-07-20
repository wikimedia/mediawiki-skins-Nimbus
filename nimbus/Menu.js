/**
 * Menu navigation and other JavaScript functions used by the Nimbus skin.
 *
 * @file
 * @author Jack Phoenix - cleanup & removal of YUI dependency, etc.
 */
/* global getElementsByClassName, window, document, setTimeout, clearTimeout */
( function ( $ ) {

	var NimbusSkin = {
		last_clicked: '',
		m_timer: '',
		displayed_menus: [],
		last_displayed: '',
		last_over: '',
		show: 'false',
		_shown: false,
		_hide_timer: '',
		menuitem_array: [],
		submenu_array: [],
		submenuitem_array: [],

		submenu: function ( id ) {
			var on_tabs, x;

			// Clear all tab classes
			on_tabs = getElementsByClassName( document, 'a', 'tab-on' );
			for ( x = 0; x <= on_tabs.length - 1; x++ ) {
				$( '#' + on_tabs[ x ] ).addClass( 'tab-off' );
			}

			on_tabs = getElementsByClassName( document, 'div', 'sub-menu' );
			for ( x = 0; x <= on_tabs.length - 1; x++ ) {
				$( '#' + on_tabs[ x ] ).hide();
			}

			// Hide submenu that might have been previously clicked
			if ( NimbusSkin.last_clicked ) {
				$( '#submenu-' + NimbusSkin.last_clicked ).hide();
			}

			// Update tab class you clicked on/show its submenu
			if ( $( '#menu-' + id ).hasClass( 'tab-off' ) ) {
				$( '#menu-' + id ).addClass( 'tab-on' );
			}

			$( '#submenu-' + id ).show();

			NimbusSkin.last_clicked = id;
		},

		editMenuToggle: function () {
			var submenu = document.getElementById( 'edit-sub-menu-id' );

			if ( submenu.style.display === 'block' ) {
				submenu.style.display = 'none';
			} else {
				submenu.style.display = 'block';
			}
		},
		// End menu nav

		// Skin Navigation
		menuItemAction: function ( e ) {
			var source_id = '*';

			clearTimeout( NimbusSkin.m_timer );

			if ( !e ) {
				e = window.event;
			}
			e.cancelBubble = true;
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}

			try {
				source_id = e.target.id;
			} catch ( ex ) {
				source_id = e.srcElement.id;
			}

			if ( source_id.indexOf( 'a-' ) === 0 ) {
				source_id = source_id.substr( 2 );
			}

			if ( source_id && NimbusSkin.menuitem_array[ source_id ] ) {
				if ( NimbusSkin.last_over !== '' && document.getElementById( NimbusSkin.last_over ) ) {
					document.getElementById( NimbusSkin.last_over ).style.backgroundColor = '#FFF';
				}
				NimbusSkin.last_over = source_id;
				document.getElementById( source_id ).style.backgroundColor = '#FFFCA9';
				NimbusSkin.check_item_in_array( NimbusSkin.menuitem_array[ source_id ] );
			}
		},

		check_item_in_array: function ( item ) {
			var sub_menu_item = 'sub-menu' + item,
				exit, count, the_last_displayed;

			clearTimeout( NimbusSkin.m_timer );

			if (
				NimbusSkin.last_displayed === '' ||
			( ( sub_menu_item.indexOf( NimbusSkin.last_displayed ) !== -1 ) &&
				( sub_menu_item !== NimbusSkin.last_displayed ) )
			) {
				NimbusSkin.do_menuItemAction( item );
			} else {
				exit = false;
				count = 0;
				while ( !exit && NimbusSkin.displayed_menus.length > 0 ) {
					the_last_displayed = NimbusSkin.displayed_menus.pop();
					if ( ( sub_menu_item.indexOf( the_last_displayed ) === -1 ) ) {
						NimbusSkin.doClear( the_last_displayed, '' );
					} else {
						NimbusSkin.displayed_menus.push( the_last_displayed );
						exit = true;
						NimbusSkin.do_menuItemAction( item );
					}
					count++;
				}

				NimbusSkin.do_menuItemAction( item );
			}
		},

		do_menuItemAction: function ( item ) {
			if ( document.getElementById( 'sub-menu' + item ) ) {
				document.getElementById( 'sub-menu' + item ).style.display = 'block';
				NimbusSkin.displayed_menus.push( 'sub-menu' + item );
				NimbusSkin.last_displayed = 'sub-menu' + item;
			}
		},

		sub_menuItemAction: function ( e ) {
			var source_id = '*',
				second_start, second_uscore;

			clearTimeout( NimbusSkin.m_timer );

			if ( !e ) {
				e = window.event;
			}
			e.cancelBubble = true;
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}

			try {
				source_id = e.target.id;
			} catch ( ex ) {
				source_id = e.srcElement.id;
			}

			if ( source_id && NimbusSkin.submenuitem_array[ source_id ] ) {
				NimbusSkin.check_item_in_array( NimbusSkin.submenuitem_array[ source_id ] );

				if ( source_id.indexOf( '_' ) ) {
					if ( source_id.indexOf( '_', source_id.indexOf( '_' ) ) ) {
						second_start = source_id.substr( 4 + source_id.indexOf( '_' ) - 1 );
						second_uscore = second_start.indexOf( '_' );
						try {
							source_id = source_id.substr( 4, source_id.indexOf( '_' ) + second_uscore - 1 );
							if ( NimbusSkin.menuitem_array[ source_id ] ) {
								document.getElementById( source_id ).style.backgroundColor = '#FFFCA9';
							}
						} catch ( ex ) {}
					} else {
						source_id = source_id.substr( 4 );
						if ( NimbusSkin.menuitem_array[ source_id ] ) {
							document.getElementById( source_id ).style.backgroundColor = '#FFFCA9';
						}
					}
				}
			}
		},

		clearBackground: function ( e ) {
			var source_id = '*';

			if ( !e ) {
				e = window.event;
			}
			e.cancelBubble = true;
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}

			try {
				source_id = e.target.id;
			} catch ( ex ) {
				source_id = e.srcElement.id;
			}

			if (
				source_id &&
			document.getElementById( source_id ) &&
			NimbusSkin.menuitem_array[ source_id ]
			) {
				document.getElementById( source_id ).style.backgroundColor = '#FFF';
				NimbusSkin.clearMenu( e );
			}
		},

		resetMenuBackground: function ( e ) {
			var source_id = '*';

			if ( !e ) {
				e = window.event;
			}
			e.cancelBubble = true;
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}

			try {
				source_id = e.target.id;
			} catch ( ex ) {
				source_id = e.srcElement.id;
			}

			source_id = source_id.substr( 2 );

			document.getElementById( source_id ).style.backgroundColor = '#FFFCA9';
		},

		clearMenu: function ( e ) {
			var source_id = '*';

			if ( !e ) {
				e = window.event;
			}
			e.cancelBubble = true;
			if ( e.stopPropagation ) {
				e.stopPropagation();
			}

			try {
				source_id = e.target.id;
			} catch ( ex ) {
				source_id = e.srcElement.id;
			}

			clearTimeout( NimbusSkin.m_timer );
			NimbusSkin.m_timer = setTimeout(
				function () {
					NimbusSkin.doClearAll();
				},
				200
			);
		},

		doClear: function ( item, type ) {
			if ( document.getElementById( type + item ) ) {
				document.getElementById( type + item ).style.display = 'none';
			}
		},

		doClearAll: function () {
			var epicElement, the_last_displayed, exit;
			// Otherwise the NimbusSkin.displayed_menus[0] line below causes a TypeError about
			// NimbusSkin.displayed_menus[0] being undefined

			if ( !NimbusSkin.displayed_menus.length ) {
				return;
			}
			epicElement = document.getElementById(
				'menu-item' +
				NimbusSkin.displayed_menus[ 0 ]
					.substr( NimbusSkin.displayed_menus[ 0 ].indexOf( '_' ) )
			);
			if ( NimbusSkin.displayed_menus.length && epicElement ) {
				epicElement.style.backgroundColor = '#FFF';
			}
			while ( NimbusSkin.displayed_menus.length > 0 ) {
				the_last_displayed = NimbusSkin.displayed_menus.pop();
				NimbusSkin.doClear( the_last_displayed, '' );
			}

			NimbusSkin.last_displayed = '';
		},

		show_more_category: function ( el, toggle ) {
			if ( toggle !== undefined ) {
				$( '#' + el ).toggle( toggle );
			} else {
				$( '#' + el ).toggle();
			}
		},

		show_actions: function ( el, type ) {
			if ( type === 'show' ) {
				clearTimeout( NimbusSkin._hide_timer );
				if ( !NimbusSkin._shown ) {
					$( '#more-tab' ).removeClass( 'more-tab-off' ).addClass( 'more-tab-on' );
					$( '#' + el ).show();
					NimbusSkin._shown = true;
				}
			} else {
				$( '#more-tab' ).removeClass( 'more-tab-on' ).addClass( 'more-tab-off' );
				$( '#' + el ).hide();
				NimbusSkin._shown = false;
			}
		},

		delay_hide: function ( el ) {
			NimbusSkin._hide_timer = setTimeout( function () {
				NimbusSkin.show_actions( el, 'hide' );
			}, 500 );
		}
	};

	$( function () {
	// Top-level menus
		$( 'div[id^="menu-item_"]' ).each( function ( idx, elem ) {
			var id = $( elem ).attr( 'id' );
			NimbusSkin.menuitem_array[ id ] = id.replace( /menu-item/gi, '' );

			$( this ).on( 'mouseover', NimbusSkin.menuItemAction );
			$( this ).on( 'mouseout', NimbusSkin.clearBackground );

			if ( document.getElementById( id ).captureEvents ) {
				document.getElementById( id ).captureEvents( Event.MOUSEOUT );
			}

			document.getElementById( 'a-' + id ).onmouseover = NimbusSkin.menuItemAction;
			if ( document.getElementById( 'a-' + id ).captureEvents ) {
				document.getElementById( 'a-' + id ).captureEvents( Event.MOUSEOVER );
			}
		} );

		// Sub-menus...
		$( 'div[id^="sub-menu_"]' ).each( function () {
			var id = $( this ).attr( 'id' );
			NimbusSkin.submenu_array[ id ] = id.replace( /sub-menu/gi, '' );

			$( this ).on( 'mouseout', NimbusSkin.clearMenu );

			if ( document.getElementById( id ).captureEvents ) {
				document.getElementById( id ).captureEvents( Event.MOUSEOUT );
			}
		} );

		// ...and their items
		$( 'div[id^="sub-menu-item_"]' ).each( function () {
			var id = $( this ).attr( 'id' );
			NimbusSkin.submenuitem_array[ id ] = id.replace( /sub-menu-item/gi, '' );

			$( this ).on( 'mouseover', NimbusSkin.sub_menuItemAction );

			if ( document.getElementById( id ).captureEvents ) {
				document.getElementById( id ).captureEvents( Event.MOUSEOVER );
			}
		} );

		$( '#more-tab' ).on( 'mouseover', function () {
			NimbusSkin.show_actions( 'article-more-container', 'show' );
		} ).on( 'mouseout', function () {
			NimbusSkin.delay_hide( 'article-more-container' );
		} );

		$( '#article-more-container' ).on( 'mouseover', function () {
			clearTimeout( NimbusSkin._hide_timer );
		} ).on( 'mouseout', function () {
			NimbusSkin.show_actions( 'article-more-container', 'hide' );
		} );

		$( '#sw-more-category' ).on( 'click', function ( e ) {
			NimbusSkin.show_more_category( 'more-wikis-menu' );
			e.stopPropagation();
		} );

		$( 'body' ).on( 'click', function () {
			NimbusSkin.show_more_category( 'more-wikis-menu', false );
		} );
	} );

}( jQuery ) );
