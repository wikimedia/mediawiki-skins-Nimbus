<?php

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @ingroup Skins
 */
class SkinNimbus extends SkinTemplate {
	public $skinname = 'nimbus', $stylename = 'nimbus',
		$template = 'NimbusTemplate', $useHeadElement = true;

	/**
	 * Load the JavaScript required by the menu and whatnot.
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addModules( 'skins.nimbus.menu' );
	}

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Add CSS & JS
		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.monobook.styles',
			'skins.nimbus'
		) );
	}
}