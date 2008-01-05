<?php
/**
 * @author Jean-Lou Dupont
 * @package ImageLink
 * @version 1.2.0
 * @Id $Id: ImageLink.php 742 2007-12-10 20:49:40Z jeanlou.dupont $
 */
//<source lang=php>
$wgExtensionCredits['other'][] = array( 
	'name'        	=> 'ImageLink', 
	'version'     	=> '1.2.0',
	'author'      	=> 'Jean-Lou Dupont', 
	'description' 	=> 'Provides a clickable image link',
	'url' 			=> 'http://mediawiki.org/wiki/Extension:ImageLink',			
);
if (class_exists('StubManager'))
	StubManager::createStub(	'ImageLink', 
								dirname(__FILE__).'/ImageLink.body.php',
								null,						// i18n file			
								array('ParserAfterTidy'),	// hooks
								false, 						// no need for logging support
								null,						// tags
								array('imagelink', 'imagelink_raw' ),	// parser Functions
								null
							 );
else
	echo '[[Extension:ImageLink]] requires [[Extension:StubManager]].';							
//</source>