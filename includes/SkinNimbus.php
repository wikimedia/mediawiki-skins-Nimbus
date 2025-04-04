<?php

use MediaWiki\Html\Html;
use MediaWiki\Linker\Linker;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @ingroup Skins
 */
class SkinNimbus extends SkinTemplate {
	public $skinname = 'nimbus', $stylename = 'nimbus',
		$template = 'NimbusTemplate';

	/**
	 * Load the JavaScript required by the menu and whatnot.
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addModules( 'skins.nimbus.menu' );
	}

	/**
	 * Sporked from MW core includes/skin/Skin.php as of MW 1.39.
	 * The only real difference compared to the footerLink method there
	 * is that this also integrates the guts of the footerLinkTitle() method.
	 *
	 * @see https://phabricator.wikimedia.org/T326109
	 *
	 * Given a pair of message keys for link and text label,
	 * return an HTML link for use in the footer.
	 *
	 * @param string $desc The i18n message key for the link text.
	 * 		The content of this message will be the visible text label.
	 * 		If this is set to nonexisting message key or the message is
	 * 		disabled, the link will not be generated, empty string will
	 * 		be returned in the stead.
	 * @param string $page The i18n message key for the page to link to.
	 * 		The content of this message will be the destination page for
	 * 		the footer link. Given a message key 'Privacypage' with content
	 * 		'Project:Privacy policy', the link will lead to the wiki page with
	 * 		the title of the content.
	 *
	 * @return string HTML anchor
	 */
	public function footerLink( $desc, $page ) {
		// If the link description has been disabled in the default language,
		if ( $this->msg( $desc )->inContentLanguage()->isDisabled() ) {
			// then it is disabled, for all languages.
			return '';
		}

		// Otherwise, we display the link for the user, described in their
		// language (which may or may not be the same as the default language),
		// but we make the link target be the one site-wide page.
		$title = Title::newFromText( $this->msg( $page )->inContentLanguage()->text() );
		if ( !$title ) {
			return '';
		}

		// Similar to Skin::addToSidebarPlain
		// Optimization: Avoid LinkRenderer here as it requires extra DB info
		// to add unneeded classes even for makeKnownLink (T313462).
		return Html::element( 'a',
			[ 'href' => $title->fixSpecialName()->getLinkURL() ],
			$this->msg( $desc )->text()
		);
	}

	/**
	 * Gets the link to the wiki's about page.
	 *
	 * @return string HTML
	 */
	function aboutLink() {
		// Core uses 'aboutsite' here but we want just 'about'
		return $this->footerLink( 'about', 'aboutpage' );
	}

	/**
	 * Gets the link to [[Special:SpecialPages]].
	 *
	 * @return string HTML
	 */
	function specialPagesLink() {
		return $this->footerLink( 'nimbus-specialpages', 'nimbus-specialpages-url' );
	}

	/**
	 * Gets the link to the wiki's help page.
	 *
	 * @return string HTML
	 */
	function helpLink() {
		// By default it's an (external) URL, hence not a valid Title.
		// But because MediaWiki is by nature very customizable, someone
		// might've changed it to point to a local page. Tricky!
		// @see https://phabricator.wikimedia.org/T155319
		$services = MediaWikiServices::getInstance();
		$urlProtocols = $services->getUrlUtils()->validProtocols();
		$helpPage = $this->msg( 'helppage' )->inContentLanguage()->plain();
		if ( preg_match( '/^(?:' . $urlProtocols . ')/', $helpPage ) ) {
			$helpLink = Linker::makeExternalLink(
				$helpPage,
				$this->msg( 'help' )->plain()
			);
		} else {
			$helpLink = $services->getLinkRenderer()->makeKnownLink(
				Title::newFromText( $helpPage ),
				$this->msg( 'help' )->text()
			);
		}
		return $helpLink;
		// This doesn't work with the default value of 'helppage' which points to an external URL
		// return $this->footerLink( 'help', 'helppage' );
	}

	/**
	 * Gets the link to the wiki's "advertise on this wiki" page, but only if
	 * the link has been configured to be visible (=[[MediaWiki:Nimbus-advertise-url]]
	 * exists and has content).
	 *
	 * @return string HTML
	 */
	function advertiseLink() {
		$link = '';
		$adMsg = $this->msg( 'nimbus-advertise-url' )->inContentLanguage();
		if ( !$adMsg->isDisabled() ) {
			$url = Sanitizer::validateAttributes( [ 'href' => $adMsg->text() ], [ 'href' => true ] )['href'] ?? false;
			if ( $url ) {
				$link = '<a href="' . htmlspecialchars( $url, ENT_QUOTES ) . '" rel="nofollow">' .
					$this->msg( 'nimbus-advertise' )->escaped() . '</a>';
			}
		}
		return $link;
	}

	/**
	 * Initialize various variables and generate the template
	 *
	 * @see https://phabricator.wikimedia.org/T198109
	 * @return QuickTemplate The template to be executed by outputPage
	 */
	protected function prepareQuickTemplate() {
		$parserOutput = $this->getRandomFeaturedUser();
		$po = '';
		if ( $parserOutput !== null ) {
			$po = $parserOutput->getText();
			// This MUST be done before the parent::prepareQuickTemplate() call!
			$this->getOutput()->addModuleStyles( $parserOutput->getModuleStyles() );
		}

		if ( class_exists( 'RandomGameUnit' ) ) {
			$this->getOutput()->addModuleStyles( 'ext.RandomGameUnit.css' );
		}

		$tpl = parent::prepareQuickTemplate();
		$originalFooterLinks = $tpl->get( 'footerlinks' );

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$tpl->set( 'mainpage', $linkRenderer->makeKnownLink(
			Title::newMainPage(),
			$this->msg( 'mainpage' )->text()
		) );
		$tpl->set( 'specialpages', $this->specialPagesLink() );
		$tpl->set( 'help', $this->helpLink() );
		$tpl->set( 'advertise', $this->advertiseLink() );

		// Can't lazily just overwrite 'footerlinks' because that way we'd also end up
		// overwriting any and all extension-added links as well!
		$originalFooterLinks = $tpl->get( 'footerlinks' );

		// Filter out "last modified on <date>" from footer items, we render that differently
		// and only for certain (NS_MAIN etc.) pages
		$originalFooterLinks['info'] = array_diff( $originalFooterLinks['info'] ?? [], [ 'lastmod' ] );

		// Filter out duplicate entries, we don't want two "Privacy Policy" or
		// "About" (etc.) links in the footer
		$a = array_diff( $originalFooterLinks['places'] ?? [], [ 'privacy', 'about', 'disclaimer' ] );
		$originalFooterLinks['places'] = array_merge(
			[
				'mainpage',
				'about',
				'specialpages',
				'help',
				'disclaimer',
				'advertise',
			],
			$a
		);

		$tpl->set( 'footerlinks', [
			'info' => $originalFooterLinks['info'],
			'places' => $originalFooterLinks['places']
		] );

		$tpl->set( 'nimbus-randomfeatureduser', $po );
		return $tpl;
	}

	/**
	 * Get the parsed version of the <randomfeatureduser period="weekly" /> tag
	 * (or nothing if we're running an ancient version of SocialProfile or not
	 * running SocialProfile at all).
	 *
	 * @see https://phabricator.wikimedia.org/T198109
	 * @return ParserOutput|null
	 */
	function getRandomFeaturedUser() {
		if ( class_exists( 'RandomFeaturedUser' ) ) {
			return $this->parseRandomFeaturedUserTag(
				'<randomfeatureduser period="weekly" />'
			);
		} else {
			return null;
		}
	}

	/**
	 * Based on REL1_31 OutputPage#parse with changes to make it return a
	 * ParserOutput and not a string.
	 *
	 * @see https://phabricator.wikimedia.org/T198109
	 *
	 * Parse wikitext and return ParserOutput.
	 *
	 * @param string $text
	 * @throws MWException
	 * @return ParserOutput
	 */
	public function parseRandomFeaturedUserTag( $text ) {
		$out = $this->getOutput();
		if ( is_null( $out->getTitle() ) ) {
			throw new MWException( 'Empty $mTitle in ' . __METHOD__ );
		}

		$popts = $out->parserOptions();

		$parser = MediaWikiServices::getInstance()->getParserFactory()->getInstance();
		$parserOutput = $parser->parse(
			$text, $out->getTitle(), $popts,
			false, true, $out->getRevisionId()
		);

		return $parserOutput;
	}
}