<?php

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
	 * Initialize various variables and generate the template
	 *
	 * @see https://phabricator.wikimedia.org/T198109
	 * @return NimbusTemplate The template to be executed by outputPage
	 */
	protected function prepareQuickTemplate() {
		$parserOutput = $this->getRandomFeaturedUser();
		$po = '';
		if ( $parserOutput !== null ) {
			$po = $parserOutput->getText();
			// This MUST be done before the parent::prepareQuickTemplate() call!
			$this->getOutput()->addModuleStyles( $parserOutput->getModuleStyles() );
		}
		$tpl = parent::prepareQuickTemplate();
		$tpl->set( 'nimbus-randomfeatureduser', $po );
		return $tpl;
	}

	/**
	 * Get the parsed version of the <randomfeatureduser period="weekly" /> tag
	 * (or nothing if we're running an ancient version of SocialProfile or not
	 * running SocialProfile at all).
	 *
	 * @see https://phabricator.wikimedia.org/T198109
	 * @return string|null
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
		global $wgParser;

		$out = $this->getOutput();
		if ( is_null( $out->getTitle() ) ) {
			throw new MWException( 'Empty $mTitle in ' . __METHOD__ );
		}

		$popts = $out->parserOptions();

		$parserOutput = $wgParser->getFreshParser()->parse(
			$text, $out->getTitle(), $popts,
			false, true, $out->getRevisionId()
		);

		return $parserOutput;
	}

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Add CSS
		$out->addModuleStyles( [
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.monobook.styles',
			'skins.nimbus'
		] );
	}
}