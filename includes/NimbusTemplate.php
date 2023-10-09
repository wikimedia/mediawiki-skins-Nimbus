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
 * @copyright Copyright © 2008-2023 Aaron Wright, David Pean, Inez Korczyński, Jack Phoenix
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use MediaWiki\MediaWikiServices;
/**
 * Main skin class.
 * @ingroup Skins
 */
class NimbusTemplate extends BaseTemplate {
	/**
	 * @var SkinNimbus
	 */
	public $skin;

	/**
	 * @var array Sidebar navigation menu structure, V3-style, parsed with getNavigationMenu()
	 */
	public $navmenu;

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
		return !in_array( $this->skin->getTitle()->getNamespace(), $nsArray );
	}

	/**
	 * Template filter callback for Nimbus skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgLogo, $wgStylePath;
		global $wgLangToCentralMap;
		global $wgUserLevels;

		$this->skin = $this->data['skin'];

		$user = $this->skin->getUser();
		$services = MediaWikiServices::getInstance();
		$contLang = $services->getContentLanguage();
		$linkRenderer = $services->getLinkRenderer();

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
		$special_pages_link = SpecialPage::getTitleFor( 'Specialpages' );

		$help_link = $this->skin->helpLink();

		$upload_file = SpecialPage::getTitleFor( 'Upload' );
		$what_links_here = SpecialPage::getTitleFor( 'Whatlinkshere' );
		$preferences_link = SpecialPage::getTitleFor( 'Preferences' );
		$watchlist_link = SpecialPage::getTitleFor( 'Watchlist' );

		$more_wikis = $this->buildMoreWikis();
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
			<div class="mw-skin-nimbus-button more-wikis-button"><span><?php echo $this->skin->msg( 'nimbus-more-wikis' )->escaped() ?></span></div>
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
				echo '<div class="visualClear"></div>' . "\n";
			}
			$x++;
		}
		?>
		</div><!-- #more-wikis-menu -->
		<?php } // if $more_wikis ?>
		<div id="wiki-login">
<?php
	if ( $user->isRegistered() ) {
		// By default Echo is not available for anons and making it work for anons is *possible*
		// but requires a lot of hacking
		if (
			ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) &&
			method_exists( MediaWiki\Extension\Notifications\Hooks::class, 'onSkinTemplateNavigationUniversal' )
		) {
			// New*est* Echo (as of October 2023), REL1_39 and newer (post-3a351cfb4fab9fb9d81ecdcb2638f29c843c26be)
			$personal_urls = [];
			// @phan-suppress-next-line PhanUndeclaredStaticMethod Obviously *not* undefined if we're here.
			MediaWiki\Extension\Notifications\Hooks::onSkinTemplateNavigationUniversal( $this->skin, $personal_urls );
			// @phan-suppress-next-line PhanImpossibleCondition Shush, phan. This is a filthy hack, I know.
			if ( isset( $personal_urls['notifications'] ) && $personal_urls['notifications'] ) {
				$personal_urls = $personal_urls['notifications'];
			}
		?>
		<div id="echo" role="log">
			<?php
				foreach ( $personal_urls as $key => $arr ) {
					echo '<li id="pt-' . Sanitizer::escapeIdForAttribute( $key ) . '">';
					$classes = '';
					if ( isset( $arr['link-class'] ) && $arr['link-class'] ) {
						// Newest Echo code (as of October 2023) calls it 'link-class'
						$classes = $arr['link-class'];
					}
					echo Html::element( 'a', [
						'href' => $arr['href'],
						// @phan-suppress-next-line PhanParamSpecial1
						'class' => implode( ' ', $classes ),
						'data-counter-num' => $arr['data']['counter-num'],
						'data-counter-text' => $arr['data']['counter-text'],
					] );
					echo '</li>';
				}
			?>
		</div>
		<?php
		} // "is Echo installed?" test

		echo "\t\t\t" . '<div id="login-message">' .
				$this->skin->msg( 'nimbus-welcome', '<b>' . $user->getName() . '</b>', $user->getName() )->parse() .
			'</div>
			<div id="mw-skin-nimbus-button-container">
				<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $profile_link->getFullURL() ) . '" rel="nofollow"><span>' . $this->skin->msg( 'nimbus-profile' )->escaped() . '</span></a>
				<a class="mw-skin-nimbus-button negative-button" href="' . htmlspecialchars( $logout_link->getFullURL() ) . '"><span>' . $this->skin->msg( 'nimbus-logout' )->escaped() . '</span></a>
			</div>';
	} else {
		echo '<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $register_link->getFullURL() ) . '" rel="nofollow"><span>' . $this->skin->msg( 'nimbus-signup' )->escaped() . '</span></a>
		<a class="mw-skin-nimbus-button positive-button" href="' . htmlspecialchars( $login_link->getFullURL() ) . '" id="nimbusLoginButton"><span>' . $this->skin->msg( 'nimbus-login' )->escaped() . '</span></a>';
	}
?>
		</div><!-- #wiki-login -->
	</header><!-- #header -->
	<div id="site-header" class="noprint">
		<div id="site-logo">
			<a href="<?php echo htmlspecialchars( $main_page_link->getFullURL() ) ?>" title="<?php echo htmlspecialchars( Linker::titleAttrib( 'p-logo', 'withaccess' ), ENT_QUOTES ) ?>" accesskey="<?php echo htmlspecialchars( Linker::accesskey( 'p-logo' ), ENT_QUOTES ) ?>" rel="nofollow">
				<img src="<?php echo $wgLogo ?>" alt="" />
			</a>
		</div>
	</div>
	<aside id="side-bar" class="noprint">
		<div id="navigation">
			<div id="navigation-title"><?php echo $this->skin->msg( 'navigation' )->escaped() ?></div>
			<?php
				$this->navmenu = $this->getNavigationMenu();
				echo $this->printMenu( 0 );
			?>
			<div id="other-links-container">
				<div id="other-links">
				<?php
					// Only show the link to Special:TopUsers if wAvatar class exists and $wgUserLevels is an array
					if ( class_exists( 'wAvatar' ) && is_array( $wgUserLevels ) ) {
						$top_fans_link = SpecialPage::getTitleFor( 'TopUsers' );
						echo '<a href="' . htmlspecialchars( $top_fans_link->getFullURL() ) . '">' . $this->skin->msg( 'topusers' )->escaped() . '</a>';
					}

					echo $linkRenderer->makeLink(
						$recent_changes_link,
						$this->skin->msg( 'recentchanges' )->text(),
						[
							'title' => Linker::titleAttrib( 'n-recentchanges', 'withaccess' ),
							'accesskey' => Linker::accesskey( 'n-recentchanges' )
						]
					) . "\n" .
					'<div class="visualClear"></div>' . "\n";

					if ( $user->isRegistered() ) {
						echo $linkRenderer->makeLink(
							$watchlist_link,
							$this->skin->msg( 'watchlist' )->text(),
							[
								'title' => Linker::titleAttrib( 'pt-watchlist', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-watchlist' )
							]
						) . "\n" .
						$linkRenderer->makeLink(
							$preferences_link,
							$this->skin->msg( 'preferences' )->text(),
							[
								'title' => Linker::titleAttrib( 'pt-preferences', 'withaccess' ),
								'accesskey' => Linker::accesskey( 'pt-preferences' )
							]
						) .
						'<div class="visualClear"></div>' . "\n";
					}

					echo $help_link;
					?>
					<a href="<?php echo htmlspecialchars( $special_pages_link->getFullURL() ) ?>"><?php echo $this->skin->msg( 'specialpages' )->escaped() ?></a>
					<div class="visualClear"></div>
				</div>
			</div>
		</div>
		<div id="search-box">
			<div id="search-title"><?php echo $this->skin->msg( 'search' )->escaped() ?></div>
			<form method="get" action="<?php echo $this->text( 'wgScript' ) ?>" name="search_form" id="searchform">
				<input id="searchInput" type="text" class="search-field" name="search" value="" />
				<input type="submit" class="mw-skin-nimbus-button positive-button search-button" value="<?php echo $this->skin->msg( 'search' )->escaped(); ?>" />
			</form>
			<div class="visualClear"></div>
			<div class="bottom-left-nav">
			<?php
			// Hook point for ShoutWikiAds
			MediaWikiServices::getInstance()->getHookContainer()->run( 'NimbusLeftSide' );

			if ( class_exists( 'RandomGameUnit' ) ) {
				// @note The CSS for this is loaded in SkinNimbus::prepareQuickTemplate();
				// it *cannot* be loaded here!
				echo RandomGameUnit::getRandomGameUnit();
			}

			$dykTemplate = Title::makeTitle( NS_TEMPLATE, 'Didyouknow' );
			if ( $dykTemplate->exists() ) {
			?>
				<div class="bottom-left-nav-container">
					<h2><?php echo $this->skin->msg( 'nimbus-didyouknow' )->escaped() ?></h2>
					<?php echo $this->skin->getOutput()->parseAsInterface( '{{Didyouknow}}' ) ?>
				</div>
			<?php
			}

			echo $this->getInterlanguageLinksBox();

			if ( class_exists( 'RandomImageByCategory' ) ) {
				$randomImage = $this->skin->getOutput()->parseAsInterface(
					'<randomimagebycategory width="200" categories="Featured Image" />',
					false
				);
				echo '<div class="bottom-left-nav-container">
				<h2>' . $this->skin->msg( 'nimbus-featuredimage' )->escaped() . '</h2>' .
				$randomImage . '</div>';
			}

			if ( class_exists( 'RandomFeaturedUser' ) ) {
				echo '<div class="bottom-left-nav-container">
					<h2>' . $this->skin->msg( 'nimbus-featureduser' )->escaped() . '</h2>' .
					$this->get( 'nimbus-randomfeatureduser' ) . '</div>';
			}

			// This is a crude hack (in a way), but it works
			// @see https://phabricator.wikimedia.org/T216851
			if ( class_exists( 'NewsBox' ) ) {
				echo '<div class="bottom-left-nav-container">
				<h2>' . $this->skin->msg( 'newsbox-title' )->escaped() . '</h2>' .
					// @phan-suppress-next-line PhanUndeclaredClassMethod One day phan will understand class_exists...one day.
					NewsBox::getNewsBoxHTML( $this->skin ) . '</div>';
			}
			?>
</div>
		</div>
	</aside>
	<div id="body-container">
		<?php echo $this->actionBar(); echo "\n"; ?>
		<div id="article" data-mw-ve-target-container>
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
	} // end of execute() method

	/**
	 * Parse MediaWiki-style messages called 'v3sidebar' to array of links,
	 * saving hierarchy structure.
	 * Message parsing is limited to first 150 lines only.
	 *
	 * @return array
	 */
	private function getNavigationMenu() {
		$message_key = 'nimbus-sidebar';
		$message = trim( $this->skin->msg( $message_key )->text() );

		if ( $this->skin->msg( $message_key )->isDisabled() ) {
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
				// @phan-suppress-next-line PhanTypeInvalidDimOffset
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
					// @phan-suppress-next-line PhanTypeInvalidDimOffset
					if ( $nodes[$x]['depth'] == $node['depth'] - 1 ) {
						$node['parentIndex'] = $x;
						break;
					}
				}
			}

			$nodes[$i + 1] = $node;
			// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
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
			$msgObj = $this->skin->msg( $line_temp[0] );
			$link = ( $msgObj->isDisabled() ? $line_temp[0] : trim( $msgObj->inContentLanguage()->escaped() ) );
			$textObj = $this->skin->msg( trim( $line_temp[1] ) );
			$line = ( !$textObj->isDisabled() ? $textObj->escaped() : trim( $line_temp[1] ) );
		} else {
			$line = $link = trim( $line_temp[0] );
		}

		// Determine what to show as the human-readable link description
		if ( $this->skin->msg( $line )->isDisabled() ) {
			// It's *not* the name of a MediaWiki message, so display it as-is
			$text = $line;
		} else {
			// Guess what -- it /is/ a MediaWiki message!
			$text = $this->skin->msg( $line )->escaped();
		}

		if ( $link != null ) {
			if ( $this->skin->msg( $line_temp[0] )->isDisabled() ) {
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
		$message = trim( $this->skin->msg( $messageKey )->escaped() );

		if ( $this->skin->msg( $messageKey )->isDisabled() ) {
			return [];
		}

		$lines = array_slice( explode( "\n", $message ), 0, 150 );

		if ( count( $lines ) == 0 ) {
			return [];
		}

		$moreWikis = [];
		foreach ( $lines as $line ) {
			$moreWikis[] = $this->parseItem( $line );
		}

		return $moreWikis;
	}

	/**
	 * Prints the sidebar menu & all necessary JS
	 */
	private function printMenu( int $id, $last_count = '', int $level = 0 ) {
		global $wgStylePath;

		$menu_output = '';
		$output = '';
		$count = 1;

		if ( isset( $this->navmenu[$id]['children'] ) ) {
			$contLang = MediaWikiServices::getInstance()->getContentLanguage();
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
					( !empty( $this->navmenu[$child]['href'] ) ? $this->navmenu[$child]['href'] : '#' ) . '">';

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
			$output .= "<div class=\"sub-menu-container\" id=\"menu{$last_count}\">";
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

		$content_actions = $this->get( 'content_actions' );

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
		if ( ExtensionRegistry::getInstance()->isLoaded( 'VisualEditor' ) ) {
			$left = [
				$this->skin->getTitle()->getNamespaceKey(),
				'edit', 've-edit', 'talk', 'viewsource', 'addsection', 'history'
			];
		} else {
			// Same as above but without 've-edit' in the array
			$left = [
				$this->skin->getTitle()->getNamespaceKey(),
				'edit', 'talk', 'viewsource', 'addsection', 'history'
			];
		}
		$actions = $this->buildActionBar();
		$leftLinks = [];
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
	 * @return string HTML for action bar
	 */
	function actionBar() {
		$skin = $this->skin;
		$title = $skin->getTitle();
		$user = $skin->getUser();
		$full_title = Title::makeTitle( $title->getNamespace(), $title->getText() );
		$services = MediaWikiServices::getInstance();

		$output = '<div id="action-bar" class="noprint">';
		// Watch/unwatch link for registered users on namespaces that can be
		// watched (i.e. everything but the Special: namespace)
		if ( $user->isRegistered() && $title->getNamespace() != NS_SPECIAL ) {
			$output .= '<div id="article-controls">
				<span class="mw-skin-nimbus-watchplus">+</span>';

			// In 1.16, all we needed was the ID for AJAX page watching to work
			// In 1.18, we need the class *and* the title...w/o the title, the
			// new, jQuery-ified version of the AJAX page watching code dies
			// if the title attribute is not present
			if ( !$services->getWatchlistManager()->isWatched( $user, $title ) ) {
				$output .= $services->getLinkRenderer()->makeLink(
					$full_title,
					$skin->msg( 'watch' )->text(),
					[
						'id' => 'ca-watch',
						'class' => 'mw-watchlink',
						'title' => Linker::titleAttrib( 'ca-watch', 'withaccess' ),
						'accesskey' => Linker::accesskey( 'ca-watch' )
					],
					[ 'action' => 'watch' ]
				);
			} else {
				$output .= $services->getLinkRenderer()->makeLink(
					$full_title,
					$skin->msg( 'unwatch' )->text(),
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
			$href = $val['href'] ?? '#';
			$additionalClasses = [];
			if ( isset( $val['class'] ) && $val['class'] ) {
				if ( strpos( $val['class'], 'selected' ) === 0 ) {
					$additionalClasses[] = 'tab-on';
				} else {
					$additionalClasses[] = 'tab-off';
				}
				if ( preg_match( '/new/i', $val['class'] ) ) {
					$additionalClasses[] = 'tab-new';
				}
			}

			$output .= '<a href="' . htmlspecialchars( $href ) . '" class="mw-skin-nimbus-actiontab ' .
					implode( ' ', $additionalClasses ) . '"' .
				( isset( $val['title'] ) ? ' title="' . htmlspecialchars( $val['title'] ) . '"' : '' ) .
				( isset( $val['accesskey'] ) ? ' accesskey="' . htmlspecialchars( $val['accesskey'] ) . '"' : '' ) .
				( isset( $val['id'] ) ? ' id="' . htmlspecialchars( $val['id'] ) . '"' : '' ) .
				' rel="nofollow">
				<span>' . ucfirst( htmlspecialchars( $val['text'], ENT_QUOTES ) ) . '</span>
			</a>';
		}

		if ( count( $moreLinks ) > 0 ) {
			$output .= '<div class="mw-skin-nimbus-actiontab more-tab tab-off" id="more-tab">
				<span>' . $skin->msg( 'nimbus-more-actions' )->escaped() . '</span>';

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
					ucfirst( htmlspecialchars( $val['text'], ENT_QUOTES ) ) .
				'</a>';

				$more_links_count++;
			}

			$output .= '</div>
			</div>';
		}

		$output .= '<div class="visualClear"></div>
			</div>
		</div>';

		return $output;
	}

	/**
	 * Returns the footer for a page
	 *
	 * @return string The generated footer, including recent editors
	 */
	function footer() {
		global $wgUploadPath;

		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$config = $skin->getConfig();
		$pageTitleId = $title->getArticleID();
		$main_page = Title::newMainPage();

		$footer = '';

		$services = MediaWikiServices::getInstance();
		$cache = $services->getMainWANObjectCache();
		$linkRenderer = $services->getLinkRenderer();

		// Show the list of recent editors and their avatars if the page is in
		// one of the allowed namespaces and it is not the main page
		if (
			$config->get( 'NimbusRecentEditors' ) &&
			in_array( $title->getNamespace(), $config->get( 'NimbusRecentEditorsNamespaces' ) ) &&
			( $pageTitleId != $main_page->getArticleID() )
		)
		{
			$key = $cache->makeKey( 'nimbus', 'recenteditors', 'list', $pageTitleId );
			$data = $cache->get( $key );
			$editors = [];
			if ( !$data ) {
				wfDebug( __METHOD__ . ": Loading recent editors for page {$pageTitleId} from DB\n" );
				$dbw = wfGetDB( DB_PRIMARY );

				if ( version_compare( MW_VERSION, '1.39', '<' ) ) {
					$res = $dbw->select(
						[ 'revision_actor_temp', 'revision', 'actor' ],
						[ 'DISTINCT revactor_actor' ],
						[
							'revactor_page' => $pageTitleId,
							'actor_user IS NOT NULL',
							"actor_name <> 'MediaWiki default'"
						],
						__METHOD__,
						[ 'ORDER BY' => 'actor_name ASC', 'LIMIT' => 8 ],
						[
							'actor' => [ 'JOIN', 'actor_id = revactor_actor' ],
							'revision_actor_temp' => [ 'JOIN', 'revactor_rev = rev_id' ]
						]
					);
				} else {
					$res = $dbw->select(
						[ 'revision', 'actor' ],
						[ 'DISTINCT rev_actor' ],
						[
							'rev_page' => $pageTitleId,
							'actor_user IS NOT NULL',
							"actor_name <> 'MediaWiki default'"
						],
						__METHOD__,
						[ 'ORDER BY' => 'actor_name ASC', 'LIMIT' => 8 ],
						[
							'actor' => [ 'JOIN', 'actor_id = rev_actor' ]
						]
					);
				}

				foreach ( $res as $row ) {
					// Prevent blocked users from appearing
					$actorColumnName = ( version_compare( MW_VERSION, '1.39', '<' ) ? 'revactor_actor' : 'rev_actor' );
					$user = User::newFromActorId( $row->$actorColumnName );
					if ( !$user->getBlock() ) {
						$editors[] = [
							'user_id' => $user->getId(),
							'user_name' => $user->getName()
						];
					}
				}

				// Cache for five minutes
				$cache->set( $key, $editors, 60 * 5 );
			} else {
				wfDebug( __METHOD__ . ": Loading recent editors for page {$pageTitleId} from cache\n" );
				$editors = $data;
			}

			$x = 1;
			$per_row = 4;

			if ( count( $editors ) > 0 ) {
				$footer .= '<div id="footer-container" class="noprint">
					<div id="footer-actions">
						<h2>' . $skin->msg( 'nimbus-contribute' )->escaped() . '</h2>'
							. $skin->msg( 'nimbus-pages-can-be-edited' )->parse() .
							$linkRenderer->makeLink(
								$title,
								$skin->msg( 'nimbus-editthispage' )->text(),
								[
									'class' => 'edit-action',
									'title' => Linker::titleAttrib( 'ca-edit', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-edit' )
								],
								[ 'action' => 'edit' ]
							) .
							$linkRenderer->makeLink(
								$title->getTalkPage(),
								$skin->msg( 'nimbus-talkpage' )->text(),
								[
									'class' => 'discuss-action',
									'title' => Linker::titleAttrib( 'ca-talk', 'withaccess' ),
									'accesskey' => Linker::accesskey( 'ca-talk' )
								]
							) .
							$linkRenderer->makeLink(
								$title,
								$skin->msg( 'pagehist' )->text(),
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
						<h2>' . $skin->msg( 'nimbus-recent-contributors' )->escaped() . '</h2>'
						. $skin->msg( 'nimbus-recent-contributors-info' )->escaped() . '<br /><br />';

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
				if ( $link === 'copyright' ) {
					$footer .= '<br />';
				}
			}
		}

		$footer .= "\n\t</footer>\n";

		return $footer;
	}

	/**
	 * Cheap ripoff from /skins/Games/Games.skin.php on 2 July 2013 with only
	 * one minor change for Nimbus: the addition of the wrapper div
	 * (.bottom-left-nav-container).
	 *
	 * @return string HTML suitable for output
	 */
	function getInterlanguageLinksBox() {
		global $wgHideInterlanguageLinks;

		$output = '';

		# Language links
		$language_urls = [];

		if ( !$wgHideInterlanguageLinks ) {
			$services = MediaWikiServices::getInstance();
			$contLang = $services->getContentLanguage();
			$languageNameUtils = $services->getLanguageNameUtils();
			foreach ( $this->skin->getOutput()->getLanguageLinks() as $l ) {
				$tmp = explode( ':', $l, 2 );
				$class = 'interwiki-' . $tmp[0];
				unset( $tmp );
				$nt = Title::newFromText( $l );
				if ( $nt ) {
					$langName = $languageNameUtils->getLanguageName(
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
			$output .= '<h2>' . $this->skin->msg( 'otherlanguages' )->escaped() . '</h2>';
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
