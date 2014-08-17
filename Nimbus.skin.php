<?php
/**
 * Notes:
 * Template:Didyouknow is a part of the interface (=should be fully protected on the wiki)
 * If SocialProfile extension (+some other social extensions) is available,
 * then more stuff will appear in the skin interface
 *
 * Feel free to improve source code documentation as you like,
 * it's in a really crappy state currently, but better than nothing.
 *
 * @file
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( -1 );
}

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @ingroup Skins
 */
class SkinNimbus extends SkinTemplate {
	public $skinname = 'nimbus', $stylename = 'nimbus',
		$template = 'NimbusTemplate', $useHeadElement = true;

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Add CSS & JS
		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'skins.monobook.styles',
			'skins.nimbus'
		) );
		$out->addModuleScripts( 'skins.nimbus' );

		// IE-specific CSS
		#$out->addStyle( 'Nimbus/nimbus/Nimbus_IE.css', 'screen', 'IE' );
	}
}

/**
 * Main skin class.
 * @ingroup Skins
 */
class NimbusTemplate extends BaseTemplate {
	/**
	 * @var Skin
	 */
	public $skin;

	/**
	 * Should we show the page title (the <h1> HTML element) for the current
	 * page or not?
	 *
	 * @see /skins/Games/Games.skin.php, GamesTemplate::pageTitle()
	 *
	 * @return bool
	 */
	function showPageTitle() {
		global $wgSupressPageTitle;

		$nsArray = array();
		// Suppress page title on NS_USER when SocialProfile ext. is installed
		if ( class_exists( 'UserProfile' ) ) {
			$nsArray[] = NS_USER;
		}
		// Also suppress page titles on social profiles (for users whose "main"
		// user page is the wiki-style page)
		if ( defined( 'NS_USER_PROFILE' ) ) {
			$nsArray[] = NS_USER_PROFILE;
		}
		// Finally do the opposite to the above for users whose main user page
		// is their social profile
		if ( defined( 'NS_USER_WIKI' ) ) {
			$nsArray[] = NS_USER_WIKI;
		}

		// Strangely enough this does *not* cause any errors even if $nsArray
		// is empty...I was sure it'd cause one.
		$nsCheck = !in_array( $this->skin->getTitle()->getNamespace(), $nsArray );

		return (bool) ( !$wgSupressPageTitle && $nsCheck );
	}

	/**
	 * Template filter callback for Nimbus skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgContLang, $wgLogo, $wgOut, $wgStylePath;
		global $wgLangToCentralMap, $wgSupressSubTitle, $wgSupressPageCategories;
		global $wgUserLevels;

		$this->skin = $this->data['skin'];

		$user = $this->skin->getUser();

		// This trick copied over from Monaco.php to allow localized central wiki URLs
		$central_url = !empty( $wgLangToCentralMap[$wgContLang->getCode()] ) ?
						$wgLangToCentralMap[$wgContLang->getCode()] :
						'http://www.shoutwiki.com/';

		$register_link = SpecialPage::getTitleFor( 'Userlogin', 'signup' );
		$login_link = SpecialPage::getTitleFor( 'Userlogin' );
		$logout_link = SpecialPage::getTitleFor( 'Userlogout' );
		$profile_link = Title::makeTitle( NS_USER, $user->getName() );
		$main_page_link = Title::newMainPage();
		$recent_changes_link = SpecialPage::getTitleFor( 'Recentchanges' );
		$top_fans_link = SpecialPage::getTitleFor( 'TopUsers' );
		$special_pages_link = SpecialPage::getTitleFor( 'Specialpages' );
		$help_link = Title::newFromText( wfMessage( 'helppage' )->inContentLanguage()->text() );
		$upload_file = SpecialPage::getTitleFor( 'Upload' );
		$what_links_here = SpecialPage::getTitleFor( 'Whatlinkshere' );
		$preferences_link = SpecialPage::getTitleFor( 'Preferences' );
		$watchlist_link = SpecialPage::getTitleFor( 'Watchlist' );

		$this->html( 'headelement' );
?><div id="container">
	<div id="header" class="noprint">
		<div id="sw-logo">
			<a href="<?php echo $central_url ?>">
				<img src="<?php echo $wgStylePath ?>/Nimbus/nimbus/sw_logo.png" alt="" />
			</a>
			<span id="sw-category">ShoutWiki</span>
		</div>
		<div id="sw-more-category">
			<div class="positive-button"><span><?php echo wfMessage( 'nimbus-more-wikis' )->plain() ?></span></div>
		</div>
		<div id="more-wikis-menu" style="display:none;">
		<?php
		$more_wikis = $this->buildMoreWikis();

		$x = 1;
		foreach ( $more_wikis as $link ) {
			$ourClass = '';
			if ( $x == count( $more_wikis ) ) {
				$ourClass = ' class="border-fix"';
			}
			echo "<a href=\"{$link['href']}\"" . $ourClass .
				">{$link['text']}</a>\n";
			if ( $x > 1 && $x % 2 == 0 ) {
				echo '<div class="cleared"></div>' . "\n";
			}
			$x++;
		}
		?>
		</div><!-- #more-wikis-menu -->
		<div id="wiki-login">
<?php
	if ( $user->isLoggedIn() ) {
		echo "\t\t\t" . '<div id="login-message">' .
				wfMessage( 'nimbus-welcome', '<b>' . $user->getName() . '</b>' )->parse() .
			'</div>
			<a class="positive-button" href="' . htmlspecialchars( $profile_link->getFullURL() ) . '" rel="nofollow"><span>' . wfMessage( 'nimbus-profile' )->plain() . '</span></a>
			<a class="negative-button" href="' . htmlspecialchars( $logout_link->getFullURL() ) . '"><span>' . wfMessage( 'nimbus-logout' )->plain() . '</span></a>';
	} else {
		echo '<a class="positive-button" href="' . htmlspecialchars( $register_link->getFullURL() ) . '" rel="nofollow"><span>' . wfMessage( 'nimbus-signup' )->plain() . '</span></a>
		<a class="positive-button" href="' . htmlspecialchars( $login_link->getFullURL() ) . '" id="nimbusLoginButton"><span>' . wfMessage( 'nimbus-login' )->plain() . '</span></a>';
	}
?>
		</div><!-- #wiki-login -->
	</div><!-- #header -->
	<div id="site-header" class="noprint">
		<div id="site-logo">
			<a href="<?php echo htmlspecialchars( $main_page_link->getFullURL() ) ?>" title="<?php echo Linker::titleAttrib( 'p-logo', 'withaccess' ) ?>" accesskey="<?php echo Linker::accesskey( 'p-logo' ) ?>" rel="nofollow">
				<img src="<?php echo $wgLogo ?>" alt="" />
			</a>
		</div>
	</div>
	<div id="side-bar" class="noprint">
		<div id="navigation">
			<div id="navigation-title"><?php echo wfMessage( 'navigation' )->plain() ?></div>
			<script type="text/javascript">
				var submenu_array = new Array();
				var menuitem_array = new Array();
				var submenuitem_array = new Array();
			</script>
			<?php
				$this->navmenu_array = array();
				$this->navmenu = $this->getNavigationMenu();
				echo $this->printMenu( 0 );
			?>
			<div id="other-links-container">
				<div id="other-links">
				<?php
					// Only show the link to Special:TopUsers if wAvatar class exists and $wgUserLevels is an array
					if ( class_exists( 'wAvatar' ) && is_array( $wgUserLevels ) ) {
						echo '<a href="' . htmlspecialchars( $top_fans_link->getFullURL() ) . '">' . wfMessage( 'topusers' )->plain() . '</a>';
					}

					echo Linker::link(
						$recent_changes_link,
						wfMessage( 'recentchanges' )->text(),
						array(
							'title' => Linker::titleAttrib( 'n-recentchanges', 'withaccess' ),
							'accesskey' => Linker::accesskey( 'n-recentchanges' )
						)
					) . "\n" .
					'<div class="cleared"></div>' . "\n";

					if ( $user->isLoggedIn() ) {
						echo Linker::link(
							$watchlist_link,
							wfMessage( 'watchlist' )->text(),
							array(
								'title' => Linker::titleAttrib( 'pt-watchlist', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-watchlist' )
							)
						) . "\n" .
						Linker::link(
							$preferences_link,
							wfMessage( 'preferences' )->text(),
							array(
								'title' => Linker::titleAttrib( 'pt-preferences', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-preferences' )
							)
						) .
						'<div class="cleared"></div>' . "\n";
					}
					?>
					<a href="<?php echo htmlspecialchars( $help_link->getFullURL() ) ?>"><?php echo wfMessage( 'help' )->plain() ?></a>
					<a href="<?php echo htmlspecialchars( $special_pages_link->getFullURL() ) ?>"><?php echo wfMessage( 'specialpages' )->plain() ?></a>
					<div class="cleared"></div>
				</div>
			</div>
		</div>
		<div id="search-box">
			<div id="search-title"><?php echo wfMessage( 'search' )->plain() ?></div>
			<form method="get" action="<?php echo $this->text( 'wgScript' ) ?>" name="search_form" id="searchform">
				<input id="searchInput" type="text" class="search-field" name="search" value="" />
				<input type="image" src="<?php echo $wgStylePath ?>/Nimbus/nimbus/search_button.gif" class="search-button" alt="search" />
			</form>
			<div class="cleared"></div>
			<div class="bottom-left-nav">
			<?php
			// Hook point for ShoutWikiAds
			wfRunHooks( 'NimbusLeftSide' );

			if ( function_exists( 'wfRandomCasualGame' ) ) {
				echo wfGetRandomGameUnit();
			}
			?>
				<div class="bottom-left-nav-container">
					<h2><?php echo wfMessage( 'nimbus-didyouknow' )->plain() ?></h2>
					<?php echo $wgOut->parse( '{{Didyouknow}}' ) ?>
				</div>
			<?php
				echo $this->getInterlanguageLinksBox();

				if ( function_exists( 'wfRandomImageByCategory' ) ) {
					$randomImage = $wgOut->parse(
						'<randomimagebycategory width="200" categories="Featured Image" />',
						false
					);
					echo '<div class="bottom-left-nav-container">
					<h2>' . wfMessage( 'nimbus-featuredimage' )->plain() . '</h2>' .
					$randomImage . '</div>';
				}

				if ( function_exists( 'wfRandomFeaturedUser' ) ) {
					$randomUser = $wgOut->parse(
						'<randomfeatureduser period="weekly" />',
						false
					);
					echo '<div class="bottom-left-nav-container">
						<h2>' . wfMessage( 'nimbus-featureduser' )->plain() . '</h2>' .
						$randomUser . '</div>';
				}
			?>
</div>
		</div>
	</div>
	<div id="body-container">
		<?php echo $this->actionBar(); echo "\n"; ?>
		<div id="article">
			<div id="mw-js-message" style="display:none;"></div>

			<div id="article-body">
				<?php if ( $this->data['sitenotice'] ) { ?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php } ?>
				<div id="article-text" class="clearfix">
					<?php if ( $this->showPageTitle() ) { ?><h1 class="pagetitle"><?php $this->html( 'title' ) ?></h1><?php } ?>
					<?php if ( !$wgSupressSubTitle ) { ?><p class='subtitle'><?php $this->msg( 'tagline' ) ?></p><?php } ?>
					<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
					<?php if ( $this->data['undelete'] ) { ?><div id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php } ?>
					<?php if ( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php } ?>
					<!-- start content -->
					<?php $this->html( 'bodytext' ) ?>
					<?php $this->html( 'debughtml' ); ?>
					<?php if ( $this->data['catlinks'] && !$wgSupressPageCategories ) { $this->html( 'catlinks' ); } ?>
					<!-- end content -->
					<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->footer(); ?>
</div><!-- #container -->
<?php
		$this->printTrail();
		echo "\n";
		echo Html::closeElement( 'body' );
		echo "\n";
		echo Html::closeElement( 'html' );
	} // end of execute() method

	/**
	 * Parse MediaWiki-style messages called 'v3sidebar' to array of links,
	 * saving hierarchy structure.
	 * Message parsing is limited to first 150 lines only.
	 */
	private function getNavigationMenu() {
		$message_key = 'nimbus-sidebar';
		$message = trim( wfMessage( $message_key )->text() );

		if ( wfMessage( $message_key )->isDisabled() ) {
			return array();
		}

		$lines = array_slice( explode( "\n", $message ), 0, 150 );

		if ( count( $lines ) == 0 ) {
			return array();
		}

		$nodes = array();
		$nodes[] = array();
		$lastDepth = 0;
		$i = 0;
		foreach ( $lines as $line ) {
			# ignore empty lines
			if ( strlen( $line ) == 0 ) {
				continue;
			}

			$node = $this->parseItem( $line );
			$node['depth'] = strrpos( $line, '*' ) + 1;

			if ( $node['depth'] == $lastDepth ) {
				$node['parentIndex'] = $nodes[$i]['parentIndex'];
			} elseif ( $node['depth'] == $lastDepth + 1 ) {
				$node['parentIndex'] = $i;
			} elseif (
				// ignore crap that works on Monobook, but not on other skins
				$node['text'] == 'SEARCH' ||
				$node['text'] == 'TOOLBOX' ||
				$node['text'] == 'LANGUAGES'
			)
			{
				continue;
			} else {
				for ( $x = $i; $x >= 0; $x-- ) {
					if ( $x == 0 ) {
						$node['parentIndex'] = 0;
						break;
					}
					if ( $nodes[$x]['depth'] == $node['depth'] - 1 ) {
						$node['parentIndex'] = $x;
						break;
					}
				}
			}

			$nodes[$i + 1] = $node;
			$nodes[$node['parentIndex']]['children'][] = $i+1;
			$lastDepth = $node['depth'];
			$i++;
		}

		return $nodes;
	}

	/**
	 * Extract the link text and destination (href) from a MediaWiki message
	 * and return them as an array.
	 */
	private function parseItem( $line ) {
		$line_temp = explode( '|', trim( $line, '* ' ), 2 );
		if ( count( $line_temp ) > 1 ) {
			$line = $line_temp[1];
			$link = wfMessage( $line_temp[0] )->inContentLanguage()->text();
		} else {
			$line = $line_temp[0];
			$link = $line_temp[0];
		}

		// Determine what to show as the human-readable link description
		if ( wfMessage( $line )->isDisabled() ) {
			// It's *not* the name of a MediaWiki message, so display it as-is
			$text = $line;
		} else {
			// Guess what -- it /is/ a MediaWiki message!
			$text = wfMessage( $line )->text();
		}

		if ( $link != null ) {
			if ( wfMessage( $line_temp[0] )->isDisabled() ) {
				$link = $line_temp[0];
			}
			if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
				$href = $link;
			} else {
				$title = Title::newFromText( $link );
				if ( $title ) {
					$title = $title->fixSpecialName();
					$href = $title->getLocalURL();
				} else {
					$href = '#';
				}
			}
		}

		return array(
			'text' => $text,
			'href' => $href
		);
	}

	/**
	 * Generate and return "More Wikis" menu, showing links to related wikis.
	 *
	 * @return Array: "More Wikis" menu
	 */
	private function buildMoreWikis() {
		$messageKey = 'morewikis';
		$message = trim( wfMessage( $messageKey )->text() );

		if ( wfMessage( $messageKey )->isDisabled() ) {
			return array();
		}

		$lines = array_slice( explode( "\n", $message ), 0, 150 );

		if ( count( $lines ) == 0 ) {
			return array();
		}

		foreach ( $lines as $line ) {
			$moreWikis[] = $this->parseItem( $line );
		}

		return $moreWikis;
	}

	/**
	 * Prints the sidebar menu & all necessary JS
	 */
	private function printMenu( $id, $last_count = '', $level = 0 ) {
		global $wgContLang, $wgStylePath;

		$menu_output = '';
		$script_output = '';
		$scriptIndent = "\t\t\t\t\t\t";
		$output = '';
		$count = 1;

		if ( isset( $this->navmenu[$id]['children'] ) ) {
			$script_output .= "\n{$scriptIndent}" . '<script type="text/javascript">/*<![CDATA[*/' . "\n";
			if ( $level ) {
				$menu_output .= '<div class="sub-menu" id="sub-menu' . $last_count . '" style="display:none;">';
				$script_output .= $scriptIndent . "\t" . 'submenu_array["sub-menu' . $last_count . '"] = "' . $last_count . '";' . "\n";
				$script_output .= $scriptIndent . "\t" . 'document.getElementById("sub-menu' . $last_count . '").onmouseout = NimbusSkin.clearMenu;' . "\n";
				$script_output .= $scriptIndent . "\t" . 'if( document.getElementById("sub-menu' . $last_count . '").captureEvents ) document.getElementById("sub-menu' . $last_count . '").captureEvents(Event.MOUSEOUT);' . "\n";
			}
			foreach ( $this->navmenu[$id]['children'] as $child ) {
				$mouseover = ' onmouseover="NimbusSkin.' . ( $level ? 'sub_' : '' ) . 'menuItemAction(\'' .
					( $level ? $last_count . '_' : '_' ) . $count . '\');"';
				$mouseout = ' onmouseout="NimbusSkin.clearBackground(\'_' . $count . '\')"' . "\n";
				$menu_output .= "\n\t\t\t\t" . '<div class="' . ( $level ? 'sub-' : '' ) . 'menu-item' .
					( ( $count == sizeof( $this->navmenu[$id]['children'] ) ) ? ' border-fix' : '' ) .
					'" id="' . ( $level ? 'sub-' : '' ) . 'menu-item' .
						( $level ? $last_count . '_' : '_' ) . $count . '">';
				$menu_output .= "\n\t\t\t\t\t" . '<a id="' . ( $level ? 'a-sub-' : 'a-' ) . 'menu-item' .
					( $level ? $last_count . '_' : '_' ) . $count . '" href="' .
					( !empty( $this->navmenu[$child]['href'] ) ? htmlspecialchars( $this->navmenu[$child]['href'] ) : '#' ) . '">';

				if ( !$level ) {
					$script_output .= 'menuitem_array["menu-item' . $last_count . '_' . $count . '"] = "' . $last_count . '_' . $count . '";';
					$script_output .= 'document.getElementById("menu-item' . $last_count . '_' . $count . '").onmouseover = NimbusSkin.menuItemAction;' . "\n";
					$script_output .= 'if( document.getElementById("menu-item' . $last_count . '_' .$count . '").captureEvents) document.getElementById("menu-item' . $last_count . '_' . $count . '").captureEvents(Event.MOUSEOVER);' . "\n";
					$script_output .= 'document.getElementById("menu-item' . $last_count . '_' . $count . '").onmouseout = NimbusSkin.clearBackground;' . "\n";
					$script_output .= 'if( document.getElementById("menu-item' . $last_count . '_' . $count . '").captureEvents) document.getElementById("menu-item' . $last_count . '_' . $count . '").captureEvents(Event.MOUSEOUT);' . "\n";

					$script_output .= 'document.getElementById("a-menu-item' . $last_count . '_' . $count . '").onmouseover = NimbusSkin.menuItemAction;if( document.getElementById("a-menu-item' . $last_count . '_' . $count . '").captureEvents) document.getElementById("a-menu-item' . $last_count . '_' . $count . '").captureEvents(Event.MOUSEOVER);' . "\n";
				} else {
					$script_output .= $scriptIndent . "\t" . 'submenuitem_array["sub-menu-item' . $last_count . '_' . $count . '"] = "' . $last_count . '_' . $count . '";' . "\n";
					$script_output .= $scriptIndent . "\t" . 'document.getElementById("sub-menu-item' . $last_count . '_' . $count . '").onmouseover = NimbusSkin.sub_menuItemAction;' . "\n";
					$script_output .= $scriptIndent . "\t" . 'if( document.getElementById("sub-menu-item' . $last_count . '_' . $count . '").captureEvents) document.getElementById("sub-menu-item' . $last_count . '_' . $count . '").captureEvents(Event.MOUSEOVER);' . "\n";
				}
				$menu_output .= $this->navmenu[$child]['text'];
				// If a menu item has submenus, show an arrow so that the user
				// knows that there are submenus available
				if (
					isset( $this->navmenu[$child]['children'] ) &&
					sizeof( $this->navmenu[$child]['children'] )
				)
				{
					$menu_output .= '<img src="' . $wgStylePath . '/Nimbus/nimbus/right_arrow' .
						( $wgContLang->isRTL() ? '_rtl' : '' ) .
						'.gif" alt="" class="sub-menu-button" />';
				}
				$menu_output .= '</a>';
				//$menu_output .= $id . ' ' . sizeof( $this->navmenu[$child]['children'] ) . ' ' . $child . ' ';
				$menu_output .= $this->printMenu( $child, $last_count . '_' . $count, $level + 1 );
				//$menu_output .= 'last';
				$menu_output .= '</div>';
				$count++;
			}
			if ( $level ) {
				$menu_output .= '</div>';
			}
			$script_output .= $scriptIndent . '/*]]>*/</script>' . "\n";
		}

		if ( $menu_output . $script_output != '' ) {
			$output .= "<div id=\"menu{$last_count}\">";
			$output .= $menu_output . $script_output;
			$output .= "</div>\n";
		}

		return $output;
	}

	/**
	 * Do some tab-related magic
	 *
	 * @param $title Object: instance of Title class
	 * @param $message String: name of a MediaWiki: message
	 * @param $selected Boolean?
	 * @param $query String: empty by default, but if not empty, query to append (such as action=edit)
	 * @param $checkEdit Boolean: false by default
	 *
	 * @return array
	 */
	function tabAction( $title, $message, $selected, $query = '', $checkEdit = false ) {
		$classes = array();
		if ( $selected ) {
			$classes[] = 'selected';
		}
		if ( $checkEdit && $title->getArticleId() == 0 ) {
			$query = 'action=edit';
			$classes[] = ' new';
		}

		$text = wfMessage( $message )->text();
		if ( wfMessage( $message )->isDisabled() ) {
			global $wgContLang;
			$text = $wgContLang->getFormattedNsText(
				MWNamespace::getSubject( $title->getNamespace() )
			);
		}

		return array(
			'class' => implode( ' ', $classes ),
			'text' => $text,
			'href' => $title->getLocalURL( $query )
		);
	}

	/**
	 * Builds the content for the top navigation tabs (edit, history, etc.).
	 *
	 * @return Array
	 */
	function buildActionBar() {
		global $wgRequest, $wgOut;

		$user = $this->skin->getUser();
		/**
		 * This function originally used to use $wgTitle, which worked
		 * relatively fine.
		 * Then it was reported then when you view a redirect, the edit tabs do
		 * not point to the page that you were redirected _to_, but rather to
		 * the page where you were redirected _from_.
		 * This issue was solved by swapping $wgTitle to this context-sensitive
		 * variable.
		 *
		 * @see http://bugzilla.shoutwiki.com/show_bug.cgi?id=224
		 */
		$title = $this->skin->getTitle();

		$action = $wgRequest->getText( 'action' );
		$section = $wgRequest->getText( 'section' );
		$content_actions = array();

		if ( $title->getNamespace() != NS_SPECIAL ) {
			$subjpage = $title->getSubjectPage();
			$talkpage = $title->getTalkPage();
			$nskey = $title->getNamespaceKey();
			$prevent_active_tabs = ''; // Prevent E_NOTICE ;-)

			$content_actions[$nskey] = $this->tabAction(
				$subjpage,
				$nskey,
				!$title->isTalkPage() && !$prevent_active_tabs,
				'',
				true
			);

			// $nskey is something like 'nstab-main' here
			$msgObj = wfMessage( 'tooltip-ca-' . $nskey );
			// Only core namespaces have tooltips (and accesskeys), so don't
			// try to add those for NSes which don't have 'em, obviously!
			if ( $msgObj->exists() ) {
				$content_actions[$nskey]['title'] = Linker::titleAttrib( 'ca-' . $nskey, 'withaccess' );
				$content_actions[$nskey]['accesskey'] = Linker::accesskey( 'ca-' . $nskey );
			}

			$content_actions['talk'] = $this->tabAction(
				$talkpage,
				'talk',
				$title->isTalkPage() && !$prevent_active_tabs,
				'',
				true
			);
			// Ugh, this is just nasty.
			$content_actions['talk']['title'] = Linker::titleAttrib( 'ca-talk', 'withaccess' );
			$content_actions['talk']['accesskey'] = Linker::accesskey( 'ca-talk' );

			if ( $title->quickUserCan( 'edit' ) && ( $title->exists() || $title->quickUserCan( 'create' ) ) ) {
				$isTalk = $title->isTalkPage();
				$isTalkClass = $isTalk ? ' istalk' : '';

				$content_actions['edit'] = array(
					'class' => ( ( ( $action == 'edit' || $action == 'submit' ) && $section != 'new' ) ? 'selected' : '' ) . $isTalkClass,
					'text' => ( $title->exists() ? wfMessage( 'edit' )->plain() : wfMessage( 'create' )->plain() ),
					'href' => $title->getLocalURL( $this->skin->editUrlOptions() ),
					'title' => Linker::titleAttrib( 'ca-edit', 'withaccess' ),
					'accesskey' => Linker::accesskey( 'ca-edit' )
				);

				if ( $isTalk || $wgOut->showNewSectionLink() ) {
					$content_actions['addsection'] = array(
						'class' => $section == 'new' ? 'selected' : false,
						'text' => wfMessage( 'addsection' )->plain(),
						'href' => $title->getLocalURL( 'action=edit&section=new' ),
						'title' => Linker::titleAttrib( 'ca-addsection', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-addsection' )
					);
				}
			} else {
				$content_actions['viewsource'] = array(
					'class' => ( $action == 'edit' ) ? 'selected' : false,
					'text' => wfMessage( 'viewsource' )->plain(),
					'href' => $title->getLocalURL( $this->skin->editUrlOptions() ),
					'title' => Linker::titleAttrib( 'ca-viewsource', 'withaccess' ),
					'accesskey' => Linker::accesskey( 'ca-viewsource' )
				);
			}

			if ( $title->getArticleId() ) {
				$content_actions['history'] = array(
					'class' => ( $action == 'history' ) ? 'selected' : false,
					'text' => wfMessage( 'history_short' )->plain(),
					'href' => $title->getLocalURL( 'action=history' ),
					'title' => Linker::titleAttrib( 'ca-history', 'withaccess' ),
					'accesskey' => Linker::accesskey( 'ca-history' )
				);

				if ( $title->getNamespace() !== NS_MEDIAWIKI && $user->isAllowed( 'protect' ) ) {
					if ( !$title->isProtected() ) {
						$content_actions['protect'] = array(
							'class' => ( $action == 'protect' ) ? 'selected' : false,
							'text' => wfMessage( 'protect' )->plain(),
							'href' => $title->getLocalURL( 'action=protect' ),
							'title' => Linker::titleAttrib( 'ca-protect', 'withaccess' ),
							'accesskey' => Linker::accesskey( 'ca-protect' )
						);

					} else {
						$content_actions['unprotect'] = array(
							'class' => ( $action == 'unprotect' ) ? 'selected' : false,
							'text' => wfMessage( 'unprotect' )->plain(),
							'href' => $title->getLocalURL( 'action=unprotect' ),
							'title' => Linker::titleAttrib( 'ca-unprotect', 'withaccess' ),
							'accesskey' => Linker::accesskey( 'ca-unprotect' )
						);
					}
				}
				if ( $user->isAllowed( 'delete' ) ) {
					$content_actions['delete'] = array(
						'class' => ( $action == 'delete' ) ? 'selected' : false,
						'text' => wfMessage( 'delete' )->plain(),
						'href' => $title->getLocalURL( 'action=delete' ),
						'title' => Linker::titleAttrib( 'ca-delete', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-delete' )
					);
				}
				if ( $title->quickUserCan( 'move' ) ) {
					$moveTitle = SpecialPage::getTitleFor( 'Movepage', $title->getPrefixedDBkey() );
					$content_actions['move'] = array(
						'class' => $title->isSpecial( 'Movepage' ) ? 'selected' : false,
						'text' => wfMessage( 'move' )->plain(),
						'href' => $moveTitle->getLocalURL(),
						'title' => Linker::titleAttrib( 'ca-move', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-move' )
					);
				}

				$whatlinkshereTitle = SpecialPage::getTitleFor( 'Whatlinkshere', $title->getPrefixedDBkey() );
				$content_actions['whatlinkshere'] = array(
					'class' => $title->isSpecial( 'Whatlinkshere' ) ? 'selected' : false,
					'text' => wfMessage( 'whatlinkshere' )->plain(),
					'href' => $whatlinkshereTitle->getLocalURL(),
					'title' => Linker::titleAttrib( 't-whatlinkshere', 'withaccess' ),
					'accesskey' => Linker::accesskey( 't-whatlinkshere' )
				);

			} else {
				// Article doesn't exist or is deleted
				if ( $user->isAllowed( 'delete' ) ) {
					$n = $title->isDeleted();
					if ( $n ) {
						$undelTitle = SpecialPage::getTitleFor( 'Undelete' );
						$content_actions['undelete'] = array(
							'class' => false,
							'text' => wfMessage( 'undelete_short', $n )->parse(),
							'href' => $undelTitle->getLocalURL( 'target=' . urlencode( $title->getPrefixedDBkey() ) )
							#'href' => self::makeSpecialUrl( "Undelete/$this->thispage" )
						);
					}
				}
			}
		} else {
			global $wgQuizID, $wgPictureGameID;

			/* show special page tab */
			if ( $title->isSpecial( 'QuizGameHome' ) && $wgRequest->getVal( 'questionGameAction' ) == 'editItem' ) {
				$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
				$content_actions[$title->getNamespaceKey()] = array(
					'class' => 'selected',
					'text' => wfMessage( 'nstab-special' )->plain(),
					'href' => $quiz->getFullURL( 'questionGameAction=renderPermalink&permalinkID=' . $wgQuizID ),
				);
			} else {
				$content_actions[$title->getNamespaceKey()] = array(
					'class' => 'selected',
					'text' => wfMessage( 'nstab-special' )->plain(),
					'href' => $wgRequest->getRequestURL(), // @bug 2457, 2510
				);
			}

			// "Edit" tab on Special:QuizGameHome for question game administrators
			if (
				$title->isSpecial( 'QuizGameHome' ) &&
				$user->isAllowed( 'quizadmin' ) &&
				$wgRequest->getVal( 'questionGameAction' ) != 'createForm' &&
				!empty( $wgQuizID )
			)
			{
				$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
				$content_actions['edit'] = array(
					'class' => ( $wgRequest->getVal( 'questionGameAction' ) == 'editItem' ) ? 'selected' : false,
					'text' => wfMessage( 'edit' )->plain(),
					'href' => $quiz->getFullURL( 'questionGameAction=editItem&quizGameId=' . $wgQuizID ), // @bug 2457, 2510
				);
			}

			// "Edit" tab on Special:PictureGameHome for picture game administrators
			if (
				$title->isSpecial( 'PictureGameHome' ) &&
				$user->isAllowed( 'picturegameadmin' ) &&
				$wgRequest->getVal( 'picGameAction' ) != 'startCreate' &&
				!empty( $wgPictureGameID )
			)
			{
				$picGame = SpecialPage::getTitleFor( 'PictureGameHome' );
				$content_actions['edit'] = array(
					'class' => ( $wgRequest->getVal( 'picGameAction' ) == 'editPanel' ) ? 'selected' : false,
					'text' => wfMessage( 'edit' )->plain(),
					'href' => $picGame->getFullURL( 'picGameAction=editPanel&id=' . $wgPictureGameID ), // @bug 2457, 2510
				);
			}
		}

		return $content_actions;
	}

	/**
	 * Gets the links for the action bar (edit, talk etc.)
	 *
	 * @return array
	 */
	function getActionBarLinks() {
		$left = array(
			$this->skin->getTitle()->getNamespaceKey(),
			'edit', 'talk', 'viewsource', 'addsection', 'history'
		);
		$actions = $this->buildActionBar();
		$moreLinks = null;

		foreach ( $actions as $action => $value ) {
			if ( in_array( $action, $left ) ) {
				$leftLinks[$action] = $value;
			} else {
				$moreLinks[$action] = $value;
			}
		}

		return array( $leftLinks, $moreLinks );
	}

	/**
	 * Generates the actual action bar - watch/unwatch links for logged-in users,
	 * "More actions" menu that has some other tools (WhatLinksHere special page etc.)
	 *
	 * @return $output HTML for action bar
	 */
	function actionBar() {
		global $wgStylePath;

		$title = $this->skin->getTitle();
		$full_title = Title::makeTitle( $title->getNamespace(), $title->getText() );

		$output = '<div id="action-bar" class="noprint">';
		// Watch/unwatch link for registered users on namespaces that can be
		// watched (i.e. everything but the Special: namespace)
		if ( $this->skin->getUser()->isLoggedIn() && $title->getNamespace() != NS_SPECIAL ) {
			$output .= '<div id="article-controls">
				<img src="' . $wgStylePath . '/Nimbus/nimbus/plus.gif" alt="" />';

			// In 1.16, all we needed was the ID for AJAX page watching to work
			// In 1.18, we need the class *and* the title...w/o the title, the
			// new, jQuery-ified version of the AJAX page watching code dies
			// if the title attribute is not present
			if ( !$title->userIsWatching() ) {
				$output .= Linker::link(
					$full_title,
					wfMessage( 'watch' )->plain(),
					array(
						'id' => 'ca-watch',
						'class' => 'mw-watchlink',
						'title' => Linker::titleAttrib( 'ca-watch', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-watch' )
					),
					array( 'action' => 'watch' )
				);
			} else {
				$output .= Linker::link(
					$full_title,
					wfMessage( 'unwatch' )->plain(),
					array(
						'id' => 'ca-unwatch',
						'class' => 'mw-watchlink',
						'title' => Linker::titleAttrib( 'ca-unwatch', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-unwatch' )
					),
					array( 'action' => 'unwatch' )
				);
			}
			$output .= '</div>';
		}

		$output .= '<div id="article-tabs">';

		list( $leftLinks, $moreLinks ) = $this->getActionBarLinks();

		foreach ( $leftLinks as $key => $val ) {
			// @todo FIXME: this code deserves to burn in hell
			$output .= '<a href="' . htmlspecialchars( $val['href'] ) . '" class="' .
				( ( strpos( $val['class'], 'selected' ) === 0 ) ? 'tab-on' : 'tab-off' ) .
				( strpos( $val['class'], 'new' ) && ( strpos( $val['class'], 'new' ) > 0 ) ? ' tab-new' : '' ) . '"' .
				( isset( $val['title'] ) ? ' title="' . htmlspecialchars( $val['title'] ) . '"' : '' ) .
				( isset( $val['accesskey'] ) ? ' accesskey="' . htmlspecialchars( $val['accesskey'] ) . '"' : '' ) .
				' rel="nofollow">
				<span>' . ucfirst( $val['text'] ) . '</span>
			</a>';
		}

		if ( count( $moreLinks ) > 0 ) {
			$output .= '<div class="more-tab-off" id="more-tab">
				<span>' . wfMessage( 'nimbus-more-actions' )->plain() . '</span>';

			$output .= '<div class="article-more-actions" id="article-more-container" style="display:none">';

			$more_links_count = 1;

			foreach ( $moreLinks as $key => $val ) {
				if ( count( $moreLinks ) == $more_links_count ) {
					$border_fix = ' class="border-fix"';
				} else {
					$border_fix = '';
				}

				$output .= '<a href="' . htmlspecialchars( $val['href'] ) .
					"\"{$border_fix} rel=\"nofollow\">" .
					ucfirst( $val['text'] ) .
				'</a>';

				$more_links_count++;
			}

			$output .= '</div>
			</div>';
		}

		$output .= '<div class="cleared"></div>
			</div>
		</div>';

		return $output;
	}

	/**
	 * Returns the footer for a page
	 *
	 * @return $footer The generated footer, including recent editors
	 */
	function footer() {
		global $wgMemc, $wgUploadPath;

		$titleObj = $this->getSkin()->getTitle();
		$title = Title::makeTitle( $titleObj->getNamespace(), $titleObj->getText() );
		$pageTitleId = $titleObj->getArticleID();
		$main_page = Title::newMainPage();
		$about = Title::newFromText( wfMessage( 'aboutpage' )->inContentLanguage()->text() );
		$special = SpecialPage::getTitleFor( 'Specialpages' );
		$help = SpecialPage::getTitleFor( 'Userlogin', 'signup' );
		$disclaimerPage = Title::newFromText( wfMessage( 'disclaimerpage' )->inContentLanguage()->text() );

		$footerShow = array( NS_MAIN, NS_FILE );
		if ( defined( 'NS_VIDEO' ) ) {
			$footerShow[] = NS_VIDEO;
		}
		$footer = '';

		// Show the list of recent editors and their avatars if the page is in
		// one of the allowed namespaces and it is not the main page
		if (
			in_array( $titleObj->getNamespace(), $footerShow ) &&
			( $pageTitleId != $main_page->getArticleID() )
		)
		{
			$key = wfMemcKey( 'recenteditors', 'list', $pageTitleId );
			$data = $wgMemc->get( $key );
			$editors = array();
			if ( !$data ) {
				wfDebug( __METHOD__ . ": Loading recent editors for page {$pageTitleId} from DB\n" );
				$dbw = wfGetDB( DB_MASTER );
				$res = $dbw->select(
					'revision',
					array( 'DISTINCT rev_user', 'rev_user_text' ),
					array(
						'rev_page' => $pageTitleId,
						'rev_user <> 0',
						"rev_user_text <> 'MediaWiki default'"
					),
					__METHOD__,
					array( 'ORDER BY' => 'rev_user_text ASC', 'LIMIT' => 8 )
				);

				foreach ( $res as $row ) {
					// Prevent blocked users from appearing
					$user = User::newFromId( $row->rev_user );
					if ( !$user->isBlocked() ) {
						$editors[] = array(
							'user_id' => $row->rev_user,
							'user_name' => $row->rev_user_text
						);
					}
				}

				// Cache in memcached for five minutes
				$wgMemc->set( $key, $editors, 60 * 5 );
			} else {
				wfDebug( __METHOD__ . ": Loading recent editors for page {$pageTitleId} from cache\n" );
				$editors = $data;
			}

			$x = 1;
			$per_row = 4;

			if ( count( $editors ) > 0 ) {
				$footer .= '<div id="footer-container" class="noprint">
					<div id="footer-actions">
						<h2>' . wfMessage( 'nimbus-contribute' )->plain() . '</h2>'
							. wfMessage( 'nimbus-pages-can-be-edited' )->parse() .
							Linker::link(
								$title,
								wfMessage( 'editthispage' )->plain(),
								array(
									'class' => 'edit-action',
									'title' => Linker::titleAttrib( 'ca-edit', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-edit' )
								),
								array( 'action' => 'edit' )
							) .
							Linker::link(
								$title->getTalkPage(),
								wfMessage( 'talkpage' )->plain(),
								array(
									'class' => 'discuss-action',
									'title' => Linker::titleAttrib( 'ca-talk', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-talk' )
								)
							) .
							Linker::link(
								$title,
								wfMessage( 'pagehist' )->plain(),
								array(
									'rel' => 'archives',
									'class' => 'page-history-action',
									'title' => Linker::titleAttrib( 'ca-history', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-history' )
								),
								array( 'action' => 'history' )
							);
				$footer .= '</div>';

				// Only load the page editors' avatars if wAvatar class exists and $wgUserLevels is an array
				global $wgUserLevels;
				if ( class_exists( 'wAvatar' ) && is_array( $wgUserLevels ) ) {
					$footer .= '<div id="footer-contributors">
						<h2>' . wfMessage( 'nimbus-recent-contributors' )->plain() . '</h2>'
						. wfMessage( 'nimbus-recent-contributors-info' )->plain() . '<br /><br />';

					foreach ( $editors as $editor ) {
						$avatar = new wAvatar( $editor['user_id'], 'm' );
						$user_title = Title::makeTitle( NS_USER, $editor['user_name'] );

						$footer .= '<a href="' . htmlspecialchars( $user_title->getFullURL() ) . '" rel="nofollow">';
						$footer .= $avatar->getAvatarURL( array(
							'alt' => htmlspecialchars( $editor['user_name'] ),
							'title' => htmlspecialchars( $editor['user_name'] )
						) );
						$footer .= '</a>';

						if ( $x == count( $editors ) || $x != 1 && $x % $per_row == 0 ) {
							$footer .= '<br />';
						}

						$x++;
					}

					$footer .= '</div>';
				}

				$footer .= '</div>';
			}
		}

		$footer .= '<div id="footer-bottom" class="noprint">
		<a href="' . htmlspecialchars( $main_page->getLocalURL() ) . '" rel="nofollow">' . wfMessage( 'mainpage' )->plain() . '</a>
		<a href="' . htmlspecialchars( $about->getLocalURL() ) . '" rel="nofollow">' . wfMessage( 'about' )->parse() . '</a>
		<a href="' . htmlspecialchars( $special->getLocalURL() ) . '" rel="nofollow">' . wfMessage( 'specialpages' )->plain() . '</a>
		<a href="' . htmlspecialchars( $help->getLocalURL() ) . '" rel="nofollow">' . wfMessage( 'help' )->plain() . '</a>
		<a href="' . htmlspecialchars( $disclaimerPage->getLocalURL() ) . '" rel="nofollow">' . wfMessage( 'disclaimers' )->plain() . '</a>';

		// "Advertise" link on the footer, but only if a URL has been specified
		// in the MediaWiki:Nimbus-advertise-url system message
		$adMsg = wfMessage( 'nimbus-advertise-url' )->inContentLanguage();
		if ( !$adMsg->isDisabled() ) {
			$footer .= "\n" . '<a href="' . $adMsg->text() . '" rel="nofollow">' .
				wfMessage( 'nimbus-advertise' )->plain() . '</a>';
		}

		$footer .= "\n\t</div>\n";

		return $footer;
	}

	/**
	 * Cheap ripoff from /skins/Games/Games.skin.php on 2 July 2013 with only
	 * one minor change for Nimbus: the addition of the wrapper div
	 * (.bottom-left-nav-container).
	 */
	function getInterlanguageLinksBox() {
		global $wgContLang, $wgHideInterlanguageLinks, $wgOut;

		$output = '';

		# Language links
		$language_urls = array();

		if ( !$wgHideInterlanguageLinks ) {
			foreach ( $wgOut->getLanguageLinks() as $l ) {
				$tmp = explode( ':', $l, 2 );
				$class = 'interwiki-' . $tmp[0];
				unset( $tmp );
				$nt = Title::newFromText( $l );
				if ( $nt ) {
					$langName = $wgContLang->getLanguageName( $nt->getInterwiki() );
					$language_urls[] = array(
						'href' => $nt->getFullURL(),
						'text' => ( $langName != '' ? $langName : $l ),
						'class' => $class
					);
				}
			}
		}

		if ( count( $language_urls ) ) {
			$output = '<div class="bottom-left-nav-container">';
			$output .= '<h2>' . wfMessage( 'otherlanguages' )->plain() . '</h2>';
			$output .= '<div class="interlanguage-links">' . "\n" . '<ul>' . "\n";
			foreach ( $language_urls as $langlink ) {
				$output .= '<li class="' . htmlspecialchars( $langlink['class'] ) . '">
					<a href="' . htmlspecialchars( $langlink['href'] ) . '">' .
						$langlink['text'] . '</a>
				</li>';
			}
			$output .= "</ul>\n</div></div>";
		}

		return $output;
	}
} // end of class