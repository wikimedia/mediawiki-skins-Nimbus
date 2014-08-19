<?php
/**
 * Nimbus skin
 *
 * @file
 * @ingroup Skins
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author David Pean <david.pean@gmail.com>
 * @author Inez Korczyński <korczynski@gmail.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @copyright Copyright © 2008-2014 Aaron Wright, David Pean, Inez Korczyński, Jack Phoenix
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @date 19 August 2014
 *
 * To install place the Nimbus folder (the folder containing this file!) into
 * skins/ and add this line to your wiki's LocalSettings.php:
 * require_once("$IP/skins/Nimbus/Nimbus.php");
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not a valid entry point.' );
}

// Skin credits that will show up on Special:Version
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'Nimbus',
	'version' => '3.0',
	'author' => array( 'Aaron Wright', 'David Pean', 'Inez Korczyński', 'Jack Phoenix' ),
	'descriptionmsg' => 'nimbus-desc',
	'url' => 'https://www.mediawiki.org/wiki/Skin:Nimbus',
);

// The first instance must be strtolower()ed so that useskin=nimbus works and
// so that it does *not* force an initial capital (i.e. we do NOT want
// useskin=Nimbus) and the second instance is used to determine the name of
// *this* file.
$wgValidSkinNames['nimbus'] = 'Nimbus';

// Autoload the skin class, set up i18n, set up CSS & JS (via ResourceLoader)
$wgAutoloadClasses['SkinNimbus'] = __DIR__ . '/Nimbus.skin.php';
$wgMessagesDirs['MonoBook'] = __DIR__ . '/i18n';
$wgResourceModules['skins.nimbus'] = array(
	'styles' => array( 'skins/Nimbus/nimbus/Nimbus.css' => array( 'media' => 'screen' ) ),
	'scripts' => 'skins/Nimbus/nimbus/Menu.js',
	'position' => 'top'
);