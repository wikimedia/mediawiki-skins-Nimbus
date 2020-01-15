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
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author David Pean <david.pean@gmail.com>
 * @author Inez Korczyński <korczynski@gmail.com>
 * @author Jack Phoenix
 * @copyright Copyright © 2008-2019 Aaron Wright, David Pean, Inez Korczyński, Jack Phoenix
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

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
		$nsArray = [];
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
		return (bool)!in_array( $this->skin->getTitle()->getNamespace(), $nsArray );
	}

	/**
	 * Template filter callback for Nimbus skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgLogo, $wgOut, $wgStylePath;
		global $wgLangToCentralMap;
		global $wgUserLevels;

		$this->skin = $this->data['skin'];

		$user = $this->skin->getUser();
		$contLang = MediaWiki\MediaWikiServices::getInstance()->getContentLanguage();

		// This trick copied over from Monaco.php to allow localized central wiki URLs
		$central_url = !empty( $wgLangToCentralMap[$contLang->getCode()] ) ?
						$wgLangToCentralMap[$contLang->getCode()] :
						'http://www.shoutwiki.com/';

		$register_link = SpecialPage::getTitleFor( 'Userlogin', 'signup' );
		$login_link = SpecialPage::getTitleFor( 'Userlogin' );
		$logout_link = SpecialPage::getTitleFor( 'Userlogout' );
		$profile_link = Title::makeTitle( NS_USER, $user->getName() );
		$main_page_link = Title::newMainPage();
		$recent_changes_link = SpecialPage::getTitleFor( 'Recentchanges' );
		$top_fans_link = SpecialPage::getTitleFor( 'TopUsers' );
		$special_pages_link = SpecialPage::getTitleFor( 'Specialpages' );

		$help_link = $this->skin->helpLink();

		$upload_file = SpecialPage::getTitleFor( 'Upload' );
		$what_links_here = SpecialPage::getTitleFor( 'Whatlinkshere' );
		$preferences_link = SpecialPage::getTitleFor( 'Preferences' );
		$watchlist_link = SpecialPage::getTitleFor( 'Watchlist' );

		$more_wikis = $this->buildMoreWikis();

		$this->html( 'headelement' );
?><div id="container">
	<header id="header" class="noprint">
		<div id="sw-logo">
			<a href="<?php echo $central_url ?>">
				<img src="<?php echo $wgStylePath ?>/Nimbus/nimbus/sw_logo.png" alt="" />
			</a>
			<span id="sw-category">ShoutWiki</span>
		</div>
		<?php if ( $more_wikis ) { ?>
		<div id="sw-more-category">
			<div class="mw-skin-nimbus-button more-wikis-button"><span><?php echo wfMessage( 'nimbus-more-wikis' )->plain() ?></span></div>
		</div>
		<div id="more-wikis-menu" style="display:none;">
		<?php
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
		<?php } // if $more_wikis ?>
		<div id="wiki-login">
<?php
	if ( $user->isLoggedIn() ) {
		echo "\t\t\t" . '<div id="login-message">' .
				wfMessage( 'nimbus-welcome', '<b>' . $user->getName() . '</b>', $user->getName() )->parse() .
			'</div>
			<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $profile_link->getFullURL() ) . '" rel="nofollow"><span>' . wfMessage( 'nimbus-profile' )->plain() . '</span></a>
			<a class="mw-skin-nimbus-button negative-button" href="' . htmlspecialchars( $logout_link->getFullURL() ) . '"><span>' . wfMessage( 'nimbus-logout' )->plain() . '</span></a>';
	} else {
		echo '<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $register_link->getFullURL() ) . '" rel="nofollow"><span>' . wfMessage( 'nimbus-signup' )->plain() . '</span></a>
		<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $login_link->getFullURL() ) . '" id="nimbusLoginButton"><span>' . wfMessage( 'nimbus-login' )->plain() . '</span></a>';
	}
?>
		</div><!-- #wiki-login -->
	</header><!-- #header -->
	<div id="site-header" class="noprint">
		<div id="site-logo">
			<a href="<?php echo htmlspecialchars( $main_page_link->getFullURL() ) ?>" title="<?php echo Linker::titleAttrib( 'p-logo', 'withaccess' ) ?>" accesskey="<?php echo Linker::accesskey( 'p-logo' ) ?>" rel="nofollow">
				<img src="<?php echo $wgLogo ?>" alt="" />
			</a>
		</div>
	</div>
	<aside id="side-bar" class="noprint">
		<div id="navigation">
			<div id="navigation-title"><?php echo wfMessage( 'navigation' )->plain() ?></div>
			<?php
				$this->navmenu_array = [];
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
						[
							'title' => Linker::titleAttrib( 'n-recentchanges', 'withaccess' ),
							'accesskey' => Linker::accesskey( 'n-recentchanges' )
						]
					) . "\n" .
					'<div class="cleared"></div>' . "\n";

					if ( $user->isLoggedIn() ) {
						echo Linker::link(
							$watchlist_link,
							wfMessage( 'watchlist' )->text(),
							[
								'title' => Linker::titleAttrib( 'pt-watchlist', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-watchlist' )
							]
						) . "\n" .
						Linker::link(
							$preferences_link,
							wfMessage( 'preferences' )->text(),
							[
								'title' => Linker::titleAttrib( 'pt-preferences', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-preferences' )
							]
						) .
						'<div class="cleared"></div>' . "\n";
					}

					echo $help_link;
					?>
					<a href="<?php echo htmlspecialchars( $special_pages_link->getFullURL() ) ?>"><?php echo wfMessage( 'specialpages' )->plain() ?></a>
					<div class="cleared"></div>
				</div>
			</div>
		</div>
		<div id="search-box">
			<div id="search-title"><?php echo wfMessage( 'search' )->plain() ?></div>
			<form method="get" action="<?php echo $this->text( 'wgScript' ) ?>" name="search_form" id="searchform">
				<input id="searchInput" type="text" class="search-field" name="search" value="" />
				<input type="submit" class="mw-skin-nimbus-button positive-button search-button" value="<?php echo wfMessage( 'search' ); ?>" />
			</form>
			<div class="cleared"></div>
			<div class="bottom-left-nav">
			<?php
			// Hook point for ShoutWikiAds
			Hooks::run( 'NimbusLeftSide' );

			if ( class_exists( 'RandomGameUnit' ) ) {
				// @note The CSS for this is loaded in SkinNimbus::prepareQuickTemplate();
				// it *cannot* be loaded here!
				echo RandomGameUnit::getRandomGameUnit();
			}

			$dykTemplate = Title::makeTitle( NS_TEMPLATE, 'Didyouknow' );
			if ( $dykTemplate->exists() ) {
			?>
				<div class="bottom-left-nav-container">
					<h2><?php echo wfMessage( 'nimbus-didyouknow' )->plain() ?></h2>
					<?php echo $wgOut->parseAsInterface( '{{Didyouknow}}' ) ?>
				</div>
			<?php
			}

			echo $this->getInterlanguageLinksBox();

			if ( class_exists( 'RandomImageByCategory' ) ) {
				$randomImage = $wgOut->parseAsInterface(
					'<randomimagebycategory width="200" categories="Featured Image" />',
					false
				);
				echo '<div class="bottom-left-nav-container">
				<h2>' . wfMessage( 'nimbus-featuredimage' )->plain() . '</h2>' .
				$randomImage . '</div>';
			}

			if ( class_exists( 'RandomFeaturedUser' ) ) {
				echo '<div class="bottom-left-nav-container">
					<h2>' . wfMessage( 'nimbus-featureduser' )->plain() . '</h2>' .
					$this->get( 'nimbus-randomfeatureduser' ) . '</div>';
			}
			?>
</div>
		</div>
	</aside>
	<div id="body-container">
		<?php echo $this->actionBar(); echo "\n"; ?>
		<div id="article">
			<main id="article-body" class="mw-body-content">
				<?php if ( $this->data['sitenotice'] ) { ?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php } ?>
				<div id="article-text" class="clearfix">
					<?php echo $this->getIndicators(); ?>
					<?php if ( $this->showPageTitle() ) { ?><h1 class="pagetitle"><?php $this->html( 'title' ) ?></h1><?php } ?>
					<p class='subtitle'><?php $this->msg( 'tagline' ) ?></p>
					<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
					<?php if ( $this->data['undelete'] ) { ?><div id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php } ?>
					<?php if ( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php } ?>
					<!-- start content -->
					<?php $this->html( 'bodytext' ) ?>
					<?php $this->html( 'debughtml' ); ?>
					<?php if ( $this->data['catlinks'] ) { $this->html( 'catlinks' ); } ?>
					<!-- end content -->
					<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
				</div>
			</main>
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
			return [];
		}

		$lines = array_slice( explode( "\n", $message ), 0, 150 );

		if ( count( $lines ) == 0 ) {
			return [];
		}

		$nodes = [];
		$nodes[] = [];
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
	 *
	 * @param string $line Line from the sidebar message, such as ** mainpage|mainpage-description
	 * @return array Array containing the 'text' (description) and 'href' (target URL) keys
	 */
	private function parseItem( $line ) {
		$href = false;

		// trim spaces and asterisks from line and then split it to maximum two chunks
		$line_temp = explode( '|', trim( $line, '* ' ), 2 );

		// $line_temp now contains page name or URL as the 0th array element
		// and the link description as the 1st array element
		if ( count( $line_temp ) >= 2 && $line_temp[1] != '' ) {
			$msgObj = wfMessage( $line_temp[0] );
			$link = ( $msgObj->isDisabled() ? $line_temp[0] : trim( $msgObj->inContentLanguage()->text() ) );
			$textObj = wfMessage( trim( $line_temp[1] ) );
			$line = ( !$textObj->isDisabled() ? $textObj->text() : trim( $line_temp[1] ) );
		} else {
			$line = $link = trim( $line_temp[0] );
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

		return [
			'text' => $text,
			'href' => $href
		];
	}

	/**
	 * Generate and return "More Wikis" menu, showing links to related wikis.
	 *
	 * @return array "More Wikis" menu
	 */
	private function buildMoreWikis() {
		$messageKey = 'morewikis';
		$message = trim( wfMessage( $messageKey )->text() );

		if ( wfMessage( $messageKey )->isDisabled() ) {
			return [];
		}

		$lines = array_slice( explode( "\n", $message ), 0, 150 );

		if ( count( $lines ) == 0 ) {
			return [];
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
		global $wgStylePath;

		$menu_output = '';
		$output = '';
		$count = 1;

		if ( isset( $this->navmenu[$id]['children'] ) ) {
			$contLang = MediaWiki\MediaWikiServices::getInstance()->getContentLanguage();
			if ( $level ) {
				$menu_output .= '<div class="sub-menu" id="sub-menu' . $last_count . '" style="display:none;">';
			}
			foreach ( $this->navmenu[$id]['children'] as $child ) {
				$menu_output .= "\n\t\t\t\t" . '<div class="' . ( $level ? 'sub-' : '' ) . 'menu-item' .
					( ( $count == sizeof( $this->navmenu[$id]['children'] ) ) ? ' border-fix' : '' ) .
					'" id="' . ( $level ? 'sub-' : '' ) . 'menu-item' .
						( $level ? $last_count . '_' : '_' ) . $count . '">';
				$menu_output .= "\n\t\t\t\t\t" . '<a id="' . ( $level ? 'a-sub-' : 'a-' ) . 'menu-item' .
					( $level ? $last_count . '_' : '_' ) . $count . '" href="' .
					( !empty( $this->navmenu[$child]['href'] ) ? htmlspecialchars( $this->navmenu[$child]['href'] ) : '#' ) . '">';

				$menu_output .= $this->navmenu[$child]['text'];
				// If a menu item has submenus, show an arrow so that the user
				// knows that there are submenus available
				if (
					isset( $this->navmenu[$child]['children'] ) &&
					sizeof( $this->navmenu[$child]['children'] )
				)
				{
					$menu_output .= '<img src="' . $wgStylePath . '/Nimbus/nimbus/right_arrow' .
						( $contLang->isRTL() ? '_rtl' : '' ) .
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
		}

		if ( $menu_output != '' ) {
			$output .= "<div id=\"menu{$last_count}\">";
			$output .= $menu_output;
			$output .= "</div>\n";
		}

		return $output;
	}

	/**
	 * Builds the content for the top navigation tabs (edit, history, etc.).
	 *
	 * @return array
	 */
	function buildActionBar() {
		$skin = $this->skin;
		$request = $skin->getRequest();
		$user = $skin->getUser();
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
		$title = $skin->getTitle();

		$content_actions = [];

		// Oh hey, this one's protected...
		$r = new ReflectionMethod( $skin, 'buildContentNavigationUrls' );
		$r->setAccessible( true );
		$content_navigation = $r->invoke( $skin );
		// In an ideal world this would Just Work(TM):
		// $content_actions = $this->buildContentActionUrls( $content_navigation );
		// But of course it *doesn't* because that method is literally _the_ only
		// private one in SkinTemplate...let's change that:
		$r = new ReflectionMethod( $skin, 'buildContentActionUrls' );
		$r->setAccessible( true );
		$content_actions = $r->invoke( $skin, $content_navigation );

		if ( !$title->inNamespace( NS_SPECIAL ) ) {
			// "What links here" isn't a part of default core content actions so we need
			// to add it there ourselves for all non-NS_SPECIAL namespaces
			$whatlinkshereTitle = SpecialPage::getTitleFor( 'Whatlinkshere', $title->getPrefixedDBkey() );
			$content_actions['whatlinkshere'] = [
				'class' => $title->isSpecial( 'Whatlinkshere' ) ? 'selected' : false,
				'text' => $skin->msg( 'whatlinkshere' )->plain(),
				'href' => $whatlinkshereTitle->getLocalURL(),
				'title' => Linker::titleAttrib( 't-whatlinkshere', 'withaccess' ),
				'accesskey' => Linker::accesskey( 't-whatlinkshere' )
			];

			// We don't need the watch (or unwatch) link in the "More actions" menu
			// as that link is already prominently exposed elsewhere in the UI
			if ( isset( $content_actions['watch'] ) ) {
				unset( $content_actions['watch'] );
			}
			if ( isset( $content_actions['unwatch'] ) ) {
				unset( $content_actions['unwatch'] );
			}
		} else {
			global $wgQuizID, $wgPictureGameID;

			/* show special page tab */
			if ( $title->isSpecial( 'QuizGameHome' ) && $request->getVal( 'questionGameAction' ) == 'editItem' ) {
				$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
				$content_actions[$title->getNamespaceKey()] = [
					'class' => 'selected',
					'text' => $skin->msg( 'nstab-special' )->plain(),
					'href' => $quiz->getFullURL( 'questionGameAction=renderPermalink&permalinkID=' . $wgQuizID ),
				];
			} else {
				$content_actions[$title->getNamespaceKey()] = [
					'class' => 'selected',
					'text' => $skin->msg( 'nstab-special' )->plain(),
					'href' => $request->getRequestURL(), // @bug 2457, 2510
				];
			}

			// "Edit" tab on Special:QuizGameHome for question game administrators
			if (
				$title->isSpecial( 'QuizGameHome' ) &&
				$user->isAllowed( 'quizadmin' ) &&
				$request->getVal( 'questionGameAction' ) != 'createForm' &&
				!empty( $wgQuizID )
			)
			{
				$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
				$content_actions['edit'] = [
					'class' => ( $request->getVal( 'questionGameAction' ) == 'editItem' ) ? 'selected' : false,
					'text' => $skin->msg( 'edit' )->plain(),
					'href' => $quiz->getFullURL( 'questionGameAction=editItem&quizGameId=' . $wgQuizID ), // @bug 2457, 2510
				];
			}

			// "Edit" tab on Special:PictureGameHome for picture game administrators
			if (
				$title->isSpecial( 'PictureGameHome' ) &&
				$user->isAllowed( 'picturegameadmin' ) &&
				$request->getVal( 'picGameAction' ) != 'startCreate' &&
				!empty( $wgPictureGameID )
			)
			{
				$picGame = SpecialPage::getTitleFor( 'PictureGameHome' );
				$content_actions['edit'] = [
					'class' => ( $request->getVal( 'picGameAction' ) == 'editPanel' ) ? 'selected' : false,
					'text' => $skin->msg( 'edit' )->plain(),
					'href' => $picGame->getFullURL( 'picGameAction=editPanel&id=' . $wgPictureGameID ), // @bug 2457, 2510
				];
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
		$left = [
			$this->skin->getTitle()->getNamespaceKey(),
			'edit', 'talk', 'viewsource', 'addsection', 'history'
		];
		$actions = $this->buildActionBar();
		$moreLinks = [];

		foreach ( $actions as $action => $value ) {
			if ( in_array( $action, $left ) ) {
				$leftLinks[$action] = $value;
			} else {
				$moreLinks[$action] = $value;
			}
		}

		return [ $leftLinks, $moreLinks ];
	}

	/**
	 * Generates the actual action bar - watch/unwatch links for logged-in users,
	 * "More actions" menu that has some other tools (WhatLinksHere special page etc.)
	 *
	 * @return $output HTML for action bar
	 */
	function actionBar() {
		$title = $this->skin->getTitle();
		$full_title = Title::makeTitle( $title->getNamespace(), $title->getText() );

		$output = '<div id="action-bar" class="noprint">';
		// Watch/unwatch link for registered users on namespaces that can be
		// watched (i.e. everything but the Special: namespace)
		if ( $this->skin->getUser()->isLoggedIn() && $title->getNamespace() != NS_SPECIAL ) {
			$output .= '<div id="article-controls">
				<span class="mw-skin-nimbus-watchplus">+</span>';

			// In 1.16, all we needed was the ID for AJAX page watching to work
			// In 1.18, we need the class *and* the title...w/o the title, the
			// new, jQuery-ified version of the AJAX page watching code dies
			// if the title attribute is not present
			if ( !$this->skin->getUser()->isWatched( $title ) ) {
				$output .= Linker::link(
					$full_title,
					wfMessage( 'watch' )->plain(),
					[
						'id' => 'ca-watch',
						'class' => 'mw-watchlink',
						'title' => Linker::titleAttrib( 'ca-watch', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-watch' )
					],
					[ 'action' => 'watch' ]
				);
			} else {
				$output .= Linker::link(
					$full_title,
					wfMessage( 'unwatch' )->plain(),
					[
						'id' => 'ca-unwatch',
						'class' => 'mw-watchlink',
						'title' => Linker::titleAttrib( 'ca-unwatch', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-unwatch' )
					],
					[ 'action' => 'unwatch' ]
				);
			}
			$output .= '</div>';
		}

		$output .= '<div id="article-tabs">';

		list( $leftLinks, $moreLinks ) = $this->getActionBarLinks();

		foreach ( $leftLinks as $key => $val ) {
			// @todo FIXME: this code deserves to burn in hell
			$output .= '<a href="' . htmlspecialchars( $val['href'] ) . '" class="mw-skin-nimbus-actiontab ' .
				( ( strpos( $val['class'], 'selected' ) === 0 ) ? 'tab-on' : 'tab-off' ) .
				( preg_match( '/new/i', $val['class'] ) ? ' tab-new' : '' ) . '"' .
				( isset( $val['title'] ) ? ' title="' . htmlspecialchars( $val['title'] ) . '"' : '' ) .
				( isset( $val['accesskey'] ) ? ' accesskey="' . htmlspecialchars( $val['accesskey'] ) . '"' : '' ) .
				( isset( $val['id'] ) ? ' id="' . htmlspecialchars( $val['id'] ) . '"' : '' ) .
				' rel="nofollow">
				<span>' . ucfirst( $val['text'] ) . '</span>
			</a>';
		}

		if ( count( $moreLinks ) > 0 ) {
			$output .= '<div class="mw-skin-nimbus-actiontab more-tab tab-off" id="more-tab">
				<span>' . wfMessage( 'nimbus-more-actions' )->plain() . '</span>';

			$output .= '<div class="article-more-actions" id="article-more-container" style="display:none">';

			$more_links_count = 1;

			foreach ( $moreLinks as $key => $val ) {
				if ( count( $moreLinks ) == $more_links_count ) {
					$border_fix = ' class="border-fix"';
				} else {
					$border_fix = '';
				}

				$output .= '<a href="' . htmlspecialchars( $val['href'] ) . '"' .
					( isset( $val['id'] ) ? ' id="' . htmlspecialchars( $val['id'] ) . '"' : '' ) .
					"{$border_fix} rel=\"nofollow\">" .
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
		global $wgActorTableSchemaMigrationStage, $wgMemc, $wgUploadPath;

		$titleObj = $this->getSkin()->getTitle();
		$title = Title::makeTitle( $titleObj->getNamespace(), $titleObj->getText() );
		$pageTitleId = $titleObj->getArticleID();
		$main_page = Title::newMainPage();

		$footerShow = [ NS_MAIN, NS_FILE ];
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
			$key = $wgMemc->makeKey( 'recenteditors', 'list', $pageTitleId );
			$data = $wgMemc->get( $key );
			$editors = [];
			if ( !$data ) {
				wfDebug( __METHOD__ . ": Loading recent editors for page {$pageTitleId} from DB\n" );
				$dbw = wfGetDB( DB_MASTER );

				// This code based on the core /includes/api/ApiQueryContributors.php code
				$revQuery = MediaWiki\MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();

				// For SCHEMA_COMPAT_READ_NEW, target indexes on the
				// revision_actor_temp table, otherwise on the revision table.
				$pageField = ( $wgActorTableSchemaMigrationStage & SCHEMA_COMPAT_READ_NEW )
					? 'revactor_page' : 'rev_page';
				$idField = ( $wgActorTableSchemaMigrationStage & SCHEMA_COMPAT_READ_NEW )
					? 'revactor_actor' : $revQuery['fields']['rev_user'];
				$userNameField = $revQuery['fields']['rev_user_text'];

				$res = $dbw->select(
					$revQuery['tables'],
					[ "DISTINCT $idField" ],
					[
						$pageField => $pageTitleId,
						ActorMigration::newMigration()->isNotAnon( $revQuery['fields']['rev_user'] ),
						$userNameField . " <> 'MediaWiki default'"
					],
					__METHOD__,
					[ 'ORDER BY' => $userNameField . ' ASC', 'LIMIT' => 8 ],
					$revQuery['joins']
				);

				foreach ( $res as $row ) {
					// Prevent blocked users from appearing
					$user = ( $wgActorTableSchemaMigrationStage & SCHEMA_COMPAT_READ_NEW )
						? User::newFromActorId( $row->$idField ) : User::newFromId( $row->rev_user );
					if ( !$user->isBlocked() ) {
						$editors[] = [
							'user_id' => $user->getId(),
							'user_name' => $user->getName()
						];
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
								wfMessage( 'nimbus-editthispage' )->plain(),
								[
									'class' => 'edit-action',
									'title' => Linker::titleAttrib( 'ca-edit', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-edit' )
								],
								[ 'action' => 'edit' ]
							) .
							Linker::link(
								$title->getTalkPage(),
								wfMessage( 'talkpage' )->plain(),
								[
									'class' => 'discuss-action',
									'title' => Linker::titleAttrib( 'ca-talk', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-talk' )
								]
							) .
							Linker::link(
								$title,
								wfMessage( 'pagehist' )->plain(),
								[
									'rel' => 'archives',
									'class' => 'page-history-action',
									'title' => Linker::titleAttrib( 'ca-history', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-history' )
								],
								[ 'action' => 'history' ]
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
						$footer .= $avatar->getAvatarURL( [
							'alt' => htmlspecialchars( $editor['user_name'] ),
							'title' => htmlspecialchars( $editor['user_name'] )
						] );
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

		$footer .= '<footer id="footer-bottom" class="noprint">';
		foreach ( $this->getFooterLinks() as $category => $links ) {
			foreach ( $links as $link ) {
				$footer .= $this->get( $link );
				$footer .= "\n";
			}
		}

		$footer .= "\n\t</footer>\n";

		return $footer;
	}

	/**
	 * Cheap ripoff from /skins/Games/Games.skin.php on 2 July 2013 with only
	 * one minor change for Nimbus: the addition of the wrapper div
	 * (.bottom-left-nav-container).
	 */
	function getInterlanguageLinksBox() {
		global $wgHideInterlanguageLinks, $wgOut;

		$output = '';

		# Language links
		$language_urls = [];

		if ( !$wgHideInterlanguageLinks ) {
			$contLang = MediaWiki\MediaWikiServices::getInstance()->getContentLanguage();
			foreach ( $wgOut->getLanguageLinks() as $l ) {
				$tmp = explode( ':', $l, 2 );
				$class = 'interwiki-' . $tmp[0];
				unset( $tmp );
				$nt = Title::newFromText( $l );
				if ( $nt ) {
					$langName = Language::fetchLanguageName(
						$nt->getInterwiki(),
						$contLang->getCode()
					);
					$language_urls[] = [
						'href' => $nt->getFullURL(),
						'text' => ( $langName != '' ? $langName : $l ),
						'class' => $class
					];
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
